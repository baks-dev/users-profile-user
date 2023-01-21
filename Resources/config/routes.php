<?php

use Symfony\Component\Routing\Loader\Configurator\RoutingConfigurator;

return function (RoutingConfigurator $routes)
{
	
	$routes->import(__DIR__.'/../../Controller', 'annotation')
      ->prefix(\BaksDev\Core\Type\Locale\Locale::routes())
      ->namePrefix('UserProfile:');
};