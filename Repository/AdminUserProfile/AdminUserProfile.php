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

namespace BaksDev\Users\Profile\UserProfile\Repository\AdminUserProfile;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\UserProfile\Entity as UserProfileEntity;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatus;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatusEnum;
use Symfony\Component\DependencyInjection\Attribute\Autowire;

final class AdminUserProfile implements AdminUserProfileInterface
{
    private DBALQueryBuilder $DBALQueryBuilder;
    private string $HOST;


    public function __construct(
        #[Autowire(env: 'HOST')] string $HOST,
        DBALQueryBuilder $DBALQueryBuilder
    )
    {
        $this->DBALQueryBuilder = $DBALQueryBuilder;
        $this->HOST = $HOST;
    }


    /**
     * Возвращает идентификатор активного профиля пользователя администратора
     */
    public function fetchUserProfile(): ?UserProfileUid
    {
        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $qb->from(Account::TABLE, 'account');

        $qb->join(
            'account',
            AccountEvent::TABLE,
            'users_event',
            '
                users_event.id = account.event AND
                users_event.email = :email
            '
        )
            ->setParameter('email', new AccountEmail('admin@'.$this->HOST), AccountEmail::TYPE);


        $qb->join(
            'users_event',
            UserProfileEntity\Info\UserProfileInfo::TABLE,
            'profile_info',
            'profile_info.usr = users_event.account AND 
            profile_info.status = :profile_status AND 
            profile_info.active = true'
        )
            ->setParameter('profile_status', new UserProfileStatus(UserProfileStatusEnum::ACTIVE), UserProfileStatus::TYPE);


        $qb->addSelect('profile.id'); /* ID профиля */

        $qb->join(
            'profile_info',
            UserProfileEntity\UserProfile::TABLE,
            'profile',
            'profile.id = profile_info.profile'
        );


        $profile = $qb->fetchOne();

        /* Кешируем результат DBAL */
        return $profile ? new UserProfileUid($profile) : null;
    }

}