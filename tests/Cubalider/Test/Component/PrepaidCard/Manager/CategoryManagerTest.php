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
                sprintf("%s/../../../../../../src/Cubalider/Component/PrepaidCard/Resources/config/doctrine", __DIR__),
                sprintf("%s/../../../../../../vendor/cubalider/money-with-doctrine-orm/src/Cubalider/Component/Money/Resources/config/doctrine", __DIR__)
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
}