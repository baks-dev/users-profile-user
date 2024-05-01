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

namespace BaksDev\Users\Profile\UserProfile\Repository\AllUserProfile;

use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Services\Paginator\Paginator;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;

interface AllUserProfileInterface
{
	/**
	 * Список всех добавленных профилей пользователей
	 *
	 * id - идентификатор профиля <br>
	 * event - идентификатор события профиля,<br>
	 * user_profile_url - адрес персональной страницы,<br>
	 * usr - идентификатор пользователя,<br>
	 *
	 * user_profile_status - статус модерации профиля,<br>
	 * user_profile_active - статус текущей активности профиля,<br>
	 * user_profile_username - username пользователя,<br>
	 * user_profile_location - местоположение,<br>
	 * user_profile_avatar_name - название файла аватарки профиля,<br>
	 * user_profile_avatar_dir - директория файла профиля,<br>
	 * user_profile_avatar_ext - расширение файла,<br>
	 * user_profile_avatar_cdn - флаг загрузки CDN,<br>
	 *
	 * account_id - идентификатор аккаунта,<br>
	 * account_email - email аккаунта,<br>
	 * user_profile_type - тип профиля пользователя,<br>
	 */
	
	public function fetchUserProfileAllAssociative(SearchDTO $search, ?UserProfileStatus $status) : Paginator;
	
}