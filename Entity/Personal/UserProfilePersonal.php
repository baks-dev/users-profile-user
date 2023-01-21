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

namespace BaksDev\Users\Profile\UserProfile\Entity\Personal;

use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Type\Gender\Gender;
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
