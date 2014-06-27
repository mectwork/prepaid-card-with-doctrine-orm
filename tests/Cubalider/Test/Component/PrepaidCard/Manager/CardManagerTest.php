<?php

namespace Cubalider\Test\Component\PrepaidCard\Manager;

use Cubalider\Component\Money\Model\Currency;
use Cubalider\Component\Money\Model\Money;
use Cubalider\Component\PrepaidCard\Model\Card;
use Cubalider\Component\PrepaidCard\Model\Category;
use Cubalider\Component\PrepaidCard\Manager\CardManager;
use Cubalider\Test\Component\PrepaidCard\EntityManagerBuilder;
use Doctrine\ORM\EntityManager;

class CardManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var EntityManager
     */
    private $em;

    protected function setUp()
    {
        $builder = new EntityManagerBuilder();
        $this->em = $builder->createEntityManager(
            array(
                realpath(sprintf("%s/../../../../../../src/Cubalider/Component/PrepaidCard/Resources/config/doctrine", __DIR__)),
                realpath(sprintf("%s/../../../../../../vendor/cubalider/money-with-doctrine-orm/src/Cubalider/Component/Money/Resources/config/doctrine", __DIR__))
            ),
            array(
                'Cubalider\Component\PrepaidCard\Model\Category',
                'Cubalider\Component\PrepaidCard\Model\Card',
                'Cubalider\Component\Money\Model\Currency'
            )
        );
    }

    /**
     * @covers \Cubalider\Component\PrepaidCard\Manager\CardManager::__construct
     */
    public function testConstructor()
    {
        $manager = new CardManager($this->em);

        $this->assertAttributeEquals($this->em, 'em', $manager);
        $this->assertAttributeEquals($this->em->getRepository('Cubalider\Component\PrepaidCard\Model\Card'), 'repository', $manager);
    }

    /**
     * @covers \Cubalider\Component\PrepaidCard\Manager\CardManager::fetch
     * @covers \Cubalider\Component\PrepaidCard\Manager\CardManager::prepare
     */
    public function testFetch()
    {
        /* Fixtures */

        $currency = new Currency('USD', 'Dollar');
        $this->em->persist($currency);

        $money = new Money(10, $currency);

        $category = new Category();
        $category->setStrid('c');
        $category->setDescription('desc');
        $category->setCost($money);
        $category->setUtility($money);
        $this->em->persist($category);

        $card1 = new Card();
        $card1->setCode('code1');
        $card1->setCategory($category);
        $this->em->persist($card1);

        $card2 = new Card();
        $card2->setCode('code2');
        $card2->setCategory($category);
        $this->em->persist($card2);

        $card3 = new Card();
        $card3->setCode('code3');
        $card3->setCategory($category);
        $this->em->persist($card3);

        $this->em->flush();

        /* Test */

        $manager = new CardManager($this->em);
        $repository = $this->em->getRepository('Cubalider\Component\PrepaidCard\Model\Card');

        $this->assertEquals(array($card1, $card2), $manager->fetch($category, 2));
        $this->assertEquals(Card::STATUS_FETCHED, $card1->getStatus());
        $this->assertEquals(Card::STATUS_FETCHED, $card2->getStatus());
        $this->assertEquals(Card::STATUS_NEW, $card3->getStatus());
        // No card was generated if amount was supplied
        $this->assertEquals(3, count($repository->findAll()));
    }

    /**
     * @covers \Cubalider\Component\PrepaidCard\Manager\CardManager::fetch
     * @covers \Cubalider\Component\PrepaidCard\Manager\CardManager::prepare
     * @covers \Cubalider\Component\PrepaidCard\Manager\CardManager::generateCode
     */
    public function testFetchWithPrepare()
    {
        /* Fixtures */

        $currency = new Currency('USD', 'Dollar');
        $this->em->persist($currency);

        $money = new Money(10, $currency);

        $category = new Category();
        $category->setStrid('c');
        $category->setDescription('desc');
        $category->setCost($money);
        $category->setUtility($money);
        $this->em->persist($category);

        $this->em->flush();

        /* Test */

        $manager = new CardManager($this->em);
        $repository = $this->em->getRepository('Cubalider\Component\PrepaidCard\Model\Card');

        $manager->fetch($category, 2);

        $this->assertEquals(2, count($repository->findAll()));
    }

    /**
     * @covers \Cubalider\Component\PrepaidCard\Manager\CardManager::fetch
     * @covers \Cubalider\Component\PrepaidCard\Manager\CardManager::prepare
     * @covers \Cubalider\Component\PrepaidCard\Manager\CardManager::generateCode
     */
    public function testFetchWithExistingCodeOnCardGeneration()
    {
        /* Fixtures */

        $currency = new Currency('USD', 'Dollar');
        $this->em->persist($currency);

        $money = new Money(10, $currency);

        $category = new Category();
        $category->setStrid('c');
        $category->setDescription('desc');
        $category->setCost($money);
        $category->setUtility($money);
        $this->em->persist($category);

        $card1 = new Card();
        $card1->setCode('xxx');
        $card1->setCategory($category);
        $this->em->persist($card1);

        $this->em->flush();

        /* Test */

        $codeGenerator = $this->getMock('Cubalider\Component\PrepaidCard\Util\CodeGeneratorInterface');

        /** @var \Cubalider\Component\PrepaidCard\Util\CodeGeneratorInterface  $codeGenerator */
        $manager = new CardManager($this->em, $codeGenerator);

        /** @var \PHPUnit_Framework_MockObject_MockObject  $codeGenerator */
        $codeGenerator
            ->expects($this->at(0))->method('generate')
            ->will($this->returnValue('xxx'));
        $codeGenerator
            ->expects($this->at(1))->method('generate')
            ->will($this->returnValue('yyy'));

        $manager->fetch($category, 2);
    }

    /**
     * @covers \Cubalider\Component\PrepaidCard\Manager\CardManager::utilize
     */
    public function testUtilize()
    {
        /* Fixtures */

        $currency = new Currency('USD', 'Dollar');
        $this->em->persist($currency);

        $money = new Money(10, $currency);

        $category = new Category();
        $category->setStrid('c');
        $category->setDescription('desc');
        $category->setCost($money);
        $category->setUtility($money);
        $this->em->persist($category);

        $card = new Card();
        $card->setCategory($category);

        /* Test */

        $manager = new CardManager($this->em);

        $this->assertEquals($money, $manager->utilize($card));
        $this->assertEquals(Card::STATUS_UTILIZED, $card->getStatus());
    }
}