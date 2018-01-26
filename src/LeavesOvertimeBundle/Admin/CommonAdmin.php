<?php

namespace LeavesOvertimeBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;

class CommonAdmin extends AbstractAdmin
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
    
    public function checkRoles()
    {
        return $this->getUser() && is_array($this->getUser()->getRoles());
    }
    
    public function getRole()
    {
        if ($this->checkRoles()) {
            return $this->getUser()->getRoles()[0];
        }
        
        return null;
    }
}
