<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus;

use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\Collection\UserProfileStatusInterface;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use InvalidArgumentException;

/**
 * Типы полей
 */
final class UserProfileStatus
{
    public const string TYPE = 'user_profile_status';

    public const string TEST = UserProfileStatusActive::class;


    private UserProfileStatusInterface $status;


    public function __construct(UserProfileStatusInterface|self|string $status)
    {
        if(is_string($status) && class_exists($status))
        {
            $instance = new $status();

            if($instance instanceof UserProfileStatusInterface)
            {
                $this->status = $instance;
                return;
            }
        }

        if($status instanceof UserProfileStatusInterface)
        {
            $this->status = $status;
            return;
        }

        if($status instanceof self)
        {
            $this->status = $status->getUserProfileStatus();
            return;
        }

        /** @var UserProfileStatusInterface $declare */
        foreach(self::getDeclared() as $declare)
        {
            if($declare::equals($status))
            {
                $this->status = new $declare();
                return;
            }
        }

        throw new InvalidArgumentException(sprintf('Not found UserProfileStatus %s', $status));
    }

    public function __toString(): string
    {
        return $this->status->getvalue();
    }


    public function getUserProfileStatus(): UserProfileStatusInterface
    {
        return $this->status;
    }

    public function getUserProfileStatusValue(): string
    {
        return $this->status->getValue();
    }


    public static function cases(): array
    {
        $case = [];

        foreach(self::getDeclared() as $key => $status)
        {
            /** @var UserProfileStatusInterface $status */
            $class = new $status();
            $case[$class::sort().$key] = new self($class);
        }

        return $case;
    }

    public static function getDeclared(): array
    {
        return array_filter(
            get_declared_classes(),
            static function ($className) {
                return in_array(UserProfileStatusInterface::class, class_implements($className), true);
            }
        );
    }


    public function equals(mixed $status): bool
    {
        $status = new self($status);

        return $this->getUserProfileStatusValue() === $status->getUserProfileStatusValue();
    }


}
