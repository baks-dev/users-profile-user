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

namespace BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Info;

use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfoInterface;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatus;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatusEnum;

use BaksDev\Users\User\Type\Id\UserUid;
use Symfony\Component\Validator\Constraints as Assert;

final class InfoDTO implements UserProfileInfoInterface
{
	/** Пользователь, кому принадлежит профиль */
	private UserUid $user;
	
	/** Статус активности профиля */
	private UserProfileStatus $status;
	
	/** Ссылка на профиль пользователя */
	private string $url;
	
	/** Статус модерации профиля */
	private bool $active = false;
	
	
	public function __construct() { $this->status = new UserProfileStatus(UserProfileStatusEnum::MODERATION); }
	
	
	/** Пользователь, кому принадлежит профиль */
	
	public function getUser() : UserUid
	{
		return $this->user;
	}
	
	
	public function setUser(UserUid $user) : void
	{
		$this->user = $user;
	}
	
	
	/** Статус активности профиля */
	public function getActive() : bool
	{
		return $this->active;
	}
	
	
	/** Статус модерации профиля */
	
	public function getStatus() : UserProfileStatus
	{
		return $this->status;
	}
	
	
	public function setStatus(UserProfileStatus $status) : void
	{
		if($status->equals(UserProfileStatusEnum::ACTIVE))
		{
			$this->active = true;
		}
		
		$this->status = $status;
	}
	
	
	public function isModeration() : bool
	{
		return $this->status->equals(UserProfileStatusEnum::MODERATION);
	}
	
	
	public function isBlock() : bool
	{
		return $this->status->equals(UserProfileStatusEnum::BLOCK);
	}
	
	
	/** Ссылка на профиль пользователя */
	
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
	
}