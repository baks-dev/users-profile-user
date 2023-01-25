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

namespace BaksDev\Users\Profile\UserProfile\Controller\Admin;

use BaksDev\Core\Services\Security\RoleSecurity;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\UserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\UserProfileForm;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Users\Profile\UserProfile\Entity\Event\UserProfileEvent;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\UserProfileHandler;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;


#[RoleSecurity(['ROLE_ADMIN', 'ROLE_USERPROFILE_EDIT'])]
final class EditController extends AbstractController
{
	
	#[Route('/admin/users/profile/edit/{id}', name: 'admin.newedit.edit', methods: ['GET', 'POST'])]
	//#[ParamConverter('Event', \BaksDev\Users\Profile\UserProfile\Entity\Event\Event::class)]
	public function edit(
		Request $request,
		#[MapEntity] UserProfileEvent $Event,
		EntityManagerInterface $entityManager,
		UserProfileHandler $handler,
	) : Response
	{
		
		$profile = new UserProfileDTO();
		$Event->getDto($profile);
		
		$Info = $entityManager->getRepository(UserProfileInfo::class)->findOneBy(
			['profile' => $Event->getProfile()]
		);
		$Info->getDto($profile->getInfo());
		
		/* Форма */
		$form = $this->createForm(UserProfileForm::class, $profile);
		$form->handleRequest($request);
		
		if($form->isSubmitted() && $form->isValid() && $form->has('Save'))
		{
			$UserProfile = $handler->handle($profile);
			$this->addFlash('success', 'admin.success.update', 'admin.user.profile');
			
			if(!$UserProfile instanceof UserProfile)
			{
				$this->addFlash('danger', 'admin.danger.update', 'admin.user.profile', $UserProfile);
			}
			
			return $this->redirectToRoute('UserProfile:admin.index');
		}
		
		return $this->render(['form' => $form->createView()]);
	}
	
}