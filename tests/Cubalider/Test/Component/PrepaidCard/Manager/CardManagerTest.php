<?php

namespace Cubalider\Test\Component\PrepaidCard\Manager;

use Cubalider\Component\Money\Model\Currency;
use Cubalider\Component\Money\Model\Money;
use Cubalider\Component\PrepaidCard\Manager\CardManager;
use Cubalider\Component\PrepaidCard\Model\Card;
use Cubalider\Component\PrepaidCard\Model\Category;
use Cubalider\Component\PrepaidCard\Util\CardCodeGenerator;
use Yosmanyga\Component\Dql\Fit\AndFit;
use Yosmanyga\Component\Dql\Fit\Builder;
use Yosmanyga\Component\Dql\Fit\LimitFit;
use Yosmanyga\Component\Dql\Fit\SelectCountFit;
use Yosmanyga\Component\Dql\Fit\WhereCriteriaFit;

class CardManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Cubalider\Component\PrepaidCard\Manager\CardManager::__construct
     */
    public function testConstructor()
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->getMock();
        /** @var \Yosmanyga\Component\Dql\Fit\Builder $builder */
        $builder = $this->getMockBuilder('Yosmanyga\Component\Dql\Fit\Builder')
            ->setConstructorArgs(array($em))
            ->getMock();
        /** @var \Cubalider\Component\PrepaidCard\Util\CardCodeGenerator $codeGenerator */
        $codeGenerator = $this->getMockBuilder('Cubalider\Component\PrepaidCard\Util\CardCodeGenerator')
            ->setConstructorArgs(array($em))
            ->getMock();

        $manager = new CardManager($em, $builder, $codeGenerator);

        $this->assertAttributeEquals($em, 'em', $manager);
        $this->assertAttributeEquals($builder, 'builder', $manager);
        $this->assertAttributeEquals($codeGenerator, 'codeGenerator', $manager);
    }

    /**
     * @covers \Cubalider\Component\PrepaidCard\Manager\CardManager::__construct
     */
    public function testConstructorWithDefaultParameters()
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->getMock();
        /** @var \Doctrine\ORM\EntityManager $em */
        $manager = new CardManager($em);

        $this->assertAttributeEquals(new Builder($em), 'builder', $manager);
        $this->assertAttributeEquals(new CardCodeGenerator($em), 'codeGenerator', $manager);
    }

    /**
     * @covers \Cubalider\Component\PrepaidCard\Manager\CardManager::fetch
     * @covers \Cubalider\Component\PrepaidCard\Manager\CardManager::prepare
     */
    public function testFetchWithPrepareWithoutNeededCard()
    {
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $builder = $this->getMockBuilder('Yosmanyga\Component\Dql\Fit\Builder')
            ->disableOriginalConstructor()
            ->getMock();
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $queryPrepare = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('getSingleScalarResult'))
            ->getMockForAbstractClass();
        $queryFetch = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('getResult'))
            ->getMockForAbstractClass();

        /** @var \Doctrine\ORM\EntityManager $em */
        /** @var \Yosmanyga\Component\Dql\Fit\Builder $builder */
        $manager = new CardManager($em, $builder);
        $category = new Category();
        $category->setStrid('foo');
        $amount = 2;

        /** Test prepare() **/

        /** @var \PHPUnit_Framework_MockObject_MockObject $builder */
        $builder
            ->expects($this->at(0))
            ->method('build')
            ->with(
                'Cubalider\Component\PrepaidCard\Model\Card',
                new AndFit(array(
                    new SelectCountFit('code'),
                    new WhereCriteriaFit(array('category' => $category->getStrid()))
                ))
            )
            ->will($this->returnValue($qb));
        $qb
            ->expects($this->at(0))
            ->method('getQuery')
            ->will($this->returnValue($queryPrepare));
        $queryPrepare
            ->expects($this->at(0))
            ->method('getSingleScalarResult')
            ->will($this->returnValue($amount));

        /** Testing Fetch **/

        $cards = array($card1 = new Card(), $card2 = new Card());

        /** @var \PHPUnit_Framework_MockObject_MockObject $builder */
        $builder
            ->expects($this->at(1))
            ->method('build')
            ->with(
                'Cubalider\Component\PrepaidCard\Model\Card',
                new AndFit(array(
                    new WhereCriteriaFit(array('category' => $category->getStrid())),
                    new LimitFit($amount)
                ))
            )
            ->will($this->returnValue($qb));
        $qb
            ->expects($this->at(1))
            ->method('getQuery')
            ->will($this->returnValue($queryFetch));
        $queryFetch
            ->expects($this->at(0))
            ->method('getResult')
            ->will($this->returnValue($cards));

        /** @var \PHPUnit_Framework_MockObject_MockObject $em */
        $em
            ->expects($this->once())->method('flush');

        $this->assertEquals($cards, $manager->fetch($category, $amount));
        /** @var Card[] $cards */
        $this->assertEquals(Card::STATUS_FETCHED, $card1->getStatus());
    }

    /**
     * @covers \Cubalider\Component\PrepaidCard\Manager\CardManager::utilize
     */
    public function testUtilize()
    {
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');

        $utility = new Money(0, new Currency('foo', 'USD'));
        $category = new Category();
        $category->setUtility($utility);
        $card = new Card();
        $card->setCategory($category);
        $card->setStatus(Card::STATUS_UTILIZED);

        /** @var \Doctrine\ORM\EntityManager $em */
        $manager = new CardManager($em);

        $this->assertEquals($utility, $manager->utilize($card));
    }
}