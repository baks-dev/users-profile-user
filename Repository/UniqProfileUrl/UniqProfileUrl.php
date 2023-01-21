<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Users\Profile\UserProfile\Repository\UniqProfileUrl;

use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Doctrine\DBAL\Connection;

final class UniqProfileUrl implements UniqProfileUrlInterface
{
    private Connection $connection;
	
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    public function exist(string $url, UserProfileUid $profile) : bool
    {
        $qbSub = $this->connection->createQueryBuilder();
        $qbSub->select('1');
        $qbSub->from(UserProfileInfo::TABLE, 'info');
        $qbSub->where('info.url = :url');
        $qbSub->andWhere('info.profile != :profile');
		
        $qb = $this->connection->createQueryBuilder();
        $qb->select('EXISTS(' . $qbSub->getSQL() . ')');
        $qb->setParameter('url', $url);
        $qb->setParameter('profile', $profile);

        return (bool) $qb->executeQuery()->fetchOne();
    }
    
}