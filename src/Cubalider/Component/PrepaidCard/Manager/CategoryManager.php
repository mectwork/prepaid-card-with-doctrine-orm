<?php

namespace Cubalider\Component\PrepaidCard\Manager;

use Cubalider\Component\PrepaidCard\Model\Category;
use Doctrine\ORM\EntityManager;

/**
 * @author Manuel Emilio Carpio <mectwork@gmail.com>
 * @author Yosmanyga Garcia <yosmanyga@gmail.com>
 */
class CategoryManager implements CategoryManagerInterface
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    private $em;

    /**
     * @var \Doctrine\ORM\EntityRepository
     */
    private $repository;

    /**
     * Constructor.
     * Additionally it creates a repository using $em, for entity class
     *
     * @param EntityManager $em
     */
    public function __construct(EntityManager $em)
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

    /**
     * @inheritdoc
     */
    public function add(Category $category)
    {
        $this->em->persist($category);
        $this->em->flush();
    }

    /**
     * @inheritdoc
     */
    public function pick($criteria)
    {
        if (is_string($criteria)) {
            $criteria = array('strid' => $criteria);
        }

        return $this->repository->findOneBy($criteria);
    }

    /**
     * @inheritdoc
     */
    public function remove(Category $category)
    {
        $this->em->remove($category);
        $this->em->flush();
    }
}
