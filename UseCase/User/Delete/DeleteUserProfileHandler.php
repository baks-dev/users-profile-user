<?php
/*
 *  Copyright 2022.  Baks.dev <admin@baks.dev>
 *
 *  Licensed under the Apache License, Version 2.0 (the "License");
 *  you may not use this file except in compliance with the License.
 *  You may obtain a copy of the License at
 *
 *  http://www.apache.org/licenses/LICENSE-2.0
 *
 *  Unless required by applicable law or agreed to in writing, software
 *  distributed under the License is distributed on an "AS IS" BASIS,
 *  WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 *  See the License for the specific language governing permissions and
 *   limitations under the License.
 *
 */

namespace BaksDev\Users\Profile\UserProfile\UseCase\User\Delete;

use BaksDev\Files\Resources\Upload\Image\ImageUploadInterface;
use BaksDev\Users\Profile\UserProfile\Entity as EntityUserProfile;

use BaksDev\Users\Profile\UserProfile\Repository\UniqProfileUrl\UniqProfileUrlInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

use BaksDev\Core\Type\Modify\ModifyActionEnum;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class DeleteUserProfileHandler
{
	private EntityManagerInterface $entityManager;
	private ImageUploadInterface $imageUpload;
	private UniqProfileUrlInterface $uniqProfileUrl;
	private TranslatorInterface $translator;
	private ValidatorInterface $validator;
	private LoggerInterface $logger;
	
	public function __construct(
		EntityManagerInterface $entityManager,
		ImageUploadInterface $imageUpload,
		UniqProfileUrlInterface $uniqProfileUrl,
		TranslatorInterface $translator,
		ValidatorInterface $validator,
		LoggerInterface $logger
	)
	{
		$this->entityManager = $entityManager;
		$this->imageUpload = $imageUpload;
		
		$this->uniqProfileUrl = $uniqProfileUrl;
		$this->translator = $translator;
		$this->validator = $validator;
		$this->logger = $logger;
	}
	
	public function handle(
		EntityUserProfile\Event\UserProfileEventInterface $command,
		//?UploadedFile $cover = null
	) : string|EntityUserProfile\UserProfile
	{
		
		/* Валидация */
		$errors = $this->validator->validate($command);
		
		if(count($errors) > 0)
		{
			$uniqid = uniqid('', false);
			$errorsString = (string) $errors;
			$this->logger->error($uniqid.': '.$errorsString);
			return $uniqid;
		}
		
		$this->entityManager->clear();
		
		/* UserProfile */
		
		$UserProfile = $this->entityManager->getRepository(EntityUserProfile\UserProfile::class)->findOneBy(
			['event' => $command->getEvent()]
		);
		
		if(empty($UserProfile))
		{
			$uniqid = uniqid('', false);
			$errorsString = sprintf(
				'%s: Невозможно получить %s с id: %s',
				self::class,
				EntityUserProfile\UserProfile::class,
				$command->getEvent()
			);
			$this->logger->error($uniqid.': '.$errorsString);
			return $uniqid;
		}
		
		/* UserProfileInfo */
		
		$UserProfileInfo = $this->entityManager->getRepository(EntityUserProfile\Info\UserProfileInfo::class)->find($UserProfile);
		
		if(empty($UserProfileInfo))
		{
			$uniqid = uniqid('', false);
			$errorsString = sprintf(
				'%s: Невозможно получить %s с id: %s',
				self::class,
				EntityUserProfile\Info\UserProfileInfo::class,
				$UserProfile->getId()
			);
			$this->logger->error($uniqid.': '.$errorsString);
			return $uniqid;
		}
		
		/* UserProfileEvent */
		
		$EventRepo = $this->entityManager->getRepository(EntityUserProfile\Event\UserProfileEvent::class)->find($command->getEvent());
		
		if(empty($EventRepo))
		{
			$uniqid = uniqid('', false);
			$errorsString = sprintf(
				'%s: Невозможно получить %s с id: %s',
				self::class,
				EntityUserProfile\Event\UserProfileEvent::class,
				$command->getEvent()
			);
			$this->logger->error($uniqid.': '.$errorsString);
			return $uniqid;
		}
		
		$Event = $EventRepo->cloneEntity();
		$Event->setEntity($command);
		
		/* Проверяем, что модификатор DELETE */
		if(!$Event->isModifyActionEquals(ModifyActionEnum::DELETE))
		{
			$uniqid = uniqid('', false);
			$errorsString = sprintf(
				'%s: Модификатор не соотвтетствует: %s',
				self::class,
				(ModifyActionEnum::DELETE)->name
			);
			$this->logger->error($uniqid.': '.$errorsString);
			return $uniqid;
		}
		
		/* Видоизменяем (освобождаем) уникальную ссылку для профиля */
		/** @var Info\InfoDTO $infoDTO */
		$infoDTO = $command->getInfo();
		$infoDTO->updateUrlUniq();
		
		/* Добавляем новое событие */
		$this->entityManager->persist($Event);
		
		/* Присваиваем событие INFO */
		$UserProfileInfo->setEntity($infoDTO);
		
		/* Удаляем профиль */
		$this->entityManager->remove($UserProfile);
		
		
		$this->entityManager->flush();
		
		/* Чистим кеш профиля */
		$cache = new FilesystemAdapter();
		$locale = $this->translator->getLocale();
		$cache->delete('profile-'.$locale.'-'.$infoDTO->getUser()->getValue());
		
		
		return $UserProfile;
	}
	
}