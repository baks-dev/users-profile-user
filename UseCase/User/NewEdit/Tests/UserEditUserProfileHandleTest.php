<?php
/*
 *  Copyright 2026.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Tests;

use BaksDev\Core\Validator\ValidatorCollectionInterface;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use BaksDev\Users\Profile\TypeProfile\Type\Section\Field\Id\TypeProfileSectionFieldUid;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusModeration;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Avatar\AvatarDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Personal\PersonalDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileHandler;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Value\ValueDTO;
use BaksDev\Users\User\Type\Id\UserUid;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\DependsOnClass;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

#[Group('users-profile-user')]
#[When(env: 'test')]
final class UserEditUserProfileHandleTest extends KernelTestCase
{
    #[DependsOnClass(UserNewUserProfileHandleTest::class)]
    public function testUseCase(): void
    {

        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);

        /** @var UserProfileEvent $UserProfileEvent */
        $UserProfileEvent = $em->getRepository(UserProfileEvent::class)->find(UserProfileEventUid::TEST);
        self::assertNotNull($UserProfileEvent);


        /** @var UserProfileDTO $UserProfileDTO */
        $UserProfileDTO = $UserProfileEvent->getDto(UserProfileDTO::class);

        self::assertEquals(123, $UserProfileDTO->getSort());
        $UserProfileDTO->setSort(321);

        self::assertEquals(TypeProfileUid::TEST, (string) $UserProfileDTO->getType());
        $UserProfileDTO->setType(clone new TypeProfileUid());

        /** тип профиля остается неизменным */
        self::assertEquals(TypeProfileUid::TEST, (string) $UserProfileDTO->getType());

        $ValueCollection = $UserProfileDTO->getValue();
        self::assertCount(1, $ValueCollection);

        /** @var ValueDTO $ValueDTO */
        $ValueDTO = $ValueCollection->current();
        self::assertEquals(TypeProfileSectionFieldUid::TEST, (string) $ValueDTO->getField());
        self::assertEquals('HKEKpYtase', (string) $ValueDTO->getValue());


        /** @var AvatarDTO $AvatarDTO */
        $AvatarDTO = $UserProfileDTO->getAvatar();


        /** @var InfoDTO $InfoDTO */
        $InfoDTO = $UserProfileDTO->getInfo();
        self::assertEquals('ZIxzonxems', $InfoDTO->getUrl());
        $InfoDTO->setUrl('peZKhJNBpR');

        self::assertEquals(UserUid::TEST, (string) $InfoDTO->getUsr());
        self::assertTrue($InfoDTO->getStatus()->equals(UserProfileStatusModeration::class));



        /** @var PersonalDTO $PersonalDTO */
        $PersonalDTO = $UserProfileDTO->getPersonal();
        self::assertEquals('ZuUXSSPtpW', $PersonalDTO->getUsername());
        $PersonalDTO->setUsername('pKgraBObwG');



        self::assertNotNull($PersonalDTO->getBirthday());
        self::assertInstanceOf(DateTimeImmutable::class,  $PersonalDTO->getBirthday());
        $birthday = new DateTimeImmutable();
        $PersonalDTO->setBirthday($birthday);


        self::assertEquals('coQltgAHLw', $PersonalDTO->getLocation());
        $PersonalDTO->setLocation('pyOwknWCtO');

        /** @var ValidatorCollectionInterface $ValidatorCollection */
        $ValidatorCollection = $container->get(ValidatorCollectionInterface::class);

        $ValidatorCollection->add($UserProfileDTO);
        self::assertFalse($ValidatorCollection->isInvalid());

        /** @var UserProfileHandler $UserProfileHandler */
        $UserProfileHandler = self::getContainer()->get(UserProfileHandler::class);
        $handle = $UserProfileHandler->handle($UserProfileDTO);

        self::assertTrue(($handle instanceof UserProfile), $handle.': Ошибка UserProfile');

        $em->clear();
        //$em->close();
    }
}