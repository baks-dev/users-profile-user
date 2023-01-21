<?php

namespace BaksDev\Users\Profile\UserProfile\Type\Status;

use Twig\Environment;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

final class UserProfileStatusExtension extends AbstractExtension
{
    public function getFunctions() : array
    {
        return [
          new TwigFunction(
            UserProfileStatus::TYPE,
            [$this, 'status'],
            ['needs_environment' => true, 'is_safe' => ['html']]),
        ];
    }
    
    public function status(Environment $twig, ?string $status) : string
    {
        return $twig->render('@UserProfileStatus/status.html.twig', ['status' => $status]);
    }
    
}