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

namespace BaksDev\Users\Profile\UserProfile\Repository\CurrentUserProfile;

use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\User\Type\Id\UserUid;

final class CurrentUserProfileDTO
{

    /** Идентификатор профиля  */
    private UserProfileUid $id;

    /** Идентификатор события профиля  */
    private UserProfileEventUid $event;

    /** Идентификатор пользователя  */
    private UserUid $usr;

    /** Username профиля */
    private string $username;

    /** Местоположение */
    private ?string $location;

    /** Аватарка профиля */
    private ?string $avatar;

    /** Расширение аватарки */
    private ?string $ext;

    /** Флаг загрузки CDN */
    private bool $cdn;

    /** Персональный адрес профиля */
    private string $url;

    /** Персональная скидка */
    private int|string|null $discount;

    /** Идентификатор профиля пользователя */
    private TypeProfileUid $type;

    /** Название профиля */
    private string $profileName;

    private ?string $host;


    public function __construct(
        UserProfileUid $id,
        UserProfileEventUid $event,
        UserUid $usr,

        string $username,
        ?string $location,

        ?string $avatar,
        ?string $ext,
        ?bool $cdn,
        ?string $host,

        string $url,
        int|string|null $discount,

        TypeProfileUid $type,
        string $profileName,
    )
    {

        $this->id = $id;
        $this->event = $event;
        $this->usr = $usr;
        $this->username = $username;
        $this->location = $location;
        $this->avatar = $avatar;
        $this->ext = $ext;
        $this->cdn = $cdn !== null ?: false;
        $this->url = $url;
        $this->discount = $discount;
        $this->type = $type;
        $this->profileName = $profileName;
        $this->host = $host;
    }


    /** Идентификатор профиля  */

    public function getId(): UserProfileUid
    {
        return $this->id;
    }


    /** Идентификатор события профиля  */

    public function getEvent(): UserProfileEventUid
    {
        return $this->event;
    }


    /** Идентификатор пользователя  */

    public function getUsr(): UserUid
    {
        return $this->usr;
    }


    /** Username профиля */

    public function getUsername(): string
    {
        return $this->username;
    }


    /** Местоположение */

    public function getLocation(): string
    {
        return $this->location;
    }


    /** Аватарка профиля */

    public function getAvatar(): string
    {
        $avatar = $this->avatar.$this->ext;

        if(!$this->cdn)
        {
            $avatar = $this->host.$this->avatar;
        }

        return $avatar;
    }


    /** Расширение аватарки */

    public function getExt(): string
    {
        return $this->ext;
    }


    /** Флаг загрузки CDN */

    public function isCdn(): bool
    {
        return $this->cdn;
    }


    /** Персональный адрес профиля */

    public function getUrl(): string
    {
        return $this->url;
    }


    /** Персональная скидка */

    public function getDiscount(): int|string|null
    {
        return $this->discount;
    }


    /** Идентификатор профиля пользователя */

    public function getType(): TypeProfileUid
    {
        return $this->type;
    }


    /** Название профиля */

    public function getProfileName(): string
    {
        return $this->profileName;
    }

}