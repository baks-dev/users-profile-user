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

namespace BaksDev\Users\Profile\UserProfile\UseCase\User\Activate;

use BaksDev\Files\Resources\Upload\Image\ImageUploadInterface;
use BaksDev\Users\Profile\UserProfile\Entity;

use BaksDev\Users\Profile\UserProfile\Entity as EntityUserProfile;
use BaksDev\Users\Profile\UserProfile\Message\ModerationUserProfile\ModerationUserProfileDTO;
use BaksDev\Users\Profile\UserProfile\Repository\UniqProfileUrl\UniqProfileUrlInterface;

use BaksDev\Core\Type\Modify\ModifyActionEnum;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Cache\Adapter\FilesystemAdapter;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class ActivateUserProfilehandler
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
		LoggerInterface $logger,
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
		
		/* ?????????????????? */
		$errors = $this->validator->validate($command);
		
		if(count($errors) > 0)
		{
			$uniqid = uniqid('', false);
			$errorsString = (string) $errors;
			$this->logger->error($uniqid.': '.$errorsString);
			
			return $uniqid;
		}
		
		/* UserProfile */
		
		$UserProfile = $this->entityManager->getRepository(EntityUserProfile\UserProfile::class)->findOneBy(
			['event' => $command->getEvent()]
		);
		
		if(empty($UserProfile))
		{
			$uniqid = uniqid('', false);
			$errorsString = sprintf(
				'%s: ???????????????????? ???????????????? %s ?? id: %s',
				self::class,
				EntityUserProfile\UserProfile::class,
				$command->getEvent()
			);
			$this->logger->error($uniqid.': '.$errorsString);
			
			return $uniqid;
		}
		
		$this->entityManager->clear();
		
		/* UserProfileInfo */
		
		$UserProfileInfo = $this->entityManager->getRepository(EntityUserProfile\Info\UserProfileInfo::class)
			->find($UserProfile)
		;
		
		if(empty($UserProfileInfo))
		{
			$uniqid = uniqid('', false);
			$errorsString = sprintf(
				'%s: ???????????????????? ???????????????? %s ?? id: %s',
				self::class,
				EntityUserProfile\Info\UserProfileInfo::class,
				$UserProfile->getId()
			);
			$this->logger->error($uniqid.': '.$errorsString);
			
			return $uniqid;
		}
		
		/** @var Info\InfoDTO $infoDTO */
		$infoDTO = $command->getInfo();
		
		/* ???????? ?? ???????????????? ???????????????????????? ?????????????? ???????????????? ?????????????? - ???????????????????????? */
		$InfoActive = $this->entityManager->getRepository(Entity\Info\UserProfileInfo::class)
			->findOneBy(['user' => $infoDTO->getUser(), 'active' => true])
		;
		if($InfoActive)
		{
			$InfoActive->deactivate();
		}
		
		$UserProfileInfo->setEntity($infoDTO);
		
		$this->entityManager->flush();
		
		/* ???????????? ?????? ?????????????? */
		$cache = new FilesystemAdapter();
		$locale = $this->translator->getLocale();
		$cache->delete('profile-'.$locale.'-'.$infoDTO->getUser()->getValue());
		
		return $UserProfile;
		
	}
	
}