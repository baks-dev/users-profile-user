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

namespace BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit;

use BaksDev\Files\Resources\Upload\Image\ImageUploadInterface;
use BaksDev\Users\Profile\UserProfile\Entity;


use BaksDev\Users\Profile\UserProfile\Entity\Avatar\UserProfileAvatar;
use BaksDev\Users\Profile\UserProfile\Message\ModerationUserProfile\ModerationUserProfileDTO;
use BaksDev\Users\Profile\UserProfile\Repository\UniqProfileUrl\UniqProfileUrlInterface;
use BaksDev\Users\Profile\UserProfile\Type\Id\UserProfileUid;

use BaksDev\Core\Type\Modify\ModifyActionEnum;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UserProfileHandler
{
	private EntityManagerInterface $entityManager;
	private ImageUploadInterface $imageUpload;
	private UniqProfileUrlInterface $uniqProfileUrl;
	private TranslatorInterface $translator;
	private ValidatorInterface $validator;
	private LoggerInterface $logger;
	private RequestStack $request;
	
	public function __construct(
		EntityManagerInterface $entityManager,
		ImageUploadInterface $imageUpload,
		UniqProfileUrlInterface $uniqProfileUrl,
		TranslatorInterface $translator,
		
		ValidatorInterface $validator,
		LoggerInterface $logger,
		RequestStack $request,
	
	)
	{
		$this->entityManager = $entityManager;
		$this->imageUpload = $imageUpload;
		
		$this->uniqProfileUrl = $uniqProfileUrl;
		$this->translator = $translator;
		$this->validator = $validator;
		$this->logger = $logger;
		$this->request = $request;
	}
	
	public function handle(
		Entity\Event\UserProfileEventInterface $command
	) : string|Entity\UserProfile
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
		
		
		if($command->getEvent())
		{
			$EventRepo = $this->entityManager->getRepository(Entity\Event\UserProfileEvent::class)->find(
				$command->getEvent()
			);
			
			
			if($EventRepo === null)
			{
				$uniqid = uniqid('', false);
				$errorsString = sprintf(
					'Not found %s by id: %s',
					Entity\Event\UserProfileEvent::class,
					$command->getEvent()
				);
				$this->logger->error($uniqid.': '.$errorsString);
				
				return $uniqid;
			}
			
			$Event = $EventRepo->cloneEntity();
			
		} else
		{
			$Event = new Entity\Event\UserProfileEvent();
			$this->entityManager->persist($Event);
		}
		
		
		
		$this->entityManager->clear();
		
		
		/** @var Entity\UserProfile $UserProfile */
		if($Event->getProfile())
		{
			$UserProfile = $this->entityManager->getRepository(Entity\UserProfile::class)->findOneBy(
				['event' => $command->getEvent()]
			);
			
			if(empty($UserProfile))
			{
				$uniqid = uniqid('', false);
				$errorsString = sprintf(
					'Not found %s by event: %s',
					Entity\UserProfile::class,
					$command->getEvent()
				);
				$this->logger->error($uniqid.': '.$errorsString);
				
				return $uniqid;
			}
			
			$UserProfileInfo = $this->entityManager->getRepository(Entity\Info\UserProfileInfo::class)->find(
				$UserProfile
			);
			
		} else
		{
			
			$UserProfile = new Entity\UserProfile();
			$this->entityManager->persist($UserProfile);
			$Event->setProfile($UserProfile);
			
			$UserProfileInfo = new Entity\Info\UserProfileInfo($UserProfile);
			$this->entityManager->persist($UserProfileInfo);
		}
		
		
		
		/** @var Info\InfoDTO $infoDTO */
		$infoDTO = $command->getInfo();
		
		
		/* Проверяем на уникальность Адрес персональной страницы */
		$uniqProfileUrl = $this->uniqProfileUrl->exist($infoDTO->getUrl(), $UserProfileInfo->getProfile());
		
		if($uniqProfileUrl)
		{
			$infoDTO->updateUrlUniq(); /* Обновляем URL на уникальный с префиксом */
		}
		
		/* Деактивируем профиль пользователя, Если был ранеее активный */
		if($infoDTO->getActive() !== $UserProfileInfo->isNotActiveProfile())
		{
			$InfoActive = $this->entityManager->getRepository(Entity\Info\UserProfileInfo::class)->findOneBy(
				['user' => $infoDTO->getUser(), 'active' => true]
			);
			
			/* Если у текущего пользователя имеется активный профиль - деактивируем */
			if($InfoActive)
			{
				$InfoActive->deactivate();
			}
		}
		
		
		$Event->setEntity($command);
		$this->entityManager->persist($Event);

		/* Загружаем файл аватарки профиля */
		
		/** @var Avatar\AvatarDTO $Avatar */
		$Avatar = $command->getAvatar();
		if($Avatar->file !== null)
		{
			$UserProfileAvatar = $Event->getUploadAvatar();
			$this->imageUpload->upload($Avatar->file, $UserProfileAvatar);
		}

		/* Присваиваем событие INFO */
		$UserProfileInfo->setEntity($infoDTO);
		/* присваиваем событие корню */
		$UserProfile->setEvent($Event);
		$this->entityManager->flush();
		
		/* Чистим кеш профиля */
		$cache = new FilesystemAdapter('CacheUserProfile');
		$locale = $this->translator->getLocale();
		$cache->delete('current_user_profile'.$infoDTO->getUser()->getValue().$locale);
		
		return $UserProfile;
	}
	
}