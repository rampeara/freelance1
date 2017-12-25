<?php

namespace LeavesOvertimeBundle\Admin;

use LeavesOvertimeBundle\Entity\BusinessUnit;
use LeavesOvertimeBundle\Entity\Department;
use LeavesOvertimeBundle\Entity\Employee;
use LeavesOvertimeBundle\Entity\Project;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\DateType;
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
//            ->add('supervisor1')
//            ->add('supervisor2')
//            ->add('supervisor3')
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
//            ->add('supervisor1')
//            ->add('supervisor2')
//            ->add('supervisor3')
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
            ->add('businessUnit', EntityType::class, [
                'class' => BusinessUnit::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('bu')
                        ->orderBy('bu.name', 'ASC');
                },
                'choice_label' => 'name',
                'required'   => false,
            ])
            ->add('department', EntityType::class, [
                'class' => Department::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('dt')
                        ->orderBy('dt.name', 'ASC');
                },
                'choice_label' => 'name',
                'required'   => false,
            ])
            ->add('project', EntityType::class, [
                'class' => Project::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('pr')
                        ->orderBy('pr.name', 'ASC');
                },
                'choice_label' => 'name',
                'required'   => false,
            ])
            ->add('supervisorsLevel1', EntityType::class, [
                'class' => Employee::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('em')
                        ->orderBy('em.lastName', 'ASC');
                },
                'choice_label' => 'fullName',
                'required'   => false,
                'expanded' => false,
                'multiple' => true,
            ])
            ->add('supervisorsLevel2', EntityType::class, [
                'class' => Employee::class,
                'query_builder' => function (EntityRepository $er) {
                    return $er->createQueryBuilder('em')
                        ->orderBy('em.lastName', 'ASC');
                },
                'choice_label' => 'fullName',
                'required'   => false,
                'expanded' => false,
                'multiple' => true,
            ])
            ->add('hireDate', DateType::class, [
                'widget'  => 'single_text'
            ])
            ->add('employmentStatus', ChoiceType::class, [
                'choices'  => [
                    "CDD" => "CDD",
                    "CDI" => "CDI",
                    "YEP" => "YEP",
                    "PART TIME" => "PART TIME",
            ]])
            ->add('departureDate', DateType::class, [
                'widget'  => 'single_text'
            ])
            ->add('departureReason', TextareaType::class, ['required' => false])
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
            ->add('supervisor1')
            ->add('supervisor2')
            ->add('supervisor3')
            ->add('hireDate')
            ->add('employmentStatus')
            ->add('departureDate')
            ->add('departureReason')
        ;
    }
}
