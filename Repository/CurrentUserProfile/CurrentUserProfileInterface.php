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
    public function fetchProfileAssociative(UserUid $usr): bool|array;

}