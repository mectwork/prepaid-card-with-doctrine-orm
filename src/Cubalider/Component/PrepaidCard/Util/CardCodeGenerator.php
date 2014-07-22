<?php

namespace Cubalider\Component\PrepaidCard\Util;

use Doctrine\ORM\EntityManagerInterface;
use Yosmanyga\Component\Dql\Fit\WhereCriteriaFit;
use Yosmanyga\Component\Dql\Fit\Builder;
use Cubalider\Component\Util\CodeGenerator;

/**
 * @author Manuel Emilio Carpio <mectwork@gmail.com>
 */
class CardCodeGenerator
{
    /**
     * @var string
     */
    private $class = 'Cubalider\Component\PrepaidCard\Model\Card';

    /**
     * @var \Doctrine\ORM\EntityManagerInterface
     */
    private $em;

    /**
     * @var Builder;
     */
    private $builder;

    /**
     * @var  \Cubalider\Component\Util\CodeGenerator
     */
    private $codeGenerator;

    /**
     * Constructor.
     *
     * @param EntityManagerInterface $em
     * @param Builder $builder
     * @param CodeGenerator $codeGenerator
     */
    public function __construct(EntityManagerInterface $em, Builder $builder = null, CodeGenerator $codeGenerator = null)
    {
        $this->em = $em;
        $this->builder = $builder ? : new Builder($em);
        $this->codeGenerator = $codeGenerator ? : new CodeGenerator();
    }

    /**
     * @return string
     */
    public function generateCode()
    {
        $code = $this->codeGenerator->generate();

        while ($this->findByCriteria(array('code' => $code)) != null) {
            $code = $this->codeGenerator->generate();
        }

        return $code;
    }

    private function findByCriteria($criteria)
    {
        $qb = $this->builder->build(
            $this->class,
            new WhereCriteriaFit($criteria)
        );

        return $qb->getQuery()->getOneOrNullResult();
    }
}