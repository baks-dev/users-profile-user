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

namespace BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Tests;

use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\Collection\UserProfileStatusCollection;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatusType;
use BaksDev\Wildberries\Orders\Type\WildberriesStatus\Status\Collection\WildberriesStatusInterface;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group users-profile-user
 */
#[When(env: 'test')]
final class UserProfileStatusTest extends KernelTestCase
{
    public function testUseCase(): void
    {
        /** @var UserProfileStatusCollection $UserProfileStatusCollection */
        $UserProfileStatusCollection = self::getContainer()->get(UserProfileStatusCollection::class);

        /** @var WildberriesStatusInterface $case */
        foreach($UserProfileStatusCollection->cases() as $case)
        {
            $UserProfileStatus = new UserProfileStatus($case->getValue());

            self::assertTrue($UserProfileStatus->equals($case::class)); // немспейс интерфейса
            self::assertTrue($UserProfileStatus->equals($case)); // объект интерфейса
            self::assertTrue($UserProfileStatus->equals($case->getValue())); // срока
            self::assertTrue($UserProfileStatus->equals($UserProfileStatus)); // объект класса


            $UserProfileStatusType = new UserProfileStatusType();
            $platform = $this->getMockForAbstractClass(AbstractPlatform::class);

            $convertToDatabase = $UserProfileStatusType->convertToDatabaseValue($UserProfileStatus, $platform);
            self::assertEquals($UserProfileStatus->getUserProfileStatusValue(), $convertToDatabase);

            $convertToPHP = $UserProfileStatusType->convertToPHPValue($convertToDatabase, $platform);
            self::assertInstanceOf(UserProfileStatus::class, $convertToPHP);
            self::assertEquals($case, $convertToPHP->getUserProfileStatus());

        }

    }
}