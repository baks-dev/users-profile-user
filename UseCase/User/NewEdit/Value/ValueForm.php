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

namespace BaksDev\Users\Profile\UserProfile\UseCase\User\NewEdit\Value;

use BaksDev\Core\Services\Fields\FieldsChoice;
use BaksDev\Users\Profile\UserProfile\Repository\FieldValueForm\FieldValueFormInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Validator\Constraints as Assert;

final class ValueForm extends AbstractType
{

    private FieldValueFormInterface $fieldValue;

    private FieldsChoice $fieldsChoice;


    public function __construct(
        FieldValueFormInterface $fieldValue,
        FieldsChoice $fieldsChoice,
    )
    {
        $this->fieldValue = $fieldValue;
        $this->fieldsChoice = $fieldsChoice;
    }


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        /* TextType */
        $builder->add('value', TextType::class);

        $builder->addEventListener(
            FormEvents::PRE_SET_DATA,
            function(FormEvent $event) use ($options) {
                /* @var ValueDTO $data */
                $data = $event->getData();
                $form = $event->getForm();

                if($data)
                {
                    //$fields = $options['fields'];

                    $field = $this->fieldValue->getFieldById($data->getField());


                    if($field)
                    {
                        $fieldType = $this->fieldsChoice->getChoice($field->getType());

                        if($fieldType)
                        {

                            $option['label'] = $field->getFieldName();
                            $option['required'] = $field->isRequired();
                            $option['help'] = $field->getFieldDescription();

                            if($field->isRequired())
                            {
                                $option['constraints'][] = new Assert\NotBlank();
                            }

                            if($fieldType->constraints())
                            {
                                foreach($fieldType->constraints() as $constraint)
                                {
                                    $option['constraints'][] = $constraint;
                                }
                            }

                            $form->add('value', $fieldType->form(), $option);


//                            $form->add
//                            (
//                                'value',
//                                $fieldType->form(),
//                                [
//                                    'label' => $field->getFieldName(),
//                                    'required' => $field->isRequired(),
//                                ]
//                            );
                        }
//                        else
//                        {
//                            $form->add
//                            (
//                                'value',
//                                TextType::class,
//                                [
//                                    'label' => $field->getFieldName(),
//                                    'required' => $field->isRequired(),
//                                ]
//                            );
//                        }
                    }
                    else
                    {
                        $form->remove('value');
                    }

//                    return;
//
//                    //dump($field);
//                    //dump($fieldType);
//
//                    dd($data->getField());
//
//
//                    if(isset($fields[(string) $data->getField()]))
//                    {
//                        /** @var FieldValueFormDTO $field */
//                        $field = end($fields[(string) $data->getField()]);
//                        $fieldType = $this->fieldsChoice->getChoice($field->getType());
//
//
//
//                        if($fieldType)
//                        {
//                            $form->add
//                            (
//                                'value',
//                                $fieldType->form(),
//                                [
//                                    'label' => $field->getFieldName(),
//                                    'required' => $field->isRequired(),
//                                ]
//                            );
//                        }
//                        else
//                        {
//                            $form->add
//                            (
//                                'value',
//                                TextType::class,
//                                [
//                                    'label' => $field->getFieldName(),
//                                    'required' => $field->isRequired(),
//                                ]
//                            );
//                        }
//                    }
//                    else
//                    {
//
//                        /** Удаляем элементы, которые были удалены  */
//                        $form->remove('value');
//
////                        $form->add
////                        (
////                            'value',
////                            TextType::class, ['label' => false]
////                        );
//                    }
                }

            }
        );

    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults
        (
            [
                'data_class' => ValueDTO::class,
                'fields' => null,
            ]
        );
    }

}
