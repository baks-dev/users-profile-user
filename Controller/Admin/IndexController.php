<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Users\Profile\UserProfile\Controller\Admin;

use BaksDev\Core\Controller\AbstractController;
use BaksDev\Core\Form\Search\SearchDTO;
use BaksDev\Core\Form\Search\SearchForm;
use BaksDev\Core\Listeners\Event\Security\RoleSecurity;
use BaksDev\Users\Profile\TypeProfile\Repository\TypeProfileChoice\TypeProfileChoiceInterface;
use BaksDev\Users\Profile\UserProfile\Repository\AllUserProfile\AllUserProfileInterface;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Attribute\AsController;
use Symfony\Component\Routing\Attribute\Route;

#[AsController]
#[RoleSecurity('ROLE_USERPROFILE')]
final class IndexController extends AbstractController
{
    #[Route('/admin/users/profiles/{page<\d+>}', name: 'admin.index', methods: [
        'GET',
        'POST',
    ])]
    public function index(
        Request $request,
        AllUserProfileInterface $allUserProfile,
        TypeProfileChoiceInterface $typeProfileChoice,
        //AllProfileTypeInterface $allTypeProfile, // Типы профилей
        int $page = 0,
    ): Response
    {
        // Список доступных типов профилей
        $profile = $typeProfileChoice->getUsersTypeProfileChoice();

        // Поиск
        $search = new SearchDTO();

        $searchForm = $this
            ->createForm(
                type: SearchForm::class,
                data: $search,
                options: ['action' => $this->generateUrl('users-profile-user:admin.index')]
            )
            ->handleRequest($request);

        // Получаем список
        $status = !$request->get('status') ? null : new UserProfileStatus($request->get('status'));
        $query = $allUserProfile->fetchUserProfileAllAssociative($search, $status);

        return $this->render(
            [
                'profiles' => $profile,
                'status' => $status,
                'query' => $query,
                'search' => $searchForm->createView(),
            ]
        );
    }
}
