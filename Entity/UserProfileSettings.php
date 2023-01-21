<?php
/*
*  Copyright Baks.dev <admin@baks.dev>
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

namespace BaksDev\Users\Profile\UserProfile\Entity;

use BaksDev\Users\Profile\UserProfile\Type\Settings\UserProfileSettingsIdentifier;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\DBAL\Types\Types;

/* Настройки сущности UserProfile */

#[ORM\Entity]
#[ORM\Table(name: 'users_profile_settings')]
class UserProfileSettings
{
    public const TABLE = 'users_profile_settings';

    /** ID */
    #[ORM\Id]
    #[ORM\Column(type: UserProfileSettingsIdentifier::TYPE)]
    private UserProfileSettingsIdentifier $id;

    /** Очищать корзину старше n дней */
    #[ORM\Column(name: 'settings_truncate', type: Types::SMALLINT, length: 3, nullable: false)]
    private int $settingsTruncate = 365;
    
    
    /** Очищать события старше n дней */
    #[ORM\Column(name: 'settings_history', type: Types::SMALLINT, length: 3, nullable: false)]
    private int $settingsHistory = 365;

    public function __construct() { $this->id = new UserProfileSettingsIdentifier();  }

    /**
    * @return UserProfileSettingsIdentifier
    */
    public function getId() : UserProfileSettingsIdentifier
    {
        return $this->id;
    }

    /**
     * @return int
     */
    public function getSettingsTruncate() : int
    {
        return $this->settingsTruncate;
    }
    
    /**
     * @param int $settingsTruncate
     */
    public function setSettingsTruncate(int $settingsTruncate) : void
    {
        $this->settingsTruncate = $settingsTruncate;
    }
    
    /**
     * @return int
     */
    public function getSettingsHistory() : int
    {
        return $this->settingsHistory;
    }
    
    /**
     * @param int $settingsHistory
     */
    public function setSettingsHistory(int $settingsHistory) : void
    {
        $this->settingsHistory = $settingsHistory;
    }
}
