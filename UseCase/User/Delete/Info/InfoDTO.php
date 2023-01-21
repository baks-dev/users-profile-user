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

namespace BaksDev\Users\Profile\UserProfile\UseCase\User\Delete\Info;


use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfoInterface;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatus;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatusEnum;
use BaksDev\Users\User\Type\Id\UserUid;
use Symfony\Component\Validator\Constraints as Assert;

final class InfoDTO implements UserProfileInfoInterface
{
    /** Статус активности профиля */
	#[Assert\NotBlank]
    private readonly UserProfileStatus $status;
	
	/** Пользователь, кому принадлежит профиль */
	private readonly UserUid $user;
    
    /** Ссылка на профиль пользователя */
    private string $url;
    
    /** Текущий активный профиль, выбранный пользователем */
	#[Assert\IsFalse]
    private readonly bool $active;
    
    public function __construct() {
		$this->status = new UserProfileStatus(UserProfileStatusEnum::DELETE);
		$this->active = false;
	}
    
    /* STATUS */
    
    /**
     * Обновляем статус на Модерация
     * @return UserProfileStatus
     */
    public function getStatus() : UserProfileStatus
    {
        return $this->status;
    }
    
    
    /* URL */
    
    /**
     * @return string
     */
    public function getUrl() : string
    {
        return $this->url;
    }
	

    public function updateUrlUniq() : void
    {
        $this->url = uniqid($this->url.'_', false);
    }
    


	public function getUser() : UserUid
	{
		return $this->user;
	}
	
	/**
	 * @return bool
	 */
	public function getActive() : bool
	{
		return $this->active;
	}
	
}