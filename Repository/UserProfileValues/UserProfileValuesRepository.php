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

namespace BaksDev\Users\Profile\UserProfile\Repository\UserProfileValues;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Type\Field\InputField;
use BaksDev\Users\Profile\TypeProfile\Entity\Section\Fields\TypeProfileSectionField;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Value\UserProfileValue;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use InvalidArgumentException;


final class UserProfileValuesRepository implements UserProfileValuesInterface
{
    private InputField|false $field = false;

    private UserProfileEventUid|false $event = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
    ) {}

    public function forFieldType(InputField|string $field): self
    {
        if(false === ($field instanceof InputField))
        {
            $field = new InputField($field);
        }

        $this->field = $field;

        return $this;
    }

    public function forUserProfileEvent(UserProfileEvent|UserProfileEventUid $event): self
    {
        if($event instanceof UserProfileEvent)
        {
            $event = $event->getId();
        }

        $this->event = $event;

        return $this;
    }

    /**
     * Метод получает указанное свойство профиля
     */
    public function find(): UserProfileValuesResult|false
    {
        if(false === $this->event instanceof UserProfileEventUid)
        {
            throw new InvalidArgumentException('Invalid Argument UserProfileEvent');
        }

        if(false === ($this->field instanceof InputField))
        {
            throw new InvalidArgumentException('Type field not found');
        }


        $dbal = $this
            ->DBALQueryBuilder
            ->createQueryBuilder(self::class);

        $dbal
            ->select('values.event')
            ->addSelect('values.value')
            ->from(UserProfileValue::class, 'values')
            ->where('values.event = :event')
            ->setParameter(
                key: 'event',
                value: $this->event,
                type: UserProfileEventUid::TYPE,
            );

        $dbal
            ->addSelect('field.type')
            ->join(
                'values',
                TypeProfileSectionField::class,
                'field',
                'field.id = values.field AND field.type = :field_type',
            )
            ->setParameter(
                key: 'field_type',
                value: $this->field,
                type: InputField::TYPE,
            );

        return $dbal->fetchHydrate(UserProfileValuesResult::class);
    }
}