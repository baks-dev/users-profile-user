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

namespace BaksDev\Users\Profile\UserProfile\Controller\Admin;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\UserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\UserProfileForm;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\UserProfileHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;

#[AsController]
#[RoleSecurity('ROLE_USERPROFILE_EDIT')]
final class EditController extends AbstractController
{
    #[Route('/admin/users/profile/edit/{id}', name: 'admin.newedit.edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        #[MapEntity] UserProfileEvent $Event,
        EntityManagerInterface $entityManager,
        UserProfileHandler $UserProfileHandler,
    ): Response
    {
        $UserProfileDTO = new UserProfileDTO();
        $Event->getDto($UserProfileDTO);

        $Info = $entityManager->getRepository(UserProfileInfo::class)->findOneBy(
            ['profile' => $Event->getProfile()]
        );
        $Info->getDto($UserProfileDTO->getInfo());

        // Форма
        $form = $this->createForm(UserProfileForm::class, $UserProfileDTO);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('Save'))
        {
            $handle = $UserProfileHandler->handle($UserProfileDTO);

            $this->addFlash
            (
                'admin.page.edit',
                $handle instanceof UserProfile ? 'admin.success.edit' : 'admin.danger.edit',
                'admin.user.profile',
                $handle
            );

            return $this->redirectToRoute('users-profile-user:admin.index');

        }

        return $this->render(['form' => $form->createView()]);
    }
}
