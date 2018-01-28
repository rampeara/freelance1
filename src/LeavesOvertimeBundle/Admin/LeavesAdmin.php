<?php

namespace LeavesOvertimeBundle\Admin;

use LeavesOvertimeBundle\Common\DatepickerOptions;
use LeavesOvertimeBundle\Entity\Leaves;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Application\Sonata\UserBundle\Entity\User;
use Symfony\Component\Form\Extension\Core\Type\NumberType;

class LeavesAdmin extends CommonAdmin
{
    private $leaves;
    
    public function __construct(string $code, string $class, string $baseControllerName) {
        parent::__construct($code, $class, $baseControllerName);
        $this->leaves = new Leaves();
    }
    
    public function configureBatchActions($actions)
    {
        // remove delete to avoid deleting entries with foreign keys without
        // giving a warning like customised in its Controller
        unset($actions['delete']);
        return $actions;
    }
    
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->remove('edit');
        $collection->remove('delete');
    }
    
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('user')
            ->add('type','doctrine_orm_string', [], 'choice', [
                'choices' => $this->leaves->getTypeChoices()
            ])
            ->add('status', 'doctrine_orm_string', [], 'choice', [
                'choices' => $this->leaves->getStatusChoices()
            ])
            ->add('startDate', 'doctrine_orm_date')
            ->add('endDate', 'doctrine_orm_date')
            ->add('duration')
            ->add('createdAt', 'doctrine_orm_datetime')
            ->add('createdBy')
            ->add('updatedAt', 'doctrine_orm_datetime')
            ->add('updatedBy')
        ;
    }
    
    public function createQuery($context = 'list')
    {
        $query = parent::createQuery($context);
    
        if (in_array($this->getRole(), ['ROLE_EMPLOYEE', 'ROLE_SUPERVISOR'])) {
            // ids of my subordinates and logged in user
            $userId = $this->getUser()->getId();
            $subordinateIds = $this->getContainer()->get('doctrine')
                ->getRepository('ApplicationSonataUserBundle:User')
                ->getMySubordinatesIds($userId);
    
            // filter my ids supplied
            $rootAlias = $query->getRootAliases()[0];
            $query->innerJoin($rootAlias . '.user', 'u')
                ->andWhere("u.id IN(:subordinatesIds)")
                ->orderBy('u.id', 'ASC')
                ->addOrderBy('u.lastname', 'ASC')
                ->addOrderBy($rootAlias . '.createdAt', 'DESC')
                ->setParameter('subordinatesIds', $subordinateIds);
        }
        
        return $query;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $status = $this->leaves->getStatusChoices();
        if (array_key_exists($this->leaves::STATUS_REQUESTED, $status)) {
            unset($status[$this->leaves::STATUS_REQUESTED]);
        }
        
        $listMapper
            ->add('user')
//            ->add('user.localBalance')
//            ->add('user.sickBalance')
            ->add('type')
            ->add('startDate')
            ->add('endDate')
            ->add('duration')
            ->add('status', 'choice', [
                'choices'=> $status,
                'editable'=> true,
            ])
            ->add('createdAt')
            ->add('createdBy')
            ->add('updatedAt')
            ->add('updatedBy')
//            ->add('_action', null, [
//                'actions' => [
//                    'show' => [],
//                ],
//            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $datetimeOptions = $this->getDateTimeFormOptions();
    
        if ($this->getRole() != 'ROLE_EMPLOYEE') {
            $formMapper
                ->add('user', EntityType::class, $this->getUserFormOptions())
                ->add('type', ChoiceType::class, [
                    'choices'  => $this->leaves->getTypeChoices()
                ])
            ;
        }
        
        $formMapper
            ->add('startDate', 'sonata_type_date_picker', $datetimeOptions)
            ->add('endDate', 'sonata_type_date_picker', $datetimeOptions)
            ->add('duration', NumberType::class, [
                'required' => true,
                'attr' => [
                    'min' => 0.5,
                    'step' => 0.5,
                    'numberType' => true, // overridden template to change field type from text to number
                ],
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('type')
            ->add('startDate')
            ->add('endDate')
            ->add('duration')
            ->add('status')
            ->add('createdAt')
            ->add('createdBy')
            ->add('updatedAt')
            ->add('updatedBy')
        ;
    }
    
    public function getExportFields()
    {
        return [
            'AB Number' => 'user.abNumber',
            'First name' => 'user.firstname',
            'Last name' => 'user.lastname',
            'Job title' => 'user.jobTitle',
            'Business unit' => 'user.businessUnit',
            'Department' => 'user.department',
            'Project' => 'user.project',
            'Type of Leave' => 'type',
            'Start date' => 'startDate',
            'End date' => 'endDate',
            'No. of days' => 'duration',
            'Hours' => 'hours',
            'Status' => 'status',
            'Created at' => 'createdAt',
            'Created by' => 'createdBy',
            'Updated at' => 'updatedAt',
            'Updated by' => 'updatedBy',
            'Current local leave balance' => 'user.localBalance',
            'Current sick leave balance' => 'user.sickBalance',
        ];
    }
    
    /**
     * @return array
     */
    protected function getUserFormOptions() {
        if (in_array($this->getRole(), ['ROLE_HR', 'ROLE_ADMIN']) || $this->isGranted('ROLE_HR')) {
            $queryBuilder = $this->getContainer()->get('doctrine')
                ->getRepository('ApplicationSonataUserBundle:User')
                ->getUsersQueryBuilder();
        }
        else {
            $userId = $this->getUser()->getId();
            $queryBuilder = $this->getContainer()->get('doctrine')
                ->getRepository('ApplicationSonataUserBundle:User')
                ->getFilteredUsersQueryBuilder($userId);
        }
        
        $userOptions = [
            'class' => User::class,
            'query_builder' => $queryBuilder,
            'choice_label' => 'fullname',
            'required' => FALSE
        ];
        return $userOptions;
    }
    
    /**
     * @return array
     */
    protected function getDateTimeFormOptions() {
        $datepickerOptions = new DatepickerOptions($this->getContainer()->get('doctrine'));
        $disabledDatesFormatted = $datepickerOptions->getDisabledDatesFormatted();
        
        $datetimeOptions = [
            'dp_disabled_dates' => $disabledDatesFormatted,
            'dp_use_current' => TRUE,
            //                'required' => false
        ];
        return $datetimeOptions;
    }
}
