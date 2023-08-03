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
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Users\Profile\TypeProfile\Entity\TypeProfile;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileForm;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileHandler;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[RoleSecurity('ROLE_USER')]
final class NewController extends AbstractController
{
    #[Route('/user/profile/new/{id}', name: 'user.newedit.new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        #[MapEntity] TypeProfile $type,
        UserProfileHandler $handler,
        //MessageBusInterface $bus,
    ): Response {
        $profile = new UserProfileDTO();
        $profile->setType($type->getId());
        $profile->getInfo()->setUser($this->getUser());

        // Форма
        $form = $this->createForm(UserProfileForm::class, $profile);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid() && $form->has('Save')) {
            $UserProfile = $handler->handle($profile);

            if ($UserProfile instanceof UserProfile) {
                $this->addFlash('success', 'user.success.new', 'user.user.profile');

                // Отправляем уведомление о модерации в телегу
                // $telega = new ModerationUserProfileDTO($UserProfile->getEvent());
                // $bus->dispatch($telega);
            } else {
                $this->addFlash('danger', 'user.danger.new', 'user.user.profile');
            }

            return $this->redirectToRoute('UserProfile:user.index');
        }

        return $this->render(['form' => $form->createView()]);
    }
}
