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

namespace BaksDev\Users\Profile\UserProfile\Messenger\Account;

use BaksDev\Auth\Email\Entity\Event\AccountEvent;
use BaksDev\Auth\Email\Messenger\Confirmation\ConfirmationAccountMessage;
use BaksDev\Auth\Email\Repository\UserNew\UserNewInterface;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatusEnum;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Info\InfoDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileHandler;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;

#[AsMessageHandler(priority: 99)]
final class CreateUserProfileByRegistration
{
    private EntityManagerInterface $entityManager;
    private UserProfileHandler $userProfileHandler;
    private UserNewInterface $userVerify;

    public function __construct(
        UserNewInterface $userVerify,
        EntityManagerInterface $entityManager,
        UserProfileHandler $userProfileHandler,
    )
    {
        $this->entityManager = $entityManager;
        $this->userProfileHandler = $userProfileHandler;
        $this->userVerify = $userVerify;
    }

    /**
     * Создаем профиль пользователя после регистрации с типом "Пользователь"
     */
    public function __invoke(ConfirmationAccountMessage $message): void
    {
        // Получаем UserUid по событию со статусом NEW
        $UserUid = $this->userVerify->getNewUserByAccountEvent($message->getEvent());

        if(!$UserUid)
        {
            return;
        }

        $this->entityManager->clear();
        $AccountEvent = $this->entityManager->getRepository(AccountEvent::class)->find($message->getEvent());
        $AccountEmail = $AccountEvent->getEmail();

        /** Создаем профиль пользователя по умолчанию */
        $UserProfileDTO = new UserProfileDTO();
        $UserProfileDTO->setSort(100);
        $UserProfileDTO->setType(TypeProfileUid::userProfileType());

        /** @var InfoDTO $InfoDTO */
        $InfoDTO = $UserProfileDTO->getInfo();
        $InfoDTO->activate();
        $InfoDTO->setUrl(uniqid('', false));
        $InfoDTO->setUsr($UserUid);
        $InfoDTO->setStatus(UserProfileStatusEnum::ACTIVE);

        $PersonalDTO = $UserProfileDTO->getPersonal();
        $PersonalDTO->setUsername($AccountEmail->getUserName());

        $UserProfile = $this->userProfileHandler->handle($UserProfileDTO);

        if(!$UserProfile instanceof UserProfile)
        {
            throw new DomainException(sprintf('%s: Ошибка при добавлении профиля пользователя', $UserProfile));
        }

    }
}