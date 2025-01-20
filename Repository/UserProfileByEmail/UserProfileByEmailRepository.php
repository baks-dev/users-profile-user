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

namespace BaksDev\Users\Profile\UserProfile\Repository\UserProfileByEmail;

use BaksDev\Auth\Email\Entity\Account;
use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Entity\Status\AccountStatus;
use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Auth\Email\Type\EmailStatus\EmailStatus;
use BaksDev\Auth\Email\Type\EmailStatus\Status\EmailStatusActive;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use InvalidArgumentException;


final class UserProfileByEmailRepository implements UserProfileByEmailInterface
{
    private AccountEmail|false $email = false;

    public function __construct(private readonly DBALQueryBuilder $DBALQueryBuilder) {}

    public function email(AccountEmail|string $email): self
    {
        if(is_string($email))
        {
            $email = new AccountEmail($email);
        }

        $this->email = $email;
        return $this;
    }

    public function find(): UserProfileUid|false
    {
        if(false === $this->email)
        {
            throw new InvalidArgumentException('Invalid Argument Email');
        }

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->from(AccountEvent::class, 'event')
            ->where('event.email = :email')
            ->setParameter('email', $this->email, AccountEmail::TYPE);

        $dbal
            ->join(
                'event',
                Account::class,
                'account',
                'account.event = event.id'
            );


        $dbal
            ->join(
                'account',
                AccountStatus::class,
                'account_status',
                '
                    account_status.event = account.event AND 
                    account_status.status = :status_account
                '
            )
            ->setParameter(
                'status_account',
                EmailStatusActive::class,
                EmailStatus::TYPE
            );


        $dbal
            ->join(
                'account',
                UserProfileInfo::class,
                'info',
                'info.usr = account.id AND info.active = true AND info.status = :status_profile'
            )
            ->setParameter(
                'status_profile',
                UserProfileStatusActive::class,
                UserProfileStatus::TYPE
            );


        $dbal->addSelect('info.profile AS value');

        return $dbal->fetchHydrate(UserProfileUid::class);

    }
}