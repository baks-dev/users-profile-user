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

namespace BaksDev\Users\Profile\UserProfile\Entity\Event\Info;

use BaksDev\Core\Entity\EntityReadonly;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusModeration;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use JetBrains\PhpStorm\Deprecated;
use Symfony\Component\Validator\Constraints as Assert;

// Неизменяемые данные UserProfile

#[ORM\Entity]
#[ORM\Table(name: 'users_profile_info')]
#[ORM\Index(columns: ['usr'])]
#[ORM\Index(columns: ['status', 'active'])]
class UserProfileInfo extends EntityReadonly
{
    /**
     * ID UserProfile
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Id]
    #[ORM\Column(type: UserProfileUid::TYPE)]
    private ?UserProfileUid $profile;

    /**
     * Связь на событие
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\OneToOne(targetEntity: UserProfileEvent::class, inversedBy: 'info')]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
    private UserProfileEvent $event;

    /**
     * Пользователь, кому принадлежит профиль
     */
    #[Assert\NotBlank]
    #[Assert\Uuid]
    #[ORM\Column(type: UserUid::TYPE)]
    private UserUid $usr;

    /**
     * Персональная скидка профиля
     *
     * @deprecated Переносится в UserProfileDiscount
     */
    #[Deprecated]
    #[ORM\Column(type: Types::STRING, length: 5, nullable: true)]
    private ?string $discount = null;

    /**
     * Текущий активный профиль, выбранный пользователем
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $active = false;

    /**
     * Статус профиля (модерация, активен, заблокирован)
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: UserProfileStatus::TYPE)]
    private UserProfileStatus $status;

    /**
     * Ссылка на профиль пользователя
     */
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::STRING, unique: true)]
    private string $url;

    //public function __construct(UserProfileUid|UserProfile $profile)
    public function __construct(UserProfileEvent $event)
    {
        $this->event = $event;
        $this->profile = $event->getMain();
        $this->status = new UserProfileStatus(UserProfileStatusModeration::class);

        //$this->profile = $profile instanceof UserProfile ? $profile->getId() : $profile;
    }

    public function __toString(): string
    {
        return (string) $this->profile;
    }

    public function getProfile(): ?UserProfileUid
    {
        return $this->profile;
    }

    public function getUsr(): UserUid
    {
        return $this->usr;
    }

    /**
     * Event
     */
    public function getEvent(): ?UserProfileEvent
    {
        return $this->event;
    }

    public function setEvent(?UserProfileEvent $event): self
    {
        $this->event = $event;
        return $this;
    }


    public function isProfileOwnedUser(UserUid|User $usr): bool
    {
        $id = $usr instanceof User ? $usr->getId() : $usr;

        return $this->usr->equals($id);
    }

    public function isNotActiveProfile(): bool
    {
        return false !== $this->active;
    }

    public function isNotStatusActive(): bool
    {
        return !$this->status->equals(UserProfileStatusActive::class);
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if($dto instanceof UserProfileInfoInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if($dto instanceof UserProfileInfoInterface || $dto instanceof self)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function deactivate(): void
    {
        $this->active = false;
    }
}
