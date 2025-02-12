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

declare(strict_types=1);

namespace BaksDev\Users\Profile\UserProfile\Listeners\Event;

use BaksDev\Users\User\Repository\UserTokenStorage\UserTokenStorageInterface;
use Symfony\Component\DependencyInjection\Attribute\AutowireIterator;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Twig\Environment;

/**
 * Слушатель инициирует профиль профиль пользователя для Environment
 */
#[AsEventListener(event: ControllerEvent::class)]
final readonly class UserProfileListener
{
    public function __construct(
        #[AutowireIterator('baks.user.profile', defaultPriorityMethod: 'priority')] private iterable $profiles,
        private UserTokenStorageInterface $tokenStorage,
        private Environment $twig,
    ) {}

    public function onKernelController(ControllerEvent $event): void
    {
        if(false === $this->tokenStorage->isUser())
        {
            return;
        }

        $data = null;

        $user = $this->tokenStorage->getUserCurrent();

        foreach($this->profiles as $profile)
        {
            if($profile->getvalue($user))
            {
                $data[$profile::KEY] = $profile->getValue($user);
            }
        }

        $this->twig->addGlobal('baks_profile', $data);

    }
}
