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
      ->autowire()
      ->autoconfigure()
    ;
    
    /* AccountStatusExtension */
    $config->path(__DIR__.'/../../Type/Status', 'UserProfileStatus');
	
    $services->set('app.user.profile.status.twig.extension')
      ->class(UserProfileStatusExtension::class)
      ->tag('twig.extension');
};




