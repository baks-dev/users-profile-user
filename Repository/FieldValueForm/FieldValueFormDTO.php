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

namespace BaksDev\Users\Profile\UserProfile\Repository\FieldValueForm;

use BaksDev\Users\Profile\TypeProfile\Type\Section\Field\Id\TypeProfileSectionFieldUid;
use BaksDev\Users\Profile\TypeProfile\Type\Section\Id\TypeProfileSectionUid;

final class FieldValueFormDTO
{
	private TypeProfileSectionUid $section;
	private string $sectionName;
	private ?string $sectionDescription;
	private TypeProfileSectionFieldUid $field;
	private string $fieldName;
	private ?string $fieldDescription;
	private string $type;
	private bool $required;
	
	public function __construct(
		$section,
		string $sectionName,
		?string $sectionDescription,
		$field,
		string $fieldName,
		?string $fieldDescription,
		string $type,
		bool $required
	)
	{
		$this->section = $section;
		$this->sectionName = $sectionName;
		$this->sectionDescription = $sectionDescription;
		$this->field = $field;
		$this->fieldName = $fieldName;
		$this->fieldDescription = $fieldDescription;
		$this->type = $type;
		$this->required = $required;
	}
	
	public function getSection() : TypeProfileSectionUid
	{
		return $this->section;
	}
	
	public function getSectionName() : string
	{
		return $this->sectionName;
	}

	public function getSectionDescription() : ?string
	{
		return $this->sectionDescription;
	}

	public function getField() : TypeProfileSectionFieldUid
	{
		return $this->field;
	}

	public function getFieldName() : string
	{
		return $this->fieldName;
	}
	

	public function getFieldDescription() : ?string
	{
		return $this->fieldDescription;
	}

	public function getType() : string
	{
		return $this->type;
	}
	
	public function isRequired() : bool
	{
		return $this->required;
	}
}