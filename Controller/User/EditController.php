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

namespace BaksDev\Users\Profile\UserProfile\Controller\User;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Services\Security\RoleSecurity;
use BaksDev\Users\Profile\UserProfile\Entity;
use BaksDev\Users\Profile\UserProfile\Message\ModerationUserProfile\ModerationUserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileForm;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[RoleSecurity('ROLE_USER')]
final class EditController extends AbstractController
{
    #[Route('/user/profile/edit/{id}', name: 'user.newedit.edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity] Entity\Event\UserProfileEvent $Event,
        EntityManagerInterface $entityManager,
        UserProfileHandler $handler,
        MessageBusInterface $bus,
    ): Response {
        $Info = $entityManager->getRepository(Entity\Info\UserProfileInfo::class)
            ->findOneBy(['profile' => $Event->getProfile()])
        ;

        // НЕ является владельцем профиля
        if (!$Info?->isProfileOwnedUser($this->getUser())) {
            throw new AccessDeniedException();
        }

        $profile = new UserProfileDTO();
        $Event->getDto($profile);
        $Info->getDto($profile->getInfo());

        // Форма
        $form = $this->createForm(UserProfileForm::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $form->has('Save')) {
            $UserProfile = $handler->handle($profile);

            if ($UserProfile instanceof Entity\UserProfile) {
                $this->addFlash('success', 'user.success.update', 'user.user.profile');

            // Отправляем уведомление о модерации в телегу
            // $telega = new ModerationUserProfileDTO($UserProfile->getEvent());
            // $bus->dispatch($telega);
            } else {
                $this->addFlash('danger', 'user.danger.update', 'user.user.profile', $UserProfile);
            }

            return $this->redirectToRoute('UserProfile:user.index');
        }

        return $this->render(['form' => $form->createView()]);
    }
}
