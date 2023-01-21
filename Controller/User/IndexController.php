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

namespace BaksDev\Users\Profile\UserProfile\Controller\User;

use BaksDev\Users\Profile\TypeProfile\Repository\AllProfileType\AllProfileTypeInterface;

use BaksDev\Users\Profile\UserProfile\Repository\UserProfileByUser\UserProfileByUserInterface;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatus;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatusEnum;
use BaksDev\Core\Controller\AbstractController;

use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Form\Search\SearchForm;
use BaksDev\Core\Type\Locale\Locale;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Component\ExpressionLanguage\Expression;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted("ROLE_USER")]
final class IndexController extends AbstractController
{
    #[Route('/user/profiles/{page<\d+>}', name: 'user.index',  methods: ['GET','POST'])]
    public function index(
      Request $request,
      UserProfileByUserInterface $userProfileByUser,
      AllProfileTypeInterface $allProfileType, /* Типы профилей */
      int $page = 0,
    ) : Response
    {
		
		/* Список доступных типов профилей */
		$profile = $allProfileType->get()->getData();
		
        /* Поиск */
        $search = new SearchDTO();
        $searchForm = $this->createForm(SearchForm::class, $search);
        $searchForm->handleRequest($request);
        
        /* Получаем список */
        $status = !$request->get('status') ? null : new UserProfileStatus($request->get('status'));
        $query = $userProfileByUser->get($search, $status);

        return $this->render(
          [
            'profiles' => $profile,
            'status' => $status,
            'query' => $query,
            'search' => $searchForm->createView(),
          ]);
    }
    
}