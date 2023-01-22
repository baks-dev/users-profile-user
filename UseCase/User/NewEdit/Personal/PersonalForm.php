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

namespace BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Personal;

use BaksDev\Reference\Gender\Type\Gender;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PersonalForm extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options) : void
    {
        /* TextType */
        $builder->add('username', TextType::class);
    
        $builder->add('location', TextType::class, ['help' => '&nbsp;', 'help_html' => true,]);
    
    
        /* Тип профиля */
        $builder
          ->add('gender', ChoiceType::class, [
            'choices' => Gender::cases(),
            'choice_value' => function (?Gender $gender)
            {
                return $gender?->getValue();
            },
            'choice_label' => function (Gender $gender)
            {
                return $gender->getName();
            },
  
            'label' => false,
            'expanded' => true,
            'multiple' => false,
            'required' => true,
            'translation_domain' => 'gender',
          ]);
    
        $builder->add('birthday', DateType::class, [
          'widget' => 'single_text',
          'html5' => false,
          'attr' => ['class' => 'js-datepicker'],
          'required' => false,
          'format' => 'dd.MM.yyyy',
          'input' => 'datetime_immutable',
        ]);
    }
    
    public function configureOptions(OptionsResolver $resolver) : void
    {
        $resolver->setDefaults
        (
          [
            'data_class' => PersonalDTO::class,
          ]);
    }
    
}
