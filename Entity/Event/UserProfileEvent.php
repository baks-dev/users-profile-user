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

namespace BaksDev\Users\Profile\UserProfile\Entity\Event;

use App\Kernel;
use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Type\Modify\Modify\ModifyActionNew;
use BaksDev\Core\Type\Modify\Modify\ModifyActionUpdate;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use BaksDev\Users\Profile\UserProfile\Entity\Avatar\UserProfileAvatar;
use BaksDev\Users\Profile\UserProfile\Entity\Discount\UserProfileDiscount;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Modify\UserProfileModify;
use BaksDev\Users\Profile\UserProfile\Entity\Orders\UserProfileOrders;
use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\Shop\UserProfileShop;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Entity\Value\UserProfileValue;
use BaksDev\Users\Profile\UserProfile\Entity\Warehouse\UserProfileWarehouse;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\User\Type\Id\UserUid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/* События UserProfile */


#[ORM\Entity]
#[ORM\Table(name: 'users_profile_event')]
#[ORM\Index(columns: ['profile'])]
#[ORM\Index(columns: ['type'])]
class UserProfileEvent extends EntityEvent
{
    /**
     * ID
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: UserProfileEventUid::TYPE)]
    private UserProfileEventUid $id;

    /**
     * ID профиля пользователя
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: UserProfileUid::TYPE)]
    private ?UserProfileUid $profile = null;

    /**
     * Тип профиля
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: TypeProfileUid::TYPE)]
    private TypeProfileUid $type;

    /**
     * Сортировка
     */
    #[Assert\NotBlank]
    #[Assert\Length(max: 3)]
    #[Assert\Range(max: 999)]
    #[ORM\Column(type: Types::SMALLINT, options: ['default' => 500])]
    private int $sort = 500;

    /**
     * Аватарка профиля
     */
    #[Assert\Valid]
    #[ORM\OneToOne(targetEntity: UserProfileAvatar::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private ?UserProfileAvatar $avatar = null;

    /**
     * Персональные данные
     */
    #[Assert\Valid]
    #[ORM\OneToOne(targetEntity: UserProfilePersonal::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private ?UserProfilePersonal $personal = null;

    /**
     * Значения профиля
     */
    #[Assert\Valid]
    #[ORM\OneToMany(targetEntity: UserProfileValue::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private Collection $value;

    /**
     * Модификатор
     */
    #[ORM\OneToOne(targetEntity: UserProfileModify::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private UserProfileModify $modify;

    /**
     * Персональные данные
     */
    #[Assert\Valid]
    #[ORM\OneToOne(targetEntity: UserProfileInfo::class, mappedBy: 'event', cascade: ['all'], fetch: 'EAGER')]
    private ?UserProfileInfo $info = null;


    /** Флаг, означающий, что профиль пользователя является магазином */
    #[ORM\OneToOne(targetEntity: UserProfileShop::class, mappedBy: 'event', cascade: ['all'])]
    private ?UserProfileShop $shop = null;

    /** Флаг, означающий, что профиль пользователя является ПВЗ */
    #[ORM\OneToOne(targetEntity: UserProfileOrders::class, mappedBy: 'event', cascade: ['all'])]
    private ?UserProfileOrders $orders = null;

    /** Флаг, означающий, что профиль пользователя является Складом */
    #[ORM\OneToOne(targetEntity: UserProfileWarehouse::class, mappedBy: 'event', cascade: ['all'])]
    private ?UserProfileWarehouse $warehouse = null;

    /** Персональная скидка профиля */
    #[ORM\OneToOne(targetEntity: UserProfileDiscount::class, mappedBy: 'event', cascade: ['all'])]
    private ?UserProfileDiscount $discount = null;


    public function __construct()
    {
        $this->id = new UserProfileEventUid();
        $this->modify = new UserProfileModify($this);
        $this->value = new ArrayCollection();
    }

    public function __clone()
    {
        $this->id = clone $this->id;
    }

    public function __toString(): string
    {
        return (string) $this->id;
    }

    public function getId(): UserProfileEventUid
    {
        return $this->id;
    }

    public function getUser(): UserUid
    {
        return $this->info->getUsr();
    }

    public function getMain(): ?UserProfileUid
    {
        return $this->profile;
    }


    public function getType(): TypeProfileUid
    {
        return $this->type;
    }

    public function setMain(UserProfileUid|UserProfile $profile): void
    {
        $this->profile = $profile instanceof UserProfile ? $profile->getId() : $profile;
    }


    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof UserProfileEventInterface || $dto instanceof self)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if($dto instanceof UserProfileEventInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }


    //	public function isModifyActionEquals(ModifyActionEnum $action) : bool
    //	{
    //		return $this->modify->equals($action);
    //	}


    public function getUploadAvatar(): UserProfileAvatar
    {
        return $this->avatar ?: $this->avatar = new UserProfileAvatar($this);
    }


    public function getNameUserProfile(): ?string
    {
        return $this->personal->name();
    }

}