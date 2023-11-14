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
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\UseCase\User\Delete\DeleteUserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\Delete\DeleteUserProfileForm;
use BaksDev\Users\Profile\UserProfile\UseCase\User\Delete\DeleteUserProfileHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[AsController]
#[RoleSecurity('ROLE_USER')]
final class DeleteController extends AbstractController
{
    #[Route(
        '/user/profile/delete/{id}', name: 'user.delete', methods: ['POST', 'GET'],
        //condition: "request.headers.get('X-Requested-With') === 'XMLHttpRequest'",
    )]
    public function delete(
        Request $request,
        #[MapEntity] UserProfileEvent $Event,
        DeleteUserProfileHandler $aggregate,
        EntityManagerInterface $entityManager,
        //      TranslatorInterface $translator,
        //      TransName $getTransName,
        //      Handler $handler,
        //      Profile $profile,
    ): Response
    {

        $Info = $entityManager->getRepository(UserProfileInfo::class)
            ->findOneBy(['profile' => $Event->getMain()]);

        if(!$Info)
        {
            throw new NotFoundHttpException();
        }

        // НЕ является владельцем профиля
        if(!$Info->isProfileOwnedUser($this->getUsr()))
        {
            throw new AccessDeniedException();
        }

        $profile = new DeleteUserProfileDTO();
        $Event->getDto($profile);

        $Info->getDto($profile->getInfo());

        $form = $this->createForm(DeleteUserProfileForm::class, $profile, [
            'action' => $this->generateUrl('users-profile-user:user.delete', ['id' => $profile->getEvent()]),
        ]);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('delete'))
        {
            $handle = $aggregate->handle($profile);

            $this->addFlash
            (
                'user.page.delete',
                $handle instanceof UserProfile ? 'user.success.delete' : 'user.danger.delete',
                'user.user.profile',
                $handle
            );

            return $this->redirectToRoute('users-profile-user:user.index');
        }

        return $this->render(
            [
                'form' => $form->createView(),
                'name' => $Event->getNameUserProfile(), // название согласно локали
            ]
        );
    }
}
