<?php

namespace LeavesOvertimeBundle\Ldap;

use Doctrine\ORM\EntityManager;
use FR3D\LdapBundle\Hydrator\HydratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Application\Sonata\UserBundle\Entity\User;

class UserHydrator implements HydratorInterface
{
    protected $entityManager;
    
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }
    
    /**
     * Populate a user with the data retrieved from LDAP.
     *
     * @param array $ldapEntry LDAP result information as a multi-dimensional array.
     *              see {@link http://www.php.net/function.ldap-get-entries} for array format examples.
     *
     * @return UserInterface
     */
    public function hydrate(array $ldapEntry)
    {
        if (!$ldapEntry) {
            return null;
        }
        
        if (!array_key_exists('samaccountname', $ldapEntry)) {
            return null;
        }
        
        $username = $ldapEntry['samaccountname'][0];
        $userFromDB = $this->entityManager->getRepository('ApplicationSonataUserBundle:User')->findOneBy(['username' => $username]);
        if ($userFromDB == null) {
            $user = new User();
            $user->setPassword('');
        }
        else {
            $user = $userFromDB;
        }
        
        // These are basically if statements without else in a short form, but do not call set methods with null
        array_key_exists('mail', $ldapEntry) ? $user->setEmail($ldapEntry['mail'][0]) : null;
        array_key_exists('givenname', $ldapEntry) ? $user->setFirstName($ldapEntry['givenname'][0]) : null;
        array_key_exists('sn', $ldapEntry) ? $user->setLastName($ldapEntry['sn'][0]) : null;
        array_key_exists('distinguishedname', $ldapEntry) ? $user->setDn($ldapEntry['distinguishedname'][0]) : null;
        /**
         * 512 = Enabled
         * 514 = Disabled
         * 66048 = Enabled, password never expires
         * 66050 = Disabled, password never expires
         */
        $user->setEnabled(array_key_exists('useraccountcontrol', $ldapEntry) ?
            $ldapEntry['useraccountcontrol'][0] == 512 || 66048 ? true : false : true);
        $user->setUsername($username);
        
        return $user;
    }
}