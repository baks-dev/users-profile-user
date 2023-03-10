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
	#[Route('/user/profiles/{page<\d+>}', name: 'user.index', methods: ['GET', 'POST'])]
	public function index(
		Request $request,
		UserProfileByUserInterface $userProfileByUser,
		AllProfileTypeInterface $allProfileType, /* ???????? ???????????????? */
		int $page = 0,
	) : Response
	{
		
		/* ???????????? ?????????????????? ?????????? ???????????????? */
		$profile = $allProfileType->get()->getData();
		
		/* ?????????? */
		$search = new SearchDTO();
		$searchForm = $this->createForm(SearchForm::class, $search);
		$searchForm->handleRequest($request);
		
		/* ???????????????? ???????????? */
		$status = !$request->get('status') ? null : new UserProfileStatus($request->get('status'));
		$query = $userProfileByUser->get($search, $status);
		
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