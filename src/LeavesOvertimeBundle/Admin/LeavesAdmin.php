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
        // ids of my subordinates and logged in user
        $userId = $this->getUser()->getId();
        $subordinateIds = $this->getContainer()->get('doctrine')
            ->getRepository('ApplicationSonataUserBundle:User')
            ->getMySubordinatesIds($userId);
        
        // filter my ids supplied
        $rootAlias = $query->getRootAliases()[0];
        $query->innerJoin($rootAlias . '.user','u')
            ->andWhere("u.id IN(:subordinatesIds)")
            ->orderBy('u.id', 'ASC')
            ->addOrderBy('u.lastname', 'ASC')
            ->addOrderBy($rootAlias . '.createdAt', 'DESC')
            ->setParameter('subordinatesIds', $subordinateIds);
        
        return $query;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('user')
            ->add('type')
            ->add('startDate')
            ->add('endDate')
            ->add('duration')
            ->add('status', 'choice', [
                'choices'=> $this->leaves->getStatusChoices(),
                'editable'=>true,
            ])
            ->add('createdAt')
            ->add('createdBy')
            ->add('updatedAt')
            ->add('updatedBy')
            ->add('_action', null, [
                'actions' => [
                    'show' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $datetimeOptions = $this->getDateTimeFormOptions();
        $formMapper
            ->add('user', EntityType::class, $this->getUserFormOptions())
            ->add('type', ChoiceType::class, [
                'choices'  => $this->leaves->getTypeChoices()
            ])
            ->add('startDate', 'sonata_type_date_picker', $datetimeOptions)
            ->add('endDate', 'sonata_type_date_picker', $datetimeOptions)
            ->add('duration', NumberType::class, [
                'required' => true,
                'attr' => [
                    'min' => 0.5,
                    'max' => 90,
                    'step' => 0.5,
                    'numberType' => true, // overridden template to change field type from text to number
                ],
            ])
            ->add('status', ChoiceType::class, [
                'choices'  => $this->leaves->getStatusChoices()
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
            'Local leave balance' => 'user.localBalance',
            'Sick leave balance' => 'user.sickBalance',
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
    protected function getUserFormOptions($user = null) {
        $queryBuilder = $this->getContainer()->get('doctrine')
            ->getRepository('ApplicationSonataUserBundle:User')
            ->getUsersQueryBuilder($user);
        
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
