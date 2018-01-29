<?php

namespace LeavesOvertimeBundle\DataFixtures;

use Application\Sonata\UserBundle\Entity\User;
use LeavesOvertimeBundle\Entity\BusinessUnit;
use LeavesOvertimeBundle\Entity\Department;
use LeavesOvertimeBundle\Entity\EmailTemplate;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use LeavesOvertimeBundle\Entity\Employee;
use LeavesOvertimeBundle\Entity\JobTitle;
use LeavesOvertimeBundle\Entity\Project;
use LeavesOvertimeBundle\Entity\PublicHoliday;

class GeneralDataFixture extends Fixture
{
    public function load(ObjectManager $manager)
    {
        $this->loadAdmin($manager);
        $this->loadEmailTemplate($manager);
        $this->loadDepartment($manager);
        $this->loadJobTitle($manager);
        $this->loadBusinessUnit($manager);
        $this->loadProject($manager);
//        $this->loadUser($manager);
        $this->loadPublicHoliday($manager);
        
        $manager->flush();
    }
    
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function loadAdmin(ObjectManager &$manager) {
        $admin = new User();
        $admin->setUsername('admin')
            ->setPlainPassword('admin')
            ->setEmail('admin@admin.com')
            ->setRoles(['ROLE_SUPER_ADMIN'])
            ->setEnabled(true)
        ;
        
        $manager->persist($admin);
    }
    
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function loadEmailTemplate(ObjectManager &$manager) {
        $templateNames = [
            'Requested',
            'Approved',
            'Rejected',
            'Cancelled',
        ];
        
        foreach ($templateNames as $templateName) {
            $emailTemplate = new EmailTemplate();
            $emailTemplate->setName($templateName)
                ->setContent('Lorem ipsum dolor sit amet, consectetur adipiscing elit. Duis sagittis porta nisl, eget convallis sapien accumsan eu. Vivamus vitae tempor magna. Duis laoreet ut lectus nec finibus. Etiam ac condimentum ante, posuere sagittis tellus. Donec cursus leo a aliquet facilisis. Interdum et malesuada fames ac ante ipsum primis in faucibus. Quisque luctus ex dignissim est viverra, vel interdum est pellentesque. Aenean scelerisque mattis nisi id congue. In hac habitasse platea dictumst. Curabitur vel cursus nisl. In hac habitasse platea dictumst.')
            ;
            
            $manager->persist($emailTemplate);
        }
    }
    
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function loadDepartment(ObjectManager &$manager) {
        $items = [
            "Administration and General Services",
            "ATI - Management",
            "ATI - Client",
            "ATI - Complaints",
            "ATI Direct Sales",
            "AXA Banque - Management",
            "AXA Banque",
            "AXA PJ",
            "AXA Schengen",
            "Commerciale et Marketing",
            "Data Team",
            "Direction Générale",
            "Finance",
            "Human Resource",
            "Internal Control & Compliance",
            "Internal Control & Compliance",
            "IT",
            "Juridica - Management",
            "Juridica",
            "Planning & Stats",
            "Project Management Office",
            "Rogers Capital ",
            "Training & Quality",
            "Webcorp",
        ];
        
        foreach ($items as $item) {
            $entity = new Department();
            $entity->setName($item);
            $manager->persist($entity);
        }
    }
    
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function loadJobTitle(ObjectManager &$manager) {
        $items = [
            "Accountant",
            "Administration Manager",
            "Administrative Assistant",
            "Assistant Planification et Statistiques",
            "Assistant Team Leader",
            "Auditeur Qualité",
            "Back Office Authoriser",
            "Back Office Coordinator",
            "Business Development Manager",
            "Chargé de Clientèle",
            "Chargé de Formation",
            "Chargée de Clientèle",
            "Chargée de Formation, Relation Client Site",
            "Chargée de Projet Opérationnel",
            "Chief Finance Officer",
            "Chief Operating Officer",
            "Coach Qualité",
            "Communications Manager",
            "Complaints Officer",
            "Compliance Officer",
            "Coordinateur Qualité",
            "Customer Experience Officer",
            "Customer Service Agent",
            "Finance Manager",
            "General Manager",
            "Head of IT and Telephony/CISO/DPO",
            "Health & Safety Officer",
            "HR/Payroll Officer",
            "Hub Support Manager",
            "Human Resources Coordinator",
            "Human Resources Officer",
            "Hyperviseur",
            "Internal Control & Compliance Manager",
            "IT Manager",
            "Medical Director",
            "Medical Network Manager and Projects",
            "Network and Infrastructure Manager",
            "Office Attendant",
            "Operations Manager",
            "Operations Unit Leader",
            "Planning & Reporting Officer",
            "Project Manager",
            "Quality Assurance Assistant Manager",
            "Quality Assurance Coordinator",
            "Recruitment Coordinator",
            "Recruitment Officer",
            "Reporting Analyst",
            "Responsable d'Unité Managériale",
            "Responsable de Formation",
            "Responsable Formation Relation Client Site",
            "Responsable Planification et Statistiques",
            "Responsable Qualité et Formation Site",
            "Responsable Qualité SITE",
            "Senior Operations Manager",
            "Senior System Administrator",
            "Senior Utilisation Review and Flight Nurse",
            "Support Engineer",
            "System Administrator",
            "Team Leader",
            "Training Coordinator",
            "Training Manager",
            "Translator",
        ];
        
        foreach ($items as $item) {
            $entity = new JobTitle();
            $entity->setName($item);
            $manager->persist($entity);
        }
    }
    
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function loadBusinessUnit(ObjectManager &$manager) {
        $items = [
            "088AD",
            "088AT",
            "088DG",
            "088FI",
            "088HR",
            "088IT",
            "088JU",
            "088PM",
            "088PR",
            "088RP",
            "088SM",
            "088TR",
            "088WB",
            "095AB",
            "095AS",
            "095PJ",
            "095RP",
            "095TR",
        ];
        
        foreach ($items as $item) {
            $entity = new BusinessUnit();
            $entity->setName($item);
            $manager->persist($entity);
        }
    }
    
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function loadProject(ObjectManager &$manager) {
        $items = [
            "ATI - CIPT",
            "ATI - SAAT & VPO",
            "ATI Direct Sales",
            "ATI - Assistance & Claims",
            "ATI - Claims FR",
            "ATI - Claims UK",
            "ATI - Recovery",
            "Medical & Assistance",
        ];
        
        foreach ($items as $item) {
            $entity = new Project();
            $entity->setName($item);
            $manager->persist($entity);
        }
    }
    
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function loadUser(ObjectManager &$manager) {
        for ($x = 1; $x < 34; $x++) {
            $user = new User();
            $user->setFirstname('Firstname' . $x)
                ->setLastname('Lastname ' . $x)
                ->setEmail(sprintf('email%s@email.com', $x))
                ->setHireDate(new \DateTime())
                ->setUsername('user' . $x)
                ->setPlainPassword($user->getUsername())
                ->setRoles(['ROLE_USER'])
                ->setEnabled(true)
            ;
            
            $manager->persist($user);
        }
    }
    
    /**
     * @param \Doctrine\Common\Persistence\ObjectManager $manager
     */
    public function loadPublicHoliday(ObjectManager &$manager) {
        $names = [
            "New Year",
            "New Year",
            "Thaipoosam Cavadee",
            "Abolition Of Slavery",
            "Maha Shivaratree",
            "Chinese Spring Festival",
            "Independence And Republic Day",
            "Ugaadi",
            "Labour Day",
            "Eid-ul-fitr",
            "Assumption Of The Blessed Virgin Mary",
            "Ganesh Chaturthi",
            "Arrival Of Indentured Labourers",
            "Divali",
            "Christmas",
        ];
        
        $dates = [
            "01 January 2018",
            "02 January 2018",
            "31 January 2018",
            "01 February 2018",
            "13 February 2018",
            "16 February 2018",
            "12 March 2018",
            "18 March 2018",
            "01 May 2018",
            "16 June 2018",
            "15 August 2018",
            "14 September 2018",
            "02 November 2018",
            "07 November 2018",
            "25 December 2018",
        ];

        for ($x = 0; $x < 15; $x++) {
            $publicHoliday = new PublicHoliday();
            $publicHoliday->setName($names[$x])
                ->setDate(new \DateTime($dates[$x]))
            ;
            $manager->persist($publicHoliday);
        }
    }
}