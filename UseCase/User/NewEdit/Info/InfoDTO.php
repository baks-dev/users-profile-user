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

namespace BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Info;

use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfoInterface;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusBlock;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusModeration;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
use ReflectionProperty;
use Symfony\Component\Validator\Constraints as Assert;

/** @see UserProfileInfo */
final class InfoDTO implements UserProfileInfoInterface
{
    /** Пользователь, кому принадлежит профиль */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    private readonly UserUid $usr;

    /** Ссылка на профиль пользователя */
    #[Assert\NotBlank]
    private string $url;

    /** Текущий активный профиль, выбранный пользователем */
    #[Assert\IsTrue]
    private readonly bool $active;

    /** Статус профиля (модерация, активен, заблокирован) */
    #[Assert\NotBlank]
    private UserProfileStatus $status;

    public function __construct()
    {
        $this->active = true;
        $this->status = new UserProfileStatus(UserProfileStatusModeration::class);
    }

    /* USER */

    /**
     * @return UserUid
     */
    public function getUsr(): UserUid
    {
        return $this->usr;
    }


    public function setUsr(UserUid|User $usr): void
    {
        if(!(new ReflectionProperty(self::class, 'usr'))->isInitialized($this))
        {
            $this->usr = $usr instanceof User ? $usr->getId() : $usr;
        }
    }

    /* STATUS */
    /* Статус после обновления всегда На модерации */

    /**
     * @return UserProfileStatus
     */
    public function getStatus(): UserProfileStatus
    {
        return $this->status;
    }

    public function setStatus(mixed $status): self
    {
        $this->status = new UserProfileStatus($status);

        return $this;
    }


    /**
     * @return bool
     */
    public function getActive(): bool
    {
        return $this->active;
    }

//    public function activate(): self
//    {
//        $this->active = true;
//        return $this;
//    }


    /* URL */

    public function getUrl(): string
    {
        return $this->url;
    }


    public function setUrl(string $url): void
    {
        $this->url = $url;
    }


    public function updateUrlUniq(): void
    {
        $this->url = uniqid($this->url.'_', false);
    }


    public function isModeration(): bool
    {
        return $this->status->equals(UserProfileStatusModeration::class);
    }


    public function isBlock(): bool
    {
        return $this->status->equals(UserProfileStatusBlock::class);
    }

}