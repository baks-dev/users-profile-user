<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Users\Profile\UserProfile\Repository\UserProfileByUser;

use BaksDev\Auth\Email\Entity as EntityAccountEmail;
use BaksDev\Users\Profile\TypeProfile\Entity as EntityTypeProfile;
use BaksDev\Users\Profile\UserProfile\Entity as EntityUserProfile;

use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatus;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;

use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Core\Services\Switcher\Switcher;
use BaksDev\Core\Type\Locale\Locale;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;
use Symfony\Bundle\SecurityBundle\Security;

//use Symfony\Component\Security\Core\Security;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UserProfileByUser implements UserProfileByUserInterface
{
	private Connection $connection;
	private Locale $local;
	private Switcher $switcher;
	private ?UserInterface $user;
	private PaginatorInterface $paginator;
	private Security $security;
	
	public function __construct(
		Connection $connection,
		TranslatorInterface $translator,
		Switcher $switcher,
		Security $security,
		PaginatorInterface $paginator
	)
	{
		$this->connection = $connection;
		$this->local = new Locale($translator->getLocale());
		//$this->user = $security->getUser();
		$this->switcher = $switcher;
		$this->paginator = $paginator;
		$this->security = $security;
	}
	
	public function get(SearchDTO $search, ?UserProfileStatus $status) : PaginatorInterface
	{
		
		$this->user = $this->security->getUser();
		
		$qb = $this->connection->createQueryBuilder();
		
		$qb->select('userprofile.id');
		$qb->addSelect('userprofile.event');
		$qb->from(EntityUserProfile\UserProfile::TABLE, 'userprofile');
		
		
		/* INFO */
		
		$qb->addSelect('info.url');
		$qb->addSelect('info.user_id');
		$qb->addSelect('info.status');
		$qb->addSelect('info.active');
		
		$qb->join(
			'userprofile',
			EntityUserProfile\Info\UserProfileInfo::TABLE,
			'info',
			'info.profile = userprofile.id AND
          info.user_id = :user_id
          '.($status ? 'AND info.status = :status' : '')
		);
		
		$qb->setParameter('user_id', $this?->user->getId(), UserUid::TYPE);
		
		if($status)
		{
			$qb->setParameter('status', $status, UserProfileStatus::TYPE);
		}
		
		
		$qb->addSelect('userprofile_event.sort');
		$qb->join(
			'userprofile',
			EntityUserProfile\Event\UserProfileEvent::TABLE,
			'userprofile_event',
			'userprofile_event.id = userprofile.event'
		);
		
		
		/* PERSONAL */
		
		$qb->addSelect('personal.username');
		$qb->addSelect('personal.location');
		
		$qb->join(
			'userprofile',
			EntityUserProfile\Personal\UserProfilePersonal::TABLE,
			'personal',
			'personal.event = userprofile.event'
		);
		
		
		/* AVATAR */
		$qb->addSelect('avatar.name AS avatar_name');
		$qb->addSelect('avatar.dir AS avatar_dir');
		$qb->addSelect('avatar.ext AS avatar_ext');
		$qb->addSelect('avatar.cdn AS avatar_cdn');
		
		$qb->leftJoin(
			'userprofile_event',
			EntityUserProfile\Avatar\UserProfileAvatar::TABLE,
			'avatar',
			'avatar.event = userprofile_event.id'
		);
		
		/** Аккаунт пользователя */
		
		/* ACCOUNT */
		$qb->join('info', EntityAccountEmail\Account::TABLE, 'account', 'account.id = info.user_id');
		
		/* ACCOUNT EVENT */
		$qb->addSelect('account_event.id as account_id');
		$qb->addSelect('account_event.email');
		$qb->leftJoin(
			'account',
			EntityAccountEmail\Event\AccountEvent::TABLE,
			'account_event',
			'account_event.id = account.event'
		);
		
		/** Тип профиля */
		
		/* TypeProfile */
		$qb->join(
			'userprofile_event',
			EntityTypeProfile\TypeProfile::TABLE,
			'type',
			'type.id = userprofile_event.type'
		);
		
		/* TypeProfileEvent */
		$qb->join(
			'type',
			EntityTypeProfile\Event\TypeProfileEvent::TABLE,
			'type_event',
			'type_event.id = type.event'
		);
		
		/* TypeProfileTrans */
		$qb->addSelect('type_trans.name as profile_type');
		$qb->join(
			'type_event',
			EntityTypeProfile\Trans\TypeProfileTrans::TABLE,
			'type_trans',
			'type_trans.event = type_event.id AND type_trans.local = :local'
		);
		
		
		$qb->setParameter('local', $this->local, Locale::TYPE);
		
		
		/* Поиск */
		if($search?->query)
		{
			$search->query = mb_strtolower($search->query);
			
			$searcher = $this->connection->createQueryBuilder();
			
			$searcher->orWhere('LOWER(personal.username) LIKE :query');
			$searcher->orWhere('LOWER(personal.username) LIKE :switcher');
			
			$searcher->orWhere('LOWER(personal.location) LIKE :query');
			$searcher->orWhere('LOWER(personal.location) LIKE :switcher');
			
			$qb->andWhere('('.$searcher->getQueryPart('where').')');
			$qb->setParameter('query', '%'.$this->switcher->toRus($search->query).'%');
			$qb->setParameter('switcher', '%'.$this->switcher->toEng($search->query).'%');
		}
		
		
		$qb->orderBy('userprofile_event.sort', 'ASC');
		$qb->addOrderBy('userprofile_event.id', 'ASC');
		
		return $this->paginator->fetchAllAssociative($qb);
	}
	
}