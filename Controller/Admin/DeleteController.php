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

use BaksDev\Users\Profile\UserProfile\Entity\Event\Event;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\Delete\DeleteUserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\Delete\DeleteUserProfileForm;
use BaksDev\Users\Profile\UserProfile\UseCase\UserProfileAggregate;
use BaksDev\Core\Controller\AbstractController;
use BaksDev\Users\Profile\UserProfile\Entity;
use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_USERPROFILE_DELETE')")]
final class DeleteController extends AbstractController
{
    
    #[Route('/admin/users/profile/delete/{id}', name: 'admin.delete', methods: ['GET', 'POST'])]
    #[ParamConverter('Event', \BaksDev\Users\Profile\UserProfile\Entity\Event\Event::class)]
    public function delete(
      Request $request,
      Event $Event,
      UserProfileAggregate $aggregate,
      EntityManagerInterface $entityManager,
        //      TranslatorInterface $translator,
        //      TransName $getTransName,
        //      Handler $handler,
        //      Profile $profile,
    ) : Response
    {
        $profile = new DeleteUserProfileDTO();
        $Event->getDto($profile);
    
        $Info = $entityManager->getRepository(\BaksDev\Users\Profile\UserProfile\Entity\Info\Info::class)->findOneBy(['profile' => $Event->getProfile()]);
        $Info->getDto($profile->getInfo());
    
        $form = $this->createForm(DeleteUserProfileForm::class, $profile, [
          'action' => $this->generateUrl('UserProfile:admin.delete', ['id' => $profile->getEvent()]),
        ]);
        $form->handleRequest($request);
    
    
        if($form->isSubmitted() && $form->isValid())
        {
            if($form->has('delete'))
            {
                $handle = $aggregate->handle($profile);
            
                if($handle)
                {
                    $this->addFlash('success', 'admin.success.delete', 'userprofile');
                    return $this->redirectToRoute('UserProfile:admin.index');
                }
            }
        
            $this->addFlash('danger', 'admin.danger.delete', 'userprofile');
            return $this->redirectToRoute('UserProfile:admin.index');
        
            //return $this->redirectToReferer();
        }
    
        return $this->render
        (
          [
            'form' => $form->createView(),
            'name' => $Event->getNameUserProfile() /*  название профиля пользователя  */
          ]
        );
    }
}