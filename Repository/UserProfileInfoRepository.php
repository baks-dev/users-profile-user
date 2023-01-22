<?php

declare(strict_types=1);

namespace BaksDev\Users\Profile\UserProfile\Repository;

use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Repository\GetUserById\GetUserByIdInterface;
use BaksDev\Users\User\Tests\TestUserAccount;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
final class UserProfileInfoRepository extends ServiceEntityRepository
{
	private GetUserByIdInterface $getUserById;
	
	public function __construct(ManagerRegistry $registry)
	{
		parent::__construct($registry, UserProfileInfo::class);
		
	}
	
	
	/** Переопределяем метод Doctrine\ORM find() */
	public function find($id, $lockMode = null, $lockVersion = null)
	{
		return 13222;
		
		/* Если идентификатор тестового пользователя */
		if((string) $id['id'] === TestUserAccount::TEST_USER_UID)
		{
			return TestUserAccount::getUser();
		}
		
		/* Если идентификатор тестового администратора */
		if( (string) $id['id'] === TestUserAccount::TEST_ADMIN_UID)
		{
			return TestUserAccount::getAdmin();
		}
		
		return $this->getUserById->get($id['id']);
	}
}