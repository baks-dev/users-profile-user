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
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatus;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatusEnum;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class CurrentUserProfile implements CurrentUserProfileInterface
{
    private ORMQueryBuilder $ORMQueryBuilder;
    private DBALQueryBuilder $DBALQueryBuilder;
    private string $CDN_HOST;
    private Security $security;
    private AppCacheInterface $cache;


    public function __construct(
        #[Autowire(env: 'CDN_HOST')] string $CDN_HOST,
        ORMQueryBuilder $ORMQueryBuilder,
        DBALQueryBuilder $DBALQueryBuilder,
        Security $security,
        AppCacheInterface $cache

    )
    {

        $this->ORMQueryBuilder = $ORMQueryBuilder;
        $this->DBALQueryBuilder = $DBALQueryBuilder;
        $this->CDN_HOST = $CDN_HOST;
        $this->security = $security;
        $this->cache = $cache;
    }


    /** Активный профиль пользователя
     *
     * Возвращает массив с ключами:
     *
     * profile_url - адрес персональной страницы <br>
     * profile_username - username провфиля <br>
     * profile_type - Тип провфиля <br>
     * profile_avatar_name - название файла аватарки профиля <br>
     * profile_avatar_dir - директория файла аватарки <br>
     * profile_avatar_ext - расширение файла <br>
     * profile_avatar_cdn - фгаг загрузки файла на CDN
     */

    public function fetchProfileAssociative(UserUid $usr, bool $authority = true): ?array
    {
        $qb = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();
        
        if($authority)
        {
            /** Если пользовтаель олицетворен - подгружаем профиль самозванца */
            $AppCache = $this->cache->init('Authority');
            $authority = ($AppCache->getItem((string) $usr))->get();

        }

        //dump($authority );

        if($authority)
        {
            //dump((string) $authority);
            
            /* Пользователь */
            $qb->from(User::TABLE, 'users');

            /* PROFILE */
            $qb->addSelect('profile_info.url AS profile_url');  /* URL профиля */
            $qb->addSelect('profile_info.discount AS profile_discount');  /* URL профиля */
            $qb->from(UserProfileInfo::TABLE, 'profile_info');


            $qb->andWhere('profile_info.profile = :authority')
            ->setParameter('authority', $authority, UserProfileUid::TYPE);

            $qb->andWhere('profile_info.status = :profile_status');

        }
        else
        {
            /* Пользователь */
            $qb->from(User::TABLE, 'users');

            $qb->where('users.usr = :usr');
            $qb->setParameter('usr', $usr, UserUid::TYPE);


            /* PROFILE */
            $qb->addSelect('profile_info.url AS profile_url');  /* URL профиля */
            $qb->addSelect('profile_info.discount AS profile_discount');  /* URL профиля */
            $qb->join(
                'users',
                UserProfileInfo::TABLE,
                'profile_info',
                'profile_info.usr = users.usr AND profile_info.status = :profile_status AND profile_info.active = true'
            );
        }


        $qb->setParameter('profile_status', new UserProfileStatus(UserProfileStatusEnum::ACTIVE), UserProfileStatus::TYPE);


        $qb->addSelect('profile.id AS user_profile_id'); /* ID профиля */
        $qb->addSelect('profile.event AS user_profile_event'); /* ID события профиля */
        $qb->join(
            'profile_info',
            UserProfile::TABLE,
            'profile',
            'profile.id = profile_info.profile'
        );


        /* Тип профиля */
        $qb->addSelect('profile_event.type as profile_type_id');
        $qb->join(
            'profile',
            UserProfileEvent::TABLE,
            'profile_event',
            'profile_event.id = profile.event'
        );

        $qb->addSelect('profile_personal.username AS profile_username'); /* Username */

        $qb->join(
            'profile',
            UserProfilePersonal::TABLE,
            'profile_personal',
            'profile_personal.event = profile.event'
        );

        $qb->addSelect('profile_avatar.ext AS profile_avatar_ext');
        $qb->addSelect('profile_avatar.cdn AS profile_avatar_cdn');
        $qb->addSelect("CONCAT ( '/upload/".UserProfileAvatar::TABLE."' , '/', profile_avatar.name) AS profile_avatar_name");


        $qb->leftJoin(
            'profile_event',
            UserProfileAvatar::TABLE,
            'profile_avatar',
            'profile_avatar.event = profile_event.id'
        );


        $qb->join(
            'profile_event',
            TypeProfile::TABLE,
            'profiletype',
            'profiletype.id = profile_event.type'
        );

        $qb->join(
            'profiletype',
            TypeProfileEvent::TABLE,
            'profiletype_event',
            'profiletype_event.id = profiletype.event'
        );

        $qb->addSelect('profiletype_trans.name as profile_type');
        $qb->join(
            'profiletype_event',
            TypeProfileTrans::TABLE,
            'profiletype_trans',
            'profiletype_trans.event = profiletype_event.id AND profiletype_trans.local = :local'
        );


        /* Кешируем результат DBAL */
        return $qb->enableCache('UserProfile', 3600)->fetchAssociative();
    }



    public function getCurrentUserProfile(UserUid $usr): ?CurrentUserProfileDTO
    {
        $qb = $this->ORMQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal()
        ;

        $sealect = sprintf("new %s(
			profile.id,
			profile.event,
			
			users.id,
			
			profile_personal.username,
			profile_personal.location,
			
			CONCAT('/upload/".UserProfileAvatar::TABLE."' , '/', profile_avatar.name),
			profile_avatar.ext ,
			profile_avatar.cdn,
			'%s',
			
			profile_info.url,
			profile_info.discount,
			
			profiletype.id,
			profiletype_trans.name
		)",
            CurrentUserProfileDTO::class,
            $this->CDN_HOST
        );

        $qb->select($sealect);

        /* Пользователь */
        $qb->from(User::class, 'users');

        /* PROFILE */

        $qb->join(

            UserProfileInfo::class,
            'profile_info',
            'WITH',
            '
				profile_info.usr = users.id AND
				profile_info.status = :profile_status AND
				profile_info.active = true
		');

        $qb->setParameter('profile_status', new UserProfileStatus(UserProfileStatusEnum::ACTIVE), UserProfileStatus::TYPE);

        $qb->join(

            UserProfile::class,
            'profile',
            'WITH',
            'profile.id = profile_info.profile'
        );

        $qb->join(

            UserProfileEvent::class,
            'profile_event',
            'WITH',
            'profile_event.id = profile.event'
        );

        $qb->join(

            UserProfilePersonal::class,
            'profile_personal',
            'WITH',
            'profile_personal.event = profile.event'
        );


        $qb->leftJoin(
            UserProfileAvatar::class,
            'profile_avatar',
            'WITH',
            'profile_avatar.event = profile_event.id'
        );

        /* Тип профиля */

        $qb->join(
            TypeProfile::class,
            'profiletype',
            'WITH',
            'profiletype.id = profile_event.type'
        );

        $qb->join(

            TypeProfileEvent::class,
            'profiletype_event',
            'WITH',
            'profiletype_event.id = profiletype.event'
        );

        $qb->leftJoin(

            TypeProfileTrans::class,
            'profiletype_trans',
            'WITH',
            'profiletype_trans.event = profiletype_event.id AND profiletype_trans.local = :local'
        )
            ;


        $qb->where('users.id = :usr');
        $qb->setParameter('usr', $usr, UserUid::TYPE);

     
        /* Кешируем результат ORM */
        return $qb->enableCache('UserProfile', 3600)->getOneOrNullResult();

    }
}