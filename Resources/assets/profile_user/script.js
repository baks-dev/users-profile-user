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

/* Статус */
let $circle = document.getElementById('user_status_circle');

let $status = document.getElementById('user_profile_form_info_status');

if ($status) {
    changeProfileStaus($status.options[$status.selectedIndex].value);

    $status.addEventListener('change', function () {
        changeProfileStaus(this.value);
    })
}

function changeProfileStaus($status) {

    let $circle = document.getElementById('profile_status_circle');

    $circle.classList.remove('bg-primary');
    $circle.classList.remove('bg-danger');
    $circle.classList.remove('bg-warning');


    if ($status === 'new') {
        $circle.classList.add('bg-warning');
    } else if ($status === 'act') {
        $circle.classList.add('bg-primary');
    } else if ($status === 'ban') {
        $circle.classList.add('bg-danger');
    }
}


let $idLocation = 'user_profile_form_personal_location';
let $idLocationHelp = 'user_profile_form_personal_location_help';


// setTimeout(function GmRrLKUUEw() {
//
//     if (typeof readGeoMaps  === 'function') {
//         readGeoMaps($idLocation, $idLocationHelp);
//         return;
//     }
//
//     setTimeout(GmRrLKUUEw, 100);
//
// }, 100);


/* Определяем поле ввода Username */
let $name = document.getElementById('user_profile_form_personal_username');

if ($name) {

    let debouncerepeat = 100;
    setTimeout(function zZFlBXCqXd() {

        if (debouncerepeat > 500) {
            return;
        }

        if (typeof profileUrl.debounce === 'function') {
            $name.addEventListener('input', profileUrl.debounce(500));
            return;
        }

        debouncerepeat += 100;

        setTimeout(zZFlBXCqXd, debouncerepeat);

    }, 100);


    function profileUrl() {
        /* Заполняем транслитом URL */
        $semantic = translitRuEn(this.value).toLowerCase();
        document.getElementById('user_profile_form_info_url').value = $semantic;
    }
}

