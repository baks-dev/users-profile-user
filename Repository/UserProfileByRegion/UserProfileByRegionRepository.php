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

namespace BaksDev\Users\Profile\UserProfile\Repository\UserProfileByRegion;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Reference\Region\Entity\Region;
use BaksDev\Reference\Region\Entity\Trans\RegionTrans;
use BaksDev\Reference\Region\Type\Id\RegionType;
use BaksDev\Reference\Region\Type\Id\RegionUid;
use BaksDev\Reference\Region\Type\Regions\Russia\Crimea;
use BaksDev\Reference\Region\Type\Regions\Russia\Moscow;
use BaksDev\Users\Profile\TypeProfile\Entity\Section\Fields\Trans\TypeProfileSectionFieldTrans;
use BaksDev\Users\Profile\TypeProfile\Entity\Section\Fields\TypeProfileSectionField;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Orders\UserProfileOrders;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Region\UserProfileRegion;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Shop\UserProfileShop;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Value\UserProfileValue;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Warehouse\UserProfileWarehouse;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use Generator;
use Symfony\Component\DependencyInjection\Attribute\Autowire;


final  class UserProfileByRegionRepository implements UserProfileByRegionInterface
{

    private bool $onlyCurrentRegion = false;

    private bool $onlyOrders = false;

    public function __construct(
        readonly private DBALQueryBuilder $DBALQueryBuilder,
        #[Autowire(env: 'PROJECT_REGION')] private readonly ?string $region = null,
    ) {}

    /** Только список указанного в .env регион проекта PROJECT_REGION */
    public function onlyCurrentRegion(): self
    {
        $this->onlyCurrentRegion = true;
        return $this;
    }

    /** Только с флагом ПВЗ */
    public function onlyOrders(): self
    {
        $this->onlyOrders = true;
        return $this;
    }

    /**
     * Метод возвращает все профили пользователей с указанной региональностью
     *
     * @return Generator<int, UserProfileByRegionResult>|false
     */
    public function findAll(): Generator|false
    {
        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->select('profile.id')
            ->from(UserProfile::class, 'profile');

        $dbal
            ->join(
                'profile',
                UserProfileInfo::class,
                'info',
                'info.profile = profile.id AND info.status = :status',
            )
            ->setParameter(
                'status',
                UserProfileStatusActive::class,
                UserProfileStatus::TYPE,
            );

        $dbal
            ->leftJoin(
                'profile',
                UserProfileEvent::class,
                'event',
                'event.id = profile.event',
            );


        $dbal
            ->addSelect('profile_region.value AS region')
            ->join(
                'profile',
                UserProfileRegion::class,
                'profile_region',
                'profile_region.event = profile.event',
            );

        $dbal
            ->addSelect('profile_shop.value AS shop')
            ->leftJoin(
                'profile',
                UserProfileShop::class,
                'profile_shop',
                'profile_shop.event = profile.event',
            );

        $dbal
            ->addSelect('profile_orders.value AS orders')
            ->{(true === $this->onlyOrders ? 'join' : 'leftJoin')}(
                'profile',
                UserProfileOrders::class,
                'profile_orders',
                'profile_orders.event = profile.event',
            );

        $dbal
            ->addSelect('profile_warehouse.value AS warehouse')
            ->leftJoin(
                'profile',
                UserProfileWarehouse::class,
                'profile_warehouse',
                'profile_warehouse.event = profile.event',
            );

        /** Регион */

        $dbal
            ->leftJoin(
                'profile_region',
                Region::class,
                'region',
                'region.id = profile_region.value',
            );

        $dbal
            ->addSelect('region_trans.name AS region_name')
            ->leftJoin(
                'region',
                RegionTrans::class,
                'region_trans',
                'region_trans.event = region.event',
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
            //->addSelect('user_profile_value.value AS profile_value')
            ->leftJoin(
                'profile',
                UserProfileValue::class,
                'user_profile_value',
                'user_profile_value.event = profile.event',
            );

        $dbal
            ->join(
                'user_profile_value',
                TypeProfileSectionField::class,
                'user_profile_field',
                '
                    user_profile_field.id = user_profile_value.field 
                    AND user_profile_field.card = true
                ',
            );

        $dbal
            ->leftJoin(
                'user_profile_field',
                TypeProfileSectionFieldTrans::class,
                'user_profile_field_trans',
                '
                    user_profile_field_trans.field = user_profile_field.id 
                    AND user_profile_field_trans.local = :local
                ',
            );

        $dbal->addSelect(
            "JSON_AGG ( DISTINCT

                JSONB_BUILD_OBJECT
                (
                    'value', user_profile_value.value,
                    'name', user_profile_field_trans.name,
                    'type', user_profile_field.type
                )

            ) AS profile_value",
        );


        if(false === is_null($this->region))
        {
            if(false === $this->onlyCurrentRegion)
            {
                $dbal->orderBy('CASE WHEN profile_region.value = :region THEN 0 ELSE 1 END');
            }
            else
            {
                $dbal->where('profile_region.value = :region');
            }

            $dbal
                ->setParameter(
                    key: 'region',
                    value: new RegionUid($this->region),
                    type: RegionUid::TYPE,
                );
        }

        $dbal->addOrderBy('profile_region.value');
        $dbal->addOrderBy('event.sort');

        // CASE WHEN region = 'EU' THEN 0 ELSE 1 END,
        $dbal->addGroupBy('event.sort');
        $dbal->allGroupByExclude();

        return $dbal
            ->enableCache('users-profile-user', '1 day')
            ->fetchAllHydrate(UserProfileByRegionResult::class);
    }
}