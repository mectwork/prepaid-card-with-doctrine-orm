<?php

namespace Cubalider\Component\PrepaidCard\Manager;

use Doctrine\ORM\EntityManagerInterface;

/**
 * @author Manuel Emilio Carpio <mectwork@gmail.com>
 * @author Yosmanyga Garcia <yosmanyga@gmail.com>
 */
class CategoryManager implements CategoryManagerInterface
{
    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repository;

    /**
     * Constructor.
     * Additionally it creates a repository using $em, for given class
     *
     * @param EntityManagerInterface $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->repository = $this->em->getRepository('Cubalider\Component\PrepaidCard\Model\Category');
    }

    /**
     * @inheritdoc
     */
    public function collect()
    {
        return $this->repository->findAll();
    }
}
