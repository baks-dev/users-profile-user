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

use BaksDev\Core\Entity\AbstractHandler;
use BaksDev\Core\Messenger\MessageDispatchInterface;
use BaksDev\Core\Validator\ValidatorCollectionInterface;
use BaksDev\Files\Resources\Upload\File\FileUploadInterface;
use BaksDev\Files\Resources\Upload\Image\ImageUploadInterface;
use BaksDev\Users\Profile\UserProfile\Entity\Avatar\UserProfileAvatar;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Messenger\UserProfileMessage;
use BaksDev\Users\Profile\UserProfile\Repository\UniqProfileUrl\UniqProfileUrlInterface;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Avatar\AvatarDTO;
use Doctrine\ORM\EntityManagerInterface;
use DomainException;

final class UserProfileHandler extends AbstractHandler
{

    private UniqProfileUrlInterface $uniqProfileUrl;

    public function __construct(
        EntityManagerInterface $entityManager,
        MessageDispatchInterface $messageDispatch,
        ValidatorCollectionInterface $validatorCollection,
        ImageUploadInterface $imageUpload,
        FileUploadInterface $fileUpload,

        UniqProfileUrlInterface $uniqProfileUrl,
    )
    {
        parent::__construct($entityManager, $messageDispatch, $validatorCollection, $imageUpload, $fileUpload);

        $this->uniqProfileUrl = $uniqProfileUrl;
    }

    public function handle(
        UserProfileDTO $command,
    ): string|UserProfile
    {
        /** Валидация DTO  */
        $this->validatorCollection->add($command);

        $this->main = new UserProfile();
        $this->event = new UserProfileEvent();

        try
        {
            $command->getEvent() ? $this->preUpdate($command, true) : $this->prePersist($command);
        }
        catch(DomainException $errorUniqid)
        {
            return $errorUniqid;
        }


        /** @var Info\InfoDTO $infoDTO */
        $infoDTO = $command->getInfo();

        /* Проверяем на уникальность Адрес персональной страницы */
        $uniqProfileUrl = $this->uniqProfileUrl->exist($infoDTO->getUrl(), $this->main->getId());

        if($uniqProfileUrl)
        {
            $this->event->getInfo()->updateUrlUniq();
        }


        /* Если у текущего пользователя имеется активный профиль - деактивируем */
        $InfoActive = $this->entityManager->getRepository(UserProfileInfo::class)
            ->findBy(['usr' => $infoDTO->getUsr(), 'active' => true]);

        if($InfoActive)
        {
            /** @var UserProfileInfo $deactivate */
            foreach($InfoActive as $deactivate)
            {
                if($deactivate->getEvent() !== $command->getEvent())
                {
                    $deactivate->deactivate();
                }
            }
        }


        /* Загружаем файл аватарки профиля */

        /** @var UserProfileAvatar $UserProfileAvatar */
        $UserProfileAvatar = $this->event->getAvatar();
        /** @var AvatarDTO $AvatarDTO */
        $AvatarDTO = $UserProfileAvatar?->getEntityDto();

        if($UserProfileAvatar && $AvatarDTO?->file !== null)
        {
            $this->imageUpload->upload($AvatarDTO->file, $UserProfileAvatar);
        }


        /** Валидация всех объектов */
        if($this->validatorCollection->isInvalid())
        {
            return $this->validatorCollection->getErrorUniqid();
        }

        $this->entityManager->flush();

        /* Отправляем сообщение в шину */
        $this->messageDispatch->dispatch(
            message: new UserProfileMessage($this->main->getId(), $this->main->getEvent(), $command->getEvent()),
            transport: 'users-profile-user'
        );

        return $this->main;
    }



}