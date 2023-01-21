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

namespace BaksDev\Users\Profile\UserProfile\Entity\Value;

use BaksDev\Users\Profile\TypeProfile\Type\Section\Field\Id\TypeProfileSectionFieldUid;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Core\Entity\EntityEvent;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Exception;
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
    
    
    /**
     * @param UserProfileEvent $event
     */
    public function __construct(UserProfileEvent $event) {
        $this->event = $event;
    }
    
    /**
     * @throws Exception
     */
    public function getDto($dto) : mixed
    {
        if($dto instanceof UserProfileValueInterface)
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
        if($dto instanceof UserProfileValueInterface)
        {
            return parent::setEntity($dto);
        }
        
        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
	
}
