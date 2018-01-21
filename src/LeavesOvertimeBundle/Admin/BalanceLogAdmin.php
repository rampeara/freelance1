<?php

namespace LeavesOvertimeBundle\Admin;

use LeavesOvertimeBundle\Entity\Leaves;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;

class BalanceLogAdmin extends AbstractAdmin
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
    
    /**
     * @return null|\Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer() {
        return $this->getConfigurationPool()->getContainer();
    }
    
    public function getDataSourceIterator()
    {
        $iterator = parent::getDataSourceIterator();
        $exportDateFormat = $this->getContainer()->getParameter('datetime_format_export');
        $iterator->setDateTimeFormat($exportDateFormat);
        return $iterator;
    }
    
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('user')
            ->add('type','doctrine_orm_string', [], 'choice', [
                'choices' => $this->leaves->getTypeChoices()
            ])
            ->add('previousBalance')
            ->add('newBalance')
            ->add('action')
            ->add('createdAt', 'doctrine_orm_datetime')
            ->add('createdBy')
        ;
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('id')
            ->add('user')
            ->add('type')
            ->add('previousBalance')
            ->add('newBalance')
            ->add('action')
            ->add('createdAt')
            ->add('createdBy')
        ;
    }
    
    public function getExportFields()
    {
        return [
            'ID' => 'id',
            'User' => 'user.fullname',
            'Type' => 'Type',
            'Previous balance' => 'previousBalance',
            'New balance' => 'newBalance',
            'Action' => 'action',
            'Created at' => 'createdAt',
            'Created by' => 'createdBy',
        ];
    }
}
