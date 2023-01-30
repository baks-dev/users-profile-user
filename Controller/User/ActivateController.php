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

use BaksDev\Users\Profile\UserProfile\UseCase\User\Activate\ActivateUserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\Activate\ActivateUserProfilehandler;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Users\Profile\UserProfile\Entity as EntityUserProfile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('ROLE_USER')]
final class ActivateController extends AbstractController
{
	#[Route('/user/profile/activate/{id}', name: 'user.activate', methods: ['GET'])]
	public function activate(
		Request $request,
		#[MapEntity] EntityUserProfile\Event\UserProfileEvent $Event,
		ActivateUserProfilehandler $handler,
		EntityManagerInterface $entityManager,
	
	) : Response
	{
		
		$profile = new ActivateUserProfileDTO();
		$Event->getDto($profile);
		
		$Info = $entityManager->getRepository(EntityUserProfile\Info\UserProfileInfo::class)
			->findOneBy(['profile' => $Event->getProfile()])
		;
		
		if(
			!$Info ||
			!$Info->isProfileOwnedUser($this->getUser()) || /* Профиль не принадлежит пользователю */
			$Info->isNotActiveProfile() || /* текущий профиль НЕ активен */
			$Info->isNotStatusActive() /* профиль НЕ на модерации или заблокирован */
		)
		{
			throw new AccessDeniedException();
		}
		
		$Info->getDto($profile->getInfo());
		$UserProfile = $handler->handle($profile);
		
		if($UserProfile instanceof EntityUserProfile\UserProfile)
		{
			$this->addFlash('success', 'user.success.activate', 'user.user.profile');
		}
		else
		{
			$this->addFlash('danger', 'user.danger.delete', 'user.user.profile', $UserProfile);
		}
		
		/* Чистим кеш профиля */
		$cache = new FilesystemAdapter('CacheUserProfile');
		$cache->delete('current_user_profile'.$this->getUser()?->getId().$request->getLocale());
		
		return $this->redirectToReferer();
	}
	
}