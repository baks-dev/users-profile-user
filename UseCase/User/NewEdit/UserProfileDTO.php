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