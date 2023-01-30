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

namespace BaksDev\Users\Profile\UserProfile\Entity\Event;

use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use BaksDev\Users\Profile\UserProfile\Entity\Avatar\UserProfileAvatar;

use BaksDev\Users\Profile\UserProfile\Entity\Modify\UserProfileModify;

use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Entity\Value\UserProfileValue;

use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Core\Type\Modify\ModifyAction;
use BaksDev\Core\Type\Modify\ModifyActionEnum;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;
use Exception;
use InvalidArgumentException;

/* События UserProfile */


#[ORM\Entity]
#[ORM\Table(name: 'users_profile_event')]
#[ORM\Index(columns: ['profile'])]
#[ORM\Index(columns: ['type'])]
class UserProfileEvent extends EntityEvent
{
	public const TABLE = 'users_profile_event';
	
	/** ID */
	#[ORM\Id]
	#[ORM\Column(type: UserProfileEventUid::TYPE)]
	private UserProfileEventUid $id;
	
	/** ID профиля пользователя */
	#[ORM\Column(type: UserProfileUid::TYPE)]
	private ?UserProfileUid $profile = null;
	
	/** Тип профиля */
	#[ORM\Column(type: TypeProfileUid::TYPE)]
	private TypeProfileUid $type;
	
	/** Сортировка */
	#[ORM\Column(type: Types::SMALLINT, length: 3, options: ['default' => 500])]
	private int $sort = 500;
	
	/** Аватарка профиля */
	#[ORM\OneToOne(mappedBy: 'event', targetEntity: UserProfileAvatar::class, cascade: ['all'])]
	private ?UserProfileAvatar $avatar = null;
	
	/** Персональные данные */
	#[ORM\OneToOne(mappedBy: 'event', targetEntity: UserProfilePersonal::class, cascade: ['all'])]
	private UserProfilePersonal $personal;
	
	/** Модификатор */
	#[ORM\OneToOne(mappedBy: 'event', targetEntity: UserProfileModify::class, cascade: ['all'])]
	private UserProfileModify $modify;
	
	/** Значения профиля */
	#[ORM\OneToMany(mappedBy: 'event', targetEntity: UserProfileValue::class, cascade: ['all'])]
	private Collection $value;
	
	
	public function __construct()
	{
		$this->id = new UserProfileEventUid();
		$this->personal = new UserProfilePersonal($this);
		$this->avatar = new UserProfileAvatar($this);
		
		$this->modify = new UserProfileModify($this);
		
		$this->value = new ArrayCollection();
		
	}
	
	
	public function __clone()
	{
		$this->id = new UserProfileEventUid();
	}
	
	
	public function getId() : UserProfileEventUid
	{
		return $this->id;
	}
	
	
	public function getProfile() : ?UserProfileUid
	{
		return $this->profile;
	}
	
	
	public function setProfile(UserProfileUid|UserProfile $profile) : void
	{
		$this->profile = $profile instanceof UserProfile ? $profile->getId() : $profile;
	}
	
	
	/**
	 * @throws Exception
	 */
	public function getDto($dto) : mixed
	{
		if($dto instanceof UserProfileEventInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	/**
	 * @throws Exception
	 */
	public function setEntity($dto) : mixed
	{
		if($dto instanceof UserProfileEventInterface)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function isModifyActionEquals(ModifyActionEnum $action) : bool
	{
		return $this->modify->equals($action);
	}
	
	
	public function getUploadAvatar() : UserProfileAvatar
	{
		return $this->avatar ?: $this->avatar = new UserProfileAvatar($this);
	}
	
	
	public function getNameUserProfile() : ?string
	{
		return $this->personal->name();
	}
	
}