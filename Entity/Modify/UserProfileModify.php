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

namespace BaksDev\Users\Profile\UserProfile\Entity\Modify;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Type\Ip\IpAddress;
use BaksDev\Core\Type\Modify\ModifyAction;
use BaksDev\Core\Type\Modify\ModifyActionEnum;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/* Модификаторы событий UserProfile */


#[ORM\Entity]
#[ORM\Table(name: 'users_profile_modify')]
#[ORM\Index(columns: ['action'])]
class UserProfileModify extends EntityEvent
{
	public const TABLE = 'users_profile_modify';
	
	/** ID события */
	#[ORM\Id]
	#[ORM\OneToOne(inversedBy: 'modify', targetEntity: UserProfileEvent::class, cascade: ['persist'])]
	#[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
	private UserProfileEvent $event;
	
	/** Модификатор */
	#[ORM\Column(type: ModifyAction::TYPE, nullable: false)]
	private ModifyAction $action;
	
	/** Дата */
	#[ORM\Column(name: 'mod_date', type: Types::DATETIME_IMMUTABLE, nullable: false)]
	private DateTimeImmutable $modDate;
	
	/** ID пользователя  */
	#[ORM\Column(name: 'user_id', type: UserUid::TYPE, nullable: true)]
	private ?UserUid $user = null;
	
	/** Ip адресс */
	#[ORM\Column(name: 'user_ip', type: IpAddress::TYPE, nullable: false)]
	private IpAddress $ipAddress;
	
	/** User-agent */
	#[ORM\Column(name: 'user_agent', type: Types::TEXT, nullable: false)]
	private string $userAgent;
	
	
	public function __construct(UserProfileEvent $event)
	{
		$this->event = $event;
		$this->modDate = new DateTimeImmutable();
		$this->ipAddress = new IpAddress('127.0.0.1');
		$this->userAgent = 'console';
		$this->action = new ModifyAction(ModifyActionEnum::NEW);
	}
	
	
	public function __clone() : void
	{
		$this->modDate = new DateTimeImmutable();
		$this->action = new ModifyAction(ModifyActionEnum::UPDATE);
		$this->ipAddress = new IpAddress('127.0.0.1');
		$this->userAgent = 'console';
	}
	
	
	public function getDto($dto) : mixed
	{
		if($dto instanceof UserProfileModifyInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function setEntity($dto) : mixed
	{
		if($dto instanceof UserProfileModifyInterface)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function upModifyAgent(IpAddress $ipAddress, ?string $userAgent) : void
	{
		$this->ipAddress = $ipAddress;
		$this->userAgent = $userAgent ?: 'console';
		$this->modDate = new DateTimeImmutable();
	}
	
	
	/**
	 * @param UserUid|User|null $user
	 */
	public function setUser(UserUid|User|null $user) : void
	{
		$this->user = $user instanceof User ? $user->getId() : $user;
	}
	
	
	public function equals(ModifyActionEnum $action) : bool
	{
		return $this->action->equals($action);
	}
	
}
