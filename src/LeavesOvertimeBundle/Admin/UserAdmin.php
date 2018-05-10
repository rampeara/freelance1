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
use Symfony\Component\Form\Extension\Core\Type\NumberType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use LeavesOvertimeBundle\Entity\JobTitle;
use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Sonata\UserBundle\Admin\Model\UserAdmin as BaseUserAdmin;
use Sonata\UserBundle\Form\Type\UserGenderListType;

class UserAdmin extends BaseUserAdmin
{
    public $loggedUser;
    
    /**
     * @return null|\FOS\UserBundle\Model\UserInterface
     */
    public function getUser() {
        if ($this->loggedUser) {
            return $this->loggedUser;
        }
        
        return $this->loggedUser = $this->getContainer()->get('security.token_storage')->getToken()->getUser();
    }
    
    /**
     * @return null|\Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer() {
        return $this->getConfigurationPool()->getContainer();
    }
    
    protected $datagridValues = [
        '_sort_order' => 'DESC',
        '_sort_by' => 'createdAt',
    ];
    
    public function getDataSourceIterator()
    {
        $iterator = parent::getDataSourceIterator();
        $exportDateFormat = $this->getContainer()->getParameter('datetime_format_export');
        $iterator->setDateTimeFormat($exportDateFormat);
        return $iterator;
    }
    
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
        // is granted not working here, so workaround used
        if ($this->getUser() && is_array($this->getUser()->getRoles())
            && $this->getUser()->getRoles()[0] == 'ROLE_EMPLOYEE' || $this->getUser()->getRoles()[0] == 'ROLE_SUPERVISOR') {
            // filter by user id
            $rootAlias = $query->getRootAliases()[0];
            $query->andWhere($rootAlias . ".id = :id")
                ->setParameter('id', $this->getUser()->getId());
        }
        
        return $query;
    }
    
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        parent::configureDatagridFilters($datagridMapper);
        $datagridMapper
            ->add('abNumber')
            ->add('title')
            ->add('firstname')
            ->add('lastname')
            ->add('userType')
            ->add('jobTitle')
            ->add('businessUnit')
            ->add('department')
            ->add('project')
            ->add('hireDate', 'doctrine_orm_date')
            ->add('employmentStatus')
//            ->add('departureDate')
//            ->add('departureReason')
        ;
    }

    protected function configureListFields(ListMapper $listMapper): void
    {
        $listMapper
            ->addIdentifier('id')
            ->add('abNumber')
            ->add('title')
            ->add('gender')
            ->add('firstname')
            ->add('lastname')
            ->add('userType')
            ->add('username')
            ->add('email')
            ->add('jobTitle')
            ->add('businessUnit')
            ->add('department')
            ->add('project')
            ->add('localBalance')
            ->add('sickBalance')
            ->add('carryForwardLocalBalance')
//            ->add('employmentStatus')
//            ->add('enabled', null, ['editable' => true])
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
        
        $supervisorOptions = $this->getSupervisorFormOptions();
        $simpleEntityOptions = $this->getSimpleEntityOptions();
        $datepickerOptions = new DatepickerOptions($this->getContainer()->get('doctrine'));
        $disabledDatesFormatted = $datepickerOptions->getDisabledDatesFormatted();
        $balanceOptions = [
            'required' => false,
            'attr' => [
                'step' => 0.5,
                'numberType' => true, // overridden template to change field type from text to number
            ]
        ];
    
        $formMapper->removeGroup('Profile', 'User');
        $formMapper->removeGroup('Social', 'User');
        $formMapper->removeGroup('Keys', 'Security', true);
        $formMapper->removeGroup('Status', 'Security', true);
        // if not admin level, remove security tab completely
        if ($this->getUser() && is_array($this->getUser()->getRoles())
            && !($this->getUser()->getRoles()[0] == 'ROLE_ADMIN' || $this->getUser()->getRoles()[0] == 'ROLE_SUPER_ADMIN')) {
            $formMapper->removeGroup('Groups', 'Security', true);
            $formMapper->removeGroup('Roles', 'Security', true);
            $formMapper->remove('plainPassword');
        }
        else {
            $formMapper
                ->tab('Security')
                    ->with('Groups', ['class' => 'col-md-12'])->end()
                    ->with('Roles', ['class' => 'col-md-12'])->end()
                ->end()
            ;
        }
    
        $formMapper
            ->tab('User')
                ->with('General', ['class' => 'col-md-6'])->end()
                ->with('Status', ['class' => 'col-md-6'])->end()
                ->with('Profile', ['class' => 'col-md-12'])->end()
            ->end()
        ;

        $userTypes = $this->getContainer()->getParameter('user_types');
        $formMapper
            ->tab('User')
                ->with('Status')
                    ->add('enabled', null, ['required' => false, 'data' => true])
                    ->add('localBalance', NumberType::class, $balanceOptions)
                    ->add('sickBalance', NumberType::class, $balanceOptions)
                    ->add('carryForwardLocalBalance', NumberType::class, $balanceOptions)
                    ->add('frozenCarryForwardLocalBalance', NumberType::class, $balanceOptions)
                    ->add('frozenLocalBalance', NumberType::class, $balanceOptions)
                ->end()
                ->with('Profile')
                    ->add('abNumber')
                    ->add('title', ChoiceType::class, [
                        'choices'  => [
                        'Mr' => 'Mr',
                        'Mrs' => 'Mrs',
                        'Ms' => 'Ms',
                    ]])
                    ->add('gender', UserGenderListType::class, [
                        'required' => true,
                        'translation_domain' => $this->getTranslationDomain(),
                    ])
                    ->add('firstname', TextType::class, ['required' => true])
                    ->add('lastname', TextType::class, ['required' => true])
                    ->add('userType', ChoiceType::class, [
                        'choices'  => $userTypes
                    ])
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
//                        'dp_disabled_dates' => $disabledDatesFormatted,
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
//                        'dp_disabled_dates' => $disabledDatesFormatted,
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
            ->add('id')
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
            ->add('supervisorsLevel1', null, ['associated_property' => 'fullname'])
            ->add('supervisorsLevel2', null, ['associated_property' => 'fullname'])
            ->add('hireDate')
            ->add('employmentStatus')
            ->add('departureDate')
            ->add('departureReason')
            ->add('yearsOfService', null, ['associated_property' => 'yearsOfService'])
            ->add('localBalance')
            ->add('sickBalance')
            ->add('carryForwardLocalBalance')
            ->add('createdAt')
            ->add('createdBy')
            ->add('updatedAt')
            ->add('updatedBy')
        ;
    }
    
    public function getExportFields()
    {
        return [
            'ID' => 'id',
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
            'Local Balance' => 'localBalance',
            'Sick Balance' => 'sickBalance',
            'Carry Forward Local Balance' => 'carryForwardLocalBalance',
            'Created at' => 'createdAt',
            'Created by' => 'createdBy',
            'Updated at' => 'updatedAt',
            'Updated by' => 'updatedBy',
        ];
    }
    
    /**
     * @param User $user
     * @return array
     */
    public function getSupervisorFormOptions($user = null) {
        $queryBuilder = $this->getContainer()->get('doctrine')
            ->getRepository('ApplicationSonataUserBundle:User')
            ->getUsersQueryBuilder($user);
        
        $supervisorOptions = [
            'class' => User::class,
            'query_builder' => $queryBuilder,
            'choice_label' => 'fullname',
            'required' => FALSE,
            'expanded' => FALSE,
            'multiple' => TRUE,
        ];
        return $supervisorOptions;
    }
    
    /**
     * @return array
     */
    protected function getSimpleEntityOptions(): array {
        $orderListByNameASC = function (EntityRepository $er) {
            return $er->createQueryBuilder('e')
                ->orderBy('e.name', 'ASC');
        };
        return [
            'query_builder' => $orderListByNameASC,
            'choice_label' => 'name',
            'required' => FALSE,
        ];
    }
}
