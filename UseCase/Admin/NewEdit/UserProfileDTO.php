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

namespace BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit;

use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use BaksDev\Users\Profile\UserProfile\Entity\Avatar\UserProfileAvatarInterface;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEventInterface;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Avatar\AvatarDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Discount\NewEditUserProfileDiscountDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Info\InfoDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Orders\NewEditUserProfileOrdersDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Personal\PersonalDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Region\UserProfileRegionDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Shop\NewEditUserProfileShopDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Value\ValueDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Warehouse\NewEditUserProfileWarehouseDTO;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Validator\Constraints as Assert;

/** @see UserProfileEvent */
final class UserProfileDTO implements UserProfileEventInterface
{

    /** Идентификатор события */
    #[Assert\Uuid]
    private ?UserProfileEventUid $id = null;

    /** Тип профиля */
    private TypeProfileUid $type;

    /** Аватарка профиля */
    #[Assert\Valid]
    protected ?AvatarDTO $avatar;

    /** Постоянная информация профиля */
    #[Assert\Valid]
    private InfoDTO $info;

    /** Сортировка */
    #[Assert\NotBlank]
    #[Assert\Length(max: 3)]
    #[Assert\Range(max: 999)]
    private int $sort = 500;

    /** Персональные данные */
    #[Assert\Valid]
    private PersonalDTO $personal;

    /** Значения профиля */
    #[Assert\Valid]
    private ArrayCollection $value;


    /** Флаг, означающий, что профиль пользователя является магазином */
    private NewEditUserProfileShopDTO $shop;

    /** Флаг, означающий, что профиль пользователя является ПВЗ */
    private NewEditUserProfileOrdersDTO $orders;

    /** Флаг, означающий, что профиль пользователя является Складом */
    private NewEditUserProfileWarehouseDTO $warehouse;

    /** Персональная скидка профиля */
    private NewEditUserProfileDiscountDTO $discount;

    /** Регион пользователя */
    private UserProfileRegionDTO $region;


    public function __construct()
    {
        $this->avatar = new AvatarDTO();
        $this->info = new InfoDTO();
        $this->personal = new PersonalDTO();
        $this->value = new ArrayCollection();

        $this->shop = new NewEditUserProfileShopDTO();
        $this->orders = new NewEditUserProfileOrdersDTO();
        $this->warehouse = new NewEditUserProfileWarehouseDTO();
        $this->discount = new NewEditUserProfileDiscountDTO();
        $this->region = new UserProfileRegionDTO();
    }


    /** Идентификатор события */

    public function getEvent(): ?UserProfileEventUid
    {
        return $this->id;
    }


    public function setId(UserProfileEventUid $id): void
    {
        $this->id = $id;
    }


    /** Тип профиля */

    public function getType(): TypeProfileUid
    {
        return $this->type;
    }


    public function setType(TypeProfileUid $type): void
    {
        $this->type = $type;
    }


    /** Постоянная информация профиля */

    public function getInfo(): InfoDTO
    {
        return $this->info;
    }


    public function setInfo(InfoDTO $info): void
    {
        $this->info = $info;
    }


    /** Сортировка */

    public function getSort(): int
    {
        return $this->sort;
    }


    public function setSort(int $sort): void
    {
        $this->sort = $sort;
    }


    /** Персональные данные */

    public function getPersonal(): PersonalDTO
    {
        return $this->personal;
    }


    public function setPersonal(PersonalDTO $personal): void
    {
        $this->personal = $personal;
    }


    /** Аватарка профиля */

    public function getAvatar(): UserProfileAvatarInterface
    {
        return $this->avatar ?: new Avatar\AvatarDTO();
    }


    public function setAvatar(?AvatarDTO $avatar): void
    {
        $this->avatar = $avatar;
    }


    /** Значения профиля */

    public function getValue(): ArrayCollection
    {
        return $this->value;
    }


    public function addValue(ValueDTO $value): void
    {
        $filter = $this->value->filter(function(ValueDTO $element) use ($value) {
            return $element->getField()->equals($value->getField());
        });

        if($filter->isEmpty())
        {
            $this->value->add($value);
            return;
        }

        /** @var ValueDTO $ValueDTO */
        $ValueDTO = $filter->current();
        $ValueDTO->setValue($value->getValue());
    }


    public function removeValue(ValueDTO $value): void
    {
        $this->value->removeElement($value);
    }

    public function getShop(): NewEditUserProfileShopDTO
    {
        return $this->shop;
    }

    public function getOrders(): NewEditUserProfileOrdersDTO
    {
        return $this->orders;
    }

    public function getWarehouse(): NewEditUserProfileWarehouseDTO
    {
        return $this->warehouse;
    }

    public function getDiscount(): NewEditUserProfileDiscountDTO
    {
        return $this->discount;
    }

    public function getRegion(): UserProfileRegionDTO
    {
        return $this->region;
    }
}