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

namespace BaksDev\Users\Profile\UserProfile\Repository\FieldValueForm;

use BaksDev\Core\Type\Field\InputField;
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
	
	private InputField $type;
	
	private bool $required;
	
	
	public function __construct(
		$section,
		string $sectionName,
		?string $sectionDescription,
		$field,
		string $fieldName,
		?string $fieldDescription,
		InputField $type,
		bool $required,
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
	
	
	public function getType() : InputField
	{
		return $this->type;
	}
	
	
	public function isRequired() : bool
	{
		return $this->required;
	}
	
}