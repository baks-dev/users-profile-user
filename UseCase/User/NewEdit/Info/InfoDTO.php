<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Info;


use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfoInterface;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatus;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatusEnum;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
use Symfony\Component\Validator\Constraints as Assert;

final class InfoDTO implements UserProfileInfoInterface
{
    /** Пользователь, кому принадлежит профиль */
    private readonly UserUid $user;
    
    /** Ссылка на профиль пользователя */
    private string $url;
    
    /** Текущий активный профиль, выбранный пользователем */
    private bool $active = false;
    
    /** Статус профиля (модерация, активен, заблокирован) */
    private readonly UserProfileStatus $status;
    
    public function __construct() {
		$this->status = new UserProfileStatus(UserProfileStatusEnum::MODERATION);
	}
    
    /* USER */

    /**
     * @return UserUid
     */
    public function getUser() : UserUid
    {
        return $this->user;
    }
    
    public function setUser(UserUid|User $user) : void
    {
        $this->user = $user instanceof User ? $user->getId() : $user;
    }
    
    
    /* STATUS */
    /* Статус после обновления всегда На модерации */
    
    /**
     * @return UserProfileStatus
     */
    public function getStatus() : UserProfileStatus
    {
        return $this->status;
    }
    
    /**
     * @return bool
     */
    public function isActive() : bool
    {
        return $this->active;
    }

    
    /* URL */

    public function getUrl() : string
    {
        return $this->url;
    }

    public function setUrl(string $url) : void
    {
        $this->url = $url;
    }

    public function updateUrlUniq() : void
    {
        $this->url = uniqid($this->url.'_', false);
    }
    
    public function isModeration() : bool
    {
        return $this->status->equals(UserProfileStatusEnum::MODERATION);
    }
    
    public function isBlock() : bool
    {
        return $this->status->equals(UserProfileStatusEnum::BLOCK);
    }
    
}