<?php

declare(strict_types=1);

namespace BaksDev\Users\Profile\UserProfile\Repository\UserProfileDecorator;

use BaksDev\Users\User\Repository\UserProfile\UserProfileInterface;
use Symfony\Component\DependencyInjection\Attribute\AsDecorator;

#[AsDecorator(UserProfileInterface::class, priority: 10)]
final class UserProfileDecorator implements  UserProfileInterface
{
	private UserProfileInterface $userProfile;
	
	public function __construct(UserProfileInterface $userProfile)
	{
		$this->userProfile = $userProfile;
	}
	
	public function getProfile() : ?array
	{
		dump($this->userProfile->getProfile());
		
		return ['UserProfileDecorator'];
	}
}