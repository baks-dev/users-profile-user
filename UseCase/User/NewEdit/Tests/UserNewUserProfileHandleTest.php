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

namespace BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Tests;

use BaksDev\Core\Type\Gps\GpsLatitude;
use BaksDev\Core\Type\Gps\GpsLongitude;
use BaksDev\Core\Validator\ValidatorCollectionInterface;
use BaksDev\Reference\Gender\Type\Genders\GenderCollection;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use BaksDev\Users\Profile\TypeProfile\Type\Section\Field\Id\TypeProfileSectionFieldUid;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\Collection\UserProfileStatusCollection;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusModeration;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Avatar\AvatarDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Info\InfoDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Personal\PersonalDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileHandler;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Value\ValueDTO;
use BaksDev\Users\User\Type\Id\UserUid;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\Attributes\Group;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Event\ConsoleCommandEvent;
use Symfony\Component\Console\Input\StringInput;
use Symfony\Component\Console\Output\NullOutput;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;

#[Group('users-profile-user')]
#[When(env: 'test')]
final class UserNewUserProfileHandleTest extends KernelTestCase
{
    public static function setUpBeforeClass(): void
    {

        // Бросаем событие консольной комманды
        $dispatcher = self::getContainer()->get(EventDispatcherInterface::class);
        $event = new ConsoleCommandEvent(new Command(), new StringInput(''), new NullOutput());
        $dispatcher->dispatch($event, 'console.command');

        /** @var UserProfileStatusCollection $UserProfileStatusCollection */
        $UserProfileStatusCollection = self::getContainer()->get(UserProfileStatusCollection::class);
        $UserProfileStatusCollection->cases();


        /** @var GenderCollection $GenderCollection */
        $GenderCollection = self::getContainer()->get(GenderCollection::class);
        $GenderCollection->cases();


        /** @var EntityManagerInterface $em */
        $em = self::getContainer()->get(EntityManagerInterface::class);

        $UserProfile = $em->getRepository(UserProfile::class)
            ->findBy(['id' => UserProfileUid::TEST]);

        foreach($UserProfile as $remove)
        {
            $em->remove($remove);
        }

        $UserProfileEventCollection = $em->getRepository(UserProfileEvent::class)
            ->findBy(['profile' => UserProfileUid::TEST]);

        foreach($UserProfileEventCollection as $remove)
        {
            $em->remove($remove);
        }

        $em->flush();

        $em->clear();
    }


    public function testUseCase(): void
    {
        $container = self::getContainer();

        $UserProfileDTO = new UserProfileDTO();

        $UserProfileDTO->setSort(123);
        self::assertEquals(123, $UserProfileDTO->getSort());

        $TypeProfileUid = new TypeProfileUid();
        $UserProfileDTO->setType($TypeProfileUid);
        self::assertSame($TypeProfileUid, $UserProfileDTO->getType());


        $ValueDTO = new ValueDTO();
        $UserProfileDTO->addValue($ValueDTO);
        self::assertTrue($UserProfileDTO->getValue()->contains($ValueDTO));
        self::assertCount(1, $UserProfileDTO->getValue());

        $TypeProfileSectionFieldUid = new TypeProfileSectionFieldUid();
        $ValueDTO->setField($TypeProfileSectionFieldUid);
        $ValueDTO->setValue('HKEKpYtase');
        self::assertSame($TypeProfileSectionFieldUid, $ValueDTO->getField());





        /** @var AvatarDTO $AvatarDTO */
        $AvatarDTO = $UserProfileDTO->getAvatar();


        /** @var InfoDTO $InfoDTO */
        $InfoDTO = $UserProfileDTO->getInfo();
        $InfoDTO->setUrl('ZIxzonxems');
        self::assertEquals('ZIxzonxems', $InfoDTO->getUrl());

        $UserUid = new UserUid();
        $InfoDTO->setUsr($UserUid);
        self::assertSame($UserUid, $InfoDTO->getUsr());
        self::assertTrue($InfoDTO->getStatus()->equals(UserProfileStatusModeration::class));


        /** @var PersonalDTO $PersonalDTO */
        $PersonalDTO = $UserProfileDTO->getPersonal();
        $PersonalDTO->setUsername('ZuUXSSPtpW');
        self::assertEquals('ZuUXSSPtpW', $PersonalDTO->getUsername());

        $PersonalDTO->setLongitude(new GpsLongitude(GpsLongitude::TEST));
        self::assertEquals(GpsLongitude::TEST, $PersonalDTO->getLongitude()->getFloat());

        $PersonalDTO->setLatitude(new GpsLatitude(GpsLatitude::TEST));
        self::assertEquals(GpsLatitude::TEST, $PersonalDTO->getLatitude()->getFloat());

        $birthday = new DateTimeImmutable();
        $PersonalDTO->setBirthday($birthday);
        self::assertSame($birthday, $PersonalDTO->getBirthday());

        $PersonalDTO->setLocation('coQltgAHLw');
        self::assertEquals('coQltgAHLw', $PersonalDTO->getLocation());

        /** @var ValidatorCollectionInterface $ValidatorCollection */
        $ValidatorCollection = $container->get(ValidatorCollectionInterface::class);

        $ValidatorCollection->add($UserProfileDTO);
        self::assertFalse($ValidatorCollection->isInvalid());

        // $ValidatorCollection->isInvalid();
        // dd($ValidatorCollection->getErrors());




        /** @var UserProfileHandler $UserProfileHandler */
        $UserProfileHandler = self::getContainer()->get(UserProfileHandler::class);
        $handle = $UserProfileHandler->handle($UserProfileDTO);


        self::assertTrue(($handle instanceof UserProfile), $handle.': Ошибка UserProfile');

    }

    public function testComplete(): void
    {
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $UserProfile = $em->getRepository(UserProfile::class)
            ->find(UserProfileUid::TEST);
        self::assertNotNull($UserProfile);

        $em->clear();
        //$em->close();
    }
}