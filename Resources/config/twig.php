<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Users\Profile\UserProfile\Entity\Avatar\UserProfileAvatar;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatusExtension;
use Symfony\Config\TwigConfig;

return static function (TwigConfig $config, ContainerConfigurator $configurator)
{
	
	$config->path(__DIR__.'/../view', 'UserProfile');
	
    /** Абсолютный Путь для загрузки аватарок профилей пользователя */
    $configurator->parameters()->set(UserProfileAvatar::TABLE, '%kernel.project_dir%/public/assets/'.UserProfileAvatar::TABLE.'/');
    
    /** Относительный путь аватарок профилей пользователя */
    $config->global(UserProfileAvatar::TABLE)->value('/assets/'.UserProfileAvatar::TABLE.'/');
	
	
    $services = $configurator->services()
      ->defaults()
      ->autowire()      // Automatically injects dependencies in your services.
      ->autoconfigure() // Automatically registers your services as commands, event subscribers, etc.
    ;
    
    /* AccountStatusExtension */
    $config->path(__DIR__.'/../../Type/Status', 'UserProfileStatus');
    $services->set('app.user.profile.status.twig.extension')
		
      ->class(UserProfileStatusExtension::class)
      ->tag('twig.extension');
};




