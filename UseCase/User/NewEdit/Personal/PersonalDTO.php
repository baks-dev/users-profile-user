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

namespace BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Personal;

use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonalInterface;
use BaksDev\Reference\Gender\Type\Gender;
use BaksDev\Reference\Gender\Type\GenderEnum;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

final class PersonalDTO implements UserProfilePersonalInterface
{
    /** Никнейм пользователя */
	#[Assert\NotBlank]
    private string $username;
    
    /** Пол (m: мужской, w-женский) */
	#[Assert\NotBlank]
    private Gender $gender;
    
    /** Дата рождения */
    private ?DateTimeImmutable $birthday = null;
    
    /** Местоположение */
    private ?string $location = null;
	
    
    /* USERNAME */

    public function __construct() { $this->gender = new Gender(GenderEnum::MEN ) ; }
	
	
	/* USERNAME */
    public function getUsername() : string
    {
        return $this->username;
    }
    public function setUsername(string $username) : void
    {
        $this->username = $username;
    }
    
    /* GENDER */

    public function getGender() : Gender
    {
        return $this->gender;
    }

    public function setGender(Gender $gender) : void
    {
        $this->gender = $gender;
    }
    
    /* BIRTHDAY */

    public function getBirthday() : ?DateTimeImmutable
    {
        return $this->birthday;
    }
	
    public function setBirthday(?DateTimeImmutable $birthday) : void
    {
        $this->birthday = $birthday;
    }
    
    /* LOCATION */
    

    public function getLocation() : ?string
    {
        return $this->location;
    }
    

    public function setLocation(?string $location) : void
    {
        $this->location = $location;
    }
    
}