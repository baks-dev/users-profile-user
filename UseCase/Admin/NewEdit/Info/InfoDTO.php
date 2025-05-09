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

namespace BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Info;

use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfoInterface;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusBlock;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusModeration;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use BaksDev\Users\User\Type\Id\UserUid;
use Symfony\Component\Validator\Constraints as Assert;

/** @see UserProfileInfo */
final class InfoDTO implements UserProfileInfoInterface
{
    /**
     * Пользователь, кому принадлежит профиль
     */
    private UserUid $usr;

    /** Статус активности профиля */
    #[Assert\NotBlank]
    private UserProfileStatus $status;

    /** Ссылка на профиль пользователя */
    #[Assert\NotBlank]
    private string $url;

    /** Статус модерации профиля */
    private bool $active = false;

    /** Персональная скидка профиля */
    private ?string $discount = null;

    public function __construct()
    {
        $this->status = new UserProfileStatus(UserProfileStatusModeration::class);
    }


    /** Пользователь, кому принадлежит профиль */

    public function getUsr(): UserUid
    {
        return $this->usr;
    }


    public function setUsr(UserUid $usr): void
    {
        $this->usr = $usr;
    }


    /** Статус активности профиля */
    public function getActive(): bool
    {
        return $this->active;
    }


    /** Статус модерации профиля */

    public function getStatus(): UserProfileStatus
    {
        return $this->status;
    }


    public function setStatus(UserProfileStatus $status): void
    {
        if($status->equals(UserProfileStatusActive::class))
        {
            $this->active = true;
        }

        $this->status = $status;
    }


    public function isModeration(): bool
    {
        return $this->status->equals(UserProfileStatusModeration::class);
    }


    public function isBlock(): bool
    {
        return $this->status->equals(UserProfileStatusBlock::class);
    }


    /** Ссылка на профиль пользователя */

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


    public function getDiscount(): ?string
    {
        return $this->discount;
    }


    public function setDiscount(?string $discount): void
    {
        $this->discount = $discount;
    }

}