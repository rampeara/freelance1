<?php

namespace LeavesOvertimeBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;

class CommonAdmin extends AbstractAdmin
{
    public $container;
   
    /**
     * @return null|\Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer() {
        return $this->container = $this->getConfigurationPool()->getContainer();
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
}
