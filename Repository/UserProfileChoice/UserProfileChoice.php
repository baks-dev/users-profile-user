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

namespace BaksDev\Users\Profile\UserProfile\Repository\UserProfileChoice;


use BaksDev\Auth\Email\Entity AS AccountEntity;
use BaksDev\Auth\Email\Type\Status\AccountStatus;
use BaksDev\Auth\Email\Type\Status\AccountStatusEnum;
use BaksDev\Users\Profile\UserProfile\Entity AS UserProfileEntity;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatus;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatusEnum;
use Doctrine\ORM\EntityManagerInterface;

final class UserProfileChoice implements UserProfileChoiceInterface
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /** Метод возвращает список идентификтаоров профилей с username профиля в качестве атрибута */
    public function getActiveUserProfile(): iterable
    {
        $select = sprintf('new %s(user_profile.id, personal.username)', UserProfileUid::class);

        $qb = $this->entityManager->createQueryBuilder();
        $qb->select($select);

        $qb->from(UserProfileEntity\UserProfile::class, 'user_profile');

        $qb->join(
            UserProfileEntity\Info\UserProfileInfo::class,
            'info',
            'WITH',
            'info.profile = user_profile.id AND info.status = :status');

        $qb->setParameter('status', new UserProfileStatus(UserProfileStatusEnum::ACTIVE), UserProfileStatus::TYPE);

        $qb->join(
            UserProfileEntity\Event\UserProfileEvent::class,
            'event',
            'WITH',
            'event.id = user_profile.event AND event.profile = user_profile.id');

        $qb->join(
            UserProfileEntity\Personal\UserProfilePersonal::class,
            'personal',
            'WITH',
            'personal.event = event.id');


        $qb->join(
            AccountEntity\Account::class,
            'account',
            'WITH',
            'account.id = info.user');

        $qb->join(
            AccountEntity\Status\AccountStatus::class,
            'status',
            'WITH',
            'status.event = account.event AND status.status = :account_status');

        $qb->setParameter('account_status', new AccountStatus(AccountStatusEnum::ACTIVE), AccountStatus::TYPE);

        return $qb->getQuery()->toIterable();
    }
}