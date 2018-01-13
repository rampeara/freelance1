<?php

namespace LeavesOvertimeBundle\EventListener;

use Application\Sonata\UserBundle\Entity\User;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use LeavesOvertimeBundle\Entity\UserImport;

class UserImportListener
{
    public function prePersist(UserImport $entity, LifecycleEventArgs  $event) {
        $entityManager = $event->getEntityManager();
        $this->upload($entity);
        $this->processUserData($entity, $entityManager);
    }
    
    /**
     * Manages the copying of the file to the relevant place on the server
     *
     * @param $entity UserImport
     */
    public function upload(&$entity)
    {
        // the file property can be empty if the field is not required
        if (null === $entity->getFile()) {
            return;
        }
        
        // we use the original file name here but you should
        // sanitize it at least to avoid any security issues
        
        // move takes the target directory and target filename as params
        $entity->getFile()->move(
            $entity->uploadAbsolutePath,
            $entity->getFile()->getClientOriginalName()
        );
        
        // set the path property to the filename where you've saved the file
        $entity->setFileName($entity->getFile()->getClientOriginalName());
        
        // clean up the file property as you won't need it anymore
        $entity->setFile(null);
    }
    
    /**
     * @param $entity
     * @param $entityManager \Doctrine\ORM\EntityManager
     *
     * @throws \Doctrine\ORM\ORMException
     */
    public function processUserData($entity, $entityManager) {
//        $filePath = sprintf('%s/uploads/user/%s', $_SERVER['DOCUMENT_ROOT'], $entity->getFileName());
        $filePath = sprintf('%s\%s', $entity->uploadAbsolutePath, $entity->getFileName());
        if (file_exists($filePath)) {
            $csv = array_map('str_getcsv', file($filePath));
            // create associative array
            array_walk($csv, function(&$a) use ($csv) {
                $a = array_combine($csv[0], $a);
            });
            array_shift($csv); // remove column header
            
            foreach ($csv as $data) {
                $jobTitle = !empty($data['Job title']) ? $entityManager->getRepository('LeavesOvertimeBundle:JobTitle')->findOneBy(['name' => $data['Job title']]) : null;
                $businessUnit = !empty($data['Business unit']) ? $entityManager->getRepository('LeavesOvertimeBundle:BusinessUnit')->findOneBy(['name' => $data['Business unit']]) : null;
                $department = !empty($data['Department']) ? $entityManager->getRepository('LeavesOvertimeBundle:Department')->findOneBy(['name' => $data['Department']]) : null;
                $project = !empty($data['Project']) ? $entityManager->getRepository('LeavesOvertimeBundle:Project')->findOneBy(['name' => $data['Project']]) : null;
    
                $supervisorsLevel1 = $this->getSupervisors($entityManager, $data['Supervisors level 1']);
                $supervisorsLevel2 = $this->getSupervisors($entityManager, $data['Supervisors level 2']);
//                $date = new \DateTime(strtotime($this->cleanData($data['Hire date'])));
                $date = new \DateTime();
                $date = $date->createFromFormat('d/m/Y', $this->cleanData($data['Hire date']));
    
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
                    ->setDn($this->cleanData($data['DN']))
                    ->setEnabled(true)
                    ->setUsername($this->cleanData($data['Username']))
//                    ->setUsername('temporary_' . uniqid())
                    ->setPlainPassword('temporary_' . uniqid())
                ;
                $entityManager->persist($user);
            }
            
            $entityManager->flush();
        }
    
    }
    
    /**
     * @param \Doctrine\ORM\EntityManager $entityManager
     * @param string $data
     *
     * @return ArrayCollection|null
     */
    public function getSupervisors($entityManager, $data) {
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
    public function cleanData($data) {
        $data = trim($data);
        return !empty($data) ? $data : null;
    }
    
}