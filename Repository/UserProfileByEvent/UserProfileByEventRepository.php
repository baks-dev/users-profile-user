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

declare(strict_types=1);

namespace BaksDev\Users\Profile\UserProfile\Repository\UserProfileByEvent;

use App\Kernel;
use BaksDev\Core\Entity\EntityTestGenerator;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Users\Profile\TypeProfile\Entity\Section\Fields\Trans\TypeProfileSectionFieldTrans;
use BaksDev\Users\Profile\TypeProfile\Entity\Section\Fields\TypeProfileSectionField;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Value\UserProfileValue;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UserProfileByEventRepository implements UserProfileByEventInterface
{
    private EntityManagerInterface $entityManager;
    private TranslatorInterface $translator;

    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->translator = $translator;
    }

    public function findUserProfileEvent(UserProfileEventUid $event): ?UserProfileEvent
    {
        if(Kernel::isTestEnvironment())
        {
            return EntityTestGenerator::get(UserProfileEvent::class);
        }

        $qb = $this->entityManager->createQueryBuilder();

        $qb->select('event');
        $qb->from(UserProfileEvent::class, 'event');

        $qb->where('event.id = :event');
        $qb->setParameter('event', $event, UserProfileEventUid::TYPE);

        return $qb->getQuery()->getOneOrNullResult();
    }




    public function fetchUserProfileAssociative(UserProfileEventUid $event): ?array
    {
        $qb = $this->entityManager->getConnection()->createQueryBuilder();

        $qb->select('event.sort');
        $qb->from(UserProfileEvent::TABLE, 'event');

        $qb->addSelect('value.value AS profile_value');
        $qb->leftJoin('event', UserProfileValue::TABLE, 'value', 'value.event = event.id');

        $qb->addSelect('type.type AS profile_type');
        $qb->join('value', TypeProfileSectionField::TABLE, 'type', 'type.id = value.field AND type.card = true');
        
        $qb->addSelect('type_trans.name');
        $qb->leftJoin(
            'type',
            TypeProfileSectionFieldTrans::TABLE,
            'type_trans',
            'type_trans.field = type.id AND type_trans.local = :local'
        );

        $qb->setParameter('local', new Locale($this->translator->getLocale()), Locale::TYPE);

        $qb->where('event.id = :event');
        $qb->setParameter('event', $event, UserProfileEventUid::TYPE);

        return $qb->fetchAllAssociative();
    }
}
