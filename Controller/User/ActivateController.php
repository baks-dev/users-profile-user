<?php
/*
 *  Copyright Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Users\Profile\UserProfile\Controller\User;


use BaksDev\Users\Profile\UserProfile\UseCase\User\Activate\ActivateUserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\Activate\ActivateUserProfilehandler;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Users\Profile\UserProfile\Entity as EntityUserProfile;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
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
		//Request $request,
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

		return $this->redirectToReferer();
		
	}
}