<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Users\Profile\UserProfile\Repository\UserProfileChoice;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Status\AccountStatus;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusActive;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Users\Profile\Group\Entity\Users\ProfileGroupUsers;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use BaksDev\Users\User\Type\Id\UserUid;
use Generator;

final class UserProfileChoiceRepository implements UserProfileChoiceInterface
{
    private ORMQueryBuilder $ORMQueryBuilder;
    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(
        ORMQueryBuilder $ORMQueryBuilder,
        DBALQueryBuilder $DBALQueryBuilder
    )
    {
        $this->ORMQueryBuilder = $ORMQueryBuilder;
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    /**
     * Метод возвращает список идентификаторов профилей с username профиля в качестве атрибута
     */
    public function getActiveUserProfile(?UserUid $usr = null): ?array
    {
        $select = sprintf('new %s(user_profile.id, personal.username, personal.latitude, personal.longitude)', UserProfileUid::class);

        $dbal = $this->ORMQueryBuilder->createQueryBuilder(self::class);
        $dbal->select($select);

        $dbal->from(UserProfile::class, 'user_profile');

        $dbal
            ->join(
                UserProfileInfo::class,
                'info',
                'WITH',
                'info.profile = user_profile.id AND info.status = :status',
            )
            ->setParameter(
                'status',
                UserProfileStatusActive::class,
                UserProfileStatus::TYPE,
            );


        if($usr)
        {
            $dbal
                ->where('info.usr = :usr')
                ->setParameter('usr', $usr, UserUid::TYPE);
        }


        $dbal->join(
            UserProfileEvent::class,
            'event',
            'WITH',
            'event.id = user_profile.event AND event.profile = user_profile.id',
        );

        $dbal->join(
            UserProfilePersonal::class,
            'personal',
            'WITH',
            'personal.event = event.id',
        );


        $dbal->join(
            Account::class,
            'account',
            'WITH',
            'account.id = info.usr',
        );

        $dbal
            ->join(
                AccountStatus::class,
                'status',
                'WITH',
                'status.event = account.event AND status.status = :account_status',
            )
            ->setParameter(
                key: 'account_status',
                value: EmailStatusActive::class,
                type: EmailStatus::TYPE,
            );


        /* Кешируем результат ORM */
        return $dbal->enableCache('users-profile-user', 86400)->getResult();

    }


    /**
     * Метод возвращает список идентификаторов профилей с username профиля в качестве атрибута
     * при условии, что имеется доверенность
     */
    public function getActiveProfileAuthority(UserUid $usr, UserUid $current): Generator
    {

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->from(UserProfile::class, 'user_profile');

        /** Получаем активный профиль текущего пользователя */

        $dbal->join(
            'user_profile',
            UserProfileInfo::class,
            'current_info',
            'current_info.usr = :current AND current_info.active IS TRUE AND current_info.status = :status',
        );


        $dbal
            ->setParameter('current', $current, UserUid::TYPE)
            ->setParameter(
                'status',
                UserProfileStatusActive::class,
                UserProfileStatus::TYPE,
            );


        $dbal->join(
            'current_info',
            ProfileGroupUsers::class,
            'group_users',
            'group_users.profile = current_info.profile AND group_users.authority = user_profile.id',
        );


        $dbal
            ->where('info.usr = :usr')
            ->setParameter('usr', $usr, UserUid::TYPE);

        $dbal->join(
            'user_profile',
            UserProfileInfo::class,
            'info',
            'info.profile = user_profile.id AND info.status = :status',
        );

        $dbal->setParameter(
            'status',
            UserProfileStatusActive::class,
            UserProfileStatus::TYPE,
        );


        $dbal
            ->join(
                'user_profile',
                UserProfileEvent::class,
                'event',
                'event.id = user_profile.event AND event.profile = user_profile.id',
            );

        $dbal
            ->join(
                'user_profile',
                UserProfilePersonal::class,
                'user_profile_personal',
                'user_profile_personal.event = user_profile.event',
            );


        $dbal->join(
            'info',
            Account::class,
            'account',
            'account.id = info.usr',
        );

        $dbal->join(
            'account',
            AccountStatus::class,
            'status',
            'status.event = account.event AND status.status = :account_status',
        );

        $dbal->setParameter('account_status', new EmailStatus(EmailStatusActive::class), EmailStatus::TYPE);


        $dbal
            ->addSelect('user_profile.id AS value')
            ->addSelect('user_profile_personal.username AS attr')
            ->addSelect('user_profile_personal.latitude AS option')
            ->addSelect('user_profile_personal.longitude AS property');

        return $dbal
            ->enableCache('users-profile-user', 86400)
            ->fetchAllHydrate(UserProfileUid::class);

    }

}
