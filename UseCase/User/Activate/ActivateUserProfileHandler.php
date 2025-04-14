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

namespace BaksDev\Users\Profile\UserProfile\UseCase\User\Activate;

use BaksDev\Core\Entity\AbstractHandler;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Messenger\UserProfileMessage;

final class ActivateUserProfileHandler extends AbstractHandler
{
    public function handle(ActivateUserProfileDTO $command): string|UserProfile
    {
        $this
            ->setCommand($command)
            ->preEventPersistOrUpdate(UserProfile::class, UserProfileEvent::class);


        $UserProfileInfo = $this
            ->getRepository(UserProfileInfo::class)
            ->find($this->event->getMain());

        if(!$UserProfileInfo)
        {
            $UserProfileInfo = new UserProfileInfo($this->event->getMain());
            $this->persist($UserProfileInfo);
        }

        $this->validatorCollection->add($UserProfileInfo);


        /** @var Info\InfoDTO $infoDTO */
        $infoDTO = $command->getInfo();


        /* Если у текущего пользователя имеется активный профиль - деактивируем */
        $InfoActive = $this
            ->getRepository(UserProfileInfo::class)
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

        /* Присваиваем событие INFO */
        $UserProfileInfo->setEntity($infoDTO);
        $this->validatorCollection->add($UserProfileInfo);

        /* Валидация всех объектов */
        if($this->validatorCollection->isInvalid())
        {
            return $this->validatorCollection->getErrorUniqid();
        }

        $this->flush();

        /* Отправляем событие в шину  */
        $this->messageDispatch
            ->dispatch(
                message: new UserProfileMessage($this->main->getId(), $this->main->getEvent(), $command->getEvent()),
                transport: 'users-profile-user'
            );

        return $this->main;

    }
}
