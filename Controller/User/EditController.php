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

namespace BaksDev\Users\Profile\UserProfile\Controller\User;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Users\Profile\UserProfile\Entity\Event\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileForm;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[AsController]
#[RoleSecurity('ROLE_USER')]
final class EditController extends AbstractController
{
    #[Route('/user/profile/edit/{id}', name: 'user.newedit.edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity] UserProfileEvent $Event,
        EntityManagerInterface $entityManager,
        UserProfileHandler $UserProfileHandler
    ): Response
    {
        $Info = $entityManager->getRepository(UserProfileInfo::class)
            ->findOneBy(['profile' => $Event->getMain()]);

        // НЕ является владельцем профиля
        if(!$Info?->isProfileOwnedUser($this->getUsr()))
        {
            throw new AccessDeniedException();
        }

        $UserProfileDTO = new UserProfileDTO();
        $Event->getDto($UserProfileDTO);
        $Info->getDto($UserProfileDTO->getInfo());

        // Форма
        $form = $this->createForm(UserProfileForm::class, $UserProfileDTO);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('Save'))
        {
            $this->refreshTokenForm($form);

            $handle = $UserProfileHandler->handle($UserProfileDTO);

            $this->addFlash
            (
                'user.page.edit',
                $handle instanceof UserProfile ? 'user.success.edit' : 'user.danger.edit',
                'user.user.profile',
                $handle
            );

            return $this->redirectToRoute('users-profile-user:user.index');
        }

        return $this->render(['form' => $form->createView()]);
    }
}
