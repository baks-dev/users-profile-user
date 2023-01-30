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

namespace BaksDev\Users\Profile\UserProfile\DataFixtures\Security\Role\Voter\Trans;

use BaksDev\Users\Groups\Role\Entity\Voters\Trans\VoterTransInterface;
use BaksDev\Core\Type\Locale\Locale;

final class VoterTransDTO implements VoterTransInterface
{
	
	/** Локаль */
	private Locale $local;
	
	/** Название */
	private string $name;
	
	
	/**
	 * @return Locale
	 */
	public function getLocal() : Locale
	{
		return $this->local;
	}
	
	
	/**
	 * @param Locale $local
	 */
	public function setLocal(Locale $local) : void
	{
		$this->local = $local;
	}
	
	
	/**
	 * @return string
	 */
	public function getName() : string
	{
		return $this->name;
	}
	
	
	/**
	 * @param string $name
	 */
	public function setName(string $name) : void
	{
		$this->name = $name;
	}
	
}

