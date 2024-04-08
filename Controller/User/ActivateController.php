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

use BaksDev\Core\Cache\AppCacheInterface;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Users\Profile\UserProfile\Entity as EntityUserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\UseCase\User\Activate\ActivateUserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\Activate\ActivateUserProfileHandler;
use BaksDev\Users\User\Repository\GetUserById\GetUserByIdRepository;
use BaksDev\Users\User\Type\Id\UserUid;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\SwitchUserToken;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

#[AsController]
#[RoleSecurity('ROLE_USER')]
final class ActivateController extends AbstractController
{
    #[Route('/user/profile/activate/{id}', name: 'user.activate', methods: ['GET'])]
    public function activate(
        Request $request,
        #[MapEntity] EntityUserProfile\Event\UserProfileEvent $Event,
        ActivateUserProfileHandler $handler,
        EntityManagerInterface $entityManager,
        AppCacheInterface $cache,
        TokenStorageInterface $tokenStorage,
        GetUserByIdRepository $getUserById
    ): Response
    {
        $AppCache = $cache->init('Authority');
        $AppCache->delete((string) $this->getCurrentUsr());
        $AppCache->getItem((string) $this->getCurrentUsr());

        $profile = new ActivateUserProfileDTO();
        $Event->getDto($profile);

        $Info = $entityManager->getRepository(EntityUserProfile\Info\UserProfileInfo::class)
            ->findOneBy(['profile' => $Event->getMain()]);

        if(
            !$Info
            || $Info->isNotStatusActive() // профиль НЕ на модерации или заблокирован
            || !$Info->isProfileOwnedUser($this->getCurrentUsr()) // Профиль не принадлежит пользователю
        )
        {
            throw new AccessDeniedException();
        }

        $Info->getDto($profile->getInfo());
        $UserProfile = $handler->handle($profile);

        if($UserProfile instanceof EntityUserProfile\UserProfile)
        {
            $AppCache = $cache->init((string) $this->getCurrentUsr());
            $AppCache->clear();

            /**
             * Если пользователь был авторизован по доверенности -
             * сбрасываем и присваиваем активный профиль с соответствующими правами
             */

            $token = $tokenStorage->getToken();

            if($token instanceof SwitchUserToken)
            {
                $CurrentUsr = $token->getOriginalToken()->getUser();
                $CurrentUsr?->setProfile($UserProfile->getId());
            }
            else
            {
                $CurrentUsr = $getUserById->get($this->getCurrentUsr());
            }

            if($CurrentUsr)
            {
                $AppCache = $cache->init((string) $CurrentUsr);
                $AppCache->clear();

                $impersonationToken = new  UsernamePasswordToken(
                    $CurrentUsr,
                    "user",
                    $CurrentUsr->getRoles()
                );

                $tokenStorage->setToken($impersonationToken);
            }

            $this->addFlash('success', 'user.success.activate', 'user.user.profile');
        }

        return $this->redirectToReferer();
    }
}
