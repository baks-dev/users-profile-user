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
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

final class FieldValueForm implements FieldValueFormInterface
{
    private ORMQueryBuilder $ORMQueryBuilder;

    private UserUid|User|string|null $user = null;

    public function __construct(
        ORMQueryBuilder $ORMQueryBuilder
    )
    {
        $this->ORMQueryBuilder = $ORMQueryBuilder;
    }


    public function getFieldById(TypeProfileSectionFieldUid $field)
    {
        $qb = $this->ORMQueryBuilder
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

        $qb->select($select);

        $qb
            ->from(TypeProfileSectionField::class, 'field')
            ->where('field.id = :field')
            ->setParameter('field', $field);

        $qb->join(TypeProfileSection::class,
            'section',
            'WITH',
            'section.id = field.section'
        );

        $qb->join(
            TypeProfileSectionTrans::class,
            'section_trans',
            'WITH',
            'section_trans.section = section.id AND section_trans.local = :local'
        );

        //$qb->join(Entity\Section\Fields\Field::class, 'field', 'WITH', 'field.section = section.id');

        $qb->join(
            TypeProfileSectionFieldTrans::class,
            'field_trans',
            'WITH',
            'field_trans.field = field.id AND field_trans.local = :local'
        );

        //$qb->where('profile.id = :profile');
        //$qb->setParameter('profile', $profile, TypeProfileUid::TYPE);

        ///$qb->orderBy('section.sort');
        //$qb->addOrderBy('field.sort');

        return $qb->enableCache('users-profile-type', 86400)->getOneOrNullResult();
    }


    public function getAllField()
    {
        $qb = $this->ORMQueryBuilder
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


        $qb->select($select);

        $qb->from(TypeProfileSectionField::class, 'field', 'field.id');


        $qb->join(TypeProfileSection::class,
            'section',
            'WITH',
            'section.id = field.section'
        );

        $qb->join(
            TypeProfileSectionTrans::class,
            'section_trans',
            'WITH',
            'section_trans.section = section.id AND section_trans.local = :local'
        );


        $qb->join(
            TypeProfileSectionFieldTrans::class,
            'field_trans',
            'WITH',
            'field_trans.field = field.id AND field_trans.local = :local'
        );

        $qb->orderBy('section.sort');
        $qb->addOrderBy('field.sort');


        return $qb->enableCache('users-profile-user', 86400)->getResult();

        //return $qb->getQuery()->getResult();

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

    public function get(TypeProfileUid $profile)
    {

        //$User = $this->tokenStorage->getToken()?->getUser();

        //$qb = $this->entityManager->createQueryBuilder();

        /** ЛОКАЛЬ */
        //$locale = new Locale($this->translator->getLocale());
        //$qb->setParameter('local', $locale, Locale::TYPE);

        //$qb->select('profile, event');

        $qb = $this->ORMQueryBuilder
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

        //$qb->select('field');
        $qb->select($select);

        //$qb->addSelect('field.id');
        //$qb->from(Entity\Section\Fields\TypeProfileSectionField::class, 'field', 'field.id');

        $qb->from(TypeProfileSectionField::class, 'field');

        $qb->join(TypeProfile::class,
            'profile',
            'WITH',
            'profile.id = :profile'
        );

        $qb->join(
            TypeProfileEvent::class,
            'event',
            'WITH',
            'event.id = profile.event'
        );

        $qb->join(TypeProfileSection::class,
            'section',
            'WITH',
            'section.id = field.section AND  section.event = event.id'
        );

        $qb->join(
            TypeProfileSectionTrans::class,
            'section_trans',
            'WITH',
            'section_trans.section = section.id AND section_trans.local = :local'
        );

        //$qb->join(Entity\Section\Fields\Field::class, 'field', 'WITH', 'field.section = section.id');

        $qb->join(
            TypeProfileSectionFieldTrans::class,
            'field_trans',
            'WITH',
            'field_trans.field = field.id AND field_trans.local = :local'
        );

        //$qb->where('profile.id = :profile');
        $qb->setParameter('profile', $profile, TypeProfileUid::TYPE);

        $qb->orderBy('section.sort');
        $qb->addOrderBy('field.sort');


        /** Если пользователь авторизован - проверяем заполненные поля */
        if($this->user)
        {
            $qb
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


            $qb
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

            //$qb->andWhere($qb->expr()->exists($subQueryBuilder->getDQL()));
            $qb->andWhere($qb->expr()->not($qb->expr()->exists($subQueryBuilder->getDQL())));

        }


        return $qb->enableCache('users-profile-user', 86400)->getResult();
        //return $qb->getQuery()->getResult();

    }


    public function fetchAllFieldValue()
    {
        $qb = $this->ORMQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal();

        /** ЛОКАЛЬ */
        //$locale = new Locale($this->translator->getLocale());
        //$qb->setParameter('local', $locale, Locale::TYPE);

        //$qb->select('profile, event');

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

        //$qb->select('field');
        $qb->select($select);

        //$qb->addSelect('field.id');


        /* FIELD */
        //$qb->from(Entity\Section\Fields\TypeProfileSectionField::class, 'field', 'field.id');
        $qb->from(TypeProfileSectionField::class, 'field');

        $qb->leftJoin(
            TypeProfileSectionFieldTrans::class,
            'field_trans',
            'WITH',
            'field_trans.field = field.id AND field_trans.local = :local'
        );

        /* SECTION */

        $qb->join(TypeProfileSection::class,
            'section',
            'WITH',
            'section.id = field.section'
        );

        $qb->leftJoin(
            TypeProfileSectionTrans::class,
            'section_trans',
            'WITH',
            'section_trans.section = section.id AND section_trans.local = :local'
        );


        $qb->join(
            TypeProfileEvent::class,
            'event',
            'WITH',
            'event.id = section.event'
        );


        $qb->join(
            TypeProfile::class,
            'profile',
            'WITH',
            'profile.id =  event.profile'
        );


        //$qb->where('profile.id = :profile');
        //$qb->setParameter('profile', $profile, TypeProfileUid::TYPE);

        $qb->orderBy('section.sort');
        $qb->addOrderBy('field.sort');

        return $qb->enableCache('users-profile-user', 86400)->getResult();
        //return $qb->getQuery()->getResult();

    }

}