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

final class AllUserProfile implements AllUserProfileInterface
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
     * id - дентификатор профиля <br>
     * event - дентификатор события профиля,<br>
     * user_profile_url - адрес персональной страницы,<br>
     * usr - идентификатор пользовтаеля,<br>
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

    public function fetchUserProfileAllAssociative(SearchDTO $search, ?UserProfileStatus $status): Paginator
    {
        $qb = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $qb->addSelect('userprofile.id');
        $qb->addSelect('userprofile.event');
        $qb->from(UserProfile::TABLE, 'userprofile');

        /* INFO */
        $qb->join(
            'userprofile',
            UserProfileInfo::TABLE,
            'userprofile_info',
            'userprofile_info.profile = userprofile.id '.($status ? 'AND userprofile_info.status = :status' : '')
        );

        if($status)
        {
            $qb->setParameter('status', $status, UserProfileStatus::TYPE);
        }

        $qb->addSelect('userprofile_info.url AS user_profile_url');
        $qb->addSelect('userprofile_info.usr');
        $qb->addSelect('userprofile_info.status AS user_profile_status');
        $qb->addSelect('userprofile_info.active AS user_profile_active');

        $qb->join(
            'userprofile',
            UserProfileEvent::TABLE,
            'userprofile_event',
            'userprofile_event.id = userprofile.event'
        );

        /* Модификатор */
        $qb->leftJoin(
            'userprofile',
            UserProfileModify::TABLE,
            'userprofile_modify',
            'userprofile_modify.event = userprofile.event'
        );


        /* Профиль */
        $qb->join(
            'userprofile',
            UserProfilePersonal::TABLE,
            'userprofile_profile',
            'userprofile_profile.event = userprofile.event'
        );

        $qb->addSelect('userprofile_profile.username AS user_profile_username');
        $qb->addSelect('userprofile_profile.location AS user_profile_location');

        $qb->addSelect('userprofile_avatar.name AS user_profile_avatar_name');
        $qb->addSelect('userprofile_avatar.ext AS user_profile_avatar_ext');
        $qb->addSelect('userprofile_avatar.cdn AS user_profile_avatar_cdn');

        $qb->leftJoin(
            'userprofile_event',
            UserProfileAvatar::TABLE,
            'userprofile_avatar',
            'userprofile_avatar.event = userprofile_event.id'
        );

        /* Аккаунт пользователя */
        /** Пользователь User */
        $qb->leftJoin('userprofile_info', Account::TABLE, 'account', 'account.id = userprofile_info.usr');

        /** Событие пользователя User\Event */
        $qb
            ->addSelect('account_event.id AS account_id')
            ->addSelect('account_event.email AS account_email')
            ->leftJoin(
                'account',
                AccountEvent::TABLE,
                'account_event',
                'account_event.id = account.event'
            );


        if(class_exists(AccountTelegram::class))
        {
            /* Аккаунт Telegram */

            $qb->leftJoin(
                'userprofile_info',
                AccountTelegram::TABLE,
                'telegram',
                'telegram.id = userprofile_info.usr'
            );

            /** Событие пользователя User\Event */
            $qb
                ->addSelect('telegram_event.id AS telegram_id')
                ->addSelect('telegram_event.firstname AS telegram_firstname')
                ->leftJoin(
                    'telegram',
                    AccountTelegramEvent::TABLE,
                    'telegram_event',
                    'telegram_event.id = telegram.event'
                );
        }


        /* Тип профиля */

        $qb->leftJoin(
            'userprofile_event',
            TypeProfile::TABLE,
            'profiletype',
            'profiletype.id = userprofile_event.type'
        );

        $qb->leftJoin(
            'profiletype',
            TypeProfileEvent::TABLE,
            'profiletype_event',
            'profiletype_event.id = profiletype.event'
        );

        $qb->leftJoin(
            'profiletype_event',
            TypeProfileTrans::TABLE,
            'profiletype_trans',
            'profiletype_trans.event = profiletype_event.id AND profiletype_trans.local = :local'
        );

        $qb->addSelect('profiletype_trans.name as user_profile_type');


        /* Поиск */
        if($search->getQuery())
        {
            $qb
                ->createSearchQueryBuilder($search)
                ->addSearchLike('userprofile_profile.username')
                ->addSearchLike('account_event.email')
                ->addSearchLike('userprofile_profile.location');
        }


        //$qb->orderBy('userprofile.event', 'DESC');
        $qb->addOrderBy('userprofile_modify.mod_date', 'DESC');

        return $this->paginator->fetchAllAssociative($qb);

    }

}