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

use BaksDev\Auth\Email\Entity as AccountEntity;

use BaksDev\Auth\Email\Type\Status\AccountStatus;
use BaksDev\Auth\Email\Type\Status\AccountStatusEnum;
use BaksDev\Users\Profile\TypeProfile\Entity as TypeProfileEntity;
use BaksDev\Users\Profile\UserProfile\Entity as UserProfileEntity;
use BaksDev\Users\Groups\Users\Entity as EntityCheckUsers;
use BaksDev\Users\Groups\Group\Entity as EntityGroup;

use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatus;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatusEnum;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
use BaksDev\Core\Type\Locale\Locale;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class CurrentUserProfile implements CurrentUserProfileInterface
{
	private Connection $connection;
	
	private Locale $locale;
	
	private AccountStatus $status;
	
	private UserProfileStatus $statusProfile;
	
	private ParameterBagInterface $parameter;
	
	
	//private bool|self $result;
	
	public function __construct(
		Connection $connection,
		TranslatorInterface $translator,
		ParameterBagInterface $parameter,
	)
	{
		$this->connection = $connection;
		$this->locale = new Locale($translator->getLocale());
		$this->status = new AccountStatus(AccountStatusEnum::ACTIVE);
		$this->statusProfile = new UserProfileStatus(UserProfileStatusEnum::ACTIVE);
		$this->parameter = $parameter;
	}
	
	
	/** ???????????????? ?????????????? ????????????????????????
	 *
	 * ???????????????????? ???????????? ?? ??????????????:
	 *
	 * profile_url - ?????????? ???????????????????????? ???????????????? <br>
	 * profile_username - username ???????????????? <br>
	 * profile_type - ?????? ???????????????? <br>
	 * profile_avatar_name - ???????????????? ?????????? ???????????????? ?????????????? <br>
	 * profile_avatar_dir - ???????????????????? ?????????? ???????????????? <br>
	 * profile_avatar_ext - ???????????????????? ?????????? <br>
	 * profile_avatar_cdn - ???????? ???????????????? ?????????? ???? CDN
	 */
	
	public function fetchProfileAssociative(UserUid $user) : bool|array
	{
		
		$qb = $this->connection->createQueryBuilder();
		
		/* ???????????????????????? */
		$qb->from(User::TABLE, 'users');
		
		/* PROFILE */
		$qb->addSelect('profile_info.url AS profile_url');  /* URL ?????????????? */
		$qb->join(
			'users',
			UserProfileEntity\Info\UserProfileInfo::TABLE,
			'profile_info',
			'profile_info.user_id = users.user_id AND profile_info.status = :profile_status AND profile_info.active = true'
		);
		
		$qb->setParameter('profile_status', $this->statusProfile, UserProfileStatus::TYPE);
		
		//$qb->addSelect('profile.id AS user_profile_id'); /* ID ?????????????? */
		//$qb->addSelect('profile.event AS user_profile_event'); /* ID ?????????????? ?????????????? */
		$qb->join(
			'profile_info',
			UserProfileEntity\UserProfile::TABLE,
			'profile',
			'profile.id = profile_info.profile'
		);
		
		//$qb->addSelect('profile_event.type'); /* ID ???????? ?????????????? */
		$qb->join(
			'profile',
			UserProfileEntity\Event\UserProfileEvent::TABLE,
			'profile_event',
			'profile_event.id = profile.event'
		);
		
		$qb->addSelect('profile_personal.username AS profile_username'); /* Username */
		//$qb->addSelect('profile_personal.gender'); /* ?????? */
		//$qb->addSelect('profile_personal.birthday'); /* ???????? ???????????????? */
		//$qb->addSelect('profile_personal.location'); /* ???????????????????????????? */
		
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
		
		$qb->leftJoin(
			'profile_event',
			UserProfileEntity\Avatar\UserProfileAvatar::TABLE,
			'profile_avatar',
			'profile_avatar.event = profile_event.id'
		);
		
		/* ?????? ?????????????? */
		
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
		
		$qb->setParameter('local', $this->locale, Locale::TYPE);
		
		$qb->where('users.user_id = :user');
		$qb->setParameter('user', $user, UserUid::TYPE);
		
		/* ???????????????? ?????????????????? ?????????????? */
		$cacheFilesystem = new FilesystemAdapter('CacheUserProfile');
		
		$config = $this->connection->getConfiguration();
		$config?->setResultCache($cacheFilesystem);
		
		return $this->connection->executeCacheQuery(
			$qb->getSQL(),
			$qb->getParameters(),
			$qb->getParameterTypes(),
			new QueryCacheProfile((60 * 60 * 30), 'current_user_profile'.$user.$this->locale)
		)->fetchAssociative();
	}
	
}