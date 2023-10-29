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

namespace BaksDev\Users\Profile\UserProfile\UseCase\User\Activate\Tests;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Core\Validator\ValidatorCollectionInterface;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use BaksDev\Users\Profile\TypeProfile\Type\Section\Field\Id\TypeProfileSectionFieldUid;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusModeration;
use BaksDev\Users\Profile\UserProfile\UseCase\User\Activate\ActivateUserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\Activate\ActivateUserProfileHandler;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Avatar\AvatarDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Personal\PersonalDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileDTO;
use BaksDev\Users\User\Type\Id\UserUid;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group users-profile-user
 * @group users-profile-user-usecase
 *
 * @depends BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Tests\UserNewUserProfileHandleTest::class
 * @depends BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Tests\UserEditUserProfileHandleTest::class
 *
 * @see UserNewUserProfileHandleTest
 * @see UserEditUserProfileHandleTest
 */
#[When(env: 'test')]
final class UserActivateUserProfileHandleTest extends KernelTestCase
{

    public function testUseCase(): void
    {

        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        //$em = $container->get(EntityManagerInterface::class);

        /** @var ORMQueryBuilder $ORMQueryBuilder */
        $ORMQueryBuilder = $container->get(ORMQueryBuilder::class);

        $qb = $ORMQueryBuilder->createQueryBuilder(self::class);

        $qb->select('event')
            ->from(UserProfile::class, 'profile')
            ->where('profile.id = :profile')
            ->setParameter('profile', UserProfileUid::TEST);

        $qb->leftJoin(
            UserProfileEvent::class,
            'event',
            'WITH',
            'event.id = profile.event'
        );

        /** @var UserProfileEvent $UserProfileEvent */
        $UserProfileEvent = $qb->getOneOrNullResult();
        self::assertNotNull($UserProfileEvent);


        /** @var UserProfileDTO $UserProfileDTO */
        $UserProfileDTO = $UserProfileEvent->getDto(UserProfileDTO::class);

        self::assertEquals(321, $UserProfileDTO->getSort());
        self::assertEquals(TypeProfileUid::TEST, (string) $UserProfileDTO->getType());


        $ValueCollection = $UserProfileDTO->getValue();
        self::assertCount(1, $ValueCollection);
        $ValueDTO = $ValueCollection->current();
        self::assertEquals(TypeProfileSectionFieldUid::TEST, (string) $ValueDTO->getField());


        /** @var AvatarDTO $AvatarDTO */
        $AvatarDTO = $UserProfileDTO->getAvatar();



        /** @var InfoDTO $InfoDTO */
        $InfoDTO = $UserProfileDTO->getInfo();
        self::assertEquals('peZKhJNBpR', $InfoDTO->getUrl());
        self::assertEquals(UserUid::TEST, (string) $InfoDTO->getUsr());
        self::assertTrue($InfoDTO->getStatus()->equals(UserProfileStatusModeration::class));


        /** @var PersonalDTO $PersonalDTO */
        $PersonalDTO = $UserProfileDTO->getPersonal();
        self::assertEquals('pKgraBObwG', $PersonalDTO->getUsername());
        self::assertNotNull($PersonalDTO->getBirthday());
        self::assertInstanceOf(DateTimeImmutable::class,  $PersonalDTO->getBirthday());
        self::assertEquals('pyOwknWCtO', $PersonalDTO->getLocation());



        /** ACTIVATE */



        $ActivateUserProfileDTO = $UserProfileEvent->getDto(ActivateUserProfileDTO::class);



        /** @var ValidatorCollectionInterface $ValidatorCollection */
        $ValidatorCollection = $container->get(ValidatorCollectionInterface::class);

        $ValidatorCollection->add($ActivateUserProfileDTO);
        self::assertFalse($ValidatorCollection->isInvalid());

        // $ValidatorCollection->isInvalid();
        // dd($ValidatorCollection->getErrors());



        /** @var ActivateUserProfileHandler $ActivateUserProfileHandler */
        $UserProfileHandler = self::getContainer()->get(ActivateUserProfileHandler::class);
        $handle = $UserProfileHandler->handle($ActivateUserProfileDTO);

        self::assertTrue(($handle instanceof UserProfile), $handle.': Ошибка UserProfile');
    }
}