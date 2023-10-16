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

//use BaksDev\Auth\Email\Entity as EntityAccountEmail;
use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
//use BaksDev\Users\Profile\TypeProfile\Entity as EntityTypeProfile;
//use BaksDev\Users\Profile\UserProfile\Entity as EntityUserProfile;
use BaksDev\Users\Profile\TypeProfile\Entity\Event\TypeProfileEvent;
use BaksDev\Users\Profile\TypeProfile\Entity\Trans\TypeProfileTrans;
use BaksDev\Users\Profile\TypeProfile\Entity\TypeProfile;
use BaksDev\Users\Profile\UserProfile\Entity\Avatar\UserProfileAvatar;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatus;
use BaksDev\Users\User\Type\Id\UserUid;
use Symfony\Bundle\SecurityBundle\Security;

final class UserProfileByUser implements UserProfileByUserInterface
{

    private PaginatorInterface $paginator;
    private Security $security;
    private DBALQueryBuilder $DBALQueryBuilder;
    private ?SearchDTO $search = null;


    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
        Security $security,
        PaginatorInterface $paginator,
    )
    {
        $this->paginator = $paginator;
        $this->security = $security;
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    public function search(SearchDTO $search): self
    {
        $this->search = $search;
        return $this;
    }

    public function findAllUserProfile(?UserProfileStatus $status = null): PaginatorInterface
    {

        $usr = $this->security->getUser();

        $qb = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal()
        ;

        $qb->select('userprofile.id');
        $qb->addSelect('userprofile.event');
        $qb->from(UserProfile::TABLE, 'userprofile');

        /* INFO */

        $qb->addSelect('info.url');
        $qb->addSelect('info.usr');
        $qb->addSelect('info.status');
        $qb->addSelect('info.active');

        $qb->join(
            'userprofile',
            UserProfileInfo::TABLE,
            'info',
            'info.profile = userprofile.id AND
          info.usr = :usr
          '.($status ? 'AND info.status = :status' : '')
        );

        $qb->setParameter('usr', $usr?->getId(), UserUid::TYPE);

        if($status)
        {
            $qb->setParameter('status', $status, UserProfileStatus::TYPE);
        }

        $qb->addSelect('userprofile_event.sort');
        $qb->join(
            'userprofile',
            UserProfileEvent::TABLE,
            'userprofile_event',
            'userprofile_event.id = userprofile.event'
        );

        /* PERSONAL */

        $qb->addSelect('personal.username');
        $qb->addSelect('personal.location');

        $qb->join(
            'userprofile',
            UserProfilePersonal::TABLE,
            'personal',
            'personal.event = userprofile.event'
        );

        /* AVATAR */
        $qb->addSelect("CONCAT('/upload/".UserProfileAvatar::TABLE."' , '/', avatar.name) AS avatar_name");
        $qb->addSelect('avatar.ext AS avatar_ext');
        $qb->addSelect('avatar.cdn AS avatar_cdn');

        $qb->leftJoin(
            'userprofile_event',
            UserProfileAvatar::TABLE,
            'avatar',
            'avatar.event = userprofile_event.id'
        );

        /** Аккаунт пользователя */

        /* ACCOUNT */
        $qb->join('info', Account::TABLE, 'account', 'account.id = info.usr');

        /* ACCOUNT EVENT */
        $qb->addSelect('account_event.id as account_id');
        $qb->addSelect('account_event.email');
        $qb->leftJoin(
            'account',
            AccountEvent::TABLE,
            'account_event',
            'account_event.id = account.event'
        );

        /** Тип профиля */

        /* TypeProfile */
        $qb->join(
            'userprofile_event',
            TypeProfile::TABLE,
            'type',
            'type.id = userprofile_event.type'
        );

        /* TypeProfileEvent */
        $qb->join(
            'type',
            TypeProfileEvent::TABLE,
            'type_event',
            'type_event.id = type.event'
        );

        /* TypeProfileTrans */
        $qb->addSelect('type_trans.name as profile_type');
        $qb->join(
            'type_event',
            TypeProfileTrans::TABLE,
            'type_trans',
            'type_trans.event = type_event.id AND type_trans.local = :local'
        );


        /* Поиск */
        if($this->search?->getQuery())
        {
            $qb
                ->createSearchQueryBuilder($this->search)
                ->addSearchLike('personal.username')
                ->addSearchLike('personal.location')
            ;
        }

        $qb->orderBy('userprofile_event.sort', 'ASC');
        $qb->addOrderBy('userprofile_event.id', 'ASC');

        return $this->paginator->fetchAllAssociative($qb);

    }

}