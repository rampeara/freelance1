<?php

namespace LeavesOvertimeBundle\Admin;

use LeavesOvertimeBundle\Entity\Leaves;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class BalanceLogAdmin extends CommonAdmin
{
    public function configureBatchActions($actions)
    {
        // remove delete to avoid deleting entries with foreign keys without
        // giving a warning like customised in its Controller
        unset($actions['delete']);
        return $actions;
    }
    
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
//            ->add('leaves')
            ->add('previousBalance')
            ->add('newBalance')
            ->add('createdAt', 'doctrine_orm_datetime')
            ->add('createdBy')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('leaves', null, ['route' => ['name' => 'show']])
            ->add('previousBalance')
            ->add('newBalance')
            ->add('createdAt')
            ->add('createdBy')
        ;
    }
    
    public function getExportFields()
    {
        return [
            'ID' => 'id',
            'Leave details' => 'leaves',
            'Previous balance' => 'previousBalance',
            'New balance' => 'newBalance',
            'Created at' => 'createdAt',
            'Created by' => 'createdBy',
        ];
    }
}
