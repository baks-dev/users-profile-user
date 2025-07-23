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

namespace BaksDev\Users\Profile\UserProfile\Repository\UserProfileById;

use App\Kernel;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\TypeProfile\Entity\Section\Fields\Trans\TypeProfileSectionFieldTrans;
use BaksDev\Users\Profile\TypeProfile\Entity\Section\Fields\TypeProfileSectionField;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Value\UserProfileValue;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileTokenStorage\UserProfileTokenStorageInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

final class UserProfileByIdRepository implements UserProfileByIdInterface
{

    private UserProfileUid|false $profile = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder,
        private readonly UserProfileTokenStorageInterface $UserProfileTokenStorage,
    ) {}


    public function profile(UserProfile|UserProfileUid $profile): self
    {
        if($profile instanceof UserProfile)
        {
            $profile = $profile->getId();
        }

        $this->profile = $profile;

        return $this;
    }

    /** Метод возвращает информацию о профиле пользователя. */
    public function find(): UserProfileResult|false
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        //$qb->select('event.sort');
        $dbal
            ->from(UserProfile::class, 'profile')
            ->where('profile.id = :profile')
            ->setParameter(
                key: 'profile',
                value: ($this->profile instanceof UserProfileUid) ? $this->profile : $this->UserProfileTokenStorage->getProfile(),
                type: UserProfileUid::TYPE,
            );


        /** Информация о профиле пользователя */

        $dbal
            ->addSelect('users_profile_personal.location')
            ->addSelect('users_profile_personal.latitude')
            ->addSelect('users_profile_personal.longitude')
            ->leftJoin(
                'profile',
                UserProfilePersonal::class,
                'users_profile_personal',
                'users_profile_personal.event = profile.event',
            );


        $dbal
            ->leftJoin(
                'profile',
                UserProfileValue::class,
                'profile_value',
                'profile_value.event = profile.event',
            );

        $dbal
            ->join(
                'profile_value',
                TypeProfileSectionField::class,
                'profile_field',
                'profile_field.id = profile_value.field AND profile_field.card = true',
            );

        $dbal
            ->leftJoin(
                'profile_field',
                TypeProfileSectionFieldTrans::class,
                'type_trans',
                'type_trans.field = profile_field.id AND type_trans.local = :local',
            );


        $dbal->addSelect(
            "JSON_AGG ( DISTINCT

                JSONB_BUILD_OBJECT
                (
                    'value', profile_value.value,
                    'type', profile_field.type,
                    'name', type_trans.name
                )

            ) AS profile_value",
        );

        $dbal->allGroupByExclude();

        return $dbal->fetchHydrate(UserProfileResult::class);
    }
}
