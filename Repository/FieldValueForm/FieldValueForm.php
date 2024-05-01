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

use BaksDev\Core\Doctrine\ORMQueryBuilder;
use BaksDev\Users\Profile\TypeProfile\Entity\Event\TypeProfileEvent;
use BaksDev\Users\Profile\TypeProfile\Entity\Section\Fields\Trans\TypeProfileSectionFieldTrans;
use BaksDev\Users\Profile\TypeProfile\Entity\Section\Fields\TypeProfileSectionField;
use BaksDev\Users\Profile\TypeProfile\Entity\Section\Trans\TypeProfileSectionTrans;
use BaksDev\Users\Profile\TypeProfile\Entity\Section\TypeProfileSection;
use BaksDev\Users\Profile\TypeProfile\Entity\TypeProfile;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use BaksDev\Users\Profile\TypeProfile\Type\Section\Field\Id\TypeProfileSectionFieldUid;
use BaksDev\Users\Profile\UserProfile\Entity\Info\UserProfileInfo;
use BaksDev\Users\Profile\UserProfile\Entity\UserProfile;
use BaksDev\Users\Profile\UserProfile\Entity\Value\UserProfileValue;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\Status\UserProfileStatusActive;
use BaksDev\Users\Profile\UserProfile\Type\UserProfileStatus\UserProfileStatus;
use BaksDev\Users\User\Entity\User;
use BaksDev\Users\User\Type\Id\UserUid;

final class FieldValueForm implements FieldValueFormInterface
{
    private ORMQueryBuilder $ORMQueryBuilder;

    private ?UserUid $user = null;

    public function __construct(
        ORMQueryBuilder $ORMQueryBuilder
    )
    {
        $this->ORMQueryBuilder = $ORMQueryBuilder;
    }


    public function getFieldById(TypeProfileSectionFieldUid $field)
    {
        $orm = $this->ORMQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $select = sprintf(

            '
          
          new %s(
            section.id,
            section_trans.name,
            section_trans.description,
            
            field.id,
            
            field_trans.name,
            field_trans.description,
            
            field.type,
            field.required
            
        )',
            FieldValueFormDTO::class
        );

        $orm->select($select);

        $orm
            ->from(TypeProfileSectionField::class, 'field')
            ->where('field.id = :field')
            ->setParameter('field', $field);

        $orm->join(TypeProfileSection::class,
            'section',
            'WITH',
            'section.id = field.section'
        );

        $orm->join(
            TypeProfileSectionTrans::class,
            'section_trans',
            'WITH',
            'section_trans.section = section.id AND section_trans.local = :local'
        );

        $orm->join(
            TypeProfileSectionFieldTrans::class,
            'field_trans',
            'WITH',
            'field_trans.field = field.id AND field_trans.local = :local'
        );

        return $orm->enableCache('users-profile-type', 86400)->getOneOrNullResult();
    }


    public function getAllField()
    {
        $orm = $this->ORMQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $select = sprintf(

            '
          
          new %s(
            section.id,
            section_trans.name,
            section_trans.description,
            
            field.id,
            
            field_trans.name,
            field_trans.description,
            
            field.type,
            field.required
            
        )',
            FieldValueFormDTO::class
        );


        $orm->select($select);

        $orm->from(TypeProfileSectionField::class, 'field', 'field.id');


        $orm->join(TypeProfileSection::class,
            'section',
            'WITH',
            'section.id = field.section'
        );

        $orm->join(
            TypeProfileSectionTrans::class,
            'section_trans',
            'WITH',
            'section_trans.section = section.id AND section_trans.local = :local'
        );


        $orm->join(
            TypeProfileSectionFieldTrans::class,
            'field_trans',
            'WITH',
            'field_trans.field = field.id AND field_trans.local = :local'
        );

        $orm->orderBy('section.sort');
        $orm->addOrderBy('field.sort');


        return $orm->enableCache('users-profile-user', 86400)->getResult();

        //return $orm->getQuery()->getResult();

    }


    public function userFilter(User|UserUid|string $user) : self
    {
        if($user instanceof User)
        {
            $user = $user->getId();
        }

        if(is_string($user))
        {
            $user = new UserUid($user);
        }


        $this->user = $user;

        return $this;
    }

    public function get(TypeProfileUid $profile): ?array
    {

        $orm = $this->ORMQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $select = sprintf(

            '
          
          new %s(
            section.id,
            section_trans.name,
            section_trans.description,
            
            field.id,
            
            field_trans.name,
            field_trans.description,
            
            field.type,
            field.required
            
        )',
            FieldValueFormDTO::class
        );

        $orm->select($select);

        $orm->from(TypeProfileSectionField::class, 'field');

        $orm->join(TypeProfile::class,
            'profile',
            'WITH',
            'profile.id = :profile'
        );

        $orm->join(
            TypeProfileEvent::class,
            'event',
            'WITH',
            'event.id = profile.event'
        );

        $orm->join(TypeProfileSection::class,
            'section',
            'WITH',
            'section.id = field.section AND  section.event = event.id'
        );

        $orm->join(
            TypeProfileSectionTrans::class,
            'section_trans',
            'WITH',
            'section_trans.section = section.id AND section_trans.local = :local'
        );


        $orm->join(
            TypeProfileSectionFieldTrans::class,
            'field_trans',
            'WITH',
            'field_trans.field = field.id AND field_trans.local = :local'
        );


        $orm->setParameter('profile', $profile, TypeProfileUid::TYPE);

        $orm->orderBy('section.sort');
        $orm->addOrderBy('field.sort');


        /** Если пользователь авторизован - проверяем заполненные поля */
        if($this->user)
        {
            $orm
                ->leftJoin(
                    UserProfileInfo::class,
                    'profile_info',
                    'WITH',
                    '
                profile_info.usr = :user AND 
                profile_info.status = :active AND 
                profile_info.active = true
            ')
                ->setParameter('user', $this->user, UserUid::TYPE)
                ->setParameter('active', new UserProfileStatus(UserProfileStatusActive::class), UserProfileStatus::TYPE);


            $orm
                ->leftJoin(

                    UserProfile::class,
                    'user_profile',
                    'WITH',
                    'user_profile.id = profile_info.profile'
                );


            /* NOT EXIST PROFILE VALUES */
            $subQueryBuilder = $this->ORMQueryBuilder->createQueryBuilder(self::class);
            $subQueryBuilder
                ->select('1')
                ->from(UserProfileValue::class, 'user_profile_value')
                ->where('user_profile_value.event = user_profile.event AND user_profile_value.field = field.id')
            ;

            //$orm->andWhere($orm->expr()->exists($subQueryBuilder->getDQL()));
            $orm->andWhere($orm->expr()->not($orm->expr()->exists($subQueryBuilder->getDQL())));

        }


        return $orm->enableCache('users-profile-user', 86400)->getResult();


    }


    public function fetchAllFieldValue()
    {
        $orm = $this->ORMQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        $select = sprintf(

            '
          
          new %s(
            section.id,
            section_trans.name,
            section_trans.description,
            
            field.id,
            
            field_trans.name,
            field_trans.description,
            
            field.type,
            field.required
            
        )',
            FieldValueFormDTO::class
        );


        $orm->select($select);



        $orm->from(TypeProfileSectionField::class, 'field');

        $orm->leftJoin(
            TypeProfileSectionFieldTrans::class,
            'field_trans',
            'WITH',
            'field_trans.field = field.id AND field_trans.local = :local'
        );

        /* SECTION */

        $orm->join(TypeProfileSection::class,
            'section',
            'WITH',
            'section.id = field.section'
        );

        $orm->leftJoin(
            TypeProfileSectionTrans::class,
            'section_trans',
            'WITH',
            'section_trans.section = section.id AND section_trans.local = :local'
        );


        $orm->join(
            TypeProfileEvent::class,
            'event',
            'WITH',
            'event.id = section.event'
        );


        $orm->join(
            TypeProfile::class,
            'profile',
            'WITH',
            'profile.id =  event.profile'
        );


        $orm->orderBy('section.sort');
        $orm->addOrderBy('field.sort');

        return $orm->enableCache('users-profile-user', 86400)->getResult();

    }

}