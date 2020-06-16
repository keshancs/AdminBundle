<?php

namespace AdminBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;

class UserRepository extends EntityRepository implements UserLoaderInterface
{
    /**
     * @inheritDoc
     */
    public function loadUserByUsername($username)
    {
        return $this->findOneBy(['username' => $username]);
    }
}