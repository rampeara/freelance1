<?php

namespace LeavesOvertimeBundle\Common;

class DatepickerOptions {
    
    /* @var $entityManager \Doctrine\ORM\EntityManager */
    protected $entityManager;
    
    protected $disabledDates;
    protected $disabledDatesFormatted;
    
    public function __construct($entityManager) {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Get public hodlidays from its table
     * @return array
     */
    protected function getDisabledDates() {
        return $this->disabledDates = $this->entityManager->getRepository('LeavesOvertimeBundle:PublicHoliday')->createQueryBuilder('ph')
            ->select('ph.date')
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
    
}