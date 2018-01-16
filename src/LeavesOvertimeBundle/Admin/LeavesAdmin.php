<?php

namespace LeavesOvertimeBundle\Admin;

use LeavesOvertimeBundle\Common\DatepickerOptions;
use LeavesOvertimeBundle\Entity\Leaves;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
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
    
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('type','doctrine_orm_string', [], 'choice', [
                'choices' => $this->getTypeChoices()
            ])
            ->add('status', 'doctrine_orm_string', [], 'choice', [
                'choices' => $this->getStatusChoices()
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

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('user')
            ->add('type')
            ->add('startDate')
            ->add('endDate')
            ->add('duration')
            ->add('status', 'choice', [
                'choices'=> $this->getStatusChoices(),
                'editable'=>true,
            ])
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
        $datetimeOptions = $this->getDateTimeFormOptions();
        $formMapper
            ->add('user', EntityType::class, $this->getUserFormOptions())
            ->add('type', ChoiceType::class, [
                'choices'  => $this->getTypeChoices()
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
                'choices'  => $this->getStatusChoices()
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
            'Type of Leave' => 'type',
            'Start date' => 'startDate',
            'End date' => 'endDate',
            'Duration' => 'duration',
            'Status' => 'status',
            'Created at' => 'createdAt',
            'Created by' => 'createdBy',
            'Updated at' => 'updatedAt',
            'Updated by' => 'updatedBy',
        ];
    }
    
    /**
     * @return array
     */
    public function getStatusChoices() {
        return [
            'Requested' => $this->leaves::STATUS_REQUESTED,
            'Withdrawn' => $this->leaves::STATUS_WITHDRAWN,
            'Approved' => $this->leaves::STATUS_APPROVED,
            'Rejected' => $this->leaves::STATUS_REJECTED,
            'Cancelled' => $this->leaves::STATUS_CANCELLED,
        ];
    }
    
    /**
     * @return array
     */
    public function getTypeChoices() {
        return [
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
