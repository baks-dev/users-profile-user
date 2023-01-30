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

namespace BaksDev\Users\Profile\UserProfile\DataFixtures\Security;

use BaksDev\Users\Groups\Group\DataFixtures\Security\Group\GroupFixtures;
use BaksDev\Users\Groups\Group\Entity\Event\GroupEvent;
use BaksDev\Users\Groups\Group\UseCase\Admin\NewEdit\CheckRoleHandler;
use BaksDev\Users\Groups\Role\UseCase\Admin\NewEdit\RoleHandler;
use BaksDev\Users\Profile\UserProfile\DataFixtures\Security\Role\RoleDTO;
use BaksDev\Users\Groups\Role\Entity\Role;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

final class RoleFixtures extends Fixture implements DependentFixtureInterface
{
	
	private RoleHandler $roleAggregate;
	
	private CheckRoleHandler $checkRoleAggregate;
	
	
	public function __construct(
		RoleHandler $roleAggregate,
		CheckRoleHandler $checkRoleAggregate,
	)
	{
		$this->roleAggregate = $roleAggregate;
		$this->checkRoleAggregate = $checkRoleAggregate;
	}
	
	
	public function load(ObjectManager $manager) : void
	{
		# php bin/console doctrine:fixtures:load --append
		
		/* Role */
		
		$RoleDTO = new RoleDTO();
		$RoleEvent = $manager->getRepository(Role::class)->find($RoleDTO->getRole());
		
		if(empty($RoleEvent))
		{
			//if($RoleEvent) { $RoleDTO->setId($RoleEvent->getEvent()); }
			$this->roleAggregate->handle($RoleDTO);
		}
		
		/* CheckRole */
		
		/** @var GroupEvent $GroupEvent */
		$GroupEvent = $this->getReference(GroupFixtures::class);
		
		$CheckRoleDTO = new Check\CheckRoleDTO($GroupEvent, $RoleDTO);
		$this->checkRoleAggregate->handle($CheckRoleDTO);
		
	}
	
	
	public function getDependencies() : array
	{
		return [
			GroupFixtures::class,
		];
	}
	
}