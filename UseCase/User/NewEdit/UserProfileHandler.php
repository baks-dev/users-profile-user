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

namespace BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit;

use BaksDev\Core\Entity\AbstractHandler;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Core\Validator\ValidatorCollectionInterface;
use BaksDev\Files\Resources\Upload\File\FileUploadInterface;
use BaksDev\Files\Resources\Upload\Image\ImageUploadInterface;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEventInterface;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Messenger\UserProfileMessage;
use BaksDev\Users\Profile\UserProfile\Repository\UniqProfileUrl\UniqProfileUrlInterface;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;
use Psr\Log\LoggerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class UserProfileHandler extends AbstractHandler
{
    public function __construct(
        private readonly UniqProfileUrlInterface $uniqProfileUrl,

        EntityManagerInterface $entityManager,
        MessageDispatchInterface $messageDispatch,
        ValidatorCollectionInterface $validatorCollection,
        ImageUploadInterface $imageUpload,
        FileUploadInterface $fileUpload,
    )
    {
        parent::__construct($entityManager, $messageDispatch, $validatorCollection, $imageUpload, $fileUpload);
    }

    public function handle(UserProfileEventInterface $command,): string|UserProfile
    {

        /* Валидация DTO  */
        $this->validatorCollection->add($command);

        $this->main = new UserProfile();
        $this->event = new UserProfileEvent();

        try
        {
            $command->getEvent() ? $this->preUpdate($command, true) : $this->prePersist($command);
        }
        catch(DomainException $errorUniqid)
        {
            return $errorUniqid->getMessage();
        }

        $UserProfileInfo = $this->entityManager->getRepository(UserProfileInfo::class)->find(
            $this->event->getMain()
        );

        if(!$UserProfileInfo)
        {
            $UserProfileInfo = new UserProfileInfo($this->event->getMain());
            $this->entityManager->persist($UserProfileInfo);
        }

        $this->validatorCollection->add($UserProfileInfo);


        /** @var Info\InfoDTO $infoDTO */
        $infoDTO = $command->getInfo();

        /* Проверяем на уникальность Адрес персональной страницы */
        $uniqProfileUrl = $this->uniqProfileUrl->exist($infoDTO->getUrl(), $UserProfileInfo->getProfile());

        if($uniqProfileUrl)
        {
            $infoDTO->updateUrlUniq(); /* Обновляем URL на уникальный с префиксом */
        }

        /* Загружаем файл аватарки профиля */
        if(method_exists($command, 'getAvatar'))
        {
            /** @var Avatar\AvatarDTO $Avatar */
            $Avatar = $command->getAvatar();

            if($Avatar->file !== null)
            {
                $UserProfileAvatar = $this->event->getUploadAvatar();
                $this->imageUpload->upload($Avatar->file, $UserProfileAvatar);
            }
        }

        /* Присваиваем событие INFO */
        $UserProfileInfo->setEntity($infoDTO);
        $this->validatorCollection->add($UserProfileInfo);


        /* Деактивируем профиль пользователя, Если был другой ранее активный */
        $InfoActive = $this
            ->entityManager
            ->getRepository(UserProfileInfo::class)
            ->findOneBy(['usr' => $infoDTO->getUsr(), 'active' => true]);

        //if($InfoActive && !$InfoActive->getProfile()?->equals($UserProfileInfo->getProfile()))
        if($InfoActive && !$InfoActive->getProfile()?->equals($this->main->getId()))
        {
            $InfoActive->deactivate();
        }

        /* Валидация всех объектов */
        if($this->validatorCollection->isInvalid())
        {
            return $this->validatorCollection->getErrorUniqid();
        }

        $this->entityManager->flush();

        /* Отправляем событие в шину  */
        $this->messageDispatch->dispatch(
            message: new UserProfileMessage($this->main->getId(), $this->main->getEvent(), $command->getEvent()),
            transport: 'users-profile-user'
        );

        return $this->main;
    }

}
