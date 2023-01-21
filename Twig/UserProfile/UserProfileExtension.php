<?php

namespace BaksDev\Users\Profile\UserProfile\Twig\UserProfile;

use Symfony\Component\Form\FormView;
use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class UserProfileExtension extends AbstractExtension
{
    public function getFunctions() : array
    {
        return [
          new TwigFunction(
            'render_user_profile',
            [$this, 'userProfile'],
            ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }
    
    public function userProfile(Environment $twig, $profile) : string
    {
        try
        {
            return $twig->render('@Template/twig/render_user_profile/user.profile.html.twig', ['profile' => $profile]);
        }
        catch(LoaderError $error)
        {
            return $twig->render('@TwigUserProfile/User/UserProfile/user.profile.html.twig', ['profile' => $profile]);
        }
    }
    
}