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

namespace BaksDev\Users\Profile\UserProfile\Entity\Personal;

use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Reference\Gender\Type\Gender;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
use InvalidArgumentException;

/* Модификаторы событий UserProfile */


#[ORM\Entity]
#[ORM\Table(name: 'users_profile_personal')]
class UserProfilePersonal extends EntityEvent
{
	public const TABLE = 'users_profile_personal';
	
	/** ID события */
	#[ORM\Id]
	#[ORM\OneToOne(inversedBy: 'personal', targetEntity: UserProfileEvent::class)]
	#[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
	private UserProfileEvent $event;
	
	/** Название профиля */
	#[ORM\Column(type: Types::STRING, length: 32)]
	private string $username;
	
	/** Пол (m: мужской, w-женский) */
	#[ORM\Column(type: Gender::TYPE, length: 5)]
	private Gender $gender;
	
	/** Дата рождения */
	#[ORM\Column(type: Types::DATE_IMMUTABLE, nullable: true)]
	private ?DateTimeImmutable $birthday = null;
	
	/** Местоположение */
	#[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
	private ?string $location = null;
	
	//    /** ID чата телеграмм */
	//    #[ORM\Column(type: Types::STRING, length: 255, nullable: true)]
	//    protected ?string $telegramm = null;
	
	public function __construct(UserProfileEvent $event)
	{
		$this->event = $event;
	}
	
	
	/**
	 * @throws Exception
	 */
	public function getDto($dto) : mixed
	{
		if($dto instanceof UserProfilePersonalInterface)
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
		if($dto instanceof UserProfilePersonalInterface)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function name() : string
	{
		return $this->username;
	}
	
}
