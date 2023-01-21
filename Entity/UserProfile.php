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

namespace BaksDev\Users\Profile\UserProfile\Entity;


use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\Mapping as ORM;
//use Fresh\CentrifugoBundle\User\CentrifugoUserInterface;

/* UserProfile */

#[ORM\Entity]
#[ORM\Table(name: 'users_profile')]
class UserProfile
{
    public const TABLE = 'users_profile';
    
    /** ID */
    #[ORM\Id]
    #[ORM\Column(type: UserProfileUid::TYPE)]
    private UserProfileUid $id;
    
    /** ID События */
    #[ORM\Column(type: UserProfileEventUid::TYPE, unique: true)]
    private UserProfileEventUid $event;
    

    public function __construct() { $this->id = new UserProfileUid(); }
    
    /**
     * @return UserProfileUid
     */
    public function getId() : UserProfileUid
    {
        return $this->id;
    }
    
    /**
     * @param UserProfileUid $id
     */
    public function setId(UserProfileUid $id) : void
    {
        $this->id = $id;
    }
    
    /**
     * @return UserProfileEventUid
     */
    public function getEvent() : UserProfileEventUid
    {
        return $this->event;
    }
    

    public function setEvent(UserProfileEventUid|UserProfileEvent $event) : void
    {
        $this->event = $event instanceof UserProfileEvent ? $event->getId() : $event;
    }
    
    
   
    

    
}