<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
 *  
 *  Permission is hereby granted, free of charge, to any person obtaining a copy
 *  of this software and associated documentation files (the "Software"), to deal
 *  in the Software without restriction, including without limitation the rights
 *  to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 *  copies of the Software, and to permit persons to whom the Software is furnished
 *  to do so, subject to the following conditions:
 *  
 *  The above copyright notice and this permission notice shall be included in all
 *  copies or substantial portions of the Software.
 *  
 *  THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 *  IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 *  FITNESS FOR A PARTICULAR PURPOSE AND NON INFRINGEMENT. IN NO EVENT SHALL THE
 *  AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 *  LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 *  OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 *  THE SOFTWARE.
 */

namespace BaksDev\Users\Profile\UserProfile\Repository\CurrentUserProfile;

use BaksDev\Auth\Email\Type\Status\AccountStatus;
use BaksDev\Auth\Email\Type\Status\AccountStatusEnum;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Users\Profile\TypeProfile\Entity as TypeProfileEntity;
use BaksDev\Users\Profile\UserProfile\Entity as UserProfileEntity;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatus;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatusEnum;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CurrentUserProfile implements CurrentUserProfileInterface
{
	private Connection $connection;
	
	private AccountStatus $status;
	
	private UserProfileStatus $statusProfile;
	
	private ParameterBagInterface $parameter;
	
	private TranslatorInterface $translator;
	
	private EntityManagerInterface $entityManager;
	
	
	public function __construct(
		TranslatorInterface $translator,
		ParameterBagInterface $parameter,
		EntityManagerInterface $entityManager,
	)
	{
		$this->connection = $entityManager->getConnection();
		$this->status = new AccountStatus(AccountStatusEnum::ACTIVE);
		$this->statusProfile = new UserProfileStatus(UserProfileStatusEnum::ACTIVE);
		$this->parameter = $parameter;
		$this->translator = $translator;
		$this->entityManager = $entityManager;
	}
	
	
	/** Активный профиль пользователя
	 *
	 * Возвращает массив с ключами:
	 *
	 * profile_url - адрес персональной страницы <br>
	 * profile_username - username провфиля <br>
	 * profile_type - Тип провфиля <br>
	 * profile_avatar_name - название файла аватарки профиля <br>
	 * profile_avatar_dir - директория файла аватарки <br>
	 * profile_avatar_ext - расширение файла <br>
	 * profile_avatar_cdn - фгаг загрузки файла на CDN
	 */
	
	public function fetchProfileAssociative(UserUid $user) : bool|array
	{
		
		$qb = $this->connection->createQueryBuilder();
		
		/** ЛОКАЛЬ */
		$locale = new Locale($this->translator->getLocale());
		$qb->setParameter('local', $locale, Locale::TYPE);
		
		/* Пользователь */
		$qb->from(User::TABLE, 'users');
		
		/* PROFILE */
		$qb->addSelect('profile_info.url AS profile_url');  /* URL профиля */
		$qb->addSelect('profile_info.discount AS profile_discount');  /* URL профиля */
		$qb->join(
			'users',
			UserProfileEntity\Info\UserProfileInfo::TABLE,
			'profile_info',
			'profile_info.user_id = users.user_id AND profile_info.status = :profile_status AND profile_info.active = true'
		);
		
		$qb->setParameter('profile_status', $this->statusProfile, UserProfileStatus::TYPE);
		
		$qb->addSelect('profile.id AS user_profile_id'); /* ID профиля */
		$qb->addSelect('profile.event AS user_profile_event'); /* ID события профиля */
		$qb->join(
			'profile_info',
			UserProfileEntity\UserProfile::TABLE,
			'profile',
			'profile.id = profile_info.profile'
		);
		
		//$qb->addSelect('profile_event.type'); /* ID типа профиля */
		$qb->join(
			'profile',
			UserProfileEntity\Event\UserProfileEvent::TABLE,
			'profile_event',
			'profile_event.id = profile.event'
		);
		
		$qb->addSelect('profile_personal.username AS profile_username'); /* Username */

		$qb->join(
			'profile',
			UserProfileEntity\Personal\UserProfilePersonal::TABLE,
			'profile_personal',
			'profile_personal.event = profile.event'
		);
		
		$qb->addSelect('profile_avatar.name AS profile_avatar_name');
		$qb->addSelect('profile_avatar.dir AS profile_avatar_dir');
		$qb->addSelect('profile_avatar.ext AS profile_avatar_ext');
		$qb->addSelect('profile_avatar.cdn AS profile_avatar_cdn');


        $qb->addSelect("
			CASE
			   WHEN profile_avatar.name IS NOT NULL THEN CONCAT ( '/upload/".UserProfileEntity\Avatar\UserProfileAvatar::TABLE."' , '/', profile_avatar.dir, '/', profile_avatar.name, '.')
			   ELSE NULL
			END AS profile_avatar_file
		"
        );


		$qb->leftJoin(
			'profile_event',
			UserProfileEntity\Avatar\UserProfileAvatar::TABLE,
			'profile_avatar',
			'profile_avatar.event = profile_event.id'
		);
		
		/* Тип профиля */
		$qb->addSelect('profiletype.id as profile_type_id');
		$qb->join(
			'profile_event',
			TypeProfileEntity\TypeProfile::TABLE,
			'profiletype',
			'profiletype.id = profile_event.type'
		);
		
		$qb->join(
			'profiletype',
			TypeProfileEntity\Event\TypeProfileEvent::TABLE,
			'profiletype_event',
			'profiletype_event.id = profiletype.event'
		);
		
		$qb->join(
			'profiletype_event',
			TypeProfileEntity\Trans\TypeProfileTrans::TABLE,
			'profiletype_trans',
			'profiletype_trans.event = profiletype_event.id AND profiletype_trans.local = :local'
		);
		$qb->addSelect('profiletype_trans.name as profile_type');
		
		$qb->where('users.user_id = :user');
		$qb->setParameter('user', $user, UserUid::TYPE);
		
		/* Кешируем результат DBAL */
		$cacheFilesystem = new FilesystemAdapter('UserProfile');
		
		$config = $this->connection->getConfiguration();
		$config?->setResultCache($cacheFilesystem);
		
		return $this->connection->executeCacheQuery(
			$qb->getSQL(),
			$qb->getParameters(),
			$qb->getParameterTypes(),
			new QueryCacheProfile((60 * 60 * 30))
		)->fetchAssociative();
	}
	
	
	
	
	public function getCurrentUserProfile(UserUid $user) : ?CurrentUserProfileDTO
	{




		$qb = $this->entityManager->createQueryBuilder();

        

		/** ЛОКАЛЬ */
		$locale = new Locale($this->translator->getLocale());

		$sealect = sprintf("new %s(
			profile.id,
			profile.event,
			
			users.id,
			
			profile_personal.username,
			profile_personal.location,
			
			
			CONCAT ( '/upload/".UserProfileEntity\Avatar\UserProfileAvatar::TABLE."' , '/', profile_avatar.dir, '/', profile_avatar.name, '.'),
			profile_avatar.ext ,
			profile_avatar.cdn,
			'%s',
			
			profile_info.url,
			profile_info.discount,
			
			profiletype.id,
			profiletype_trans.name
		)",
			CurrentUserProfileDTO::class,
			$this->parameter->get('cdn.host')
		);
		
		$qb->select($sealect);
		
		/* Пользователь */
		$qb->from(User::class, 'users');
		
		/* PROFILE */

		$qb->join(
			
			UserProfileEntity\Info\UserProfileInfo::class,
			'profile_info',
			'WITH',
			'
				profile_info.user = users.id AND
				profile_info.status = :profile_status AND
				profile_info.active = true
		');
		
		
		
		//$qb->addSelect('profile.id AS user_profile_id'); /* ID профиля */
		//$qb->addSelect('profile.event AS user_profile_event'); /* ID события профиля */
		$qb->join(
			
			UserProfileEntity\UserProfile::class,
			'profile',
			'WITH',
			'profile.id = profile_info.profile'
		);
		
		//$qb->addSelect('profile_event.type'); /* ID типа профиля */
		$qb->join(

			UserProfileEntity\Event\UserProfileEvent::class,
			'profile_event',
			'WITH',
			'profile_event.id = profile.event'
		);

		$qb->join(
		
			UserProfileEntity\Personal\UserProfilePersonal::class,
			'profile_personal',
			'WITH',
			'profile_personal.event = profile.event'
		);
		

		$qb->leftJoin(
			UserProfileEntity\Avatar\UserProfileAvatar::class,
			'profile_avatar',
			'WITH',
			'profile_avatar.event = profile_event.id'
		);
		
		/* Тип профиля */
	
		$qb->join(
			TypeProfileEntity\TypeProfile::class,
			'profiletype',
			'WITH',
			'profiletype.id = profile_event.type'
		);
		
		$qb->join(

			TypeProfileEntity\Event\TypeProfileEvent::class,
			'profiletype_event',
			'WITH',
			'profiletype_event.id = profiletype.event'
		);
		
		$qb->leftJoin(
			
			TypeProfileEntity\Trans\TypeProfileTrans::class,
			'profiletype_trans',
			'WITH',
			'profiletype_trans.event = profiletype_event.id AND profiletype_trans.local = :local'
		);
		

		$qb->where('users.id = :user');
		
		/* Кешируем результат ORM */
		
		//$cacheFilesystem = new FilesystemAdapter($user->getValue());
		$cacheQueries = new ApcuAdapter($user);
		
		
		$query = $this->entityManager->createQuery($qb->getDQL());
		$query->setQueryCache($cacheQueries);
		$query->setResultCache($cacheQueries);
		$query->enableResultCache();
		$query->setLifetime(3600);
		
		
		
		
		$query->setParameter('local', $locale, Locale::TYPE);
		$query->setParameter('user', $user, UserUid::TYPE);
		$query->setParameter('profile_status', $this->statusProfile, UserProfileStatus::TYPE);
		
		
		return $query->getOneOrNullResult();
	}
	
}