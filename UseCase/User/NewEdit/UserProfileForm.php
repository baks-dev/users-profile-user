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

namespace BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit;


use BaksDev\Users\Profile\UserProfile\Repository\FieldValueForm\FieldValueFormDTO;
use BaksDev\Users\Profile\UserProfile\Repository\FieldValueForm\FieldValueFormInterface;
use BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Value\ValueDTO;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatorInterface;

final class UserProfileForm extends AbstractType
{
	
    private FieldValueFormInterface $fieldValue;
    
    public function __construct(FieldValueFormInterface $fieldValue) {
        $this->fieldValue = $fieldValue;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
	
		$builder->add('sort', IntegerType::class);
		
        $builder->add('personal', Personal\PersonalForm::class);
        $builder->add('info', Info\InfoForm::class);
        $builder->add('avatar', Avatar\AvatarForm::class);
		
    
        $profileType = $options['data']->getType();
        $fields = $this->fieldValue->get($profileType);
    
        $builder->add('value', CollectionType::class, [
          'entry_type' => Value\ValueForm::class,
          'entry_options' => ['label' => false, 'fields' => $fields],
          'label' => false,
          'by_reference' => false,
          'allow_delete' => true,
          'allow_add' => true,
        ]);
        
        
        $builder->addEventListener(
          FormEvents::PRE_SET_DATA,
          function (FormEvent $event) use ($fields)
          {
          
              /** @var UserProfileDTO $data */
              $data = $event->getData();

              /** @var FieldValueFormDTO $field */
              foreach($fields as $field)
              {
                  $field = end($field);
                  
                  $new = true;
    
                  /** @var ValueDTO $value  */
                  foreach($data->getValue() as $value)
                  {
                      if($value->getField()->equals($field->getField()))
                      {
                          $value->updSection($field);
                          $new = false;
                          break;
                      }
                  }
    
                  if($new)
                  {
                      $value = new ValueDTO();
                      $value->setField($field->getField());
                      $value->updSection($field);
                      $data->addValue($value);
                  }
              }
          });
        
        /* Сохранить ******************************************************/
        $builder->add
        (
          'Save',
          SubmitType::class,
          ['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary']]);

    }
    
    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults
        (
          [
            'data_class' => UserProfileDTO::class,
            'method' => 'POST',
            'attr' => ['class' => 'w-100'],
          ]);
    }
    
	
}
