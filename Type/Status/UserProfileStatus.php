<?php

namespace BaksDev\Users\Profile\UserProfile\Type\Status;

/**
 * Типы полей
 */
final class UserProfileStatus
{
    public const TYPE = 'user_profile_status';
    
    /**
     * @var UserProfileStatusEnum
     */
    private UserProfileStatusEnum $status;
    
    /**
     * Field constructor
     *
     * @param string|UserProfileStatusEnum $status
     */
    public function __construct(string|UserProfileStatusEnum $status)
    {
        $this->status = $status instanceof UserProfileStatusEnum ? $status : UserProfileStatusEnum::from($status);
    }
    
    /**
     * @return string
     */
    public function __toString() : string
    {
        return $this->status->value;
    }
    
    /**
     * @return string
     */
    public function getValue() : string
    {
        return $this->status->value;
    }
    
    /**
     * @return string
     */
    public function getName() : string
    {
        return $this->status->name;
    }
    
    /**
     * @return UserProfileStatusEnum
     */
    public function getGender() : UserProfileStatusEnum
    {
        return $this->status;
    }
    
    public function equals(string|UserProfileStatusEnum $status) : bool
    {
        return $this->status === ($status instanceof UserProfileStatusEnum ? $status : UserProfileStatusEnum::from($status));
    }
    
    /**
     * @return array
     */
    public static function cases() : array
    {
        $case = null;
        
        foreach(UserProfileStatusEnum::cases() as $status)
        {
            $case[] = new self($status);
        }
        
        return $case;
    }
}