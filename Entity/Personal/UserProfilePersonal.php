<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Type\Gps\GpsLatitude;
use BaksDev\Core\Type\Gps\GpsLongitude;
use BaksDev\Reference\Gender\Type\Gender;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/* Модификаторы событий UserProfile */


#[ORM\Entity]
#[ORM\Table(name: 'users_profile_personal')]
class UserProfilePersonal extends EntityEvent
{
	/** ID события */
	#[ORM\Id]
    #[ORM\OneToOne(targetEntity: UserProfileEvent::class, inversedBy: 'personal')]
	#[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
	private UserProfileEvent $event;
	
	/** Название профиля */
    #[ORM\Column(type: Types::STRING, length: 64)]
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

    /** GPS широта:*/
    #[ORM\Column(type: GpsLatitude::TYPE, nullable: true)]
    private ?GpsLatitude $latitude = null;

    /** GPS долгота:*/
    #[ORM\Column(type: GpsLongitude::TYPE, nullable: true)]
    private ?GpsLongitude $longitude = null;
	
	public function __construct(UserProfileEvent $event)
	{
		$this->event = $event;
	}

    public function __toString(): string
    {
        return (string) $this->event;
    }

	public function getDto($dto): mixed
	{
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

		if($dto instanceof UserProfilePersonalInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}

	public function setEntity($dto): mixed
	{
		if($dto instanceof UserProfilePersonalInterface || $dto instanceof self)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
	
	public function name(): string
	{
		return $this->username;
	}
	
}
