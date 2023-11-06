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

namespace BaksDev\Users\Profile\UserProfile\UseCase\User\Delete\Info;

use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfoInterface;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusBlock;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use BaksDev\Users\User\Type\Id\UserUid;
use Symfony\Component\Validator\Constraints as Assert;

final class InfoDTO implements UserProfileInfoInterface
{
	/** Статус активности профиля */
	#[Assert\NotBlank]
	private readonly UserProfileStatus $status;
	
	/** Пользователь, кому принадлежит профиль */
    #[Assert\NotBlank]
    #[Assert\Uuid]
	private readonly UserUid $usr;
	
	/** Ссылка на профиль пользователя */
    #[Assert\NotBlank]
	private string $url;
	
	/** Текущий активный профиль, выбранный пользователем */
	#[Assert\IsFalse]
	private readonly bool $active;
	
	
	public function __construct()
	{
		$this->status = new UserProfileStatus(UserProfileStatusBlock::class);
		$this->active = false;
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
	public function getUrl(): string
	{
        $this->updateUrlUniq();
		return $this->url;
	}
	
	public function updateUrlUniq() : void
	{
		$this->url = uniqid($this->url.'_', false);
	}
	
	
	public function getUsr() : UserUid
	{
		return $this->usr;
	}
	
	
	/**
	 * @return bool
	 */
	public function getActive() : bool
	{
		return $this->active;
	}
	
}