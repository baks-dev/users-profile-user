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

namespace BaksDev\Users\Profile\UserProfile\Repository\CurrentAllUserProfiles;

use BaksDev\Users\Profile\UserProfile\Entity;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatus;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatusEnum;
use BaksDev\Users\User\Type\Id\UserUid;
use Doctrine\DBAL\Cache\QueryCacheProfile;
use Doctrine\DBAL\Connection;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;

final class CurrentAllUserProfilesByUser implements CurrentAllUserProfilesByUserInterface
{
	public const LIFETIME = 60 * 60 * 30;
	
	private Connection $connection;
	
	
	//private Security $security;
	
	public function __construct(
		Connection $connection,
		//Security $security
	)
	{
		$this->connection = $connection;
		//$this->security = $security;
	}
	
	
	/** Список профилей пользователя в меню
	 *
	 * Возвращает массив с ключами: <br>
	 * user_profile_event - идентификатор события для активации профиля <br>
	 * user_profile_username - username профиля <br>
	 */
	public function fetchUserProfilesAllAssociative(UserUid $user) : ?array
	{
		
		$qb = $this->connection->createQueryBuilder();
		
		$qb->addSelect('userprofile.event AS user_profile_event');
		
		$qb->from(Entity\Info\UserProfileInfo::TABLE, 'userprofile_info');
		$qb->where('userprofile_info.user_id = :user_id AND userprofile_info.status = :status');
		
		$qb->join('userprofile_info',
			Entity\UserProfile::TABLE,
			'userprofile',
			'userprofile.id = userprofile_info.profile'
		);
		
		$qb->setParameter('user_id', $user, UserUid::TYPE);
		$qb->setParameter('status', new UserProfileStatus(UserProfileStatusEnum::ACTIVE), UserProfileStatus::TYPE);
		
		$qb->join(
			'userprofile',
			Entity\Event\UserProfileEvent::TABLE,
			'userprofile_event',
			'userprofile_event.id = userprofile.event'
		);
		
		$qb->addSelect('userprofile_profile.username AS user_profile_username');
		
		/* Профиль */
		$qb->join(
			'userprofile',
			Entity\Personal\UserProfilePersonal::TABLE,
			'userprofile_profile',
			'userprofile_profile.event = userprofile.event'
		);
		
		$qb->orderBy('userprofile_event.sort', 'ASC');
		
		$cacheFilesystem = new FilesystemAdapter('CacheUserProfile');
		
		$config = $this->connection->getConfiguration();
		$config?->setResultCache($cacheFilesystem);
		
		return $this->connection->executeCacheQuery(
			$qb->getSQL(),
			$qb->getParameters(),
			$qb->getParameterTypes(),
			new QueryCacheProfile((60 * 60 * 30), (string) 'choice_user_profiles'.$user)
		)->fetchAllAssociative();
	}
	
}
