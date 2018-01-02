<?php

namespace LeavesOvertimeBundle\Admin;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Doctrine\ORM\EntityRepository;
use Application\Sonata\UserBundle\Entity\User;

class LeavesAdmin extends CommonAdmin
{
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('type')
            ->add('startDate', 'doctrine_orm_datetime')
            ->add('endDate', 'doctrine_orm_datetime')
            ->add('createdAt', 'doctrine_orm_datetime')
            ->add('createdBy')
            ->add('updatedAt', 'doctrine_orm_datetime')
            ->add('updatedBy')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('type')
            ->add('startDate')
            ->add('endDate')
            ->add('createdAt')
            ->add('createdBy')
            ->add('updatedAt')
            ->add('updatedBy')
            ->add('_action', null, [
                'actions' => [
                    'show' => [],
                    'edit' => [],
                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $datetimeOptions = [
            'dp_disabled_dates' => $this->getDisabledDatesFormatted(),
            'dp_use_current' => true,
            'dp_side_by_side' => true,
            //                'required' => false
        ];
    
        $orderListByLastNameASC = function (EntityRepository $er) {
            return $er->createQueryBuilder('em')
                ->orderBy('em.lastname', 'ASC');
        };
    
        $employeeOptions = [
            'class' => User::class,
            'query_builder' => $orderListByLastNameASC,
            'choice_label' => 'fullname',
            'required'   => false
        ];
        
        $formMapper
            ->add('user', EntityType::class, $employeeOptions)
            ->add('type', ChoiceType::class, [
                'choices'  => [
                    'Local leave' => 'Local leave',
                    'Sick leave' => 'Sick leave',
                    'Absence from work' => 'Absence from work',
                    'Leave without pay' => 'Leave without pay',
                    'Special paid leave' => 'Special paid leave',
                    'Maternity leave' => 'Maternity leave',
                    'Maternity leave without pay' => 'Maternity leave without pay',
                    'Paternity leave' => 'Paternity leave',
                    'Paternity leaves without pay' => 'Paternity leaves without pay',
                    'Compassionate leave' => 'Compassionate leave',
                    'Wedding leave' => 'Wedding leave',
                    'Wedding leave without pay' => 'Wedding leave without pay',
                    'Injury leave' => 'Injury leave',
                    'Injury leave without pay' => 'Injury leave without pay',
            ]])
            ->add('startDate', 'sonata_type_datetime_picker', $datetimeOptions)
            ->add('endDate', 'sonata_type_datetime_picker', $datetimeOptions)
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('type')
            ->add('startDate')
            ->add('endDate')
            ->add('createdAt')
            ->add('createdBy')
            ->add('updatedAt')
            ->add('updatedBy')
        ;
    }
}
