<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Users\Profile\UserProfile\Repository\MenuUserProfileByUser;

//use App\Module\Users\AuthEmail\Account\Entity\Account;
//use App\Module\Users\AuthEmail\Account\Entity\Event\Event as AccountEvent;
//use BaksDev\Users\Profile\TypeProfile\Entity as TypeProfileEntity;
use BaksDev\Users\Profile\UserProfile\Entity;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatus;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatusEnum;
//use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;
//use BaksDev\Core\Handler\Search\SearchDTO;

use BaksDev\Core\Services\Switcher\Switcher;
use BaksDev\Core\Type\Locale\Locale;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Cache\Adapter\ApcuAdapter;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class MenuUserProfileByUser implements MenuUserProfileByUserInterface
{
    public const LIFETIME = 60 * 60 * 30;
    
    private Connection $connection;
    //private Locale $local;
    //private Switcher $switcher;
    //private ?UserInterface $user = null;
	private Security $security;
	
	public function __construct(
      Connection $connection,
      //TranslatorInterface $translator,
      //Switcher $switcher,
      Security $security
    )
    {
        $this->connection = $connection;
		$this->security = $security;
	}
    
    /** Список профилей пользователя в меню  */
    public function get() : array
    {
		$this->user = $this->security->getUser();
		
        if($this->user === null) { return []; }
        
        $qb = $this->connection->createQueryBuilder();
        
        $qb->addSelect('userprofile.event');
    
    
        $qb->from(Entity\Info\UserProfileInfo::TABLE, 'userprofile_info');
        $qb->where('userprofile_info.user_id = :user_id AND userprofile_info.status = :status');
        
        
        $qb->join('userprofile_info', Entity\UserProfile::TABLE, 'userprofile', 'userprofile.id = userprofile_info.profile');
        

        $qb->setParameter('user_id', $this->user->getId(), UserUid::TYPE);
        $qb->setParameter('status', new UserProfileStatus(UserProfileStatusEnum::ACTIVE), UserProfileStatus::TYPE);
        
        $qb->join(
          'userprofile',
          Entity\Event\UserProfileEvent::TABLE,
          'userprofile_event',
          'userprofile_event.id = userprofile.event');
        
    
        $qb->addSelect('userprofile_profile.username');
    
        /* Профиль */
        $qb->join(
          'userprofile',
          Entity\Personal\UserProfilePersonal::TABLE,
          'userprofile_profile',
          'userprofile_profile.event = userprofile.event');
        
      
        $qb->orderBy('userprofile_event.sort', 'ASC');
    
        $cacheFilesystem = new FilesystemAdapter('UserProfile');
   
        
       $config = $this->connection->getConfiguration();
       $config?->setResultCache($cacheFilesystem);
       
    
        return $this->connection->executeCacheQuery(
          $qb->getSQL(),
          $qb->getParameters(),
          $qb->getParameterTypes(),
          new QueryCacheProfile(self::LIFETIME, (string) $this->user->getId()->getValue())
        )->fetchAllAssociative();
    }
}
