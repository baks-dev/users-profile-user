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

namespace BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit;

use BaksDev\Reference\Region\Type\Id\RegionUid;
use BaksDev\Users\Profile\TypeProfile\Repository\TypeProfileChoice\TypeProfileChoiceInterface;
use BaksDev\Users\Profile\TypeProfile\Type\Id\Choice\Collection\TypeProfileCollection;
use BaksDev\Users\Profile\TypeProfile\Type\Id\Choice\Collection\TypeProfileInterface;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use BaksDev\Users\Profile\UserProfile\Repository\FieldValueForm\FieldValueFormDTO;
use BaksDev\Users\Profile\UserProfile\Repository\FieldValueForm\FieldValueFormInterface;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Delivery\UserProfileDeliveryForm;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Discount\NewEditUserProfileDiscountForm;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Orders\NewEditUserProfileOrdersForm;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Region\UserProfileRegionForm;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Shop\NewEditUserProfileShopForm;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Value\ValueDTO;
use BaksDev\Users\Profile\UserProfile\UseCase\Admin\NewEdit\Warehouse\NewEditUserProfileWarehouseForm;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\ButtonType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolver;

final class UserProfileForm extends AbstractType
{


    public function __construct(
        private readonly FieldValueFormInterface $fieldValue,
        private readonly TypeProfileChoiceInterface $TypeProfileChoice
    ) {}


    public function buildForm(FormBuilderInterface $builder, array $options): void
    {

        $builder->add('personal', Personal\PersonalForm::class);

        $builder->add('info', Info\InfoForm::class);

        $builder->add('avatar', Avatar\AvatarForm::class);

        $builder->add('sort', IntegerType::class);

        /** Флаг, означающий, что профиль пользователя является магазином */
        $builder->add('shop', NewEditUserProfileShopForm::class);

        /** Флаг, означающий, что профиль пользователя является ПВЗ */
        $builder->add('orders', NewEditUserProfileOrdersForm::class);

        /** Флаг, означающий, что профиль пользователя является Складом */
        $builder->add('warehouse', NewEditUserProfileWarehouseForm::class);

        /** Персональная скидка профиля */
        $builder->add('discount', NewEditUserProfileDiscountForm::class);

        /** Регион пользователя */
        $builder->add('region', UserProfileRegionForm::class);

        /** Количество дней предзаказа */
        $builder->add('delivery', UserProfileDeliveryForm::class);

        $types = $this->TypeProfileChoice->getActiveTypeProfileChoice();

        $builder
            ->add('type', ChoiceType::class, [
                'choices' => $types,
                'choice_value' => function(TypeProfileUid|null $type) {
                    return $type?->getValue();
                },
                'choice_label' => function(TypeProfileUid $type) {

                    return $type->getAttr();
                },
                'label' => false,
                'expanded' => false,
                'multiple' => false,
                'required' => false,
            ]);

        /**
         * Свойства профиля пользователя
         */

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
            function(FormEvent $event) use ($fields) {

                if(empty($fields))
                {
                    return;
                }

                /** @var UserProfileDTO $data */
                $data = $event->getData();
                $form = $event->getForm();

                /** @var FieldValueFormDTO $field */
                foreach($fields as $field)
                {
                    //$field = end($field);

                    $new = true;

                    /** @var ValueDTO $value */
                    foreach($data->getValue() as $value)
                    {
                        /* Если поле присутствует в профиле - не добавляем*/
                        if($field->getField()->equals($value->getField()))
                        {
                            $value->updSection($field);
                            $new = false;
                            break;
                        }


                    }

                    /** Создаем новое поле для заполнения */
                    if($new)
                    {
                        $value = new ValueDTO();
                        $value->setField($field->getField());
                        $value->updSection($field);
                        $data->addValue($value);
                    }
                }

                /** Удаляем старые элементы */

                foreach($data->getValue() as $value)
                {
                    $remove = true;

                    foreach($fields as $field)
                    {
                        if(true === $field->getField()->equals($value->getField()))
                        {
                            $remove = false;
                            break;
                        }
                    }

                    if($remove)
                    {
                        $value->remove();
                        //$data->removeValue($value);
                    }
                }
            },
        );


        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function(FormEvent $event) use ($fields) {

                /** @var UserProfileDTO $data */
                $data = $event->getData();

                /**
                 * Удаляем старые элементы
                 *
                 * @var ValueDTO $remove
                 */
                foreach($data->getValue() as $remove)
                {
                    if($remove->isRemove())
                    {
                        $data->removeValue($remove);
                    }
                }

            });


        /* Сохранить ******************************************************/
        $builder->add
        (
            'Save',
            SubmitType::class,
            ['label' => 'Save', 'label_html' => true, 'attr' => ['class' => 'btn-primary']],
        );

    }


    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver->setDefaults
        (
            [
                'data_class' => UserProfileDTO::class,
                'method' => 'POST',
                'attr' => ['class' => 'w-100'],
            ],
        );
    }

}
