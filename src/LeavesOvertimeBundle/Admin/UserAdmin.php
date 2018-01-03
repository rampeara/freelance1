<?php

namespace LeavesOvertimeBundle\Admin;

use Application\Sonata\UserBundle\Entity\User;
use LeavesOvertimeBundle\Common\DatepickerOptions;
use LeavesOvertimeBundle\Entity\BusinessUnit;
use LeavesOvertimeBundle\Entity\Department;
use LeavesOvertimeBundle\Entity\Project;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use LeavesOvertimeBundle\Entity\JobTitle;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Doctrs\SonataImportBundle\Admin\AdminImportTrait;
use Sonata\UserBundle\Admin\Model\UserAdmin as BaseUserAdmin;
use Sonata\UserBundle\Form\Type\UserGenderListType;

class UserAdmin extends BaseUserAdmin
{
    use AdminImportTrait;
    
    protected $datagridValues = [
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];
    
    public function getDataSourceIterator()
    {
        $iterator = parent::getDataSourceIterator();
        $exportDateFormat = $this->getConfigurationPool()->getContainer()->getParameter('datetime_format_export');
        $iterator->setDateTimeFormat($exportDateFormat);
        return $iterator;
    }
    
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        parent::configureDatagridFilters($datagridMapper);
        $datagridMapper
            ->add('abNumber')
            ->add('title')
            ->add('firstname')
            ->add('lastname')
            ->add('jobTitle')
            ->add('businessUnit')
            ->add('department')
            ->add('project')
//            ->add('hireDate')
            ->add('employmentStatus')
//            ->add('departureDate')
//            ->add('departureReason')
        ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('abNumber')
            ->add('title')
            ->add('gender')
            ->add('firstname')
            ->add('lastname')
            ->add('jobTitle')
            ->add('email')
            ->add('businessUnit')
            ->add('department')
            ->add('project')
            ->add('employmentStatus')
            ->add('enabled', null, ['editable' => true])
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

    protected function configureFormFields(FormMapper $formMapper): void
    {
        parent::configureFormFields($formMapper);
        
        $orderListByNameASC = function (EntityRepository $er) {
            return $er->createQueryBuilder('e')
                ->orderBy('e.name', 'ASC');
        };
        
        $orderListByLastNameASC = function (EntityRepository $er) {
            return $er->createQueryBuilder('em')
                ->orderBy('em.lastname', 'ASC');
        };
        
        $supervisorOptions = [
            'class' => User::class,
            'query_builder' => $orderListByLastNameASC,
            'choice_label' => 'fullname',
            'required'   => false,
            'expanded' => false,
            'multiple' => true,
        ];
        
        $simpleEntityOptions = [
            'query_builder' => $orderListByNameASC,
            'choice_label' => 'name',
            'required'   => false,
        ];
    
        $datepickerOptions = new DatepickerOptions($this->configurationPool->getContainer()->get('doctrine'));
        $disabledDatesFormatted = $datepickerOptions->getDisabledDatesFormatted();
        
        $formMapper->removeGroup('Profile', 'User');
        $formMapper->removeGroup('Social', 'User');
        $formMapper->removeGroup('Keys', 'Security');
        
        $formMapper
            ->tab('User')
                ->with('Profile')
                    ->add('abNumber')
                    ->add('title', ChoiceType::class, [
                        'choices'  => [
                        'Mr' => 'Mr',
                        'Mrs' => 'Mrs',
                        'Ms' => 'Ms',
                    ]])
    //                ->add('gender', ChoiceType::class, [
    //                    'choices'  => [
    //                        'Male' => 'Male',
    //                        'Female' => 'Female',
    //                    ]])
                    ->add('gender', UserGenderListType::class, [
                        'required' => true,
                        'translation_domain' => $this->getTranslationDomain(),
                    ])
                    ->add('firstname', TextType::class, ['required' => true])
                    ->add('lastname', TextType::class, ['required' => true])
                    ->add('jobTitle', EntityType::class, array_merge($simpleEntityOptions, [
                        'class' => JobTitle::class
                    ]))
                    ->add('businessUnit', EntityType::class, array_merge($simpleEntityOptions, [
                        'class' => BusinessUnit::class
                    ]))
                    ->add('department', EntityType::class, array_merge($simpleEntityOptions, [
                        'class' => Department::class
                    ]))
                    ->add('project', EntityType::class, array_merge($simpleEntityOptions, [
                        'class' => Project::class
                    ]))
                    ->add('supervisorsLevel1', EntityType::class, $supervisorOptions)
                    ->add('supervisorsLevel2', EntityType::class, $supervisorOptions)
                    ->add('hireDate', 'sonata_type_date_picker', [
                        'dp_disabled_dates' => $disabledDatesFormatted,
                        'dp_use_current' => false,
                    ])
                    ->add('employmentStatus', ChoiceType::class, [
                        'choices'  => [
                            "CDD" => "CDD",
                            "CDI" => "CDI",
                            "YEP" => "YEP",
                            "PART TIME" => "PART TIME",
                    ]])
                    ->add('departureDate', 'sonata_type_date_picker', [
                        'dp_disabled_dates' => $disabledDatesFormatted,
                        'dp_use_current' => false,
                        'required' => false
                    ])
                    ->add('departureReason', TextareaType::class, ['required' => false])
                ->end()
            ->end()
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $showMapper
            ->add('abNumber')
            ->add('username')
            ->add('email')
            ->add('title')
            ->add('gender')
            ->add('firstname')
            ->add('lastname')
            ->add('jobTitle')
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
            'Username' => 'username',
            'Email' => 'email',
            'Title' => 'title',
            'Gender' => 'gender',
            'First name' => 'firstname',
            'Last name' => 'lastname',
            'Job title' => 'jobTitle',
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
}
