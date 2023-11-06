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

namespace BaksDev\Users\Profile\UserProfile\Entity;

use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\Mapping as ORM;

// use Fresh\CentrifugoBundle\User\CentrifugoUserInterface;

// UserProfile

#[ORM\Entity]
#[ORM\Table(name: 'users_profile')]
class UserProfile
{
    public const TABLE = 'users_profile';

    /** ID */
    #[ORM\Id]
    #[ORM\Column(type: UserProfileUid::TYPE)]
    private UserProfileUid $id;

    /** ID События */
    #[ORM\Column(type: UserProfileEventUid::TYPE, unique: true)]
    private UserProfileEventUid $event;

    public function __construct()
    {
        $this->id = new UserProfileUid();
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): UserProfileUid
    {
        return $this->id;
    }

    public function setId(UserProfileUid $id): void
    {
        $this->id = $id;
    }

    public function getEvent(): UserProfileEventUid
    {
        return $this->event;
    }

    public function setEvent(UserProfileEventUid|UserProfileEvent $event): void
    {
        $this->event = $event instanceof UserProfileEvent ? $event->getId() : $event;
    }
}
