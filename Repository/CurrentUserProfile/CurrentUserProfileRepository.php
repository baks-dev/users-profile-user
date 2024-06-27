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

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Users\Profile\TypeProfile\Entity\Event\TypeProfileEvent;
use BaksDev\Users\Profile\TypeProfile\Entity\Trans\TypeProfileTrans;
use BaksDev\Users\Profile\TypeProfile\Entity\TypeProfile;
use BaksDev\Users\Profile\UserProfile\Entity\Avatar\UserProfileAvatar;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;

use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class CurrentUserProfileRepository implements CurrentUserProfileInterface
{
    private ORMQueryBuilder $ORMQueryBuilder;
    private DBALQueryBuilder $DBALQueryBuilder;
    private string $CDN_HOST;
    private AppCacheInterface $cache;

    public function __construct(
        #[Autowire(env: 'CDN_HOST')] string $CDN_HOST,
        ORMQueryBuilder $ORMQueryBuilder,
        DBALQueryBuilder $DBALQueryBuilder,
        AppCacheInterface $cache
    ) {
        $this->ORMQueryBuilder = $ORMQueryBuilder;
        $this->DBALQueryBuilder = $DBALQueryBuilder;
        $this->CDN_HOST = $CDN_HOST;
        $this->cache = $cache;
    }


    /** Активный профиль пользователя
     *
     * Возвращает массив с ключами:
     *
     * profile_url - адрес персональной страницы <br>
     * profile_username - username профиля <br>
     * profile_type - Тип профиля <br>
     * profile_avatar_name - название файла аватарки профиля <br>
     * profile_avatar_dir - директория файла аватарки <br>
     * profile_avatar_ext - расширение файла <br>
     * profile_avatar_cdn - флаг загрузки файла на CDN
     */

    public function fetchProfileAssociative(UserUid $usr, bool $authority = true): bool|array
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        /** Если пользователь олицетворен - подгружаем профиль самозванца */
        if($authority)
        {
            $AppCache = $this->cache->init('Authority');
            $authority = ($AppCache->getItem((string) $usr))->get();
        }

        /* PROFILE */
        $dbal->addSelect('profile_info.url AS profile_url');  /* URL профиля */
        $dbal->addSelect('profile_info.discount AS profile_discount');  /* URL профиля */

        if(empty($authority))
        {
            /* Пользователь */
            $dbal
                ->from(User::class, 'users')
                ->where('users.usr = :usr')
                ->setParameter('usr', $usr, UserUid::TYPE);

            $dbal->join(
                'users',
                UserProfileInfo::class,
                'profile_info',
                'profile_info.usr = users.usr 
                AND profile_info.status = :profile_status 
                AND profile_info.active = true'
            );
        }
        else
        {
            $dbal
                ->from(UserProfileInfo::class, 'profile_info')
                ->andWhere(' profile_info.active = true')
                ->andWhere('profile_info.status = :profile_status');

            $dbal
                ->andWhere('profile_info.profile = :authority')
                ->setParameter('authority', $authority, UserProfileUid::TYPE);


            /* Пользователь */
            $dbal->join(
                'profile_info',
                User::class,
                'users',
                'users.usr = profile_info.usr'
            );
        }

        $dbal->setParameter(
            'profile_status',
            UserProfileStatusActive::class,
            UserProfileStatus::TYPE
        );


        $dbal
            ->addSelect('profile.id AS user_profile_id')
            ->addSelect('profile.event AS user_profile_event')
            ->leftJoin(
                'profile_info',
                UserProfile::class,
                'profile',
                'profile.id = profile_info.profile'
            );


        /* Тип профиля */
        $dbal
            ->addSelect('profile_event.type as profile_type_id')
            ->leftJoin(
                'profile',
                UserProfileEvent::class,
                'profile_event',
                'profile_event.id = profile.event'
            );

        $dbal
            ->addSelect('profile_personal.username AS profile_username')
            ->leftJoin(
                'profile',
                UserProfilePersonal::class,
                'profile_personal',
                'profile_personal.event = profile.event'
            );

        $dbal
            ->addSelect('profile_avatar.ext AS profile_avatar_ext')
            ->addSelect('profile_avatar.cdn AS profile_avatar_cdn')
            ->addSelect("CONCAT ( '/upload/".$dbal->table(UserProfileAvatar::class)."' , '/', profile_avatar.name) AS profile_avatar_name")
            ->leftJoin(
                'profile_event',
                UserProfileAvatar::class,
                'profile_avatar',
                'profile_avatar.event = profile_event.id'
            );


        $dbal->leftJoin(
            'profile_event',
            TypeProfile::class,
            'profile_type',
            'profile_type.id = profile_event.type'
        );

        /*$dbal->leftJoin(
            'profile_type',
            TypeProfileEvent::class,
            'profile_type_event',
            'profile_type_event.id = profile_type.event'
        );*/

        $dbal
            ->addSelect('profile_type_trans.name as profile_type')
            ->leftJoin(
                'profile_type',
                TypeProfileTrans::class,
                'profile_type_trans',
                'profile_type_trans.event = profile_type.event AND profile_type_trans.local = :local'
            );

        /* Кешируем результат DBAL */
        return $dbal->enableCache('users-profile-user', 3600)->fetchAssociative();
    }


    public function getCurrentUserProfile(UserUid $usr): ?CurrentUserProfileDTO
    {
        $orm = $this->ORMQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $select = sprintf(
            "new %s(
			profile.id,
			profile.event,
			
			users.id,
			
			profile_personal.username,
			profile_personal.location,
			
			CONCAT('/upload/".$orm->table(UserProfileAvatar::class)."' , '/', profile_avatar.name),
			profile_avatar.ext ,
			profile_avatar.cdn,
			'%s',
			
			profile_info.url,
			profile_info.discount,
			
			profile_type.id,
			profile_type_trans.name
		)",
            CurrentUserProfileDTO::class,
            $this->CDN_HOST
        );

        $orm->select($select);

        /* Пользователь */
        $orm
            ->from(User::class, 'users')
            ->where('users.id = :usr')
            ->setParameter('usr', $usr, UserUid::TYPE);

        /* PROFILE */

        $orm
            ->join(
                UserProfileInfo::class,
                'profile_info',
                'WITH',
                '
				profile_info.usr = users.id AND
				profile_info.status = :profile_status AND
				profile_info.active = true
		'
            )
            ->setParameter(
                'profile_status',
                UserProfileStatusActive::class,
                UserProfileStatus::TYPE
            );

        $orm->leftJoin(
            UserProfile::class,
            'profile',
            'WITH',
            'profile.id = profile_info.profile'
        );


        $orm->leftJoin(
            UserProfilePersonal::class,
            'profile_personal',
            'WITH',
            'profile_personal.event = profile.event'
        );


        $orm->leftJoin(
            UserProfileAvatar::class,
            'profile_avatar',
            'WITH',
            'profile_avatar.event = profile.event'
        );


        $orm->leftJoin(
            UserProfileEvent::class,
            'profile_event',
            'WITH',
            'profile_event.id = profile.event'
        );

        /* Тип профиля */

        $orm->leftJoin(
            TypeProfile::class,
            'profile_type',
            'WITH',
            'profile_type.id = profile_event.type'
        );

        /*orm->leftJoin(

            TypeProfileEvent::class,
            'profile_type_event',
            'WITH',
            'profile_type_event.id = profile_type.event'
        );*/

        $orm->leftJoin(
            TypeProfileTrans::class,
            'profile_type_trans',
            'WITH',
            'profile_type_trans.event = profile_type.event AND profile_type_trans.local = :local'
        );


        /* Кешируем результат ORM */
        return $orm->enableCache('users-profile-user', 3600)->getOneOrNullResult();

    }
}
