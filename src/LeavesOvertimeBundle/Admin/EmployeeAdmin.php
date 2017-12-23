<?php

namespace LeavesOvertimeBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use LeavesOvertimeBundle\Entity\JobTitle;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class EmployeeAdmin extends AbstractAdmin
{
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('abNumber')
            ->add('title')
            ->add('firstName')
            ->add('lastName')
            ->add('jobTitle')
            ->add('email')
            ->add('businessUnit')
            ->add('department')
            ->add('project')
            ->add('approverN1')
            ->add('approverN2')
            ->add('approverN3')
            ->add('hireDate')
            ->add('employmentStatus')
            ->add('departureDate')
            ->add('departureReason')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('abNumber')
            ->add('title')
            ->add('firstName')
            ->add('lastName')
            ->add('jobTitle')
            ->add('email')
            ->add('businessUnit')
            ->add('department')
            ->add('project')
            ->add('approverN1')
            ->add('approverN2')
            ->add('approverN3')
            ->add('hireDate')
            ->add('employmentStatus')
            ->add('departureDate')
            ->add('departureReason')
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
        $formMapper
            ->add('abNumber')
            ->add('title', ChoiceType::class, [
              'choices'  => [
                'Mr' => 'Mr',
                'Mrs' => 'Mrs',
                'Ms' => 'Ms',
              ]])
            ->add('firstName')
            ->add('lastName')
            ->add('jobTitle', EntityType::class, [
              'class' => JobTitle::class,
              'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('jt')
                  ->orderBy('jt.value', 'ASC');
              },
              'choice_label' => 'value',
            ])
            ->add('email', EmailType::class)
            ->add('businessUnit')
            ->add('department')
            ->add('project')
            ->add('approverN1')
            ->add('approverN2')
            ->add('approverN3')
            ->add('hireDate')
            ->add('employmentStatus')
            ->add('departureDate')
            ->add('departureReason')
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('abNumber')
            ->add('title')
            ->add('firstName')
            ->add('lastName')
            ->add('jobTitle')
            ->add('email')
            ->add('businessUnit')
            ->add('department')
            ->add('project')
            ->add('approverN1')
            ->add('approverN2')
            ->add('approverN3')
            ->add('hireDate')
            ->add('employmentStatus')
            ->add('departureDate')
            ->add('departureReason')
        ;
    }
}
