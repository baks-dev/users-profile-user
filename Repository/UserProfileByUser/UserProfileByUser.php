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

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\PaginatorInterface;
use BaksDev\Users\Profile\TypeProfile\Entity\Trans\TypeProfileTrans;
use BaksDev\Users\Profile\TypeProfile\Entity\TypeProfile;
use BaksDev\Users\Profile\UserProfile\Entity\Avatar\UserProfileAvatar;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
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

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->addSelect('userprofile.id')
            ->addSelect('userprofile.event')
            ->from(UserProfile::class, 'userprofile');

        /* INFO */


        $dbal
            ->addSelect('info.url')
            ->addSelect('info.usr')
            ->addSelect('info.status')
            ->addSelect('info.active')
            ->join(
                'userprofile',
                UserProfileInfo::class,
                'info',
                'info.profile = userprofile.id AND info.usr = :usr '.($status ? 'AND info.status = :status' : '')
            );

        $dbal->setParameter('usr', $usr?->getId(), UserUid::TYPE);

        if($status)
        {
            $dbal->setParameter('status', $status, UserProfileStatus::TYPE);
        }

        $dbal
            ->addSelect('userprofile_event.sort')
            ->join(
                'userprofile',
                UserProfileEvent::class,
                'userprofile_event',
                'userprofile_event.id = userprofile.event'
            );

        /* PERSONAL */

        $dbal
            ->addSelect('personal.username')
            ->addSelect('personal.location')
            ->join(
                'userprofile',
                UserProfilePersonal::class,
                'personal',
                'personal.event = userprofile.event'
            );


        /* AVATAR */
        $dbal->addSelect("CONCAT('/upload/".UserProfileAvatar::TABLE."' , '/', avatar.name) AS avatar_name");
        $dbal->addSelect('avatar.ext AS avatar_ext');
        $dbal->addSelect('avatar.cdn AS avatar_cdn');

        $dbal->leftJoin(
            'userprofile_event',
            UserProfileAvatar::class,
            'avatar',
            'avatar.event = userprofile_event.id'
        );

        /** Аккаунт пользователя */

        /* ACCOUNT */
        $dbal->join('info', Account::class, 'account', 'account.id = info.usr');

        /* ACCOUNT EVENT */
        $dbal
            ->addSelect('account_event.id as account_id')
            ->addSelect('account_event.email')
            ->leftJoin(
                'account',
                AccountEvent::class,
                'account_event',
                'account_event.id = account.event'
            );

        /** Тип профиля */

        /* TypeProfile */
        $dbal->join(
            'userprofile_event',
            TypeProfile::class,
            'type',
            'type.id = userprofile_event.type'
        );

        /* TypeProfileEvent */
        //        $qb->join(
        //            'type',
        //            TypeProfileEvent::class,
        //            'type_event',
        //            'type_event.id = type.event'
        //        );

        /* TypeProfileTrans */
        $dbal
            ->addSelect('type_trans.name as profile_type')
            ->join(
                'type',
                TypeProfileTrans::class,
                'type_trans',
                'type_trans.event = type.event AND type_trans.local = :local'
            );


        /* Поиск */
        if($this->search?->getQuery())
        {
            $dbal
                ->createSearchQueryBuilder($this->search)
                ->addSearchLike('personal.username')
                ->addSearchLike('personal.location');
        }


        $dbal->addOrderBy('userprofile_event.sort', 'ASC');
        $dbal->addOrderBy('info.active', 'DESC');
        $dbal->addOrderBy('info.status', 'ASC');
        $dbal->addOrderBy('userprofile_event.id', 'DESC');


        return $this->paginator->fetchAllAssociative($dbal);
    }
}