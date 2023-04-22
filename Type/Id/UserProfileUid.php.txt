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

namespace BaksDev\Users\Profile\UserProfile\Type\Id;

//use Fresh\CentrifugoBundle\User\CentrifugoUserInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;
use Symfony\Component\Uid\AbstractUid;
use Symfony\Component\Uid\Uuid;
use Symfony\Component\Uid\UuidV7;

final class UserProfileUid implements ValueResolverInterface
{
	public const TYPE = 'user_profile_id';
	
	private Uuid $value;
	
	private ?string $name;
	
	
	public function __construct(AbstractUid|string|null $value = null, string $name = null)
	{
		if($value === null)
		{
			$value = Uuid::v7();
		}
		
		else if(is_string($value))
		{
			$value = new UuidV7($value);
		}
		
		$this->value = $value;
		$this->name = $name;
	}
	
	
	public function __toString() : string
	{
		return $this->value;
	}
	
	
	public function getValue() : AbstractUid
	{
		return $this->value;
	}
	
	
	/**
	 * @return mixed|null
	 */
	public function getName() : mixed
	{
		return $this->name;
	}
	
	
	public function getCentrifugoSubject() : string
	{
		return (string) $this->value;
	}
	
	
	public function getCentrifugoUserInfo() : array
	{
		return [
			'id' => $this->value,
			'event' => $this->name,
		];
	}
	
	
	public function equals(AbstractUid $uid) : bool
	{
		return (string) $this->value === (string) $uid;
	}
	
	
	public function resolve(Request $request, ArgumentMetadata $argument) : iterable
	{
		$argumentType = $argument->getType();
		
		if($argumentType !== self::class)
		{
			return [];
		}
		
		$value = $request->attributes->get($argument->getName()) ?: $request->attributes->get('id'
		) ?: $request->get('id');
		
		return [new self($value)];
	}
	
}