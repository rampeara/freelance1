<?php

namespace LeavesOvertimeBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;

class CommonAdmin extends AbstractAdmin
{
    public $container;
    /* @var $entityManager \Doctrine\ORM\EntityManager */
    public $entityManager;
    
    public $disabledDates;
    public $disabledDatesFormatted;
    
    public function __construct($code, $class, $baseControllerName, $entityManager = null) {
        parent::__construct($code, $class, $baseControllerName);
        $this->entityManager = $entityManager;
    }

    protected function getDisabledDates() {
        return $this->disabledDates = $this->entityManager->getRepository('LeavesOvertimeBundle:PublicHoliday')->createQueryBuilder('ph')->select('ph.date')
            ->getQuery()->getArrayResult();
    }
    
    /**
     * Transforms date objects to array of string dates with specific format only
     * Used to specify which dates to disabled in Sonata datepicker using Public Holidays table
     * @return array|null
     */
    public function getDisabledDatesFormatted() {
        if ($this->disabledDatesFormatted) {
            return $this->disabledDatesFormatted;
        }
        
        $disabledDatesFormatted = [];
        $disabledDates = $this->getDisabledDates();
        if ($disabledDates) {
            foreach ($disabledDates as $disabledDate) {
                if ($disabledDate) {
                    $obj = $disabledDate['date'];
                    $disabledDatesFormatted[] = $obj->format('M/d/y');
                }
            }
        }
        return $this->disabledDatesFormatted = $disabledDatesFormatted;
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
    
    /**
     * @return null|\Symfony\Component\DependencyInjection\ContainerInterface
     */
    public function getContainer() {
        return $this->container = $this->getConfigurationPool()->getContainer();
    }
}
