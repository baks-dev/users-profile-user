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
use BaksDev\Core\Services\Security\RoleSecurity;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Info\Info;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\Delete\DeleteUserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\Delete\DeleteUserProfileForm;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[RoleSecurity('ROLE_USERPROFILE_DELETE')]
final class DeleteController extends AbstractController
{
    #[Route('/admin/users/profile/delete/{id}', name: 'admin.delete', methods: ['GET', 'POST'])]
    public function delete(
        Request $request,
        #[MapEntity] UserProfileEvent $Event,
        // UserProfileAggregate $aggregate,
        // EntityManagerInterface $entityManager,
    ): Response {
        return new Response('OK');

        $profile = new DeleteUserProfileDTO();
        $Event->getDto($profile);

        $Info = $entityManager->getRepository(Info::class)
            ->findOneBy(['profile' => $Event->getProfile()])
        ;
        $Info->getDto($profile->getInfo());

        $form = $this->createForm(DeleteUserProfileForm::class, $profile, [
            'action' => $this->generateUrl('UserProfile:admin.delete', ['id' => $profile->getEvent()]),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->has('delete')) {
                $handle = $aggregate->handle($profile);

                if ($handle) {
                    $this->addFlash('success', 'admin.success.delete', 'userprofile');

                    return $this->redirectToRoute('UserProfile:admin.index');
                }
            }

            $this->addFlash('danger', 'admin.danger.delete', 'userprofile');

            return $this->redirectToRoute('UserProfile:admin.index');
            // return $this->redirectToReferer();
        }

        return $this->render(
            [
                'form' => $form->createView(),
                'name' => $Event->getNameUserProfile(), // название профиля пользователя
            ]
        );
    }
}
