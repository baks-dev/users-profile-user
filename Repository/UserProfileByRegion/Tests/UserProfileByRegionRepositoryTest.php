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

namespace BaksDev\Users\Profile\UserProfile\Repository\UserProfileByRegion\Tests;

use BaksDev\Core\Doctrine\DBALQueryBuilder;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileByRegion\UserProfileByRegionInterface;
use BaksDev\Users\Profile\UserProfile\Repository\UserProfileByRegion\UserProfileByRegionResult;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('users-profile-user')]
#[When(env: 'test')]
class UserProfileByRegionRepositoryTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @var UserProfileByRegionInterface $UserProfileByRegion */
        $UserProfileByRegion = self::getContainer()->get(UserProfileByRegionInterface::class);
        $result = $UserProfileByRegion->findAll();

        if(false === $result || false === $result->valid())
        {
            echo PHP_EOL.'ozon-products: Не найдено информации о количестве'.PHP_EOL;
            self::assertTrue(true);
            return;
        }

        foreach($result as $UserProfileByRegionResult)
        {
            // Вызываем все геттеры
            $reflectionClass = new ReflectionClass(UserProfileByRegionResult::class);
            $methods = $reflectionClass->getMethods(ReflectionMethod::IS_PUBLIC);

            foreach($methods as $method)
            {
                // Методы без аргументов
                if($method->getNumberOfParameters() === 0)
                {
                    // Вызываем метод
                    $method->invoke($UserProfileByRegionResult);
                    self::assertTrue(true);
                }
            }

            if(false !== $UserProfileByRegionResult->getProfileValue())
            {
                foreach($UserProfileByRegionResult->getProfileValue() as $item)
                {
                    self::assertTrue(isset($item->name));
                    self::assertTrue(isset($item->type));
                    self::assertTrue(isset($item->value));
                }
            }

            // dd($UserProfileByRegionResult);
        }

        self::assertTrue(true);

    }
}