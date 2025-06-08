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

namespace BaksDev\Users\Profile\UserProfile\Repository\Authority\isGrantedByRole;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\Group\BaksDevUsersProfileGroupBundle;
use BaksDev\Users\Profile\Group\Entity\ProfileGroup;
use BaksDev\Users\Profile\Group\Entity\Role\ProfileRole;
use BaksDev\Users\Profile\Group\Entity\Role\Voter\ProfileVoter;
use BaksDev\Users\Profile\Group\Entity\Users\ProfileGroupUsers;
use BaksDev\Users\Profile\Group\Type\Prefix\Voter\RoleVoterPrefix;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

final class isGrantedUserProfileByRoleRepository implements isGrantedUserProfileByRoleInterface
{
    private UserProfileUid|false $profile = false;

    private UserProfileUid|false $authority = false;

    private RoleVoterPrefix|false $voter = false;

    public function __construct(
        private readonly DBALQueryBuilder $DBALQueryBuilder
    ) {}

    public function onProfile(UserProfileUid|string $profile): self
    {
        if(empty($profile))
        {
            $this->profile = false;
            return $this;
        }

        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        $this->profile = $profile;
        return $this;
    }

    public function onAuthority(UserProfileUid|string $authority): self
    {
        if(empty($authority))
        {
            $this->authority = false;
            return $this;
        }

        if(is_string($authority))
        {
            $authority = new UserProfileUid($authority);
        }

        $this->authority = $authority;
        return $this;
    }

    public function onRoleVoter(RoleVoterPrefix|string $voter): self
    {
        if(empty($voter))
        {
            $this->voter = false;
            return $this;
        }

        if(is_string($voter))
        {
            $voter = new RoleVoterPrefix($voter);
        }

        $this->voter = $voter;
        return $this;
    }

    /**
     * Метод проверяет, имеется ли у профиля доступ по доверенности и роли
     */
    public function isGranted(): bool
    {
        $builder = $this->builder();

        $roles = $builder->fetchAssociative();

        /** Нет ролей */
        if(empty($roles))
        {
            return false;
        }

        /** Если роль администратора */
        if($roles['profile_group_users'] === 'ROLE_ADMIN')
        {
            return true;
        }

        return $this->voter->equals($roles['profile_group_voter_prefix']);
    }

    private function builder(): DBALQueryBuilder
    {

        $this->validate();

        $dbal = $this->DBALQueryBuilder->createQueryBuilder(self::class);

        $dbal
            ->select('profile_group_users.prefix AS profile_group_users') // Если роль администратора - ROLE_ADMIN
            ->from(ProfileGroupUsers::class, 'profile_group_users')
            ->where('profile_group_users.profile = :current')
            ->setParameter('current', $this->profile, UserProfileUid::TYPE);

        /** Группы */
        if(false === $this->authority->equals($this->profile))
        {

            $dbal->join(
                'profile_group_users',
                ProfileGroup::class,
                'profile_group',
                'profile_group.prefix = profile_group_users.prefix AND profile_group.profile = :authority'
            )
                ->setParameter('authority', $this->authority, UserProfileUid::TYPE);
        }
        else
        {
            $dbal->leftJoin(
                'profile_group_users',
                ProfileGroup::class,
                'profile_group',
                'profile_group.prefix = profile_group_users.prefix'
            );

            $dbal->andWhere('profile_group_users.authority IS NULL');
        }

        /** Группы ролей */
        $dbal
            ->addSelect('profile_group_role.prefix AS profile_group_role_prefix')
            ->leftJoin(
                'profile_group',
                ProfileRole::class,
                'profile_group_role',
                'profile_group_role.event = profile_group.event'
            );

        /** Роли */
        $dbal
            ->join(
                'profile_group_role',
                ProfileVoter::class,
                'profile_group_voter',
                'profile_group_voter.role = profile_group_role.id AND profile_group_voter.prefix = :voter'
            )
            ->setParameter('voter', $this->voter, RoleVoterPrefix::TYPE);

        $dbal->addSelect('profile_group_voter.prefix AS profile_group_voter_prefix');

        return $dbal;
    }

    private function validate(): void
    {
        if(false === class_exists(BaksDevUsersProfileGroupBundle::class))
        {
            throw new \Exception('Для выполнения запроса необходим модуль BaksDevUsersProfileGroupBundle');
        }

        if(false === ($this->profile instanceof UserProfileUid))
        {
            throw new \InvalidArgumentException(sprintf(
                'Некорректной тип для параметра $this->profile: `%s`. Ожидаемый тип %s',
                var_export($this->profile, true), UserProfileUid::class
            ));
        }

        if(false === ($this->authority instanceof UserProfileUid))
        {
            throw new \InvalidArgumentException(sprintf(
                'Некорректной тип для параметра $this->authority: `%s`. Ожидаемый тип %s',
                var_export($this->authority, true), UserProfileUid::class
            ));
        }

        if(false === ($this->voter instanceof RoleVoterPrefix))
        {
            throw new \InvalidArgumentException(sprintf(
                'Некорректной тип для параметра $this->voter: `%s`. Ожидаемый тип %s',
                var_export($this->voter, true), RoleVoterPrefix::class
            ));
        }
    }
}