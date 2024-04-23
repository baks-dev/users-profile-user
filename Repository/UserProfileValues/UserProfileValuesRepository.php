<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Users\Profile\UserProfile\Repository\UserProfileValues;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Type\Field\InputField;
use BaksDev\Users\Profile\TypeProfile\Entity\Section\Fields\TypeProfileSectionField;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Value\UserProfileValue;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use InvalidArgumentException;


final class UserProfileValuesRepository implements UserProfileValuesInterface
{
    private DBALQueryBuilder $DBALQueryBuilder;

    private ?InputField $field = null;

    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
    )
    {
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    public function field(InputField $field): self
    {
        $this->field = $field;
        return $this;
    }

    private function builder(UserProfileEvent|UserProfileEventUid|string $event): DBALQueryBuilder
    {
        if($event instanceof UserProfileEvent)
        {
            $event = $event->getId();
        }

        if(is_string($event))
        {
            $event = new UserProfileEventUid($event);
        }

        $dbal = $this
            ->DBALQueryBuilder
            ->createQueryBuilder(self::class);

        $dbal
            ->select('values.event')
            ->addSelect('values.value')
            ->from(UserProfileValue::class, 'values')
            ->where('values.event = :event')
            ->setParameter('event', $event, UserProfileEventUid::TYPE);

        return $dbal;
    }


    /**
     * Метод получает все заполненные динамические свойства профиля
     */
    public function findAllByEvent(UserProfileEvent|UserProfileEventUid|string $event): array|bool
    {
        if(!$this->field)
        {
            throw new InvalidArgumentException('Type field not found');
        }

        $dbal = $this->builder($event);


        $dbal
            ->addSelect('field.type')
            ->leftJoin(
                'values',
                TypeProfileSectionField::class,
                'field',
                'field.id = values.field'
            );

        return $dbal->fetchAllAssociative();
    }

    /**
     * Метод получает указанное свойство профиля
     */
    public function findFieldByEvent(UserProfileEvent|UserProfileEventUid|string $event): array|bool
    {
        if(!$this->field)
        {
            throw new InvalidArgumentException('Type field not found');
        }

        $dbal = $this->builder($event);

        $dbal
            ->addSelect('field.type')
            ->join(
                'values',
                TypeProfileSectionField::class,
                'field',
                'field.id = values.field AND field.type = :field_type'
            )
            ->setParameter('field_type', $this->field, InputField::TYPE);

        return $dbal->fetchAssociative();
    }
}