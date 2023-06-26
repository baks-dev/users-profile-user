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

namespace BaksDev\Users\Profile\UserProfile\Decorator\UserProfile;

use BaksDev\Users\Profile\UserProfile\Repository\CurrentUserProfile\CurrentUserProfileInterface;
use BaksDev\Users\User\Decorator\UserProfile\UserProfileInterface;
use BaksDev\Users\User\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

/** Username профиля пользователя */
#[AutoconfigureTag('baks.user.profile')]
#[AsEntityListener(event: Events::postLoad, method: 'postLoad', entity: User::class, priority: 99)]
final class UserProfileUsername implements UserProfileInterface
{
    public const KEY = 'user_profile_username';

    private CurrentUserProfileInterface $currentUserProfile;

    private bool|string $value = false;

    public function __construct(CurrentUserProfileInterface $currentUserProfile)
    {
        $this->currentUserProfile = $currentUserProfile;
    }

    public function postLoad(User $data, LifecycleEventArgs $event): void
    {
        $current = $this->currentUserProfile->fetchProfileAssociative($data->getId());
        $this->value = $current ? $current['profile_username'] : false;
    }

    /** Возвращает значение (value) */
    public function getValue(): bool|string
    {
        return $this->value;
    }

    public static function priority(): int
    {
        return 9;
    }
}
