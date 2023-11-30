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

namespace BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Personal;

use BaksDev\Core\Type\Gps\GpsLatitude;
use BaksDev\Core\Type\Gps\GpsLongitude;
use BaksDev\Reference\Gender\Type\Gender;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Personal\PersonalDTO;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\CallbackTransformer;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
use Symfony\Component\Form\Extension\Core\Type\HiddenType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class PersonalForm extends AbstractType
{
	public function buildForm(FormBuilderInterface $builder, array $options)
	{

		$builder->add('username', TextType::class);

        $builder->add('location', TextType::class, ['attr' => ['data-address' => true]]);


        $builder->add('latitude', HiddenType::class, ['required' => false, 'attr' => ['data-latitude' => 'true']]);

        $builder->get('latitude')->addModelTransformer(
            new CallbackTransformer(
                function ($gps) {
                    return $gps instanceof GpsLatitude ? $gps->getValue() : $gps;
                },
                function ($gps) {
                    return new GpsLatitude($gps);
                }
            )
        );

        /* GPS долгота:*/

        $builder->add('longitude', HiddenType::class, ['required' => false, 'attr' => ['data-longitude' => 'true']]);

        $builder->get('longitude')->addModelTransformer(
            new CallbackTransformer(
                function ($gps) {
                    return $gps instanceof GpsLongitude ? $gps->getValue() : $gps;
                },
                function ($gps) {
                    return new GpsLongitude($gps);
                }
            )
        );


        $builder
			->add('gender', ChoiceType::class, [
				'choices' => Gender::cases(),
				'choice_value' => function(?Gender $gender) {
					return $gender?->getGenderValue();
				},
				'choice_label' => function(Gender $gender) {
					return $gender->getGenderValue();
				},
				
				'label' => false,
				'expanded' => true,
				'multiple' => false,
				'required' => true,
				'translation_domain' => 'reference.gender',
			])
		;
		
		$builder->add('birthday', DateType::class, [
			'widget' => 'single_text',
			'html5' => false,
			'attr' => ['class' => 'js-datepicker'],
			'required' => false,
			'format' => 'dd.MM.yyyy',
			'input' => 'datetime_immutable',
		]);
	}
	
	
	public function configureOptions(OptionsResolver $resolver)
	{
		$resolver->setDefaults
		(
			[
				'data_class' => PersonalDTO::class,
			]
		);
	}
	
}
