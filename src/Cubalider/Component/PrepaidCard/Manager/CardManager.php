<?php

namespace Cubalider\Component\PrepaidCard\Manager;

use Cubalider\Component\PrepaidCard\Model\Card;
use Cubalider\Component\PrepaidCard\Model\Category;
use Cubalider\Component\PrepaidCard\Util\CardCodeGenerator;
use Doctrine\ORM\EntityManagerInterface;
use Yosmanyga\Component\Dql\Fit\AndFit;
use Yosmanyga\Component\Dql\Fit\Builder;
use Yosmanyga\Component\Dql\Fit\LimitFit;
use Yosmanyga\Component\Dql\Fit\WhereCriteriaFit;
use Yosmanyga\Component\Dql\Fit\SelectCountFit;

/**
 * @author Manuel Emilio Carpio <mectwork@gmail.com>
 * @author Yosmanyga Garcia <yosmanyga@gmail.com>
 */
class CardManager implements CardManagerInterface
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
     * @var \Cubalider\Component\PrepaidCard\Util\CardCodeGenerator
     */
    private $codeGenerator;

    /**
     * @param EntityManagerInterface $em
     * @param Builder $builder
     * @param CardCodeGenerator $codeGenerator
     */
    public function __construct(EntityManagerInterface $em, Builder $builder = null, CardCodeGenerator $codeGenerator = null)
    {
        $this->em = $em;
        $this->builder = $builder ? : new Builder($em);
        $this->codeGenerator = $codeGenerator ? : new CardCodeGenerator($em);
    }

    /**
     * Fetches given amount of new cards, of given category.
     * It marks cards as fetched, so they are not fetched again.
     *
     * @api
     *
     * @param Category $category
     * @param integer $amount
     *
     * @return Card[]
     */
    public function fetch(Category $category, $amount = 1)
    {
         $this->prepare($category, $amount);

        $qb = $this->builder->build(
            $this->class,
            new AndFit(array(
                new WhereCriteriaFit(array('category' => $category->getStrid())),
                new LimitFit($amount)
            ))
        );

        /** @var Card[] $cards */
        $cards = $qb->getQuery()->getResult();

        for ($i = 0; $i < $amount; $i++) {
            $cards[$i]->setStatus(Card::STATUS_FETCHED);
        }

        $this->em->flush();

        return $cards;
    }

    /**
     * @inheritdoc
     */
    public function utilize(Card $card)
    {
        $card->setStatus(Card::STATUS_UTILIZED);

        return $card->getCategory()->getUtility();
    }

    /**
     * Prepares the database, adding the needed cards of given category to
     * supply given amount.
     *
     * @param Category $category
     * @param int $amount
     */
    private function prepare(Category $category, $amount)
    {
        $dq = $this->builder->build(
            $this->class,
            new AndFit(array(
                new SelectCountFit('code'),
                new WhereCriteriaFit(array('category' => $category->getStrid()))
            ))
        );

        $count = $dq->getQuery()->getSingleScalarResult();

        $needed = $amount - $count;
        if ($needed > 0) {
            for ($i = 0; $i < $needed; $i++) {
                $card = new Card();
                $card->setCode($this->codeGenerator->generateCode());
                $card->setCategory($category);

                $this->em->persist($card);
            }

            $this->em->flush();
        }
    }
}
