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

namespace BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit;

use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Files\Resources\Upload\Image\ImageUploadInterface;
use BaksDev\Users\Profile\UserProfile\Entity;
use BaksDev\Users\Profile\UserProfile\Messenger\UserProfileMessage;
use BaksDev\Users\Profile\UserProfile\Repository\UniqProfileUrl\UniqProfileUrlInterface;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UserProfileHandler
{
    private EntityManagerInterface $entityManager;

    private ImageUploadInterface $imageUpload;

    private UniqProfileUrlInterface $uniqProfileUrl;

    private ValidatorInterface $validator;

    private LoggerInterface $logger;

    private MessageDispatchInterface $messageDispatch;


    public function __construct(
        EntityManagerInterface $entityManager,
        ImageUploadInterface $imageUpload,
        UniqProfileUrlInterface $uniqProfileUrl,
        ValidatorInterface $validator,
        LoggerInterface $logger,
        MessageDispatchInterface $messageDispatch

    )
    {
        $this->entityManager = $entityManager;
        $this->imageUpload = $imageUpload;

        $this->uniqProfileUrl = $uniqProfileUrl;
        $this->validator = $validator;
        $this->logger = $logger;

        $this->messageDispatch = $messageDispatch;
    }


    public function handle(
        Entity\Event\UserProfileEventInterface $command,
    ): string|Entity\UserProfile
    {

        /* Валидация */
        $errors = $this->validator->validate($command);

        if(count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [__FILE__.':'.__LINE__]);

            return $uniqid;
        }

        if($command->getEvent())
        {
            $EventRepo = $this->entityManager->getRepository(Entity\Event\UserProfileEvent::class)->find(
                $command->getEvent()
            );

            if($EventRepo === null)
            {
                $uniqid = uniqid('', false);
                $errorsString = sprintf(
                    'Not found %s by id: %s',
                    Entity\Event\UserProfileEvent::class,
                    $command->getEvent()
                );
                $this->logger->error($uniqid.': '.$errorsString);

                return $uniqid;
            }

            $EventRepo->setEntity($command);
            $EventRepo->setEntityManager($this->entityManager);
            $Event = $EventRepo->cloneEntity();
        }
        else
        {
            $Event = new Entity\Event\UserProfileEvent();
            $Event->setEntity($command);
            $this->entityManager->persist($Event);
        }

//        $this->entityManager->clear();
//        $this->entityManager->persist($Event);


        /** @var Entity\UserProfile $UserProfile */
        if($Event->getProfile())
        {
            $UserProfile = $this->entityManager
                ->getRepository(Entity\UserProfile::class)
                ->findOneBy(['event' => $command->getEvent()]);

            if(empty($UserProfile))
            {
                $uniqid = uniqid('', false);
                $errorsString = sprintf(
                    'Not found %s by event: %s',
                    Entity\UserProfile::class,
                    $command->getEvent()
                );
                $this->logger->error($uniqid.': '.$errorsString);

                return $uniqid;
            }

            $UserProfileInfo = $this->entityManager
                ->getRepository(Entity\Info\UserProfileInfo::class)
                ->find($UserProfile);

        }
        else
        {

            $UserProfile = new Entity\UserProfile();
            $this->entityManager->persist($UserProfile);
            $Event->setProfile($UserProfile);

            $UserProfileInfo = new Entity\Info\UserProfileInfo($UserProfile);
            $this->entityManager->persist($UserProfileInfo);
        }


        /** @var Info\InfoDTO $infoDTO */
        $infoDTO = $command->getInfo();

        /* Проверяем на уникальность Адрес персональной страницы */
        $uniqProfileUrl = $this->uniqProfileUrl->exist($infoDTO->getUrl(), $UserProfileInfo->getProfile());

        if($uniqProfileUrl)
        {
            $infoDTO->updateUrlUniq(); /* Обновляем URL на уникальный с префиксом */
        }


        /* Деактивируем профиль пользователя, Если был ранеее активный */
        if($infoDTO->getActive() !== $UserProfileInfo->isNotActiveProfile())
        {
            $InfoActive = $this->entityManager->getRepository(Entity\Info\UserProfileInfo::class)
                ->findOneBy(['usr' => $infoDTO->getUsr(), 'active' => true]);

            /* Если у текущего пользователя имеется активный профиль - деактивируем */
            if($InfoActive)
            {
                $InfoActive->deactivate();
            }
        }


        /* Загружаем файл аватарки профиля */

        /** @var Avatar\AvatarDTO $Avatar */
        $Avatar = $command->getAvatar();
        if($Avatar->file !== null)
        {
            $UserProfileAvatar = $Event->getUploadAvatar();
            $this->imageUpload->upload($Avatar->file, $UserProfileAvatar);
        }

        /* Присваиваем событие INFO */
        $UserProfileInfo->setEntity($infoDTO);

        /* присваиваем событие корню */
        $UserProfile->setEvent($Event);


        /**
         * Валидация Event
         */

        $errors = $this->validator->validate($Event);

        if(count($errors) > 0)
        {
            /** Ошибка валидации */
            $uniqid = uniqid('', false);
            $this->logger->error(sprintf('%s: %s', $uniqid, $errors), [__FILE__.':'.__LINE__]);

            return $uniqid;
        }

        $this->entityManager->flush();


        /* Отправляем событие в шину  */
        $this->messageDispatch->dispatch(
            message: new UserProfileMessage($UserProfile->getId(), $UserProfile->getEvent(), $command->getEvent()),
            transport: 'users-profile-user'
        );

        return $UserProfile;
    }

}