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

use BaksDev\Users\Profile\UserProfile\Entity;
use BaksDev\Users\Profile\UserProfile\Message\ModerationUserProfile\ModerationUserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileForm;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileHandler;
use BaksDev\Users\Profile\UserProfile\UseCase\UserProfileAggregate;
use BaksDev\Core\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('ROLE_USER')]
final class EditController extends AbstractController
{
	
	#[Route('/user/profile/edit/{id}', name: 'user.newedit.edit', methods: ['GET', 'POST'])]
	public function edit(
		Request $request,
		#[MapEntity] Entity\Event\UserProfileEvent $Event,
		EntityManagerInterface $entityManager,
		UserProfileHandler $handler,
		MessageBusInterface $bus
	) : Response
	{
		
		$Info = $entityManager->getRepository(Entity\Info\UserProfileInfo::class)
			->findOneBy(['profile' => $Event->getProfile()])
		;
		
		if(!$Info?->isProfileOwnedUser($this->getUser()))
		{
			throw new AccessDeniedException();
		}
		
		$profile = new UserProfileDTO();
		$Event->getDto($profile);
		$Info->getDto($profile->getInfo());
		
		/* Форма */
		$form = $this->createForm(UserProfileForm::class, $profile);
		$form->handleRequest($request);
		
		
		if($form->isSubmitted() && $form->isValid() && $form->has('Save'))
		{
			$UserProfile = $handler->handle($profile);
			
			if($UserProfile instanceof Entity\UserProfile)
			{
				$this->addFlash('success', 'user.success.update', 'user.user.profile');
				
				/* Отправляем уведомление о модерации в телегу */
				//$telega = new ModerationUserProfileDTO($UserProfile->getEvent());
				//$bus->dispatch($telega);
			}
			else
			{
				$this->addFlash('danger', 'user.danger.update', 'user.user.profile', $UserProfile);
			}
			
			return $this->redirectToRoute('UserProfile:user.index');
			
		}
		
		return $this->render(['form' => $form->createView()]);
	}
	
}