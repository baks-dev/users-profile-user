<?php
/*
*  Copyright  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Users\Profile\UserProfile\EntityListeners;

use BaksDev\Users\Profile\UserProfile\Entity\Modify\UserProfileModify;
use BaksDev\Core\Type\Ip\IpAddress;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class UserProfileModifyListener
{
    private RequestStack $request;
    private TokenStorageInterface $token;
    
    public function __construct(
      RequestStack $request,
      TokenStorageInterface $token,
    )
    {
        $this->request = $request;
        $this->token = $token;
    }
    
    public function prePersist(UserProfileModify $data, LifecycleEventArgs $event) : void
    {
        $token = $this->token->getToken();
        
        if($token)
        {
            $data->setUser($token->getUser());
        }
        
        /* Если пользователь не из консоли */
        if($this->request->getCurrentRequest())
        {
            
            $data->upModifyAgent(
              new IpAddress($this->request->getCurrentRequest()->getClientIp()), /* Ip */
              $this->request->getCurrentRequest()->headers->get('User-Agent') /* User-Agent */
            );
        }
    }
    
}