<?php

namespace Cubalider\Test\Component\PrepaidCard\Manager;

use Cubalider\Component\Money\Model\Currency;
use Cubalider\Component\Money\Model\Money;
use Cubalider\Component\PrepaidCard\Manager\CategoryManager;
use Cubalider\Component\PrepaidCard\Model\Category;
use Cubalider\Test\Component\PrepaidCard\EntityManagerBuilder;
use Doctrine\ORM\EntityManager;

class CategoryManagerTest extends \PHPUnit_Framework_TestCase
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
                'Cubalider\Component\Money\Model\Currency'
            )
        );
    }

    /**
     * @covers \Cubalider\Component\PrepaidCard\Manager\CategoryManager::__construct
     */
    public function testConstructor()
    {
        $manager = new CategoryManager($this->em);

        $this->assertAttributeEquals($this->em, 'em', $manager);
        $this->assertAttributeEquals($this->em->getRepository('Cubalider\Component\PrepaidCard\Model\Category'), 'repository', $manager);
    }

    /**
     * @covers \Cubalider\Component\PrepaidCard\Manager\CategoryManager::collect
     */
    public function testCollect()
    {
        /* Fixtures */

        $currency = new Currency('USD', 'Dollar');
        $this->em->persist($currency);

        $money = new Money(10, $currency);

        $category1 = new Category();
        $category1->setStrid('c1');
        $category1->setDescription('desc');
        $category1->setCost($money);
        $category1->setUtility($money);
        $this->em->persist($category1);

        $category2 = new Category();
        $category2->setStrid('c2');
        $category2->setDescription('desc');
        $category2->setCost($money);
        $category2->setUtility($money);
        $this->em->persist($category2);

        $this->em->flush();

        /* Test */

        $manager = new CategoryManager($this->em);
        $this->assertEquals(array($category1, $category2), $manager->collect());
    }

    /**
     * @covers \Cubalider\Component\PrepaidCard\Manager\CategoryManager::add
     */
    public function testAdd()
    {
        /* Fixtures */

        $currency = new Currency('USD', 'Dollar');
        $this->em->persist($currency);

        $money = new Money(10, $currency);

        $category = new Category();
        $category->setStrid('c1');
        $category->setDescription('desc');
        $category->setCost($money);
        $category->setUtility($money);
        $this->em->persist($category);

        $this->em->flush();

        /* Test */

        $manager = new CategoryManager($this->em);
        $manager->add($category);
        $repository = $this->em->getRepository('Cubalider\Component\PrepaidCard\Model\Category');

        $this->assertEquals(1, count($repository->findAll()));
        $this->assertSame($category, $manager->pick(array('strid' => 'c1')));
    }


    /**
     * @covers \Cubalider\Component\PrepaidCard\Manager\CategoryManager::pick
     */
    public function testPick()
    {
        /* Fixtures */

        $currency = new Currency('USD', 'Dollar');
        $this->em->persist($currency);

        $money = new Money(10, $currency);

        $category1 = new Category();
        $category1->setStrid('c1');
        $category1->setDescription('c1-descrip');
        $category1->setCost($money);
        $category1->setUtility($money);
        $this->em->persist($category1);

        $category2 = new Category();
        $category2->setStrid('c2');
        $category2->setDescription('c2-descrip');
        $category2->setCost($money);
        $category2->setUtility($money);
        $this->em->persist($category2);

        $this->em->flush();

        /* Tests */

        $manager = new CategoryManager($this->em);
        $this->assertEquals($category2, $manager->pick('c2'));

        $manager = new CategoryManager($this->em);
        $this->assertEquals($category2, $manager->pick(array('strid' => 'c2')));
    }

    /**
     * @covers \Cubalider\Component\PrepaidCard\Manager\CategoryManager::remove
     */
    public function testRemove()
    {
        /* Fixtures */

        $currency = new Currency('USD', 'Dollar');
        $this->em->persist($currency);

        $money = new Money(10, $currency);

        $category = new Category();
        $category->setStrid('c1');
        $category->setDescription('desc');
        $category->setCost($money);
        $category->setUtility($money);
        $this->em->persist($category);

        $this->em->flush();

        /* Test */

        $manager = new CategoryManager($this->em);
        $manager->remove($category);
        $repository = $this->em->getRepository('Cubalider\Component\PrepaidCard\Model\Category');

        $this->assertEquals(0, count($repository->findAll()));
        $this->assertNull($manager->pick(array('strid' => $category->getStrid())));
    }
}