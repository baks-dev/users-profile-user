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

use BaksDev\Users\Profile\UserProfile\Repository\FieldValueForm\FieldValueFormInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class ValueForm extends AbstractType
{
    
    private FieldValueFormInterface $fieldValue;
    
    public function __construct(FieldValueFormInterface $fieldValue)
    {
        $this->fieldValue = $fieldValue;
    }
    
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        
        /* TextType */
        $builder->add('value', TextType::class);
        
        $builder->addEventListener(
          FormEvents::PRE_SET_DATA,
          function (FormEvent $event) use ($options)
          {
              /* @var ValueDTO $data */
              $data = $event->getData();
              $form = $event->getForm();
              
              if($data)
              {
                  
                  $fields = $options['fields'];
				  
				  //dd($fields);
                  
                  /** @var \BaksDev\Users\Profile\UserProfile\Repository\FieldValueForm\FieldValueFormDTO $field */
                  $field = end($fields[(string)$data->getField()]);
                  

    
                  match ($field->getType())
                  {
                      /* INTEGER */
                      'integer' => $form->add
                      (
                        'value',
                        IntegerType::class,
                        [
                          'label' => $field->getFieldName(),
                          'required' => $field->isRequired(),
						  
                        ]
                      ),

                      /* MAIL */
                      'mail' => $form->add
                      (
                        'value',
                        EmailType::class,
                        [
                          'label' => $field->getFieldName(),
                          'required' => $field->isRequired(),
                          'help' => $field->getFieldDescription(),
                        ]),

                      /* PHONE */
                      'phone' => $form->add
                      (
                        'value',
                        TextType::class,
                        [
                          'label' => $field->getFieldName(),
                          'required' => $field->isRequired(),
                          'attr' =>
                            [
                              'placeholder' => $field->getFieldDescription(),
                            ]
                        ]),
//
//                      /* SELECT */
//                      'select' => $form->add
//                      (
//                        'value',
//                        ChoiceType::class,
//                        [
//                          'label' => $propCat->fieldTrans,
//                          'required' => $propCat->fieldRequired,
//                          'placeholder' => $propCat->fieldDesc,
//                        ]),
//
                      /* TEXTAREA */
                      'textarea' => $form->add(
                        'value',
                        TextareaType::class,
                        [
                          'label' => $field->getFieldName(),
                          'required' => $field->isRequired(),
                          'help' => $field->getFieldDescription(),
                        ]),

                      default => $form->add
                      (
                        'value',
                        TextType::class,
                        [
                          'label' => $field->getFieldName(),
                          'required' => $field->isRequired(),
						  'help' => $field->getFieldDescription()
                        ])

                  };
              }
              
          });
        
    }
    
    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults
        (
          [
            'data_class' => ValueDTO::class,
            'fields' => null,
          ]);
    }
    
}
