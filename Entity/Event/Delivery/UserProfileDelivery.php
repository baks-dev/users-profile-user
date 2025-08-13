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

namespace BaksDev\Users\Profile\UserProfile\Entity\Event\Delivery;


use BaksDev\Core\Entity\EntityEvent;
use BaksDev\Core\Entity\EntityState;
use BaksDev\Files\Resources\Upload\UploadEntityInterface;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use DateTimeImmutable;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use InvalidArgumentException;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * UserProfileDelivery
 *
 * @see UserProfileEvent
 */
#[ORM\Entity]
#[ORM\Table(name: 'user_profile_delivery')]
class UserProfileDelivery extends EntityEvent
{
    /** Связь на событие */
    #[Assert\NotBlank]
    #[ORM\Id]
    #[ORM\OneToOne(targetEntity: UserProfileEvent::class, inversedBy: 'delivery')]
    #[ORM\JoinColumn(name: 'event', referencedColumnName: 'id')]
    private UserProfileEvent $event;

    /** Дата предыдущей поставки */
    #[ORM\Column(name: 'value', type: Types::DATETIME_IMMUTABLE, nullable: false)]
    private DateTimeImmutable $value;

    /** Периодичность поставок */
    #[Assert\NotBlank]
    #[ORM\Column(type: Types::SMALLINT)]
    private int $day;

    public function __construct(UserProfileEvent $event)
    {
        $this->event = $event;
    }

    public function getEventUid(): UserProfileEventUid
    {
        return $this->event->getId();
    }

    public function __toString(): string
    {
        return (string) $this->event;
    }

    public function getValue(): DateTimeImmutable
    {
        return $this->value;
    }

    public function updateDeliveryDate(): void
    {
        $this->value = new DateTimeImmutable();
    }

    public function getDay(): int
    {
        return max($this->day, 1);
    }

    /** @return UserProfileDeliveryInterface */
    public function getDto($dto): mixed
    {
        if(is_string($dto) && class_exists($dto))
        {
            $dto = new $dto();
        }

        if($dto instanceof UserProfileDeliveryInterface)
        {
            return parent::getDto($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }

    /** @var UserProfileDeliveryInterface $dto */
    public function setEntity($dto): mixed
    {
        if($dto instanceof UserProfileDeliveryInterface)
        {
            return parent::setEntity($dto);
        }

        throw new InvalidArgumentException(sprintf('Class %s interface error', $dto::class));
    }
}