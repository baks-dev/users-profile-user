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

namespace BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Value;

//use BaksDev\Users\Profile\TypeProfile\Type\Section\Field\Id\FieldUid;
//use BaksDev\Users\Profile\TypeProfile\Type\Section\Id\SectionUid;
//use BaksDev\Users\Profile\UserProfile\Entity\Value\ValueInterface;
//use BaksDev\Users\Profile\UserProfile\Repository\FieldValueForm\FieldValueFormDTO;
use BaksDev\Users\Profile\TypeProfile\Type\Section\Field\Id\TypeProfileSectionFieldUid;
use BaksDev\Users\Profile\TypeProfile\Type\Section\Id\TypeProfileSectionUid;
use BaksDev\Users\Profile\UserProfile\Entity\Value\UserProfileValueInterface;
use BaksDev\Users\Profile\UserProfile\Repository\FieldValueForm\FieldValueFormDTO;
use BaksDev\Core\Type\Field\FieldEnum;
use BaksDev\Core\Type\Field\InputField;
use BaksDev\Core\Type\Field\InputFieldType;
use Symfony\Component\Validator\Constraints as Assert;

final class ValueDTO implements UserProfileValueInterface
{
    /** Связь на поле */
    private TypeProfileSectionFieldUid $field;
    
    /** Заполненное значение */
    private ?string $value = null;

	
    /** Вспомогательные свойства */

    private ?TypeProfileSectionUid $section = null;
    private ?string $sectionName = null;
    private ?string $sectionDescription = null;
	
    private string $type;
    

    public function __construct() {
    
         $this->type = new InputField(FieldEnum::INPUT);
    }
    
    
    /* FIELD */

    public function getField() : TypeProfileSectionFieldUid
    {
        return $this->field;
    }
	
    public function setField(TypeProfileSectionFieldUid $field) : void
    {
        $this->field = $field;
    }
    
    /* VALUE */
    
    /**
     * @return string|null
     */
    public function getValue() : ?string
    {
        return $this->value;
    }

    /**
     * @param string|null $value
     */
    public function setValue(?string $value) : void
    {
        $this->value = $value;
    }
    
    
    
    /* Вспомогательные методы */
    
    public function updSection(FieldValueFormDTO $fieldValueFormDTO) : void
    {
        $this->section = $fieldValueFormDTO->getSection();
        $this->sectionName = $fieldValueFormDTO->getSectionName();
        $this->sectionDescription = $fieldValueFormDTO->getSectionDescription();
        $this->type = $fieldValueFormDTO->getType();
    }
    

    public function getSection() : ?TypeProfileSectionUid
    {
        return $this->section;
    }
    
    /**
     * @return string
     */
    public function getSectionName() : string
    {
        return $this->sectionName;
    }
    
    /**
     * @return string|null
     */
    public function getSectionDescription() : ?string
    {
        return $this->sectionDescription;
    }
    
    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->type;
    }

    
}