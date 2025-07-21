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

declare(strict_types=1);

namespace BaksDev\Users\Profile\UserProfile\Repository\UserProfileByRegion;

use BaksDev\Core\Type\Gps\GpsLatitude;
use BaksDev\Core\Type\Gps\GpsLongitude;
use BaksDev\Reference\Region\Type\Id\RegionUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Symfony\Component\Validator\Constraints as Assert;

/** @see UserProfileByRegionResult */
final class UserProfileByRegionResult
{
    public function __construct(
        private string $id, //" => "0197f935-a3c8-701a-9dfb-5e6f951e4c6e"
        private string $region, //" => "201042a6-c35d-7bc4-9cb9-ef8bc1c8711e"
        private string $region_name,
        private ?bool $shop, //" => null
        private ?bool $orders, //" => null
        private ?bool $warehouse, //" => null
        private string $location,
        //" => "Московская область, городской округ Химки, деревня Поярково, улица Лесная Цесарка, 8"
        private string $latitude, //" => "56.030678"
        private string $longitude, //" => "37.301518"
        private string $profile_value,
    ) {}

    public function getId(): UserProfileUid
    {
        return new UserProfileUid($this->id);
    }

    public function getRegion(): RegionUid
    {
        return new RegionUid($this->region);
    }

    public function getShop(): bool
    {
        return $this->shop === true;
    }

    public function getOrders(): bool
    {
        return $this->orders === true;
    }

    public function getWarehouse(): bool
    {
        return $this->warehouse === true;
    }

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getLatitude(): GpsLatitude
    {
        return new GpsLatitude($this->latitude);
    }

    public function getLongitude(): GpsLongitude
    {
        return new GpsLongitude($this->longitude);
    }

    public function getProfileValue(): array|false
    {
        if(empty($this->profile_value))
        {
            return false;
        }

        if(false === json_validate($this->profile_value))
        {
            return false;
        }

        return json_decode($this->profile_value, false, 512, JSON_THROW_ON_ERROR);
    }

    public function getRegionName(): string
    {
        return $this->region_name;
    }
}