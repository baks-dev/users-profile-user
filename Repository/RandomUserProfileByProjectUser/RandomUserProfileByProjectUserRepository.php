<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Users\Profile\UserProfile\Repository\RandomUserProfileByProjectUser;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Shop\UserProfileShop;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Repository\UserTokenStorage\UserTokenStorage;
use BaksDev\Users\User\Type\Id\UserUid;
use InvalidArgumentException;

final class RandomUserProfileByProjectUserRepository implements RandomUserProfileByProjectUserInterface
{

    private UserUid|false $usr = false;

    public function forUser(User|UserUid|null|false|string $user): self
    {

        if(empty($user))
        {
            $this->usr = false;
            return $this;
        }

        if(is_string($user))
        {
            $user = new UserUid($user);
        }

        if($user instanceof User)
        {
            $user = $user->getId();
        }


        $this->usr = $user;

        return $this;
    }

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly UserTokenStorage $userTokenStorage
    ) {}


    public function find(): false|UserProfileUid
    {

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->addSelect('userprofile.id as value')
            ->from(UserProfile::class, 'userprofile');


        /* Профиль должен быть активным: status = UserProfileStatusActive */

        $dbal
            ->join(
                'userprofile',
                UserProfileInfo::class,
                'info',
                'info.profile = userprofile.id AND info.usr = :usr '.' AND info.status = :status',
            );

        $dbal->setParameter(
            'usr',
             true === ($this->usr instanceof UserUid) ? $this->usr : $this->userTokenStorage->getUserCurrent(),
            UserUid::TYPE
        );

        $dbal->setParameter(
            'status',
            UserProfileStatusActive::class,
            UserProfileStatus::TYPE
        );


        $dbal
            ->join(
                'userprofile',
                UserProfileEvent::class,
                'userprofile_event',
                'userprofile_event.id = userprofile.event'
            );

        $dbal
            ->join(
                'userprofile_event',
                UserProfileShop::class,
                'user_profile_shop',
                'user_profile_shop.event = userprofile_event.id AND user_profile_shop.value = true' // МАГАЗИН
            );


        /* Задать RANDOM */

        $dbal->orderBy('RANDOM()')
            ->setMaxResults(1);

        return $dbal->fetchHydrate(UserProfileUid::class);

    }
}