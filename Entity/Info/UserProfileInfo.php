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
