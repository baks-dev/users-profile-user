<?php
/*
 *  Copyright 2025.  Baks.dev <admin@baks.dev>
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

use App\Kernel;
use BaksDev\Core\Type\UidType\Uid;
use Symfony\Component\Uid\AbstractUid;

final class UserProfileUid extends Uid
{
    public const string TEST = '0188a9a8-7508-7b3e-a0a1-312e03f7bdd9';

    public const string TYPE = 'user_profile';

    private mixed $attr;

    private mixed $option;

    private mixed $property;

    private mixed $characteristic;

    /*
           $dbal->addSelect(' AS value');
           $dbal->addSelect(' AS attr');
           $dbal->addSelect(' AS option');
           $dbal->addSelect(' AS property');
           $dbal->addSelect(' AS characteristic');
    */

    private ?string $params;

    private object|false|null $decode = null;

    public function __construct(
        AbstractUid|self|string|null $value = null,
        mixed $attr = null,
        mixed $option = null,
        mixed $property = null,
        mixed $characteristic = null,
        ?string $params = null, // строка JSON с параметрами
    )
    {

        parent::__construct($value);

        $this->attr = $attr;
        $this->option = $option;
        $this->property = $property;
        $this->characteristic = $characteristic;
        $this->params = $params;

    }

    /**
     * Attr
     */
    public function getAttr(): mixed
    {
        return $this->attr;
    }

    /**
     * Option
     */
    public function getOption(): mixed
    {
        return $this->option;
    }

    /**
     * Property
     */
    public function getProperty(): mixed
    {
        return $this->property;
    }

    /**
     * Characteristic
     */
    public function getCharacteristic(): mixed
    {
        return $this->characteristic;
    }


    public function getParams(): object|false
    {
        if($this->decode === null)
        {
            if(empty($this->params))
            {
                $this->decode = false;
                return false;
            }

            if(false === json_validate($this->params))
            {
                $this->decode = false;
                return false;
            }

            $this->decode = json_decode($this->params, false, 512, JSON_THROW_ON_ERROR);
        }

        return $this->decode;
    }
}
