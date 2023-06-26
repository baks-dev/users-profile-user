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

declare(strict_types=1);

namespace BaksDev\Users\Profile\UserProfile\Repository\CurrentUserProfile;

use BaksDev\Users\Profile\UserProfile\Entity\Avatar\UserProfileAvatar;
use BaksDev\Users\Profile\UserProfile\Repository\CurrentAllUserProfiles\CurrentAllUserProfilesByUserInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\User\Repository\UserProfile\UserProfileInterface;
use BaksDev\Users\User\Type\Id\UserUid;

final class UserProfileDecorator implements UserProfileInterface
{
	
	public ?UserUid $user;
	
	public ?UserProfileUid $id;
	
	/** Массив текущего профиля пользовтаеля */
	public bool|array $current;
	
	private ?string $username;
	
	private ?string $contact;
	
	private ?string $type;
	
	private ?string $avatar;
	
	private ?string $personal;
	
	private array $allProfiles;
	
	
	public function __construct(
		UserProfileInterface $profile,
		CurrentUserProfileInterface $current,
		CurrentAllUserProfilesByUserInterface $allProfiles,
		string $cdn,
	)
	{
		
		$this->user = $profile->user;
		$this->contact = $profile->getContact();
		
		/* Переопределяем ствойства */
		$this->allProfiles = $allProfiles->fetchUserProfilesAllAssociative($this->user);
		
		$UserProfile = $current->fetchProfileAssociative($this->user);

		$this->username = $UserProfile ? $UserProfile['profile_username'] : $profile->getUsername();
		$this->type = $UserProfile ? $UserProfile['profile_type'] : $profile->getType();
		$this->personal = $UserProfile ? $UserProfile['profile_url'] : null;
		$this->id = $UserProfile ? new UserProfileUid($UserProfile['user_profile_id']) : null;
		
		/* Файл аватарки профиля */
		$avatar = null;
		
		if($UserProfile && !empty($UserProfile['profile_avatar_name']))
		{
			if($UserProfile['profile_avatar_cdn'])
			{
				$avatar .= $cdn;
			}
			
			$avatar .= '/upload/'.UserProfileAvatar::TABLE;
			$avatar .= '/'.$UserProfile['profile_avatar_dir'];
			$avatar .= '/'.$UserProfile['profile_avatar_name'];
			$avatar .= '.'.$UserProfile['profile_avatar_ext'];
		}
		
		$this->avatar = $avatar;
		
	}
	
	
	/**  Username пользователя */
	public function getUsername() : ?string
	{
		return $this->username;
	}
	
	
	/** Контакт */
	public function getContact() : ?string
	{
		return $this->contact;
	}
	
	
	/** Тип пользователя */
	public function getType() : ?string
	{
		return $this->type;
	}
	
	
	/** Адрес персональной страницы */
	public function getPage() : ?string
	{
		return $this->personal;
		///return $this->current ? $this->current['profile_url'] : $this->profile->getType();
	}
	
	
	/** Аватарка */
	public function getImage() : ?string
	{
		return $this->avatar;
	}
	
	
	/** Массив всех профилей пользователя  */
	public function getProfiles() : ?array
	{
		return $this->allProfiles;
	}
	
	
	/** Идентификатор профиля	 */
	public function getId() : ?UserProfileUid
	{
		return $this->id;
	}
	

}