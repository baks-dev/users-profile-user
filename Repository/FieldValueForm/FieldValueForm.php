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

namespace BaksDev\Users\Profile\UserProfile\Repository\FieldValueForm;

use BaksDev\Users\Profile\TypeProfile\Entity;
use BaksDev\Users\Profile\TypeProfile\Type\Id\TypeProfileUid;
use BaksDev\Core\Type\Locale\Locale;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

final class FieldValueForm implements FieldValueFormInterface
{
    private EntityManagerInterface $entityManager;
    private Locale $locale;
    
    public function __construct(EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $this->entityManager = $entityManager;
        $this->locale = new Locale($translator->getLocale());
    }
    
    public function get(TypeProfileUid $profile)
    {
        $qb = $this->entityManager->createQueryBuilder();
        
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
          FieldValueFormDTO::class);
    
        //$qb->select('field');
        $qb->select($select);
    
        $qb->addSelect('field.id');
    
        $qb->from(Entity\Section\Fields\TypeProfileSectionField::class, 'field', 'field.id');
    
        
    
        $qb->join(Entity\TypeProfile::class, 'profile', 'WITH', 'profile.id = :profile');
        $qb->join(Entity\Event\TypeProfileEvent::class, 'event', 'WITH', 'event.id = profile.event');
        
        $qb->join(Entity\Section\TypeProfileSection::class, 'section', 'WITH', 'section.id = field.section AND  section.event = event.id');
        $qb->join(
          Entity\Section\Trans\TypeProfileSectionTrans::class,
          'section_trans',
          'WITH',
          'section_trans.section = section.id AND section_trans.local = :locale');
        
        
        
        //$qb->join(Entity\Section\Fields\Field::class, 'field', 'WITH', 'field.section = section.id');
        
        $qb->join(
          Entity\Section\Fields\Trans\TypeProfileSectionFieldsTrans::class,
          'field_trans',
          'WITH',
          'field_trans.field = field.id AND field_trans.local = :locale');
        
        $qb->setParameter('locale', $this->locale, Locale::TYPE);
        
        //$qb->where('profile.id = :profile');
        $qb->setParameter('profile', $profile, TypeProfileUid::TYPE);
        
        $qb->orderBy('section.sort');
        $qb->addOrderBy('field.sort');

		
        return $qb->getQuery()->getResult();
        
    }
    
}