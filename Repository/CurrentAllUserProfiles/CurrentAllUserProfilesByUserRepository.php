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

namespace BaksDev\Users\Profile\UserProfile\Repository\CurrentAllUserProfiles;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Users\Profile\UserProfile\Entity;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;

final class CurrentAllUserProfilesByUserRepository implements CurrentAllUserProfilesByUserInterface
{

    private DBALQueryBuilder $DBALQueryBuilder;
    private ORMQueryBuilder $ORMQueryBuilder;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
        ORMQueryBuilder $ORMQueryBuilder,
        TokenStorageInterface $tokenStorage,
    )
    {

        $this->DBALQueryBuilder = $DBALQueryBuilder;
        $this->ORMQueryBuilder = $ORMQueryBuilder;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Список личных профилей пользователя в меню.
     *
     * Возвращает массив с ключами: <br>
     * user_profile_event - идентификатор события для активации профиля <br>
     * user_profile_username - username профиля <br>
     */
    public function fetchUserProfilesAllAssociative(UserUid $usr): ?array
    {

        $token = $this->tokenStorage->getToken();

        /** @var User $usr */
        $User = $token?->getUser();

        if(!$User)
        {
            return null;
        }

        $UserUid = $User->getId();

        if($token instanceof SwitchUserToken)
        {
            /** @var User $originalUser */
            $originalUser = $token->getOriginalToken()->getUser();
            $UserUid = $originalUser->getId();

        }

        //dump((string) $UserUid);

        $qb = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $qb->addSelect('userprofile.id AS user_profile_id');
        $qb->addSelect('userprofile.event AS user_profile_event');

        $qb->from(Entity\Info\UserProfileInfo::TABLE, 'userprofile_info');
        $qb->where('userprofile_info.usr = :usr AND userprofile_info.status = :status AND userprofile_info.active IS NOT TRUE');

        $qb->setParameter('usr', $UserUid, UserUid::TYPE);
        $qb->setParameter('status', new UserProfileStatus(UserProfileStatusActive::class), UserProfileStatus::TYPE);

        $qb->join(
            'userprofile_info',
            Entity\UserProfile::TABLE,
            'userprofile',
            'userprofile.id = userprofile_info.profile'
        );


        $qb->join(
            'userprofile',
            Entity\Event\UserProfileEvent::TABLE,
            'userprofile_event',
            'userprofile_event.id = userprofile.event'
        );

        $qb->addSelect('userprofile_profile.username AS user_profile_username');

        // Профиль
        $qb->join(
            'userprofile',
            Entity\Personal\UserProfilePersonal::TABLE,
            'userprofile_profile',
            'userprofile_profile.event = userprofile.event'
        );

        $qb->orderBy('userprofile_event.sort', 'ASC');


        /* Кешируем результат DBAL */
        return $qb->enableCache('users-profile-user', 86400)->fetchAllAssociative();

    }

}
