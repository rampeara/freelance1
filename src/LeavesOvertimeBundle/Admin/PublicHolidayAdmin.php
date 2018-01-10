<?php

namespace LeavesOvertimeBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

class PublicHolidayAdmin extends AbstractAdmin
{
    public function getDataSourceIterator()
    {
        $iterator = parent::getDataSourceIterator();
        $exportDateFormat = $this->getConfigurationPool()->getContainer()->getParameter('date_format_export');
        $iterator->setDateTimeFormat($exportDateFormat);
        return $iterator;
    }
    
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('date', 'doctrine_orm_datetime')
            ->add('createdAt', 'doctrine_orm_datetime')
            ->add('createdBy')
            ->add('updatedAt', 'doctrine_orm_datetime')
            ->add('updatedBy')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('name')
            ->add('date')
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
        $formMapper
            ->add('name')
            ->add('date')
        ;
    }

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $showMapper
            ->add('name')
            ->add('date')
            ->add('createdAt')
            ->add('createdBy')
            ->add('updatedAt')
            ->add('updatedBy')
        ;
    }
}
