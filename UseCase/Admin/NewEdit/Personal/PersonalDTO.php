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

namespace BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Personal;

use BaksDev\Core\Type\Gps\GpsLatitude;
use BaksDev\Core\Type\Gps\GpsLongitude;
use BaksDev\Reference\Gender\Type\Gender;
use BaksDev\Reference\Gender\Type\Genders\Collection\GenderMen;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Personal\UserProfilePersonalInterface;
use DateTimeImmutable;
use Symfony\Component\Validator\Constraints as Assert;

final class PersonalDTO implements UserProfilePersonalInterface
{
	/** Никнейм пользователя */
	private string $username;
	
	/** Пол (m: мужской, w-женский) */
	private Gender $gender;
	
	/** Дата рождения */
	private ?DateTimeImmutable $birthday = null;
	
	/** Местоположение */
	private ?string $location = null;


    /**
     * GPS широта.
     */
    #[Assert\NotBlank]
    private ?GpsLatitude $latitude = null;

    /**
     * GPS долгота.
     */
    #[Assert\NotBlank]
    private ?GpsLongitude $longitude = null;



    public function __construct() { $this->gender = new Gender(GenderMen::class); }
	
	
	/** Никнейм пользователя */
	public function getUsername(): string
	{
		return $this->username;
	}
	
	
	public function setUsername(string $username) : void
	{
		$this->username = $username;
	}
	
	
	/** Пол (m: мужской, w-женский) */
	
	public function getGender() : Gender
	{
		return $this->gender;
	}
	
	
	public function setGender(Gender $gender) : void
	{
		$this->gender = $gender;
	}
	
	
	/** Дата рождения */
	
	public function getBirthday() : ?DateTimeImmutable
	{
		return $this->birthday;
	}
	
	
	public function setBirthday(?DateTimeImmutable $birthday) : void
	{
		$this->birthday = $birthday;
	}
	
	
	/** Местоположение */
	
	public function getLocation() : ?string
	{
		return $this->location;
	}
	
	
	public function setLocation(?string $location) : void
	{
		$this->location = $location;
	}

    /**
     * Latitude
     */
    public function getLatitude(): ?GpsLatitude
    {
        return $this->latitude;
    }

    public function setLatitude(?GpsLatitude $latitude): self
    {
        $this->latitude = $latitude;
        return $this;
    }

    /**
     * Longitude
     */
    public function getLongitude(): ?GpsLongitude
    {
        return $this->longitude;
    }

    public function setLongitude(?GpsLongitude $longitude): self
    {
        $this->longitude = $longitude;
        return $this;
    }



}