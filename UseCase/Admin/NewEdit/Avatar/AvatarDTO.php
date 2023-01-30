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

namespace BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Avatar;

use BaksDev\Users\Profile\UserProfile\Entity\Avatar\UserProfileAvatarInterface;
use BaksDev\Users\Profile\UserProfile\Type\Event\UserProfileEventUid;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

final class AvatarDTO implements UserProfileAvatarInterface
{
	/** Файл загрузки */
	#[Assert\File(
		maxSize: '1024k',
		mimeTypes: [
			'image/png',
			'image/gif',
			'image/jpeg',
			'image/pjpeg',
			'image/webp',
		],
		mimeTypesMessage: 'Please upload a valid file'
	)]
	public ?File $file = null;
	
	/** Название файла без расширения */
	private ?string $name = null;
	
	/** Расширение файла  */
	private ?string $ext = null;
	
	/** Флаг загрузки файла CDN */
	private bool $cdn = false;
	
	/** Директория загрузки файла  */
	private ?UserProfileEventUid $dir = null;
	
	
	/** Название файла без расширения */
	
	public function getName() : ?string
	{
		return $this->name;
	}
	
	
	public function setName(?string $name) : void
	{
		$this->name = $name;
	}
	
	
	/** Расширение файла  */
	
	public function getExt() : ?string
	{
		return $this->ext;
	}
	
	
	public function setExt(?string $ext) : void
	{
		$this->ext = $ext;
	}
	
	
	/** Флаг загрузки файла CDN */
	
	public function getCdn() : bool
	{
		return $this->cdn;
	}
	
	
	public function setCdn(bool $cdn) : void
	{
		$this->cdn = $cdn;
	}
	
	
	/** Директория загрузки файла  */
	
	public function getDir() : ?UserProfileEventUid
	{
		return $this->dir;
	}
	
	
	public function setDir(?UserProfileEventUid $dir) : void
	{
		$this->dir = $dir;
	}
	
}