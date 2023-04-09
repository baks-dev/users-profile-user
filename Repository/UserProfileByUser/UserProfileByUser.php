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
	
	private Switcher $switcher;
	
	private PaginatorInterface $paginator;
	
	private Security $security;
	
	private TranslatorInterface $translator;
	
	
	public function __construct(
		Connection $connection,
		TranslatorInterface $translator,
		Switcher $switcher,
		Security $security,
		PaginatorInterface $paginator,
	)
	{
		$this->connection = $connection;
		$this->switcher = $switcher;
		$this->paginator = $paginator;
		$this->security = $security;
		$this->translator = $translator;
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
		
		$qb->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);
		
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