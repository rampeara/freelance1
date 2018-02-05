<?php

namespace LeavesOvertimeBundle\Ldap;

use FR3D\LdapBundle\Hydrator\HydratorInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Application\Sonata\UserBundle\Entity\User;

class UserHydrator implements HydratorInterface
{
    /**
     * Populate an user with the data retrieved from LDAP.
     *
     * @param array $ldapEntry LDAP result information as a multi-dimensional array.
     *              see {@link http://www.php.net/function.ldap-get-entries} for array format examples.
     *
     * @return UserInterface
     */
    public function hydrate(array $ldapEntry)
    {
        $user = new User();
        if ($ldapEntry) {
            if (array_key_exists('samaccountname', $ldapEntry)) {
                $user->setUsername($ldapEntry['samaccountname'][0]);
                $user->setEnabled(true);
                $user->setPassword('');
            }
            array_key_exists('mail', $ldapEntry) ? $user->setEmail($ldapEntry['mail'][0]) : null;
            array_key_exists('givenname', $ldapEntry) ? $user->setFirstName($ldapEntry['givenname'][0]) : null;
            array_key_exists('sn', $ldapEntry) ? $user->setLastName($ldapEntry['sn'][0]) : null;
            array_key_exists('distinguishedname', $ldapEntry) ? $user->setDn($ldapEntry['distinguishedname'][0]) : null;
        }
        
        return $user;
    }
}