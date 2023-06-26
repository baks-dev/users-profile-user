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

namespace BaksDev\Users\Profile\UserProfile\Listeners\Event;

use BaksDev\Users\Profile\UserProfile\Repository\CurrentUserProfile\CurrentUserProfileInterface;
use Symfony\Bridge\Twig\AppVariable;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Twig\Environment;

#[AsEventListener(event: RequestEvent::class, priority: 7)] /* <- больше 7 не срабатывает TokenStorageInterface */
final class UserProfileListener
{
    private $twig;

    private CurrentUserProfileInterface $currentUserProfile;

    public function __construct(
        Environment $twig,
        CurrentUserProfileInterface $currentUserProfile,
    ) {
        $this->twig = $twig;
        $this->currentUserProfile = $currentUserProfile;
    }

    /** Событие определяет профиль пользователя */
    public function onKernelRequest(RequestEvent $event): void
    {
        $globals = $this->twig->getGlobals();
        $baks_profile = $globals['baks_profile'] ?? [];
        $Userprofile = null;

        /** @var AppVariable $app */
        $app = $globals['app'];

        if ($app->getUser())
        {
            $Userprofile = $this->currentUserProfile->fetchProfileAssociative($app->getUser()->getId());
        }

        if ($Userprofile)
        {
            $this->twig->addGlobal('baks_profile', array_replace_recursive($baks_profile, $Userprofile));
        }
    }
}
