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

namespace BaksDev\Users\Profile\UserProfile\Repository\UsersChoiceForm;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Entity\Status\AccountStatus;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusActive;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
use Doctrine\ORM\EntityManagerInterface;
use Generator;

final class UsersChoiceOptionalAccountEmailRepository implements UsersChoiceOptionalAccountEmailInterface
{

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}


    /** Список объектов UserUid аккаунтов с опциональным Account Email  */

    public function getChoice(): Generator|false
    {
        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal->from(User::class, 'users');

        $dbal->join(
            'users',
            Account::class,
            'account',
            'account.id = users.usr',
        );

        $dbal
            ->join(
                'account',
                AccountStatus::class,
                'account_status',
                '
                account_status.event = account.event 
                AND account_status.status = :status
            ')
            ->setParameter(
                'status',
                EmailStatusActive::class,
                EmailStatus::TYPE,
            );


        $dbal->join(
            'account',
            AccountEvent::class,
            'account_event',
            'account_event.id = account.event',
        );


        $dbal->addSelect('users.usr AS value');
        $dbal->addSelect('account_event.email AS option');

        return $dbal->fetchAllHydrate(UserUid::class);
    }

}