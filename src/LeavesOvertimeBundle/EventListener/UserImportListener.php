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
    private $router;
    
    public function __construct($container){
        $this->container = $container;
        $this->router = $this->container->get('router');
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
       
        $newFileName = sprintf('user_import_%s.%s', date('d-m-Y'), $entity->getFile()->getClientOriginalExtension());
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
            $csv = $this->getVerifiedCSV($filePath, $userImport);
            if ($csv == null) {
                return;
            }
            
            foreach ($csv as $key => $data) {
                $retryMessage = 'The import has been stopped at this error. Please verify where it stopped in User List page and in the CSV, remove successfully imported rows from the CSV and try again.'; //$this->router->generate('admin_sonata_user_user_list', [], 0)
                $inputDate = $this->cleanData($data['Hire date']);
                list($date, $format) = $this->getDate($inputDate);
                if (!$this->isValidData($date, $format, $data, $entityManager, $userImport, $retryMessage)) {
                    $entityManager->clear();
                    return;
                }
                
                // find corresponding objects
                $jobTitle = $this->findNameFromRepository('JobTitle', $data['Job title'], $entityManager);
                $businessUnit = $this->findNameFromRepository('BusinessUnit', $data['Business unit'], $entityManager);
                $department = $this->findNameFromRepository('Department', $data['Department'], $entityManager);
                $project = $this->findNameFromRepository('Project', $data['Project'], $entityManager);
                $supervisorsLevel1 = $this->getSupervisors($entityManager, $data['Supervisors level 1']);
                $supervisorsLevel2 = $this->getSupervisors($entityManager, $data['Supervisors level 2']);
                $email = $this->cleanData($data['Email']);
    
                $user = new User();
                $user->setAbNumber($this->cleanData($data['AB number']))
                    ->setEmail($email)
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
                    ->setUsername($this->generateUsername($email)) //$this->cleanData($data['Username'])
                    ->setPassword('')
//                    ->setDn(sprintf('%s=%s,%s', $this->container->getParameter('dn_username_attribute'), $user->getUsername(), $this->container->getParameter('base_dn')))
//                    ->setDn($data['DN'])
                    ->setLocalBalance($this->cleanNumber($data['Local balance']))
                    ->setSickBalance($this->cleanNumber($data['Sick balance']))
                    ->setCarryForwardLocalBalance($this->cleanNumber($data['Carry forward local balance']))
                    ->setFrozenCarryForwardLocalBalance($this->cleanNumber($data['Frozen carry forward local balance']))
                    ->setRoles(['ROLE_' . strtoupper($data['Role'])])
                ;
                $entityManager->persist($user);
                $entityManager->flush();
            }
            $userImport->setIsSuccess(true);
        }
        else {
            $this->setError($userImport, sprintf('The import file could not be found on path %s', $filePath));
        }
    }
    
    /**
     * Returns a valid numeric representation
     * @param $value
     *
     * @return float|int|string
     */
    private function cleanNumber($value) {
        return is_numeric($value) ? $value : 0;
    }
    
    /**
     * Generates username from email string before @
     * @param $email
     *
     * @return string
     */
    private function generateUsername($email) {
        if (empty($email)) {
            return 'invalid.username';
        }
        
        $email = strtolower($email);
        list($username) = explode('@', $email);
        list($firstName, $lastName) = explode('.', $username);
        return sprintf('%s.%s', $firstName[0], $lastName);
    }
    
    /**
     * @param $dataKeys
     * @param $userImport
     *
     * @return bool
     */
    private function hasMissingHeaders($dataKeys, &$userImport) {
        $requiredHeaders = [
            "AB number",
            "Email",
            "Title",
            "Gender",
            "First name",
            "Last name",
            "Job title",
            "Business unit",
            "Department",
            "Project",
            "Supervisors level 1",
            "Supervisors level 2",
            "Hire date",
            "Employment status",
            "Local balance",
            "Sick balance",
            "Carry forward local balance",
            "Frozen carry forward local balance",
            "Role"
        ];
        $missingHeaders = [];
        
        foreach ($requiredHeaders as $requiredHeader) {
            if (!in_array($requiredHeader, $dataKeys)) {
                $missingHeaders[] = $requiredHeader;
            }
        }
        
        if ($missingHeaders != []) {
            $this->setError($userImport, sprintf('The following header names are missing, please verify that the names are exactly as shown: %s. The import has been cancelled, please correct the headers and try again.', implode(', ', $missingHeaders)));
            return true;
        }
        
        return false;
    }
    
    /**
     * @param $filePath
     * @param $userImport
     *
     * @return array|null
     */
    private function getVerifiedCSV($filePath, &$userImport) {
        try {
            $csv = $this->getCsvArray($filePath);
        }
        catch (\Exception $e) {
            $this->setError($userImport, sprintf('The CSV is invalid! Please verify that the number of headers match the number of data under them.'));
            return null;
        }
    
        if (empty($csv)) {
            $this->setError($userImport, sprintf('The CSV is empty!'));
            return null;
        }
    
        $dataKeys = array_keys($csv[0]);
        if ($this->hasMissingHeaders($dataKeys, $userImport)) {
            return null;
        }
        
        return $csv;
    }
    
    /**
     * @param $date
     * @param $format
     * @param $data
     * @param $entityManager
     * @param $userImport
     * @param $retryMessage
     *
     * @return bool
     */
    private function isValidData($date, $format, $data, &$entityManager, &$userImport, $retryMessage) {
        if (!($date && $date->format($format) == $data['Hire date'])) {
            $this->setError($userImport, sprintf('The date %s is an invalid date format. Please verify that all hire dates use "dd-mm-yy" format. %s', $data['Hire date'], $retryMessage));
            return false;
        }
        $emailExists = $entityManager->getRepository('ApplicationSonataUserBundle:User')->findOneBy(['email' => $data['Email']]);
        if ($emailExists) {
            $this->setError($userImport, sprintf('The email %s already exists in the system. %s', $data['Email'], $retryMessage));
            return false;
        }
    
//        $usernameExists = $entityManager->getRepository('ApplicationSonataUserBundle:User')->findOneBy(['username' => $data['Username']]);
//        if ($usernameExists) {
//            $this->setError($userImport, sprintf('The username %s already exists in the system. %s', $data['Username'], $retryMessage));
//            return false;
//        }
        
        return true;
    }
    
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $entityManager
     * @param string $data
     *
     * @return ArrayCollection
     */
    private function getSupervisors($entityManager, $data) {
        $supervisorsArrayCollection = new ArrayCollection();
        if (!empty($data)) {
            $supervisorsIds = explode(',', $data);
            foreach ($supervisorsIds as $supervisorId) {
                $supervisorObj = $entityManager->getRepository('ApplicationSonataUserBundle:User')
                    ->find($supervisorId);
                if ($supervisorObj) {
                    $supervisorsArrayCollection->add($supervisorObj);
                }
            }
        }
        return $supervisorsArrayCollection;
    }
    
    /**
     * @param $repositoryName
     * @param $nameToFind
     * @param $entityManager
     *
     * @return null|object
     */
    private function findNameFromRepository($repositoryName, $nameToFind, &$entityManager) {
        $nameToFind = $this->cleanData($nameToFind);
        return  $nameToFind != null ? $entityManager->getRepository('LeavesOvertimeBundle:' . $repositoryName)->findOneBy(['name' => $nameToFind]) : null;
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