<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Users\Profile\UserProfile\Repository\UserProfileByAuthority;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\Group\Entity\ProfileGroup;
use BaksDev\Users\Profile\Group\Entity\Role\ProfileRole;
use BaksDev\Users\Profile\Group\Entity\Role\Voter\ProfileVoter;
use BaksDev\Users\Profile\Group\Entity\Users\ProfileGroupUsers;
use BaksDev\Users\Profile\Group\Type\Prefix\Role\GroupRolePrefix;
use BaksDev\Users\Profile\Group\Type\Prefix\Voter\RoleVoterPrefix;
use BaksDev\Users\Profile\UserProfile\Entity\Personal\UserProfilePersonal;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Generator;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class UserProfileByAuthorityRepository implements UserProfileByAuthorityInterface
{
    private DBALQueryBuilder $DBALQueryBuilder;

    private TokenStorageInterface $tokenStorage;

    private ?GroupRolePrefix $role = null;

    private ?RoleVoterPrefix $voter = null;

    private ?UserProfileUid $profile = null;

    public function __construct(
        DBALQueryBuilder $DBALQueryBuilder,
        TokenStorageInterface $tokenStorage
    )
    {
        $this->DBALQueryBuilder = $DBALQueryBuilder;
        $this->tokenStorage = $tokenStorage;
    }

    public function withProfile(UserProfileUid|string $profile): self
    {
        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        $this->profile = $profile;

        return $this;
    }

    public function withRole(GroupRolePrefix|string $role): self
    {
        if(is_string($role))
        {
            $role = new GroupRolePrefix($role);
        }

        $this->role = $role;

        return $this;
    }

    public function withVoter(RoleVoterPrefix|string $voter): self
    {
        if(is_string($voter))
        {
            $voter = new RoleVoterPrefix($voter);
        }

        $this->voter = $voter;

        return $this;
    }

    /**
     * Метод возвращает профили пользователей, которые имеют доверенности к указанному либо активному профилю
     *
     * Фильтр по конкретному профилю либо профилю активного пользователя
     * @see withProfile
     * @example  $this->withProfile('018d3196-6c42-7822-9366-c023860f90bb');
     *
     * Фильтр по роли
     * @see withRole
     * @example  $this->withRole('ROLE_ORDERS');
     *
     * Фильтр по правилу
     * @see withVoter
     * @example  $this->withVoter('ROLE_ORDERS_DELETE');
     *
     */
    public function findAll(): Generator
    {
        $authority = $this->profile ?: $this->tokenStorage->getToken()?->getUser()?->getProfile();

        $dbal = $this->DBALQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $dbal
            ->from(ProfileGroupUsers::class, 'profile_group_usr')
            ->where('profile_group_usr.authority = :authority')
            ->setParameter('authority', $authority, UserProfileUid::TYPE);


        /** Фильтр по роли */
        if($this->role || $this->voter)
        {
            $dbal->join(
                'profile_group_usr',
                ProfileGroup::class,
                'profile_group',
                'profile_group.prefix = profile_group_usr.prefix'
            );

            $dbal->join(
                'profile_group',
                ProfileRole::class,
                'profile_group_role',
                'profile_group_role.event = profile_group.event'.($this->role ? ' AND profile_group_role.prefix = :role' : '')
            )
                ->setParameter('role', $this->role, GroupRolePrefix::TYPE);


            /** Фильтр по роли */
            if($this->voter)
            {
                $dbal->join(
                    'profile_group_role',
                    ProfileVoter::class,
                    'profile_group_voter',
                    'profile_group_voter.role = profile_group_role.id AND profile_group_voter.prefix = :voter'
                )
                    ->setParameter('voter', $this->voter, RoleVoterPrefix::TYPE);

            }
        }


        /** Ответственное лицо (Профиль пользователя) */

        $dbal
            ->leftJoin(
                'profile_group_usr',
                UserProfile::class,
                'users_profile',
                'users_profile.id = profile_group_usr.profile'
            );

        $dbal
            //->addSelect('users_profile_personal.username AS users_profile_username')
            ->leftJoin(
                'users_profile',
                UserProfilePersonal::TABLE,
                'users_profile_personal',
                'users_profile_personal.event = users_profile.event'
            );


        $dbal->orderBy('users_profile_personal.username');

        $dbal->addSelect('users_profile.id AS value');
        $dbal->addSelect('users_profile_personal.username AS attr');


        return $dbal
            ->enableCache('users-profile-group', 86400)
            ->fetchAllHydrate(UserProfileUid::class);

    }
}