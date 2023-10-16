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

namespace BaksDev\Users\Profile\UserProfile\Entity\Value;

use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Users\Profile\TypeProfile\Type\Section\Field\Id\TypeProfileSectionFieldUid;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

/* Модификаторы событий UserProfile */


#[ORM\Entity]
#[ORM\Table(name: 'users_profile_value')]
class UserProfileValue extends EntityEvent
{
	public const TABLE = 'users_profile_value';
	
	/** ID события */
	#[ORM\Id]
	#[ORM\ManyToOne(targetEntity: UserProfileEvent::class, inversedBy: 'value')]
	#[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
	private UserProfileEvent $event;
	
	/** Связь на поле в секции типа профиля */
	#[ORM\Id]
	#[ORM\Column(type: TypeProfileSectionFieldUid::TYPE)]
	private TypeProfileSectionFieldUid $field;
	
	/** Заполненное значение */
	#[ORM\Column(type: Types::TEXT, nullable: true)]
	private ?string $value = null;

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

		if($dto instanceof UserProfileValueInterface)
		{
			return parent::getDto($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}

	public function setEntity($dto): mixed
	{
		if($dto instanceof UserProfileValueInterface || $dto instanceof self)
		{
			return parent::setEntity($dto);
		}
		
		throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
	}
	
}
