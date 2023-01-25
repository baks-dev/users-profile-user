<?php

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventType;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUidConverter;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUidType;

use BaksDev\Users\Profile\UserProfile\Type\Settings\UserProfileSettingsIdentifier;
use BaksDev\Users\Profile\UserProfile\Type\Settings\UserProfileSettingsType;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatus;
use BaksDev\Users\Profile\UserProfile\Type\Status\UserProfileStatusType;
use Symfony\Config\DoctrineConfig;

return static function(ContainerConfigurator $container, DoctrineConfig $doctrine){
	
	$doctrine->dbal()->type(UserProfileUid::TYPE)->class(UserProfileUidType::class);
	
	$doctrine->dbal()->type(UserProfileEventUid::TYPE)->class(UserProfileEventType::class);
	$doctrine->dbal()->type(UserProfileSettingsIdentifier::TYPE)->class(UserProfileSettingsType::class);
	$doctrine->dbal()->type(UserProfileStatus::TYPE)->class(UserProfileStatusType::class);
	
	$emDefault = $doctrine->orm()->entityManager('default');
	
	$emDefault->autoMapping(true);
	$emDefault->mapping('UserProfile')
		->type('attribute')
		->dir(__DIR__.'/../../Entity')
		->isBundle(false)
		->prefix('BaksDev\Users\Profile\UserProfile\Entity')
		->alias('UserProfile')
	;
};