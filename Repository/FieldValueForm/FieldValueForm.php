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
use BaksDev\Users\Profile\TypeProfile\Entity;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use BaksDev\Core\Type\Locale\Locale;
use BaksDev\Users\Profile\TypeProfile\Type\Section\Field\Id\TypeProfileSectionFieldUid;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class FieldValueForm implements FieldValueFormInterface
{
    private ORMQueryBuilder $ORMQueryBuilder;

    public function __construct(ORMQueryBuilder $ORMQueryBuilder) {
        $this->ORMQueryBuilder = $ORMQueryBuilder;
    }

    public function getFieldById(TypeProfileSectionFieldUid $field)
    {
        $qb = $this->ORMQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal()
        ;

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
            ->from(Entity\Section\Fields\TypeProfileSectionField::class, 'field')
            ->where('field.id = :field')
            ->setParameter('field', $field)
        ;

        $qb->join(Entity\Section\TypeProfileSection::class,
            'section',
            'WITH',
            'section.id = field.section'
        );

        $qb->join(
            Entity\Section\Trans\TypeProfileSectionTrans::class,
            'section_trans',
            'WITH',
            'section_trans.section = section.id AND section_trans.local = :local'
        );

        //$qb->join(Entity\Section\Fields\Field::class, 'field', 'WITH', 'field.section = section.id');

        $qb->join(
            Entity\Section\Fields\Trans\TypeProfileSectionFieldTrans::class,
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
            ->bindLocal()
        ;

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

        $qb->from(Entity\Section\Fields\TypeProfileSectionField::class, 'field', 'field.id');


        $qb->join(Entity\Section\TypeProfileSection::class,
            'section',
            'WITH',
            'section.id = field.section'
        );

        $qb->join(
            Entity\Section\Trans\TypeProfileSectionTrans::class,
            'section_trans',
            'WITH',
            'section_trans.section = section.id AND section_trans.local = :local'
        );


        $qb->join(
            Entity\Section\Fields\Trans\TypeProfileSectionFieldTrans::class,
            'field_trans',
            'WITH',
            'field_trans.field = field.id AND field_trans.local = :local'
        );

        $qb->orderBy('section.sort');
        $qb->addOrderBy('field.sort');


        return $qb->enableCache('users-profile-user', 86400)->getResult();

        //return $qb->getQuery()->getResult();

    }


    public function get(TypeProfileUid $profile)
    {
        //$qb = $this->entityManager->createQueryBuilder();

        /** ЛОКАЛЬ */
        //$locale = new Locale($this->translator->getLocale());
        //$qb->setParameter('local', $locale, Locale::TYPE);

        //$qb->select('profile, event');

        $qb = $this->ORMQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal()
        ;

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

        $qb->from(Entity\Section\Fields\TypeProfileSectionField::class, 'field');

        $qb->join(Entity\TypeProfile::class, 'profile', 'WITH', 'profile.id = :profile');
        $qb->join(Entity\Event\TypeProfileEvent::class, 'event', 'WITH', 'event.id = profile.event');

        $qb->join(Entity\Section\TypeProfileSection::class,
            'section',
            'WITH',
            'section.id = field.section AND  section.event = event.id'
        );
        $qb->join(
            Entity\Section\Trans\TypeProfileSectionTrans::class,
            'section_trans',
            'WITH',
            'section_trans.section = section.id AND section_trans.local = :local'
        );

        //$qb->join(Entity\Section\Fields\Field::class, 'field', 'WITH', 'field.section = section.id');

        $qb->join(
            Entity\Section\Fields\Trans\TypeProfileSectionFieldTrans::class,
            'field_trans',
            'WITH',
            'field_trans.field = field.id AND field_trans.local = :local'
        );

        //$qb->where('profile.id = :profile');
        $qb->setParameter('profile', $profile, TypeProfileUid::TYPE);

        $qb->orderBy('section.sort');
        $qb->addOrderBy('field.sort');

        return $qb->enableCache('users-profile-user', 86400)->getResult();
        //return $qb->getQuery()->getResult();

    }


    public function fetchAllFieldValue()
    {
        $qb = $this->ORMQueryBuilder
            ->createQueryBuilder(self::class)
            ->bindLocal()
        ;

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
        $qb->from(Entity\Section\Fields\TypeProfileSectionField::class, 'field');

        $qb->leftJoin(
            Entity\Section\Fields\Trans\TypeProfileSectionFieldTrans::class,
            'field_trans',
            'WITH',
            'field_trans.field = field.id AND field_trans.local = :local'
        );

        /* SECTION */

        $qb->join(Entity\Section\TypeProfileSection::class,
            'section',
            'WITH',
            'section.id = field.section'
        );

        $qb->leftJoin(
            Entity\Section\Trans\TypeProfileSectionTrans::class,
            'section_trans',
            'WITH',
            'section_trans.section = section.id AND section_trans.local = :local'
        );


        $qb->join(Entity\Event\TypeProfileEvent::class, 'event', 'WITH', 'event.id = section.event');


        $qb->join(Entity\TypeProfile::class, 'profile', 'WITH', 'profile.id =  event.profile');


        //$qb->where('profile.id = :profile');
        //$qb->setParameter('profile', $profile, TypeProfileUid::TYPE);

        $qb->orderBy('section.sort');
        $qb->addOrderBy('field.sort');

        return $qb->enableCache('users-profile-user', 86400)->getResult();
        //return $qb->getQuery()->getResult();

    }

}