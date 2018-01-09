<?php

namespace LeavesOvertimeBundle\Admin;

use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Show\ShowMapper;

class UserImportAdmin extends CommonAdmin
{
    public function getExportFields()
    {
        return [
            'File name' => 'fileName',
            'Created at' => 'createdAt',
            'Created by' => 'createdBy',
        ];
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
    }
    
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('fileName')
            ->add('createdAt', 'doctrine_orm_datetime')
            ->add('createdBy')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('fileName')
            ->add('createdAt')
            ->add('createdBy')
            ->add('_action', null, [
                'actions' => [
                    'show' => [],
//                    'edit' => [],
//                    'delete' => [],
                ],
            ])
        ;
    }

    protected function configureFormFields(FormMapper $formMapper)
    {
        $formMapper
            ->add('file', 'file', [
                'required' => false
            ])
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('fileName')
            ->add('createdAt')
            ->add('createdBy')
        ;
    }
}
