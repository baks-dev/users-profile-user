<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Users\Profile\UserProfile\UseCase\User\Activate;



use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEventInterface;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use Symfony\Component\Validator\Constraints as Assert;
final class ActivateUserProfileDTO implements UserProfileEventInterface
{
	#[Assert\NotBlank]
	#[Assert\Uuid]
    private readonly UserProfileEventUid $id;
    
    /** Тип профиля */
	#[Assert\Valid]
    private Info\InfoDTO $info;

    public function __construct() {
        $this->info = new Info\InfoDTO();
    }
    
    /* EVENT */
    public function getEvent() : UserProfileEventUid
    {
        return $this->id;
    }
    
	/* INFO */
    public function getInfo() : Info\InfoDTO
    {
        return $this->info;
    }
	
    public function setInfo(Info\InfoDTO $info) : void
    {
        $this->info = $info;
    }
    
}