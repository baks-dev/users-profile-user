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

namespace BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit;

use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEventInterface;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

final class UserProfileDTO implements UserProfileEventInterface
{
	
	#[Assert\Uuid]
	private ?UserProfileEventUid $id = null;
	
	/** Тип профиля */
	#[Assert\NotBlank]
	#[Assert\Uuid]
	private readonly TypeProfileUid $type;
	
	/** Аватарка */
	#[Assert\Valid]
	private ?Avatar\AvatarDTO $avatar;
	
	/** Тип профиля */
	#[Assert\Valid]
	private Info\InfoDTO $info;
	
	/** Персональные данные */
	#[Assert\Valid]
	private Personal\PersonalDTO $personal;
	
	/** Значения профиля */
	#[Assert\Valid]
	private ArrayCollection $value;
	
	/** Сортировка */
	private int $sort = 500;
	
	
	public function __construct()
	{
		$this->avatar = new Avatar\AvatarDTO();
		$this->info = new Info\InfoDTO();
		$this->personal = new Personal\PersonalDTO();
		$this->value = new ArrayCollection();
	}
	
	
	/* EVENT */
	
	public function getEvent() : ?UserProfileEventUid
	{
		return $this->id;
	}
	
	
	/* TYPE */
	
	public function getType() : TypeProfileUid
	{
		return $this->type;
	}
	
	
	public function setType(TypeProfileUid $type) : void
	{
		$this->type = $type;
	}
	
	
	/* INFO */
	
	public function getInfo() : Info\InfoDTO
	{
		return $this->info;
	}
	
	
	public function setInfo(Info\InfoDTO $info) : void
	{
		$this->info = $info;
	}
	
	
	/* SORT */
	
	public function getSort() : int
	{
		return $this->sort;
	}
	
	
	public function setSort(int $sort) : void
	{
		$this->sort = $sort;
	}
	
	
	/* PERSONAL */
	
	public function getPersonal() : Personal\PersonalDTO
	{
		return $this->personal;
	}
	
	
	public function setPersonal(Personal\PersonalDTO $personal) : void
	{
		$this->personal = $personal;
	}
	
	
	/* AVATAR */
	
	public function getAvatar() : Avatar\AvatarDTO
	{
		return $this->avatar ?: new Avatar\AvatarDTO();
	}
	
	
	public function setAvatar(?Avatar\AvatarDTO $avatar) : void
	{
		$this->avatar = $avatar;
	}
	
	
	/* VALUE */
	
	/**
	 * @return ArrayCollection
	 */
	public function getValue() : ArrayCollection
	{
		return $this->value;
	}
	
	
	public function addValue(Value\ValueDTO $value) : void
	{
		$this->value->add($value);
	}
	
	
	public function removeValue(Value\ValueDTO $value) : void
	{
		$this->value->removeElement($value);
	}
	
}