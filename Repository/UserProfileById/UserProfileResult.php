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

namespace BaksDev\Users\Profile\UserProfile\Repository\UserProfileById;

use BaksDev\Core\Type\Gps\GpsLatitude;
use BaksDev\Core\Type\Gps\GpsLongitude;
use BaksDev\Field\Pack\Contact\Type\ContactField;
use BaksDev\Field\Pack\Organization\Type\OrganizationField;
use BaksDev\Field\Pack\Phone\Type\PhoneField;
use Symfony\Component\Validator\Constraints as Assert;

final class UserProfileResult
{
    private array|null|false $profile_value_decode = null;

    public function __construct(
        private readonly ?string $location,
        //" => "Московская область, городской округ Химки, деревня Поярково, улица Лесная Цесарка, 8"
        private readonly ?string $latitude, //" => "56.030678"
        private readonly ?string $longitude, //" => "37.301518"
        private readonly ?string $profile_value, //" => "[{"name": "BIK банка", "type": "input_field", "value": "044525104"}, {"name": "section.requisite.field.invoice.name", "type": "invoice_field", "value": "40702810401500109737"}, {"name": "section.requisite.field.kpp.name", "type": "kpp_field", "value": "2030199826"}, {"name": "ИНН", "type": "inn_field", "value": "3.2469000000993E+14"}, {"name": "Контактное лицо", "type": "input_field", "value": "Васин Станислав Евгеньевич"}, {"name": "Контактный E-mail", "type": "account_email", "value": "info@white-sign.ru"}, {"name": "Контактный телефон", "type": "phone_field", "value": "+7 (800) 555-03-64"}, {"name": "Корреспондентский счёт", "type": "input_field", "value": "40702810401500109737"}, {"name": "Название организации", "type": "input_field", "value": "ООО \"Рога и копыта\""}, {"name": "Юр. адрес", "type": "address_field", "value": "Московская область, городской округ Химки, деревня Поярково, улица Лесная Цесарка, 8"}]"
    ) {}

    public function getLocation(): string
    {
        return $this->location;
    }

    public function getLatitude(): GpsLatitude
    {
        return $this->latitude ? new GpsLatitude($this->latitude) : false;
    }

    public function getLongitude(): GpsLongitude
    {
        return $this->longitude ? new GpsLongitude($this->longitude) : false;
    }

    public function getProfileValue(): array|false
    {
        if(is_null($this->profile_value_decode))
        {
            if(empty($this->profile_value))
            {
                $this->profile_value_decode = false;
                return false;
            }

            if(false === json_validate($this->profile_value))
            {
                $this->profile_value_decode = false;
                return false;
            }

            $this->profile_value_decode = json_decode($this->profile_value, false, 512, JSON_THROW_ON_ERROR);

        }

        return $this->profile_value_decode;
    }

    public function isOrganization(): bool
    {
        return true;
    }

    /**
     * @return object{
     *    name: string,
     *    type: string,
     *    value: string
     *  }|false
     */
    public function getContactName(): object|false
    {
        if(empty($this->getProfileValue()))
        {
            return false;
        }

        return array_find($this->getProfileValue(), function($element) {
            return $element->type === ContactField::TYPE;
        }) ?: false;
    }

    /**
     * @return object{
     *    name: string,
     *    type: string,
     *    value: string
     *  }|false
     */
    public function getOrganizationName(): object|false
    {
        if(empty($this->getProfileValue()))
        {
            return false;
        }

        return array_find($this->getProfileValue(), function($element) {
            return $element->type === OrganizationField::TYPE;
        }) ?: false;
    }

    /**
     * @return object{
     *    name: string,
     *    type: string,
     *    value: string
     *  }|false
     */
    public function getContactPhone(): object|false
    {
        if(empty($this->getProfileValue()))
        {
            return false;
        }

        return array_find($this->getProfileValue(), function($element) {
            return $element->type === PhoneField::TYPE;
        }) ?: false;
    }

}