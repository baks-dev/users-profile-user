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

namespace BaksDev\Users\Profile\UserProfile\Repository\UserProfileByAuthority;

use BaksDev\Users\Profile\Group\Type\Prefix\Role\GroupRolePrefix;
use BaksDev\Users\Profile\Group\Type\Prefix\Voter\RoleVoterPrefix;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use Generator;

interface UserProfileByAuthorityInterface
{
    public function withProfile(UserProfileUid|string $profile): self;

    public function withRole(GroupRolePrefix|string $role): self;

    public function withVoter(RoleVoterPrefix|string $voter): self;

    /**
     * Метод возвращает профили пользователей, которые имеют доверенности к указанному либо активному профилю
     *
     * Фильтр по конкретному профилю либо профилю активного пользователя
     *
     * @return Generator<int, UserProfileUid>
     *@example  $this->withProfile('018d3196-6c42-7822-9366-c023860f90bb');
     *
     * Фильтр по роли
     * @see withRole
     * @example  $this->withRole('ROLE_ORDERS');
     *
     * Фильтр по правилу
     * @see withVoter
     * @example  $this->withVoter('ROLE_ORDERS_DELETE');
     *
     * @see withProfile
     */
    public function findAll(): Generator;
}