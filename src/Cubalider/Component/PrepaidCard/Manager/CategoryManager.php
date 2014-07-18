<?php

namespace Cubalider\Component\PrepaidCard\Manager;

use Cubalider\Component\PrepaidCard\Model\Category;
use Doctrine\ORM\EntityManagerInterface;
use Yosmanyga\Component\Dql\Fit\WhereCriteriaFit;
use Yosmanyga\Component\Dql\Fit\Builder;

/**
 * @author Manuel Emilio Carpio <mectwork@gmail.com>
 * @author Yosmanyga Garcia <yosmanyga@gmail.com>
 */
class CategoryManager implements CategoryManagerInterface
{
    /**
     * @var string
     */
    private $class = 'Cubalider\Component\PrepaidCard\Model\Category';

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var Builder;
     */
    private $builder;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $em
     * @param Builder $builder
     */
    public function __construct(EntityManagerInterface $em, Builder $builder = null)
    {
        $this->em = $em;
        $this->builder = $builder ? : new Builder($em);
    }

    /**
     * Gets all categories.
     *
     * @api
     *
     * @return Category[]
     */
    public function collect()
    {
        $qb = $this->builder->build(
            $this->class
        );

        return $qb
            ->getQuery()
            ->getResult();
    }

    /**
     * Adds given category.
     *
     * @api
     *
     * @param Category $category
     */
    public function add(Category $category)
    {
        $this->em->persist($category);
        $this->em->flush();
    }

    /**
     * Picks a category using given criteria.
     * Criteria can be also a category strid.
     *
     * @api
     *
     * @param array|string $criteria
     *
     * @return Category The category
     */
    public function pick($criteria)
    {
        if (is_string($criteria)) {
            $criteria = array('strid' => $criteria);
        }

        $qb = $this->builder->build(
            $this->class,
            new WhereCriteriaFit($criteria)
        );

        return $qb
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * Removes given category.
     *
     * @api
     *
     * @param Category $category
     */
    public function remove(Category $category)
    {
        $this->em->remove($category);
        $this->em->flush();
    }
}
