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
        
        $ldapAttributes = [
            'username' => 'samaccountname',
            'email' => 'mail',
            'firstname' => 'givenname',
            'lastname' => 'sn',
            'dn' => 'distinguishedname',
            'enabled' => 'useraccountcontrol'
        ];
        
        $usernameAttr = $ldapAttributes['username'];
        if (!array_key_exists($usernameAttr, $ldapEntry)) {
            return null;
        }
        
        $username = $ldapEntry[$usernameAttr][0];
        $userFromDB = $this->entityManager->getRepository('ApplicationSonataUserBundle:User')->findOneBy(['username' => $username]);
        if ($userFromDB == null) {
            $user = new User();
            $user->setPassword('');
        }
        else {
            $user = $userFromDB;
        }
    
        $emailAttr = $ldapAttributes['email'];
        $firstNameAttr = $ldapAttributes['firstname'];
        $lastNameAttr = $ldapAttributes['lastname'];
        $dnAttr = $ldapAttributes['dn'];
        $enabledAttr = $ldapAttributes['enabled'];
        // These are basically if statements without else in a short form, but do not call set methods with null
        array_key_exists($emailAttr, $ldapEntry) ? $user->setEmail($ldapEntry[$emailAttr][0]) : null;
        array_key_exists($firstNameAttr, $ldapEntry) ? $user->setFirstName($ldapEntry[$firstNameAttr][0]) : null;
        array_key_exists($lastNameAttr, $ldapEntry) ? $user->setLastName($ldapEntry[$lastNameAttr][0]) : null;
        array_key_exists($dnAttr, $ldapEntry) ? $user->setDn($ldapEntry[$dnAttr][0]) : null;
        /**
         * 512 = Enabled
         * 514 = Disabled
         * 66048 = Enabled, password never expires
         * 66050 = Disabled, password never expires
         */
        $user->setEnabled(array_key_exists($enabledAttr, $ldapEntry) ?
            $ldapEntry[$enabledAttr][0] == 512 || 66048 ? true : false : true);
        $user->setUsername($username);
        
        return $user;
    }
}