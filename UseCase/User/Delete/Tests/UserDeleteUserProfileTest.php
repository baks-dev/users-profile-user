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

namespace BaksDev\Users\Profile\UserProfile\UseCase\User\Delete\Tests;

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Core\Validator\ValidatorCollectionInterface;
use BaksDev\Products\Category\Type\Id\ProductCategoryUid;
use BaksDev\Products\Category\Type\Section\Field\Id\ProductCategorySectionFieldUid;
use BaksDev\Reference\Gender\Type\Genders\Collection\GenderCollection;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use BaksDev\Users\Profile\TypeProfile\Type\Section\Field\Id\TypeProfileSectionFieldUid;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\Collection\UserProfileStatusCollection;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusModeration;
use BaksDev\Users\Profile\UserProfile\UseCase\User\Activate\ActivateUserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\Activate\ActivateUserProfileHandler;
use BaksDev\Users\Profile\UserProfile\UseCase\User\Delete\DeleteUserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\Delete\DeleteUserProfileHandler;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Avatar\AvatarDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Personal\PersonalDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileDTO;
use BaksDev\Users\User\Type\Id\UserUid;
use BaksDev\Wildberries\Products\Entity\Barcode\Event\WbBarcodeEvent;
use BaksDev\Wildberries\Products\Entity\Barcode\WbBarcode;
use BaksDev\Wildberries\Products\UseCase\Barcode\Delete\WbBarcodeDeleteDTO;
use BaksDev\Wildberries\Products\UseCase\Barcode\Delete\WbBarcodeDeleteHandler;
use BaksDev\Wildberries\Products\UseCase\Barcode\NewEdit\Custom\WbBarcodeCustomDTO;
use BaksDev\Wildberries\Products\UseCase\Barcode\NewEdit\Property\WbBarcodePropertyDTO;
use BaksDev\Wildberries\Products\UseCase\Barcode\NewEdit\Tests\EditHandleTest;
use BaksDev\Wildberries\Products\UseCase\Barcode\NewEdit\WbBarcodeDTO;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\DependencyInjection\Attribute\When;

/**
 * @group users-profile-user
 * @group users-profile-user-usecase
 *
 * @depends BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Tests\UserNewUserProfileHandleTest::class
 * @depends BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Tests\UserEditUserProfileHandleTest::class
 * @depends BaksDev\Users\Profile\UserProfile\UseCase\User\Activate\Tests\UserActivateUserProfileHandleTest::class
 *
 * @see UserNewUserProfileHandleTest
 * @see UserEditUserProfileHandleTest
 * @see UserActivateUserProfileHandleTest
 */
#[When(env: 'test')]
final class UserDeleteUserProfileTest extends KernelTestCase
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


        $DeleteUserProfileDTO = $UserProfileEvent->getDto(DeleteUserProfileDTO::class);

        /** @var ValidatorCollectionInterface $ValidatorCollection */
        $ValidatorCollection = $container->get(ValidatorCollectionInterface::class);

        $ValidatorCollection->add($DeleteUserProfileDTO);
        self::assertFalse($ValidatorCollection->isInvalid());

        // $ValidatorCollection->isInvalid();
        // dd($ValidatorCollection->getErrors());



        /** @var DeleteUserProfileHandler $DeleteUserProfileHandler */
        $UserProfileHandler = self::getContainer()->get(DeleteUserProfileHandler::class);
        $handle = $UserProfileHandler->handle($DeleteUserProfileDTO);

        self::assertTrue(($handle instanceof UserProfile), $handle.': Ошибка UserProfile');
    }

    public function testComplete(): void
    {
        $container = self::getContainer();

        /** @var EntityManagerInterface $em */
        $em = $container->get(EntityManagerInterface::class);
        $UserProfile = $em->getRepository(UserProfile::class)
            ->find(UserProfileUid::TEST);
        self::assertNull($UserProfile);

        $em->clear();
        //$em->close();
    }

    /**
     * Этот метод вызывается после выполнения последнего теста этого тестового класса.
     */
    public static function tearDownAfterClass(): void
    {
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
        //$em->close();
    }
}
