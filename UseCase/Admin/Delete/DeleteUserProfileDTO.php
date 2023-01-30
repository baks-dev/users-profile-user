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

namespace BaksDev\Users\Profile\UserProfile\UseCase\Admin\Delete;

use BaksDev\Users\Profile\UserProfile\Entity\Event\EventInterface;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEvent;

final class DeleteUserProfileDTO implements EventInterface
{
	
	/**
	 * @var UserProfileEvent|null
	 */
	private ?UserProfileEvent $id = null;
	
	/** Тип профиля */
	private \BaksDev\Users\Profile\UserProfile\UseCase\Admin\Delete\Info\InfoDTO $info;
	
	/**
	 * Модификатор профиля пользователя
	 *
	 * @var \BaksDev\Users\Profile\UserProfile\UseCase\Admin\Delete\Modify\ModifyDTO
	 */
	private \BaksDev\Users\Profile\UserProfile\UseCase\Admin\Delete\Modify\ModifyDTO $modify;
	
	
	public function __construct()
	{
		$this->modify = new \BaksDev\Users\Profile\UserProfile\UseCase\Admin\Delete\Modify\ModifyDTO();
		$this->info = new \BaksDev\Users\Profile\UserProfile\UseCase\Admin\Delete\Info\InfoDTO();
	}
	
	/* EVENT */
	
	/**
	 * @return UserProfileEvent|null
	 */
	public function getEvent() : ?UserProfileEvent
	{
		return $this->id;
	}
	
	
	/**
	 * @param UserProfileEvent $id
	 *
	 * @return void
	 */
	public function setId(UserProfileEvent $id) : void
	{
		$this->id = $id;
	}
	
	
	/**
	 * @return \BaksDev\Users\Profile\UserProfile\UseCase\Admin\Delete\Info\InfoDTO
	 */
	public function getInfo() : \BaksDev\Users\Profile\UserProfile\UseCase\Admin\Delete\Info\InfoDTO
	{
		return $this->info;
	}
	
	
	/**
	 * @param \BaksDev\Users\Profile\UserProfile\UseCase\Admin\Delete\Info\InfoDTO $info
	 */
	public function setInfo(\BaksDev\Users\Profile\UserProfile\UseCase\Admin\Delete\Info\InfoDTO $info) : void
	{
		$this->info = $info;
	}
	
	
	/* Modify  */
	
	/**
	 * @return \BaksDev\Users\Profile\UserProfile\UseCase\Admin\Delete\Modify\ModifyDTO
	 */
	public function getModify() : \BaksDev\Users\Profile\UserProfile\UseCase\Admin\Delete\Modify\ModifyDTO
	{
		return $this->modify;
	}
	
	
	/**
	 * @return \BaksDev\Users\Profile\UserProfile\UseCase\Admin\Delete\Modify\ModifyDTO
	 */
	public function getModifyClass() : \BaksDev\Users\Profile\UserProfile\UseCase\Admin\Delete\Modify\ModifyDTO
	{
		return new \BaksDev\Users\Profile\UserProfile\UseCase\Admin\Delete\Modify\ModifyDTO();
	}
	
}