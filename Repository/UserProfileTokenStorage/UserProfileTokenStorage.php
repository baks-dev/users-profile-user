<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage;

use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\User\Repository\UserTokenStorage\UserTokenStorageInterface;
use BaksDev\Users\User\Type\Id\UserUid;
use Symfony\Component\Security\Core\User\UserInterface;

final class UserProfileTokenStorage implements UserProfileTokenStorageInterface
{
    private UserProfileUid|false|null $profile = null;

    private UserProfileUid|false|null $current = null;

    public function __construct(private readonly UserTokenStorageInterface $userTokenStorage) {}

    /**
     * Метод возвращает идентификатор профиля текущего пользователя либо идентификатор олицетворенного
     */
    public function getProfile(): UserProfileUid|false
    {
        if(is_null($this->profile))
        {
            $user = $this->userTokenStorage->getUserInterface();
            $this->profile = $user instanceof UserInterface ? new UserProfileUid($user->getProfile()) : false;
        }

        return $this->profile;
    }

    /**
     * Метод всегда возвращает идентификатор профиля текущего пользователя вне зависимости от олицетворения
     */
    public function getProfileCurrent(): UserProfileUid|false
    {
        if(is_null($this->current))
        {
            $user = $this->userTokenStorage->getCurrentUserInterface();
            $this->current = $user instanceof UserInterface ? new UserProfileUid($user->getProfile()) : false;
        }

        return $this->current;
    }

    /**
     * Метод возвращает идентификатор текущего пользователя либо идентификатор олицетворенного
     */
    public function getUser(): UserUid|false
    {
        return $this->userTokenStorage->getUser();
    }

    /**
     * Метод всегда возвращает идентификатор текущего пользователя вне зависимости от олицетворения
     */
    public function getUserCurrent(): UserUid|false
    {
        return $this->userTokenStorage->getUserCurrent();
    }

}
