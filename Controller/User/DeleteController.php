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

use BaksDev\Users\Profile\UserProfile\Entity as EntityUserProfile;

//use BaksDev\Users\Profile\UserProfile\UseCase\User\Delete\DeleteUserProfileDTO;
//use BaksDev\Users\Profile\UserProfile\UseCase\User\Delete\DeleteUserProfileForm;
//use BaksDev\Users\Profile\UserProfile\UseCase\UserProfileAggregate;
use BaksDev\Users\Profile\UserProfile\UseCase\User\Delete\DeleteUserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\User\Delete\DeleteUserProfileForm;
use BaksDev\Users\Profile\UserProfile\UseCase\User\Delete\DeleteUserProfileHandler;
use BaksDev\Core\Controller\AbstractController;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Contracts\Translation\TranslatorInterface;

#[IsGranted('ROLE_USER')]
final class DeleteController extends AbstractController
{
	
	#[Route('/user/profile/delete/{id}', name: 'user.delete', methods: ['POST', 'GET'],
		condition: "request.headers.get('X-Requested-With') === 'XMLHttpRequest'",
	)]
	public function delete(
		Request $request,
		EntityUserProfile\Event\UserProfileEvent $Event,
		DeleteUserProfileHandler $aggregate,
		EntityManagerInterface $entityManager,
		//      TranslatorInterface $translator,
		//      TransName $getTransName,
		//      Handler $handler,
		//      Profile $profile,
	) : Response
	{
		
		$Info = $entityManager->getRepository(EntityUserProfile\Info\UserProfileInfo::class)
			->findOneBy(['profile' => $Event->getProfile()])
		;
		
		if(!$Info)
		{
			throw new NotFoundHttpException();
		}
		
		if(!$Info->isProfileOwnedUser($this->getUser()))
		{
			throw new AccessDeniedException();
		}
		
		$profile = new DeleteUserProfileDTO();
		$Event->getDto($profile);
		
		$Info->getDto($profile->getInfo());
		
		$form = $this->createForm(DeleteUserProfileForm::class, $profile, [
			'action' => $this->generateUrl('UserProfile:user.delete', ['id' => $profile->getEvent()]),
		]);
		$form->handleRequest($request);
		
		
		if($form->isSubmitted() && $form->isValid() && $form->has('delete'))
		{
			
			$UserProfile = $aggregate->handle($profile);
			if($UserProfile instanceof EntityUserProfile\UserProfile)
			{
	
				$this->addFlash('user.form.delete.header', 'user.success.delete', 'user.user.profile');
				return $this->redirectToRoute('UserProfile:admin.index');
			}
			
			$this->addFlash('user.form.delete.header', 'user.danger.delete', 'user.user.profile', $UserProfile);
			return $this->redirectToRoute('UserProfile:user.index', status: 400);
			
		}
		
		
		return $this->render
		(
			[
				'form' => $form->createView(),
				'name' => $Event->getNameUserProfile(), /*  название согласно локали  */
			],
			'content.html.twig'
		);
		
		//        return $this->render
		//        (
		//          [
		//            'form' => $form->createView(),
		//            'name' => $Event->getNameUserProfile() /*  название профиля пользователя  */
		//          ]
		//        );
	}
}