<?php

namespace LeavesOvertimeBundle\Admin;

use LeavesOvertimeBundle\Entity\Leaves;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class BalanceLogAdmin extends CommonAdmin
{
    
    protected $datagridValues = [
        '_sort_order' => 'DESC',
        '_sort_by' => 'id',
    ];
 
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
//            ->add('leaves')
            ->add('previousBalance')
            ->add('newBalance')
            ->add('type')
            ->add('createdAt', 'doctrine_orm_datetime')
            ->add('createdBy')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('user')
            ->add('leave', null, ['route' => ['name' => 'show']])
            ->add('previousBalance')
            ->add('newBalance')
            ->add('type')
            ->add('createdAt')
            ->add('createdBy')
//            ->add('_action', null, [
//                'actions' => [
//                    'delete' => [],
//                ],
//            ])
        ;
    }
    
    public function getExportFields()
    {
        return [
            'ID' => 'id',
            'User' => 'user',
            'Leave details' => 'leave',
            'Previous balance' => 'previousBalance',
            'New balance' => 'newBalance',
            'Log Type' => 'type',
            'Created at' => 'createdAt',
            'Created by' => 'createdBy',
        ];
    }
}
