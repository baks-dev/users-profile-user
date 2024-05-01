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
use BaksDev\Auth\Telegram\Entity\AccountTelegram;
use BaksDev\Auth\Telegram\Entity\Event\AccountTelegramEvent;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\Paginator;
use BaksDev\Users\Profile\TypeProfile\Entity\Event\TypeProfileEvent;
use BaksDev\Users\Profile\TypeProfile\Entity\Trans\TypeProfileTrans;
use BaksDev\Users\Profile\TypeProfile\Entity\TypeProfile;
use BaksDev\Users\Profile\UserProfile\Entity\Avatar\UserProfileAvatar;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Modify\UserProfileModify;
use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;

final class AllUserProfileRepository implements AllUserProfileInterface
{

    private Paginator $paginator;

    private DBALQueryBuilder $DBALQueryBuilder;


    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
        Paginator $paginator,
    )
    {
        $this->paginator = $paginator;
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }


    /**
     * Список всех добавленных профилей пользователей
     *
     * id - идентификатор профиля <br>
     * event - идентификатор события профиля,<br>
     * user_profile_url - адрес персональной страницы,<br>
     * usr - идентификатор пользователя,<br>
     *
     * user_profile_status - статус модерации профиля,<br>
     * user_profile_active - статус текущей активности профиля,<br>
     * user_profile_username - username пользователя,<br>
     * user_profile_location - местоположение,<br>
     * user_profile_avatar_name - название файла аватарки профиля,<br>
     * user_profile_avatar_dir - директория файла прафиля,<br>
     * user_profile_avatar_ext - расширение файла,<br>
     * user_profile_avatar_cdn - флаг загрузки CDN,<br>
     *
     * account_id - идентификатор аккаунта,<br>
     * account_email - email аккаунта,<br>
     * user_profile_type - тип профиля пользователя,<br>
     */

    public function fetchUserProfileAllAssociative(SearchDTO $search, ?UserProfileStatus $status): Paginator
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->addSelect('user_profile.id')
            ->addSelect('user_profile.event')
            ->from(UserProfile::class, 'user_profile');

        /* INFO */


        $dbal
            ->addSelect('user_profile_info.url AS user_profile_url')
            ->addSelect('user_profile_info.usr')
            ->addSelect('user_profile_info.status AS user_profile_status')
            ->addSelect('user_profile_info.active AS user_profile_active')
            ->join(
                'user_profile',
                UserProfileInfo::class,
                'user_profile_info',
                'user_profile_info.profile = user_profile.id '.($status ? 'AND user_profile_info.status = :status' : '')
            );

        if($status)
        {
            $dbal->setParameter('status', $status, UserProfileStatus::TYPE);
        }


        /* Модификатор */
        $dbal->leftJoin(
            'user_profile',
            UserProfileModify::class,
            'user_profile_modify',
            'user_profile_modify.event = user_profile.event'
        );


        /* Профиль */
        $dbal
            ->addSelect('user_profile_personal.username AS user_profile_username')
            ->addSelect('user_profile_personal.location AS user_profile_location')
            ->join(
                'user_profile',
                UserProfilePersonal::class,
                'user_profile_personal',
                'user_profile_personal.event = user_profile.event'
            );


        $dbal
            ->addSelect('user_profile_avatar.name AS user_profile_avatar_name')
            ->addSelect('user_profile_avatar.ext AS user_profile_avatar_ext')
            ->addSelect('user_profile_avatar.cdn AS user_profile_avatar_cdn')
            ->leftJoin(
                'user_profile',
                UserProfileAvatar::class,
                'user_profile_avatar',
                'user_profile_avatar.event = user_profile.event'
            );

        /* Аккаунт пользователя */

        /** Пользователь User */
        $dbal->leftJoin(
            'user_profile_info',
            Account::class,
            'account',
            'account.id = user_profile_info.usr'
        );

        /** Событие пользователя User\Event */
        $dbal
            ->addSelect('account_event.id AS account_id')
            ->addSelect('account_event.email AS account_email')
            ->leftJoin(
                'account',
                AccountEvent::class,
                'account_event',
                'account_event.id = account.event'
            );


        if(class_exists(AccountTelegram::class))
        {
            /* Аккаунт Telegram */

            $dbal->leftJoin(
                'user_profile_info',
                AccountTelegram::class,
                'telegram',
                'telegram.id = user_profile_info.usr'
            );

            /** Событие пользователя User\Event */
            $dbal
                ->addSelect('telegram_event.id AS telegram_id')
                ->addSelect('telegram_event.firstname AS telegram_firstname')
                ->leftJoin(
                    'telegram',
                    AccountTelegramEvent::class,
                    'telegram_event',
                    'telegram_event.id = telegram.event'
                );
        }

        

        /* Тип профиля */

        $dbal->leftJoin(
            'user_profile',
            UserProfileEvent::class,
            'user_profile_event',
            'user_profile_event.id = user_profile.event'
        );

        $dbal->leftJoin(
            'user_profile_event',
            TypeProfile::class,
            'profile_type',
            'profile_type.id = user_profile_event.type'
        );

        /*$dbal->leftJoin(
            'profile_type',
            TypeProfileEvent::class,
            'profile_type_event',
            'profile_type_event.id = profile_type.event'
        );*/

        $dbal
            ->addSelect('profile_type_trans.name as user_profile_type')
            ->leftJoin(
                'profile_type_event',
                TypeProfileTrans::class,
                'profile_type_trans',
                'profile_type_trans.event = profile_type.event AND profile_type_trans.local = :local'
            );


        /* Поиск */
        if($search->getQuery())
        {
            $dbal
                ->createSearchQueryBuilder($search)
                ->addSearchLike('user_profile_personal.username')
                ->addSearchLike('user_profile_personal.location')
                ->addSearchLike('account_event.email');
        }


        //$dbal->orderBy('userprofile.event', 'DESC');
        $dbal->addOrderBy('user_profile_modify.mod_date', 'DESC');

        return $this->paginator->fetchAllAssociative($dbal);

    }

}