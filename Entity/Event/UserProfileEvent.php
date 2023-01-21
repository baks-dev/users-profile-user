<?php
/*
 *  Copyright Baks.dev <admin@baks.dev>
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