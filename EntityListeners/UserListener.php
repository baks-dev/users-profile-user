<?php
/*
 * Copyright (c) 2022.  Baks.dev <admin@baks.dev>
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

namespace BaksDev\Users\Profile\UserProfile\EntityListeners;

use BaksDev\Users\Profile\UserProfile\Repository\CurrentUserProfile\CurrentUserProfileInterface;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UserListener
{
    //private KernelInterface $kernel;
    private CurrentUserProfileInterface $getCurrentProfile;
    //private RequestStack $request;
    private TranslatorInterface $translator;
    
    public function __construct(
      CurrentUserProfileInterface $getCurrentProfile,
      //KernelInterface $kernel,
      RequestStack $request,
      TranslatorInterface $translator,
    )
    {
        
        //$this->kernel = $kernel;
        $this->getCurrentProfile = $getCurrentProfile;
        //$this->request = $request;
        $this->translator = $translator;
    }
    
    public function postLoad(UserInterface $data, LifecycleEventArgs $event) : void
    {

        $cache = new FilesystemAdapter();
        
        //$locale = $this->request->getCurrentRequest()->getLocale();
        $locale = $this->translator->getLocale();
        
        $userProfile = $cache->get('profile-'.$locale.'-'.$data->getId(),
          function (ItemInterface $item) use ($data)
          {
              $item->expiresAfter(3600); // 3600 = 1 час / 86400 - сутки
 
              $profile = $this->getCurrentProfile->get($data->getId());
    
    
    
              //dd($userProfile);
              /* Присваиваем пользователю роли */
              //!$userProfile ?: $data->setProfile($userProfile);
    
              //return;
              
              
              
              /*$profile = $this->getCurrentProfile->get($data->getId(), new Locale($locale));
              
              if($profile['user_profile_id'] === null)
              {
                  $this->request->getSession()->getFlashBag()->add
                  (
                    'danger',
                    $this->translator->trans('user.profile.added.flash', domain: 'user.userprofile')
                  );
              }*/
              
              /* Получаем профиль согласно группе пользователя */
              return $profile ?: null;
          });
        
        /* Сбрасываем кеш если DEV */
//        if($this->kernel->getEnvironment() === 'dev')
//        {
//            /* Сбрасываем кеш */
          $cache->delete('profile-'.$locale.'-'.$data->getId());
//        }
        
        /* Присваиваем пользователю роли */
        $data->setProfile($userProfile);
        
    }
    
}