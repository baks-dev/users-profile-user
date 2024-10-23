<?php
/*
 *  Copyright 2024.  Baks.dev <admin@baks.dev>
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

namespace BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Info;

use BaksDev\Users\Profile\UserProfile\Repository\UsersChoiceForm\UsersChoiceOptionalAccountEmailInterface;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use BaksDev\Users\User\Type\Id\UserUid;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class InfoForm extends AbstractType
{
	private UsersChoiceOptionalAccountEmailInterface $usersChoice;
	
	
	public function __construct(UsersChoiceOptionalAccountEmailInterface $usersChoice)
	{
		$this->usersChoice = $usersChoice;
	}
	
	
	public function buildForm(FormBuilderInterface $builder, array $options): void
    {
		$builder
			->add('usr', ChoiceType::class, [
				'choices' => $this->usersChoice->getChoice(),
				'choice_value' => function(?UserUid $status) {
					return $status?->getValue();
				},
				'choice_label' => function(UserUid $status) {
					/* Account Email */
					return $status->getOption();
				},
				'label' => false,
				'expanded' => false,
				'multiple' => false,
				'required' => true,
				'attr' => ['data-select' => 'select2',],
			])
		;
		
		$builder->add('url', TextType::class);
		
		$builder->add('discount',
			IntegerType::class, [
				'required' => false,
                'attr' => ['min' => -99, 'max' => 99],
			]
		);



		$builder
			->add('status', ChoiceType::class, [
				'choices' => UserProfileStatus::cases(),
				'choice_value' => function(?UserProfileStatus $status) {
					return $status?->getUserProfileStatusValue();
				},
				'choice_label' => function(UserProfileStatus $status) {
					return $status->getUserProfileStatusValue();
				},
				
				'label' => false,
				'expanded' => false,
				'multiple' => false,
				'required' => true,
				'translation_domain' => 'user.profile.status',
			])
		;
	}
	
	
	public function configureOptions(OptionsResolver $resolver): void
    {
		$resolver->setDefaults([
			'data_class' => InfoDTO::class,
		]);
	}
	
}
