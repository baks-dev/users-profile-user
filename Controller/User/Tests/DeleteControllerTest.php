<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Users\Profile\UserProfile\Controller\User\Tests;

use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use BaksDev\Users\User\Tests\TestUserAccount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/** @group users-profile-user */
#[When(env: 'test')]
final class DeleteControllerTest extends WebTestCase
{
    private const URL = '/admin/users/profile/delete/%s';

    private static ?UserProfileEventUid $identifier;

    public static function setUpBeforeClass(): void
    {
        $em = self::getContainer()->get(EntityManagerInterface::class);
        self::$identifier = $em->getRepository(UserProfile::class)->findOneBy([], ['id' => 'DESC'])?->getEvent();
    }


    /** Доступ по роли ROLE_ADMIN */
    public function testRoleAdminSuccessful(): void
    {
        // Получаем одно из событий
        $Event = self::$identifier;

        if ($Event)
        {
            self::ensureKernelShutdown();
            $client = static::createClient();

            foreach (TestUserAccount::getDevice() as $device)
            {
                $client->setServerParameter('HTTP_USER_AGENT', $device);

                $user = TestUserAccount::getAdmin();

                $client->loginUser($user, 'user');
                $client->request('GET', sprintf(self::URL, $Event->getValue()));

                self::assertResponseIsSuccessful();
                /* Тестовый профиль не является владельцем профиля */
                //self::assertResponseStatusCodeSame(403);
            }
        } else
        {
            self::assertTrue(true);
        }
    }

    /** Доступ по роли ROLE_USER */
    public function testRoleUserSuccessful(): void
    {
        // Получаем одно из событий
        $Event = self::$identifier;

        if ($Event)
        {
            self::ensureKernelShutdown();
            $client = static::createClient();

            foreach (TestUserAccount::getDevice() as $device)
            {
                $client->setServerParameter('HTTP_USER_AGENT', $device);

                $user = TestUserAccount::getUser();
                $client->loginUser($user, 'user');
                $client->request('GET', sprintf(self::URL, $Event->getValue()));

                //self::assertResponseIsSuccessful();

                /* Тестовый профиль не является владельцем профиля */
                self::assertResponseStatusCodeSame(403);
            }
        } else
        {
            self::assertTrue(true);
        }
    }

    /** Доступ по без роли */
    public function testGuestDany(): void
    {
        // Получаем одно из событий
        $Event = self::$identifier;

        if ($Event)
        {
            self::ensureKernelShutdown();
            $client = static::createClient();

            foreach (TestUserAccount::getDevice() as $device)
            {
                $client->setServerParameter('HTTP_USER_AGENT', $device);

                $client->request('GET', sprintf(self::URL, $Event->getValue()));

                // Full authentication is required to access this resource
                self::assertResponseStatusCodeSame(401);
            }
        } else
        {
            self::assertTrue(true);
        }
    }
}
