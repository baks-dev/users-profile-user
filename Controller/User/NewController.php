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

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Users\Profile\TypeProfile\Entity\TypeProfile;
use BaksDev\Users\Profile\TypeProfile\Type\Id\Choice\TypeProfileUser;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileForm;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileHandler;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Repository\GetUserById\GetUserByIdRepository;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

#[AsController]
#[RoleSecurity('ROLE_USER')]
final class NewController extends AbstractController
{
    #[Route('/user/profile/new/{id}', name: 'user.newedit.new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        #[MapEntity] TypeProfile $type,
        UserProfileHandler $UserProfileHandler,
        TokenStorageInterface $tokenStorage,
        GetUserByIdRepository $getUserById,
        AppCacheInterface $cache,
    ): Response
    {
        $UserProfileDTO = new UserProfileDTO();
        $UserProfileDTO->setType($type->getId());
        $UserProfileDTO->getInfo()->setUsr($this->getUsr());

        /** Если профиль пользовательский - делаем активным */
        if($UserProfileDTO->getType()->getTypeProfile() instanceof TypeProfileUser)
        {
            $UserProfileDTO->getInfo()->setStatus(UserProfileStatusActive::class);
        }

        // Форма
        $form = $this->createForm(UserProfileForm::class, $UserProfileDTO);
        $form->handleRequest($request);

        if($form->isSubmitted() && $form->isValid() && $form->has('Save'))
        {
            $this->refreshTokenForm($form);

            $handle = $UserProfileHandler->handle($UserProfileDTO);

            /** сбрасываем и присваиваем активный профиль с соответствующими только с правами ROLE_USER */

            if($handle instanceof  UserProfile)
            {

                //$Session = $request->getSession();
                //$Session->remove('Authority');

                //$AppCache = $cache->init('Authority');
                //$AppCache->delete((string) $this->getCurrentUsr());

                //$AppCache = $cache->init((string) $this->getCurrentUsr());
                //$AppCache->clear();

                /** @var User $CurrentUsr */
                $CurrentUsr = $getUserById->get($this->getCurrentUsr());
                //$CurrentUsr->setRole(['ROLE_USER']);

                $impersonationToken = new  UsernamePasswordToken(
                    $CurrentUsr,
                    "user",
                    ['ROLE_USER']
                );

                $tokenStorage->setToken($impersonationToken);
            }

            $this->addFlash
            (
                type: 'page.new',
                message: $handle instanceof UserProfile ? 'user.success.new' : 'user.danger.new',
                domain: 'user.profile',
                arguments: $handle,
            );

            return $this->redirectToRoute('users-profile-user:user.index');
        }

        return $this->render(['form' => $form->createView()]);
    }
}
