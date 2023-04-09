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

declare(strict_types=1);

namespace BaksDev\Users\Profile\UserProfile\Repository\AllUserProfile;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Users\Profile\TypeProfile\Entity as TypeProfileEntity;
use BaksDev\Users\Profile\UserProfile\Entity as UserProfileEntity;

use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\Paginator;
use BaksDev\Core\Services\Switcher\Switcher;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatus;
use Doctrine\DBAL\Connection;
use Symfony\Contracts\Translation\TranslatorInterface;

final class AllUserProfile implements AllUserProfileInterface
{
	private Connection $connection;
	
	private Switcher $switcher;
	
	private Paginator $paginator;
	
	private TranslatorInterface $translator;
	
	
	public function __construct(
		Connection $connection,
		TranslatorInterface $translator,
		Switcher $switcher,
		Paginator $paginator,
	)
	{
		$this->connection = $connection;
		$this->switcher = $switcher;
		$this->paginator = $paginator;
		$this->translator = $translator;
	}
	
	
	/**
	 * Список всех добавленных профилей пользователей
	 *
	 * id - дентификатор профиля <br>
	 * event - дентификатор события профиля,<br>
	 * user_profile_url - адрес персональной страницы,<br>
	 * user_id - идентификатор пользовтаеля,<br>
	 *
	 * user_profile_status - статус модерации пролфиля,<br>
	 * user_profile_active - статус текущей активности профиля,<br>
	 * user_profile_username - username пользователя,<br>
	 * user_profile_location - местоположение,<br>
	 * user_profile_avatar_name - название файла аватарки профиля,<br>
	 * user_profile_avatar_dir - директория файла прафиля,<br>
	 * user_profile_avatar_ext - расширение файла,<br>
	 * user_profile_avatar_cdn - флаг загрузки CDN,<br>
	 *
	 * account_id - идентификтаор аккаунта,<br>
	 * account_email - email аккаунта,<br>
	 * user_profile_type - тип профиля пользователя,<br>
	 */
	
	public function fetchUserProfileAllAssociative(SearchDTO $search, ?UserProfileStatus $status) : Paginator
	{
		$qb = $this->connection->createQueryBuilder();
		
		$qb->addSelect('userprofile.id');
		$qb->addSelect('userprofile.event');
		$qb->from(UserProfileEntity\UserProfile::TABLE, 'userprofile');
		
		/* INFO */
		$qb->join(
			'userprofile',
			UserProfileEntity\Info\UserProfileInfo::TABLE,
			'userprofile_info',
			'userprofile_info.profile = userprofile.id '.($status ? 'AND userprofile_info.status = :status' : '')
		);
		
		if($status)
		{
			$qb->setParameter('status', $status, UserProfileStatus::TYPE);
		}
		
		$qb->addSelect('userprofile_info.url AS user_profile_url');
		$qb->addSelect('userprofile_info.user_id');
		$qb->addSelect('userprofile_info.status AS user_profile_status');
		$qb->addSelect('userprofile_info.active AS user_profile_active');
		
		$qb->join(
			'userprofile',
			UserProfileEntity\Event\UserProfileEvent::TABLE,
			'userprofile_event',
			'userprofile_event.id = userprofile.event'
		);
		
		/* Профиль */
		$qb->join(
			'userprofile',
			UserProfileEntity\Personal\UserProfilePersonal::TABLE,
			'userprofile_profile',
			'userprofile_profile.event = userprofile.event'
		);
		$qb->addSelect('userprofile_profile.username AS user_profile_username');
		$qb->addSelect('userprofile_profile.location AS user_profile_location');
		
		$qb->addSelect('userprofile_avatar.name AS user_profile_avatar_name');
		$qb->addSelect('userprofile_avatar.dir AS user_profile_avatar_dir');
		$qb->addSelect('userprofile_avatar.ext AS user_profile_avatar_ext');
		$qb->addSelect('userprofile_avatar.cdn AS user_profile_avatar_cdn');
		
		$qb->leftJoin(
			'userprofile_event',
			UserProfileEntity\Avatar\UserProfileAvatar::TABLE,
			'userprofile_avatar',
			'userprofile_avatar.event = userprofile_event.id'
		);
		
		/* Аккаунт пользователя */
		/** Пользователь User */
		$qb->join('userprofile_info', Account::TABLE, 'account', 'account.id = userprofile_info.user_id');
		
		/** Событие пользователя User\Event */
		$qb->addSelect('account_event.id AS account_id');
		$qb->addSelect('account_event.email AS account_email');
		$qb->leftJoin('account', AccountEvent::TABLE, 'account_event', 'account_event.id = account.event');
		
		/* Тип профиля */
		
		$qb->join(
			'userprofile_event',
			TypeProfileEntity\TypeProfile::TABLE,
			'profiletype',
			'profiletype.id = userprofile_event.type'
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
		$qb->addSelect('profiletype_trans.name as user_profile_type');
		
		$qb->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);
		
		/* Поиск */
		if($search?->query)
		{
			$search->query = mb_strtolower($search->query);
			
			$searcher = $this->connection->createQueryBuilder();
			
			$searcher->orWhere('LOWER(userprofile_profile.username) LIKE :query');
			$searcher->orWhere('LOWER(userprofile_profile.username) LIKE :switcher');
			
			$searcher->orWhere('LOWER(account_event.user_email) LIKE :query');
			$searcher->orWhere('LOWER(account_event.user_email) LIKE :switcher');
			
			$searcher->orWhere('LOWER(userprofile_profile.location) LIKE :query');
			$searcher->orWhere('LOWER(userprofile_profile.location) LIKE :switcher');
			
			$qb->andWhere('('.$searcher->getQueryPart('where').')');
			$qb->setParameter('query', '%'.$this->switcher->toRus($search->query).'%');
			$qb->setParameter('switcher', '%'.$this->switcher->toEng($search->query).'%');
		}
		
		$qb->orderBy('userprofile.event', 'ASC');
		
		return $this->paginator->fetchAllAssociative($qb);
		
	}
	
}