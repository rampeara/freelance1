<?php

namespace LeavesOvertimeBundle\EventListener;

use Application\Sonata\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use LeavesOvertimeBundle\Entity\UserImport;

class UserImportListener
{
    private $container;
    private $flashBag;
    
    public function __construct($container){
        $this->container = $container;
        $this->flashBag = $this->container->get('session')->getFlashBag();
    }
    
    public function prePersist(LifecycleEventArgs $args) {
        $entity = $args->getObject();
    
        if ($entity instanceof UserImport) {
            $entityManager = $args->getObjectManager();
            try {
                $this->upload($entity);
                $this->processUserData($entity, $entityManager);
            }
            catch (\Exception $e) {
                $this->setError($entity, sprintf('The following error has occurred: %s', $e->getMessage()));
            }
        }
    }
    
    /**
     * Manages the copying of the file to the relevant place on the server
     *
     * @param $entity UserImport
     */
    public function upload($entity)
    {
        // the file property can be empty if the field is not required
        if (null === $entity->getFile()) {
            return;
        }
       
        $newFileName = sprintf('user-import-%s.%s', time(), $entity->getFile()->getClientOriginalExtension());
        // move takes the target directory and target filename as params
        $entity->getFile()->move(
            $entity->uploadAbsolutePath,
            $newFileName
        );
        
        // set the path property to the filename where you've saved the file
        $entity->setFileName($newFileName);
        
        // clean up the file property as you won't need it anymore
        $entity->setFile(null);
    }
    
    /**
     * Validate and persist CSV data to User entity
     * @param $userImport UserImport
     * @param $entityManager \Doctrine\Common\Persistence\ObjectManager
     */
    public function processUserData(&$userImport, &$entityManager) {
        // $filePath = sprintf('%s/uploads/user/%s', $_SERVER['DOCUMENT_ROOT'], $entity->getFileName());
        $filePath = sprintf('%s\%s', $userImport->uploadAbsolutePath, $userImport->getFileName());
        if (file_exists($filePath)) {
            $csv = $this->getCsvArray($filePath);
            foreach ($csv as $data) {
                $inputDate = $this->cleanData($data['Hire date']);
                list($date, $format) = $this->getDate($inputDate);
                // Validations
                if (!$this->isValidData($date, $format, $data, $entityManager, $userImport)) {
                    // rollback
                    $entityManager->clear();
                    return;
                }
                
                // find corresponding objects
                $jobTitle = !empty($data['Job title']) ? $entityManager->getRepository('LeavesOvertimeBundle:JobTitle')->findOneBy(['name' => $data['Job title']]) : null;
                $businessUnit = !empty($data['Business unit']) ? $entityManager->getRepository('LeavesOvertimeBundle:BusinessUnit')->findOneBy(['name' => $data['Business unit']]) : null;
                $department = !empty($data['Department']) ? $entityManager->getRepository('LeavesOvertimeBundle:Department')->findOneBy(['name' => $data['Department']]) : null;
                $project = !empty($data['Project']) ? $entityManager->getRepository('LeavesOvertimeBundle:Project')->findOneBy(['name' => $data['Project']]) : null;
                $supervisorsLevel1 = $this->getSupervisors($entityManager, $data['Supervisors level 1']);
                $supervisorsLevel2 = $this->getSupervisors($entityManager, $data['Supervisors level 2']);
    
                $user = new User();
                $user->setAbNumber($this->cleanData($data['AB number']))
                    ->setEmail($this->cleanData($data['Email']))
                    ->setTitle($this->cleanData($data['Title']))
                    ->setGender($this->cleanData($data['Gender']))
                    ->setFirstname($this->cleanData($data['First name']))
                    ->setLastname($this->cleanData($data['Last name']))
                    ->setJobTitle($jobTitle)
                    ->setBusinessUnit($businessUnit)
                    ->setDepartment($department)
                    ->setProject($project)
                    ->setHireDate($date)
                    ->setEmploymentStatus($this->cleanData($data['Employment status']))
                    ->setSupervisorsLevel1($supervisorsLevel1)
                    ->setSupervisorsLevel2($supervisorsLevel2)
                    ->setEnabled(true)
                    ->setUsername($this->cleanData($data['Username']))
                    ->setPassword('')
//                    ->setDn(sprintf('%s=%s,%s', $this->container->getParameter('dn_username_attribute'), $user->getUsername(), $this->container->getParameter('base_dn')))
                    ->setDn($data['DN'])
                    ->setLocalBalance($data['Local balance'])
                    ->setSickBalance($data['Sick balance'])
                    ->setCarryForwardLocalBalance($data['Carry forward local balance'])
                    ->setFrozenCarryForwardLocalBalance($data['Frozen carry forward local balance'])
                    ->setRoles(['ROLE_' . strtoupper($data['Role'])])
                ;
                $entityManager->persist($user);
            }
            $entityManager->flush();
            $userImport->setIsSuccess(true);
        }
        else {
            $this->setError($userImport, sprintf('The import file could not be found on path %s', $filePath));
        }
    }
    
    /**
     * @param $date
     * @param $format
     * @param $data
     * @param $entityManager
     * @param $userImport
     *
     * @return bool
     */
    private function isValidData($date, $format, $data, &$entityManager, &$userImport) {
        $retryMessage = 'The import has been cancelled, please correct the data and try again.';
        if (!($date && $date->format($format) == $data['Hire date'])) {
            $this->setError($userImport, sprintf('A date format is invalid. Please verify that all date formats use "dd-mm-yy". %s', $retryMessage));
            return false;
        }
        $emailExists = $entityManager->getRepository('ApplicationSonataUserBundle:User')->findOneBy(['email' => $data['Email']]);
        if ($emailExists) {
            $this->setError($userImport, sprintf('The email %s already exists in the system. %s', $data['Email'], $retryMessage));
            return false;
        }
    
        $usernameExists = $entityManager->getRepository('ApplicationSonataUserBundle:User')->findOneBy(['username' => $data['Username']]);
        if ($usernameExists) {
            $this->setError($userImport, sprintf('The username %s already exists in the system. %s', $data['Username'], $retryMessage));
            return false;
        }
        
        return true;
    }
    
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $entityManager
     * @param string $data
     *
     * @return ArrayCollection|null
     */
    private function getSupervisors($entityManager, $data) {
        $supervisorsArrayCollection = null;
        if (!empty($data)) {
            $supervisorsIds = explode(',', $data);
            $supervisorsArrayCollection = new ArrayCollection();
            foreach ($supervisorsIds as $supervisorId) {
                $supervisorObj = $entityManager->getRepository('ApplicationSonataUserBundle:User')
                    ->find($supervisorId);
                $supervisorsArrayCollection->add($supervisorObj);
            }
        }
        return $supervisorsArrayCollection;
    }
    
    /**
     * @param $data
     *
     * @return mixed
     */
    private function cleanData($data) {
        $data = trim($data);
        return !empty($data) ? $data : null;
    }
    
    /**
     * @param $inputDate
     *
     * @return array
     */
    private function getDate($inputDate): array {
        $date = new \DateTime();
        //                $date = $date->createFromFormat('d/m/Y', $this->cleanData($data['Hire date']));
        $format = 'd-m-y';
        $date = $date->createFromFormat($format, $inputDate);
        return [$date, $format];
    }
    
    /**
     * @param UserImport $userImport
     * @param $message
     */
    private function setError(&$userImport, $message): void {
        $userImport->setIsSuccess(false);
        $this->flashBag->add("error", $message);
    }
    
    /**
     * @param $filePath
     *
     * @return array
     */
    private function getCsvArray($filePath): array {
        $csv = array_map('str_getcsv', file($filePath));
        // create associative array
        array_walk($csv, function (&$a) use ($csv) {
            $a = array_combine($csv[0], $a);
        });
        // remove column header
        array_shift($csv);
        return $csv;
    }
    
}