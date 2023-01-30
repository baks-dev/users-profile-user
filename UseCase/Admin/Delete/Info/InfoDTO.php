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

namespace BaksDev\Users\Profile\UserProfile\UseCase\Admin\Delete\Info;

use BaksDev\Users\Profile\UserProfile\Entity\Info\InfoInterface;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatus;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatusEnum;
use App\Module\Users\User\Type\Id\UserUid;
use Symfony\Component\Validator\Constraints as Assert;

final class InfoDTO implements InfoInterface
{
	/** Пользователь, кому принадлежит профиль */
	private UserUid $user;
	
	/** Статус активности профиля */
	private UserProfileStatus $status;
	
	/** Ссылка на профиль пользователя */
	private string $url;
	
	
	/**
	 * @param UserProfileStatus $status
	 */
	public function __construct() { $this->status = new UserProfileStatus(UserProfileStatusEnum::MODERATION); }
	
	
	/* USER */
	
	/**
	 * @return UserUid
	 */
	public function getUser() : UserUid
	{
		return $this->user;
	}
	
	
	/**
	 * @param UserUid $user
	 */
	public function setUser(UserUid $user) : void
	{
		$this->user = $user;
	}
	
	/* STATUS */
	
	/**
	 * Обновляем статус на Модерация
	 *
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
	
	
	/**
	 * @param string $url
	 */
	public function setUrl(string $url) : void
	{
		$this->url = $url;
	}
	
	
	/**
	 * @param string $url
	 */
	public function updateUrlUniq() : void
	{
		$this->url = uniqid($this->url.'_', false);
	}
	
}