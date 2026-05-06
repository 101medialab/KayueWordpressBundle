<?php

namespace Kayue\WordpressBundle\Security\User;

use Kayue\WordpressBundle\Wordpress\ManagerRegistry;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UserNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\UserProviderInterface;

class WordpressUserProvider implements UserProviderInterface
{
    /**
     * @var ManagerRegistry
     */
    protected $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;
    }

    public function loadUserByIdentifier(string $identifier): UserInterface
    {
        if (null === $user = $this->managerRegistry->getManager()->getRepository('KayueWordpressBundle:User')
            ->findOneBy(['username' => $identifier])) {
            throw new UserNotFoundException();
        }

        return $user;
    }

    public function loadUserByUsername(string $username): UserInterface
    {
        return $this->loadUserByIdentifier($username);
    }

    public function refreshUser(UserInterface $user): UserInterface
    {
        return $this->managerRegistry
            ->getManager()
            ->getRepository('KayueWordpressBundle:User')
            ->findOneBy(['username' => $user->getUserIdentifier()])
        ;
    }

    /**
     * Whether this provider supports the given user class
     *
     * @param string $class
     *
     * @return bool
     */
    public function supportsClass($class)
    {
        return $class === 'Kayue\WordpressBundle\Entity\User';
    }
}
