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

namespace BaksDev\Users\Profile\UserProfile\Repository\UserProfileById;

use App\Kernel;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Entity\EntityTestGenerator;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Users\Profile\TypeProfile\Entity\Section\Fields\Trans\TypeProfileSectionFieldTrans;
use BaksDev\Users\Profile\TypeProfile\Entity\Section\Fields\TypeProfileSectionField;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Entity\Value\UserProfileValue;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UserProfileById implements UserProfileByIdInterface
{

    private DBALQueryBuilder $DBALQueryBuilder;

    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder
    )
    {

        $this->DBALQueryBuilder = $DBALQueryBuilder;
    }

    public function fetchUserProfileAssociative(UserProfileUid $profile): ?array
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        //$qb->select('event.sort');
        $dbal
            ->from(UserProfile::class, 'main')
            ->where('main.id = :profile')
            ->setParameter('profile', $profile, UserProfileUid::TYPE);


        $dbal
            ->addSelect('value.value AS profile_value')
            ->leftJoin(
                'main',
                UserProfileValue::class,
                'value',
                'value.event = main.event'
            );

        $dbal
            ->addSelect('type.type AS profile_type')
            ->join(
                'value',
                TypeProfileSectionField::class,
                'type',
                'type.id = value.field AND type.card = true'
            );

        $dbal
            ->addSelect('type_trans.name')
            ->leftJoin(
                'type',
                TypeProfileSectionFieldTrans::TABLE,
                'type_trans',
                'type_trans.field = type.id AND type_trans.local = :local'
            );


        return $dbal->fetchAllAssociative();
    }
}
