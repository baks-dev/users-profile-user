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

namespace BaksDev\Users\Profile\UserProfile\Repository\CurrentAllUserProfiles;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Repository\UserTokenStorage\UserTokenStorageInterface;
use BaksDev\Users\User\Type\Id\UserUid;
use Generator;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;

final  class CurrentAllUserProfilesByUserRepository implements CurrentAllUserProfilesByUserInterface
{

    private UserUid|false $user = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly UserTokenStorageInterface $tokenStorage,
    ) {}

    public function forUser(User|UserUid|false|null $user): self
    {
        if(empty($user))
        {
            $this->user = false;
            return $this;
        }

        if($user instanceof User)
        {
            $user = $user->getId();
        }

        $this->user = $user;

        return $this;
    }

    /**
     * Список профилей пользователя в меню
     *
     * Возвращает массив с ключами: <br>
     * user_profile_event - идентификатор события для активации профиля <br>
     * user_profile_username - username профиля <br>
     *
     */
    public function findAll(): Generator|false
    {
        if(false === $this->user && false === $this->tokenStorage->isUser())
        {
            return false;
        }

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->from(UserProfileInfo::class, 'user_profile_info');

        $dbal
            ->where('user_profile_info.usr = :usr')
            ->setParameter(
                key: 'usr',
                value: $this->user ?: $this->tokenStorage->getUserCurrent(),
                type: UserUid::TYPE,
            );

        $dbal
            ->andWhere('user_profile_info.status = :status')
            ->setParameter(
                'status',
                UserProfileStatusActive::class,
                UserProfileStatus::TYPE,
            );


        $dbal
            ->addSelect('user_profile.id AS value')
            ->join(
                'user_profile_info',
                UserProfile::class,
                'user_profile',
                'user_profile.id = user_profile_info.profile',
            );


        $dbal->leftJoin(
            'user_profile',
            UserProfileEvent::class,
            'user_profile_event',
            'user_profile_event.id = user_profile.event',
        );

        $dbal
            ->leftJoin(
                'user_profile',
                UserProfilePersonal::class,
                'user_profile_profile',
                'user_profile_profile.event = user_profile.event',
            );

        $dbal
            ->orderBy('user_profile_event.sort', 'ASC')
            ->addOrderBy('user_profile_info.active', 'DESC')
            ->addOrderBy('user_profile_info.status', 'ASC')
            ->addOrderBy('user_profile_event.id', 'DESC');


        $dbal->addSelect("
            JSONB_BUILD_OBJECT (
                'event', user_profile.event, 
                'username', user_profile_profile.username
            ) AS params",
        );

        return $dbal
            //->enableCache('users-profile-user', '1 day')
            ->fetchAllHydrate(UserProfileUid::class);
    }

    /**
     * @deprecated
     * Список личных профилей пользователя в меню.
     *
     * Возвращает массив с ключами: <br>
     * user_profile_event - идентификатор события для активации профиля <br>
     * user_profile_username - username профиля <br>
     */

    // - public function fetchUserProfilesAllAssociative(UserUid $usr): ?array
    // + public function fetchUserProfilesAllAssociative(): ?array

    public function fetchUserProfilesAllAssociative(): ?array
    {
        if(false === $this->tokenStorage->isUser())
        {
            return null;
        }

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal->from(UserProfileInfo::class, 'user_profile_info');

        $dbal
            ->where('user_profile_info.usr = :usr')
            ->setParameter(
                'usr',
                $this->tokenStorage->getUserCurrent(),
                UserUid::TYPE,
            );

        $dbal
            ->andWhere('user_profile_info.status = :status')
            ->setParameter(
                'status',
                UserProfileStatusActive::class,
                UserProfileStatus::TYPE,
            );


        $dbal
            ->addSelect('user_profile.id AS user_profile_id')
            ->addSelect('user_profile.event AS user_profile_event')
            ->join(
                'user_profile_info',
                UserProfile::class,
                'user_profile',
                'user_profile.id = user_profile_info.profile',
            );


        $dbal->leftJoin(
            'user_profile',
            UserProfileEvent::class,
            'user_profile_event',
            'user_profile_event.id = user_profile.event',
        );

        $dbal
            ->addSelect('user_profile_profile.username AS user_profile_username')
            ->leftJoin(
                'user_profile',
                UserProfilePersonal::class,
                'user_profile_profile',
                'user_profile_profile.event = user_profile.event',
            );

        $dbal
            ->orderBy('user_profile_event.sort', 'ASC')
            ->addOrderBy('user_profile_info.active', 'DESC')
            ->addOrderBy('user_profile_info.status', 'ASC')
            ->addOrderBy('user_profile_event.id', 'DESC');

        return $dbal
            ->enableCache('users-profile-user', 86400)
            ->fetchAllAssociative();

    }

}
