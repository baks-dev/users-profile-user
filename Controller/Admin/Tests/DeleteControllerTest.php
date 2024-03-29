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

namespace BaksDev\Users\Profile\UserProfile\Controller\Admin\Tests;

use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use BaksDev\Users\User\Tests\TestUserAccount;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group users-profile-user
 *
 * @depends BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Tests\UserNewUserProfileHandleTest::class
 */
#[When(env: 'test')]
final class DeleteControllerTest extends WebTestCase
{
    private const URL = '/admin/users/profile/delete/%s';

    private const ROLE = 'ROLE_USERPROFILE_DELETE';

//    private static ?UserProfileEventUid $identifier;
//
//    public static function setUpBeforeClass(): void
//    {
//        $em = self::getContainer()->get(EntityManagerInterface::class);
//        self::$identifier = $em->getRepository(UserProfile::class)->findOneBy([], ['id' => 'DESC'])?->getEvent();
//    }
    

    /** Доступ по роли */
    public function testRoleSuccessful(): void
    {

            self::ensureKernelShutdown();
            $client = static::createClient();

            foreach (TestUserAccount::getDevice() as $device)
            {
                $client->setServerParameter('HTTP_USER_AGENT', $device);
                $usr = TestUserAccount::getModer(self::ROLE);

                $client->loginUser($usr, 'user');
                $client->request('GET', sprintf(self::URL, UserProfileEventUid::TEST));

                self::assertResponseIsSuccessful();
            }

    }

    /** Доступ по роли ROLE_ADMIN */
    public function testRoleAdminSuccessful(): void
    {
//        // Получаем одно из событий
//        $Event = self::$identifier;
//
//        if ($Event)
//        {
            self::ensureKernelShutdown();
            $client = static::createClient();

            foreach (TestUserAccount::getDevice() as $device)
            {
                $client->setServerParameter('HTTP_USER_AGENT', $device);

                $usr = TestUserAccount::getAdmin();

                $client->loginUser($usr, 'user');
                $client->request('GET', sprintf(self::URL, UserProfileEventUid::TEST));

                self::assertResponseIsSuccessful();
            }
//        } else
//        {
//            self::assertTrue(true);
//        }
    }

    // доступ по роли ROLE_USER
    public function testRoleUserDeny(): void
    {
//        // Получаем одно из событий
//        $Event = self::$identifier;
//
//        if ($Event)
//        {
            self::ensureKernelShutdown();
            $client = static::createClient();

            foreach (TestUserAccount::getDevice() as $device)
            {
                $client->setServerParameter('HTTP_USER_AGENT', $device);

                $usr = TestUserAccount::getUsr();
                $client->loginUser($usr, 'user');
                $client->request('GET', sprintf(self::URL, UserProfileEventUid::TEST));

                self::assertResponseStatusCodeSame(403);
            }
//        } else
//        {
//            self::assertTrue(true);
//        }
    }

    /** Доступ по без роли */
    public function testGuestDany(): void
    {
//        // Получаем одно из событий
//        $Event = self::$identifier;
//
//        if ($Event)
//        {
            self::ensureKernelShutdown();
            $client = static::createClient();

            foreach (TestUserAccount::getDevice() as $device)
            {
                $client->setServerParameter('HTTP_USER_AGENT', $device);

                $client->request('GET', sprintf(self::URL, UserProfileEventUid::TEST));

                // Full authentication is required to access this resource
                self::assertResponseStatusCodeSame(401);
            }
//        } else
//        {
//            self::assertTrue(true);
//        }
    }
}
