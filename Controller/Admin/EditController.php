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

use BaksDev\Users\Profile\UserProfile\Entity;
use BaksDev\Users\Profile\UserProfile\Message\ModerationUserProfile\ModerationUserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\UserProfileDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\UserProfileForm;
use BaksDev\Users\Profile\UserProfile\UseCase\UserProfileAggregate;
use BaksDev\Core\Controller\AbstractController;

use Doctrine\ORM\EntityManagerInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Security("is_granted('ROLE_ADMIN') or is_granted('ROLE_USERPROFILE_EDIT')")]
final class EditController extends AbstractController
{
    
    #[Route('/admin/users/profile/edit/{id}', name: 'admin.newedit.edit', methods: ['GET', 'POST'])]
    #[ParamConverter('Event', \BaksDev\Users\Profile\UserProfile\Entity\Event\Event::class)]
    public function edit(
      Request $request,
      \BaksDev\Users\Profile\UserProfile\Entity\Event\Event $Event,
      EntityManagerInterface $entityManager,
      UserProfileAggregate $userProfileAggregate,
    ) : Response
    {
        
        $profile = new UserProfileDTO();
        $Event->getDto($profile);
        
        $Info = $entityManager->getRepository(\BaksDev\Users\Profile\UserProfile\Entity\Info\Info::class)->findOneBy(['profile' => $Event->getProfile()]);
        $Info->getDto($profile->getInfo());
        
        /* Форма */
        $form = $this->createForm(UserProfileForm::class, $profile);
        $form->handleRequest($request);
        
        if($form->isSubmitted() && $form->isValid())
        {
            if($form->has('Save'))
            {
                $UserProfile = $userProfileAggregate->handle($profile);
                $this->addFlash('success', 'admin.success.update', 'userprofile');
                
                if(!$UserProfile instanceof \BaksDev\Users\Profile\UserProfile\Entity\UserProfile)
                {
                    $this->addFlash('danger', 'admin.danger.update', 'userprofile', [$UserProfile]);
                }
            }
            else
            {
                $this->addFlash('danger', 'admin.danger.update', 'userprofile', ['POST']);
            }
            
            return $this->redirectToRoute(
              'UserProfile:admin.index',
              ['status' => $profile->getInfo()->getStatus()]);
            
        }
        
        return $this->render(['form' => $form->createView()]);
    }
    
}