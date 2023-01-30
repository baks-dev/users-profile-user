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

use BaksDev\Users\Profile\TypeProfile\Entity\TypeProfile;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\UserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\UserProfileForm;
use BaksDev\Users\Profile\UserProfile\UseCase\UserProfileAggregate;
use BaksDev\Core\Controller\AbstractController;

//use BaksDev\Core\Services\EntityClone;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_USERPROFILE_NEW')")]
final class NewController extends AbstractController
{
	#[Route('/admin/users/profile/new/{id}', name: 'admin.newedit.new', methods: ['GET', 'POST'])]
	#[ParamConverter('type', TypeProfile::class)]
	public function new(
		Request $request,
		TypeProfile $type,
		UserProfileAggregate $userProfileAggregate,
		//      Event $eventUserProfile,
		//      InfoRepository $getInfo,
		//      TranslatorInterface $translator,
		//      EntityClone $entityClone,
		//      Handler $handler
	) : Response
	{
		
		$profile = new UserProfileDTO();
		$profile->setType($type->getId());
		
		/* Форма */
		$form = $this->createForm(UserProfileForm::class, $profile);
		$form->handleRequest($request);
		
		if($form->isSubmitted() && $form->isValid())
		{
			if($form->has('Save'))
			{
				$handle = $userProfileAggregate->handle($profile);
				
				if($handle)
				{
					$this->addFlash('success', 'admin.success.new', 'userprofile');
					
					/* Если статус Предмодерация */
					if($profile->getInfo()->isModeration())
					{
						return $this->redirectToRoute('UserProfile:admin.index', ['status' => 'mod']);
					}
					
					return $this->redirectToRoute('UserProfile:admin.index');
				}
				
			}
		}
		
		return $this->render(['form' => $form->createView()]);
	}
	
}