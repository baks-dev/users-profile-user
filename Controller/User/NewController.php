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

use BaksDev\Auth\Email\Type\Event\AccountEventConverter;
use BaksDev\Auth\Email\Type\Event\AccountEventType;
use BaksDev\Auth\Email\Type\Event\AccountEventUid;
use BaksDev\Users\Profile\TypeProfile\Entity\TypeProfile;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileForm;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\UserProfileHandler;
use BaksDev\Core\Controller\AbstractController;

use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;


#[IsGranted('ROLE_USER')]
final class NewController extends AbstractController
{
	#[Route('/user/profile/new/{id}', name: 'user.newedit.new', methods: ['GET', 'POST'])]
	public function new(
		Request $request,
		#[MapEntity] TypeProfile $type,
		UserProfileHandler $handler,
		MessageBusInterface $bus
	) : Response
	{

		$profile = new UserProfileDTO();
		$profile->setType($type->getId());
		$profile->getInfo()->setUser($this->getUser());
		
		/* Форма */
		$form = $this->createForm(UserProfileForm::class, $profile);
		$form->handleRequest($request);
		
		if($form->isSubmitted() && $form->isValid() && $form->has('Save'))
		{
			$UserProfile = $handler->handle($profile);
			
			if($UserProfile instanceof UserProfile)
			{
				$this->addFlash('success', 'user.success.new', 'user.user.profile');
				
				/* Отправляем уведомление о модерации в телегу */
				//$telega = new ModerationUserProfileDTO($UserProfile->getEvent());
				//$bus->dispatch($telega);
			} else
			{
				$this->addFlash('danger', 'user.danger.new', 'user.user.profile');
			}
			
			return $this->redirectToRoute('UserProfile:user.index');
			
		}
		
		
		return $this->render(['form' => $form->createView()]);
	}
}