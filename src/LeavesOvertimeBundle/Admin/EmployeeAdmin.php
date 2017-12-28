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
use Symfony\Component\Form\Extension\Core\Type\TextType;

class EmployeeAdmin extends AbstractAdmin
{
    protected $datagridValues = [
        '_sort_order' => 'ASC',
        '_sort_by' => 'lastName',
    ];
    
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
            ->add('gender')
            ->add('firstName')
            ->add('lastName')
            ->add('jobTitle')
            ->add('email')
            ->add('businessUnit')
            ->add('department')
            ->add('project')
            ->add('employmentStatus')
//            ->add('createdAt')
//            ->add('createdBy')
//            ->add('updatedAt')
//            ->add('updatedBy')
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
            ->add('gender', ChoiceType::class, [
                'choices'  => [
                    'Male' => 'Male',
                    'Female' => 'Female',
                ]])
            ->add('firstName', TextType::class, ['required' => true])
            ->add('lastName', TextType::class, ['required' => true])
            ->add('jobTitle', EntityType::class, [
                'class' => JobTitle::class,
                'query_builder' => function (EntityRepository $er) {
                return $er->createQueryBuilder('jt')
                  ->orderBy('jt.value', 'ASC');
                },
                'choice_label' => 'value',
                'required'   => false,
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
                'widget'  => 'single_text',
                'required'   => false,
            ])
            ->add('departureReason', TextareaType::class, ['required' => false])
        ;
    }


    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('abNumber')
            ->add('title')
            ->add('gender')
            ->add('firstName')
            ->add('lastName')
            ->add('jobTitle')
            ->add('email')
            ->add('businessUnit')
            ->add('department')
            ->add('project')
            ->add('supervisorsLevel1', null, ['associated_property' => 'fullName'])
            ->add('supervisorsLevel2', null, ['associated_property' => 'fullName'])
            ->add('hireDate')
            ->add('employmentStatus')
            ->add('departureDate')
            ->add('departureReason')
            ->add('yearsOfService', null, ['associated_property' => 'yearsOfService'])
            ->add('createdAt')
            ->add('createdBy')
            ->add('updatedAt')
            ->add('updatedBy')
        ;
    }
    
    public function getExportFields()
    {
        return [
            'AB number' => 'abNumber',
            'Title' => 'title',
            'Gender' => 'gender',
            'First name' => 'firstName',
            'Last name' => 'lastName',
            'Job title' => 'jobTitle',
            'Email' => 'email',
            'Business unit' => 'businessUnit',
            'Department' => 'department',
            'Project' => 'project',
            'Supervisors level 1' => 'supervisorsLevel1String',
            'Supervisors level 2' => 'supervisorsLevel2String',
            'Hire date' => 'hireDate',
            'Employment status' => 'employmentStatus',
            'Departure date' => 'departureDate',
            'Departure reason' => 'departureReason',
            'Years of service' => 'yearsOfService',
            'Created at' => 'createdAt',
            'Created by' => 'createdBy',
            'Updated at' => 'updatedAt',
            'Updated by' => 'updatedBy',
        ];
    }
    
    public function getDataSourceIterator()
    {
        $iterator = parent::getDataSourceIterator();
        $exportDateFormat = $this->getConfigurationPool()->getContainer()->getParameter('date_format_export');
        $iterator->setDateTimeFormat($exportDateFormat);
        return $iterator;
    }
}
