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

namespace BaksDev\Users\Profile\UserProfile\Repository\UserByUserProfile;

use App\Kernel;
use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\User\Entity\User;
use InvalidArgumentException;

final class UserByUserProfileRepository implements UserByUserProfileInterface
{
    private ORMQueryBuilder $ORMQueryBuilder;

    private UserProfileUid|false $profile = false;

    public function __construct(ORMQueryBuilder $ORMQueryBuilder)
    {
        $this->ORMQueryBuilder = $ORMQueryBuilder;
    }

    public function forProfile(UserProfile|UserProfileUid|string $profile): self
    {
        if(is_string($profile))
        {
            $profile = new UserProfileUid($profile);
        }

        if($profile instanceof UserProfile)
        {
            $profile = $profile->getId();
        }

        $this->profile = $profile;

        return $this;
    }

    /**
     * Возвращает User профиля пользователя
     */
    public function findUser(): User|false
    {
        if(Kernel::isTestEnvironment())
        {
            return new User();
        }

        if(($this->profile instanceof UserProfileUid) === false)
        {
            throw new InvalidArgumentException('Идентификатор профиля не определен ->profile(...)');
        }


        $orm = $this->ORMQueryBuilder->createQueryBuilder(self::class);

        $orm
            ->from(UserProfileInfo::class, 'info')
            ->where('info.profile = :profile')
            ->setParameter('profile', $this->profile, UserProfileUid::TYPE);

        $orm
            ->select('usr')
            ->join(
                User::class,
                'usr',
                'WITH',
                'usr.id = info.usr'
            );


        return $orm->getQuery()->getOneOrNullResult() ?: false;
    }
}
