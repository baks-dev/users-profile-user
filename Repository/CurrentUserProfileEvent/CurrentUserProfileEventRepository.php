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

namespace BaksDev\Users\Profile\UserProfile\Repository\CurrentUserProfileEvent;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;

final class CurrentUserProfileEventRepository implements CurrentUserProfileEventInterface
{
    private ORMQueryBuilder $ORMQueryBuilder;

    public function __construct(ORMQueryBuilder $ORMQueryBuilder)
    {
        $this->ORMQueryBuilder = $ORMQueryBuilder;
    }

    public function findByUser(User|UserUid|string $user): UserProfileEvent|false
    {
        if(is_string($user))
        {
            $user = new UserUid($user);
        }

        if($user instanceof User)
        {
            $user = $user->getId();
        }

        $orm = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $orm->select('event');

        $orm
            ->from(UserProfileInfo::class, 'info')
            ->andWhere('info.active = true');

        $orm->andWhere('info.usr = :usr')
            ->setParameter('usr', $user, UserUid::TYPE);

        $orm->andWhere('info.status = :status')
            ->setParameter(
                'status',
                UserProfileStatusActive::class,
                UserProfileStatus::TYPE
            );

        $orm->leftJoin(
            UserProfile::class,
            'profile',
            'WITH',
            'profile.id = info.profile'
        );

        $orm->leftJoin(
            UserProfileEvent::class,
            'event',
            'WITH',
            'event.id = profile.event'
        );

        return $orm->getOneOrNullResult() ?: false;
    }

    public function findByEvent(UserProfileEventUid|string $event): UserProfileEvent|false
    {
        if(is_string($event))
        {
            $event = new UserProfileEventUid($event);
        }

        $orm = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $orm->select('event');

        $orm
            ->from(UserProfileEvent::class, 'event_param')
            ->where('event_param.id = :event')
            ->setParameter('event', $event, UserProfileEventUid::TYPE);

        $orm->leftJoin(
            UserProfile::class,
            'profile',
            'WITH',
            'profile.id = event_param.profile'
        );

        $orm->leftJoin(
            UserProfileEvent::class,
            'event',
            'WITH',
            'event.id = profile.event'
        );

        return $orm->getOneOrNullResult() ?: false;
    }


}
