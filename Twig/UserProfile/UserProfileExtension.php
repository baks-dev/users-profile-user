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

namespace BaksDev\Users\Profile\UserProfile\Twig\UserProfile;

use BaksDev\Users\Profile\UserProfile\Repository\UserProfileByEvent\UserProfileByEventInterface;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class UserProfileExtension extends AbstractExtension
{
    private UserProfileByEventInterface $profileByEvent;

    public function __construct(UserProfileByEventInterface $profileByEvent)
    {
        $this->profileByEvent = $profileByEvent;
    }

    /*public function getFunctions() : array
    {
    	return [
    		new TwigFunction(
    			'render_user_profile',
    			[$this, 'userProfile'],
    			['needs_environment' => true, 'is_safe' => ['html']]
    		),
    	];
    }*/

    public function getFunctions(): array
    {
        return [
            new TwigFunction('user_profile', [$this, 'content'], ['needs_environment' => true, 'is_safe' => ['html']]),
            new TwigFunction('user_profile_render', [$this, 'render'], ['needs_environment' => true, 'is_safe' => ['html']]),
            new TwigFunction('user_profile_template', [$this, 'template'], ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }

    public function content(Environment $twig, string $value): string
    {
        $data = $this->profileByEvent->fetchUserProfileAssociative(new UserProfileEventUid($value));

        try
        {
            return $twig->render('@Template/UserProfile/user_profile/content.html.twig', ['value' => $data]);
        } catch(LoaderError $loaderError)
        {
            return $twig->render('@UserProfile/twig/user_profile/content.html.twig', ['value' => $data]);
        }
    }

    public function render(Environment $twig, $value): string
    {
        try
        {
            return $twig->render('@Template/UserProfile/user_profile/render.html.twig', ['value' => $value]);
        } catch(LoaderError $loaderError)
        {
            return $twig->render('@UserProfile/twig/user_profile/render.html.twig', ['value' => $value]);
        }
    }

    public function template(Environment $twig, $value): string
    {
        try
        {
            return $twig->render('@Template/UserProfile/user_profile/template.html.twig', ['value' => $value]);
        } catch(LoaderError $loaderError)
        {
            return $twig->render('@UserProfile/twig/user_profile/template.html.twig', ['value' => $value]);
        }
    }

    /*public function userProfile(Environment $twig, $profile) : string
    {
        try
        {
            return $twig->render('@Template/twig/render_user_profile/user.profile.html.twig', ['profile' => $profile]);
        }
        catch(LoaderError $error)
        {
            return $twig->render('@TwigUserProfile/User/UserProfile/user.profile.html.twig', ['profile' => $profile]);
        }
    }*/
}
