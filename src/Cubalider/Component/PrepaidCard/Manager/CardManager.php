<?php

namespace Cubalider\Component\PrepaidCard\Manager;

use Cubalider\Component\Money\Model\Money;
use Cubalider\Component\PrepaidCard\Model\Card;
use Cubalider\Component\PrepaidCard\Model\Category;
use Cubalider\Component\PrepaidCard\Util\CodeGenerator;
use Cubalider\Component\PrepaidCard\Util\CodeGeneratorInterface;
use Doctrine\ORM\EntityManager;

/**
 * @author Manuel Emilio Carpio <mectwork@gmail.com>
 * @author Yosmanyga Garcia <yosmanyga@gmail.com>
 */
class CardManager implements CardManagerInterface
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
     * @var \Cubalider\Component\PrepaidCard\Util\CodeGeneratorInterface
     */
    private $codeGenerator;

    /**
     * Additionally it creates a repository using $em, for given class
     *
     * @param EntityManager $em
     * @param CodeGeneratorInterface $codeGenerator
     */
    public function __construct(
        EntityManager $em,
        CodeGeneratorInterface $codeGenerator = null
    )
    {
        $this->em = $em;
        $this->repository = $this->em->getRepository('Cubalider\Component\PrepaidCard\Model\Card');
        $this->codeGenerator = $codeGenerator ? : new CodeGenerator();
    }

    /**
     * @inheritdoc
     */
    public function fetch(Category $category, $amount = 1)
    {
        $this->prepare($category, $amount);

        /** @var Card[] $cards */
        $cards = $this->repository->findBy(
            array('category' => $category),
            array(),
            $amount
        );

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
     * @param int      $amount
     */
    private function prepare(Category $category, $amount)
    {
        /** @var \Doctrine\ORM\QueryBuilder $queryBuilder */
        $queryBuilder = $this->repository->createQueryBuilder('Card');

        $count = $queryBuilder
            ->select('COUNT(Card)')
            ->where('Card.category = :category')
            ->setParameter('category', $category)
            ->getQuery()
            ->getSingleScalarResult();

        $needed = $amount - $count;
        if ($needed > 0) {
            for ($i = 0; $i < $needed; $i++) {
                $card = new Card();
                $card->setCode($this->generateCode());
                $card->setCategory($category);

                $this->em->persist($card);
            }

            $this->em->flush();
        }
    }

    /**
     * Returns a unique code.
     *
     * @return string
     */
    private function generateCode()
    {
        $code = $this->codeGenerator->generate();
        while ($this->repository->findOneBy(array('code' => $code))) {
            $code = $this->codeGenerator->generate();
        }

        return $code;
    }
}
