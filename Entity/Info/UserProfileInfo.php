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

namespace BaksDev\Users\Profile\UserProfile\Entity\Info;

use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatus;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatusEnum;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Entity\UserProfile\UserProfileInterface;
use BaksDev\Users\User\Type\Id\UserUid;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Entity\EntityState;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use InvalidArgumentException;

/* Неизменяемые данные UserProfile */

#[ORM\Entity]
#[ORM\Table(name: 'users_profile_info')]
#[ORM\Index(columns: ['user_id'])]
#[ORM\Index(columns: ['active'])]
#[ORM\Index(columns: ['status'])]
class UserProfileInfo extends EntityState
{
    public const TABLE = 'users_profile_info';

    /** ID UserProfile */
    #[ORM\Id]
    #[ORM\Column(type: UserProfileUid::TYPE)]
    private ?UserProfileUid $profile;
    
    /** Пользователь, кому принадлежит профиль */
    #[ORM\Column(name: 'user_id', type: UserUid::TYPE)]
    private UserUid $user;
    
    /** Текущий активный профиль, выбранный пользователем */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $active = false;
    
    /** Статус профиля (модерация, активен, заблокирован) */
    #[ORM\Column(type: UserProfileStatus::TYPE)]
    private UserProfileStatus $status;
    
    /** Ссылка на профиль пользователя */
    #[ORM\Column(type: Types::STRING, unique: true)]
    private string $url;


    public function __construct(UserProfileUid|UserProfile $profile)
    {
        $this->profile = $profile instanceof UserProfile ? $profile->getId() : $profile;
        $this->status = new UserProfileStatus(UserProfileStatusEnum::MODERATION);
    }
    
    /**
     * @return UserProfileUid|null
     */
    public function getProfile() : ?UserProfileUid
    {
        return $this->profile;
    }

    public function isProfileOwnedUser(UserUid|User $user) : bool
    {
		$id = $user instanceof User ? $user->getId() : $user;
        return $this->user->equals($id);
    }
    

    public function isNotActiveProfile() : bool
    {
        return $this->active !== false;
    }

    public function isNotStatusActive() : bool
    {
        return !$this->status->equals(UserProfileStatusEnum::ACTIVE);
    }

    
    /**
     * @throws Exception
     */
    public function getDto($dto) : mixed
    {
        if($dto instanceof UserProfileInfoInterface)
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
        if($dto instanceof UserProfileInfoInterface)
        {
            return parent::setEntity($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
    
    public function deactivate() : void
    {
        $this->active = false;
    }
}
