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

namespace BaksDev\Users\Profile\UserProfile\Repository\UsersChoiceForm;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Entity\Status\AccountStatus;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusActive;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
use Doctrine\ORM\EntityManagerInterface;

final class UsersChoiceOptionalAccountEmailRepository implements UsersChoiceOptionalAccountEmailInterface
{
    private EntityManagerInterface $entityManager;

    private EmailStatus $status;


    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->status = new EmailStatus(EmailStatusActive::class);
    }


    /** Список объектов UserUid аккаунтов с опциональным Account Email  */

    public function getChoice(): mixed
    {
        $qb = $this->entityManager->createQueryBuilder();

        $select = sprintf('new %s(users.id, account_event.email)', UserUid::class);

        $qb->select($select);

        $qb->from(User::class, 'users', 'users.id');

        $qb->join(
            Account::class,
            'account',
            'WITH',
            'account.id = users.id'
        );

        $qb->join(
            AccountEvent::class,
            'account_event',
            'WITH',
            'account_event.id = account.event'
        );

        $qb->join(
            AccountStatus::class,
            'account_status',
            'WITH',
            'account_status.event = account_event.id 
            AND account_status.status = :status'
        )
            ->setParameter(
                'status',
                $this->status,
                EmailStatus::TYPE
            );

        return $qb->getQuery()->getResult();
    }

}