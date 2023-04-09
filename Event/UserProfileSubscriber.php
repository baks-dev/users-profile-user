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

namespace BaksDev\Users\Profile\UserProfile\Event;

use BaksDev\Products\Category\Repository\AllCategory\AllCategoryInterface;
use BaksDev\Products\Category\Repository\AllCategoryByMenu\AllCategoryByMenuInterface;
use BaksDev\Users\Profile\UserProfile\Repository\CurrentUserProfile\CurrentUserProfileInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Event\ViewEvent;
use Symfony\Component\HttpKernel\KernelEvents;

//use App\Repository\ConferenceRepository;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Twig\Environment;

final class UserProfileSubscriber implements EventSubscriberInterface
{
	private $twig;
	
	//private TokenStorageInterface $user;
	
	private CurrentUserProfileInterface $currentUserProfile;
	
	
	public function __construct(
		Environment $twig,
		//TokenStorageInterface $user,
		CurrentUserProfileInterface $currentUserProfile,
	)
	{
		$this->twig = $twig;
		//$this->user = $user;
		$this->currentUserProfile = $currentUserProfile;
	}
	
	
	public static function getSubscribedEvents()
	{
		return [
			KernelEvents::REQUEST => ['onRequestEvent', 7], /* <- больше 7 не срабатывает TokenStorageInterface */
		];
	}
	
	
	/** Событие определяет профиль пользователя */
	public function onRequestEvent(RequestEvent $event) : void
	{
		
		//$User = $this->user->getToken()?->getUser();
		
		
		$globals = $this->twig->getGlobals();
		$baks_profile = $globals['baks_profile'] ?? [];
		$Userprofile = null;
		
		
		/** @var \Symfony\Bridge\Twig\AppVariable $app */
		$app = $globals['app'];
		
//		dump($app->getUser()?->getId());
//
//		if($User)
//		{
//			$Userprofile = $this->currentUserProfile->fetchProfileAssociative($User->getId());
//		}
		
		
		if($app->getUser())
		{
			$Userprofile = $this->currentUserProfile->fetchProfileAssociative($app->getUser()->getId());
		}
		
		if($Userprofile)
		{
			$this->twig->addGlobal('baks_profile', array_replace_recursive($baks_profile, $Userprofile));
		}
	}
	
}