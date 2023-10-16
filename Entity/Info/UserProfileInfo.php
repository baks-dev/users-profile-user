<?php
/*
 *  Copyright 2023.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Users\Profile\UserProfile\Entity\Info;

use BaksDev\Core\Entity\EntityState;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatus;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatusEnum;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;

// Неизменяемые данные UserProfile

#[ORM\Entity]
#[ORM\Table(name: 'users_profile_info')]
#[ORM\Index(columns: ['usr'])]
#[ORM\Index(columns: ['status', 'active'])]
class UserProfileInfo extends EntityState
{
    public const TABLE = 'users_profile_info';

    /**
     * ID UserProfile
     */
    #[ORM\Id]
    #[ORM\Column(type: UserProfileUid::TYPE)]
    private ?UserProfileUid $profile;

    /**
     * Пользователь, кому принадлежит профиль
     */
    #[ORM\Column(type: UserUid::TYPE)]
    private UserUid $usr;

    /**
     * Персональная скидка профиля
     */
    #[ORM\Column(type: Types::SMALLINT, nullable: true)]
    private ?int $discount = null;

    /**
     * Текущий активный профиль, выбранный пользователем
     */
    #[ORM\Column(type: Types::BOOLEAN)]
    private bool $active = false;

    /**
     * Статус профиля (модерация, активен, заблокирован)
     */
    #[ORM\Column(type: UserProfileStatus::TYPE)]
    private UserProfileStatus $status;

    /**
     * Ссылка на профиль пользователя
     */
    #[ORM\Column(type: Types::STRING, unique: true)]
    private string $url;

    public function __construct(UserProfileUid|UserProfile $profile)
    {
        $this->profile = $profile instanceof UserProfile ? $profile->getId() : $profile;
        $this->status = new UserProfileStatus(UserProfileStatusEnum::MODERATION);
    }

    public function __toString(): string
    {
        return (string) $this->profile;
    }

    public function getProfile(): ?UserProfileUid
    {
        return $this->profile;
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
        return !$this->status->equals(UserProfileStatusEnum::ACTIVE);
    }

    public function getDto($dto): mixed
    {
        $dto = is_string($dto) && class_exists($dto) ? new $dto() : $dto;

        if ($dto instanceof UserProfileInfoInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    public function setEntity($dto): mixed
    {
        if ($dto instanceof UserProfileInfoInterface || $dto instanceof self)
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
