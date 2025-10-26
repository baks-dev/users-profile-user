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

namespace BaksDev\Users\Profile\UserProfile\Repository\UserProfileValues\Tests;

use BaksDev\Auth\Email\Type\Email\AccountEmail;
use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Core\Type\Field\InputField;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileValues\UserProfileValuesInterface;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileValues\UserProfileValuesResult;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;


#[Group('users-profile-user')]
#[When(env: 'test')]
class UserProfileValuesRepositoryTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        self::assertTrue(true);

        /** @var UserProfileValuesInterface $UserProfileValuesRepository */
        $UserProfileValuesRepository = self::getContainer()->get(UserProfileValuesInterface::class);

        $UserProfileValuesResult = $UserProfileValuesRepository
            ->forFieldType(AccountEmail::TYPE)
            ->forUserProfileEvent(new UserProfileEventUid('019a1db4-5bb6-7915-988d-e6d102cc1ea4'))
            ->find();

        if($UserProfileValuesResult instanceof UserProfileValuesResult)
        {
            // Вызываем все геттеры
            $reflectionClass = new ReflectionClass(UserProfileValuesResult::class);
            $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach($methods as $method)
            {
                // Методы без аргументов
                if($method->getNumberOfParameters() === 0)
                {
                    // Вызываем метод
                    $data = $method->invoke($UserProfileValuesResult);
                    dump($data);
                }
            }
        }
    }
}