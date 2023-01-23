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

namespace BaksDev\Users\Profile\UserProfile\Repository\CurrentUserProfile;

use BaksDev\Users\User\Type\Id\UserUid;

interface CurrentUserProfileInterface
{
	/** Активный профиль пользователя
	 *
	 * Возвращает массив с ключами:
	 *
	 * profile_url - адрес персональной страницы <br>
	 * profile_username - username провфиля <br>
	 * profile_type - Тип провфиля <br>
	 * profile_avatar_name - название файла аватарки профиля <br>
	 * profile_avatar_dir - директория файла аватарки <br>
	 * profile_avatar_ext - расширение файла <br>
	 * profile_avatar_cdn - фгаг загрузки файла на CDN
	 */
	public function fetchProfileAssociative(UserUid $user);
}