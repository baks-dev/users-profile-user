<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Core\Entity\EntityTestGenerator;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Users\Profile\TypeProfile\Entity\Section\Fields\Trans\TypeProfileSectionFieldTrans;
use BaksDev\Users\Profile\TypeProfile\Entity\Section\Fields\TypeProfileSectionField;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Value\UserProfileValue;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UserProfileByEventRepository implements UserProfileByEventInterface
{
    private ORMQueryBuilder $ORMQueryBuilder;
    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(
        ORMQueryBuilder $ORMQueryBuilder,
        DBALQueryBuilder $DBALQueryBuilder,
    )
    {
        $this->ORMQueryBuilder = $ORMQueryBuilder;
        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    public function findUserProfileEvent(UserProfileEventUid|string $event): ?UserProfileEvent
    {
        if(Kernel::isTestEnvironment())
        {
            return EntityTestGenerator::get(UserProfileEvent::class);
        }

        if(is_string($event))
        {
            $event = new UserProfileEventUid($event);
        }

        $orm = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $orm
            ->select('event')
            ->from(UserProfileEvent::class, 'event')
            ->where('event.id = :event')
            ->setParameter('event', $event, UserProfileEventUid::TYPE);

        return $orm->getOneOrNullResult();
    }


    public function fetchUserProfileAssociative(UserProfileEventUid|string $event): ?array
    {
        if(is_string($event))
        {
            $event = new UserProfileEventUid($event);
        }

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('event.sort')
            ->from(UserProfileEvent::class, 'event')
            ->where('event.id = :event')
            ->setParameter('event', $event, UserProfileEventUid::TYPE);


        $dbal->addSelect('user_profile_value.value AS profile_value');

        $dbal->leftJoin(
            'event',
            UserProfileValue::class,
            'user_profile_value',
            'user_profile_value.event = event.id',
        );

        $dbal
            ->addSelect('user_profile_field.type AS profile_type')
            ->join(
                'user_profile_value',
                TypeProfileSectionField::class,
                'user_profile_field',
                'user_profile_field.id = user_profile_value.field AND user_profile_field.card = true',
            );


        $dbal
            ->addSelect('user_profile_field_trans.name')
            ->leftJoin(
                'user_profile_field',
                TypeProfileSectionFieldTrans::class,
                'user_profile_field_trans',
                '
                user_profile_field_trans.field = user_profile_field.id 
                AND user_profile_field_trans.local = :local',
            );

        return $dbal
            ->enableCache('users-profile-user')
            ->fetchAllAssociative();
    }
}
