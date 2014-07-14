<?php

namespace Cubalider\Test\Component\PrepaidCard\Manager;

use Cubalider\Component\Money\Model\Currency;
use Cubalider\Component\Money\Model\Money;
use Cubalider\Component\PrepaidCard\Manager\CategoryManager;
use Cubalider\Component\PrepaidCard\Model\Category;
use Doctrine\ORM\EntityManagerInterface;
use Yosmanyga\Component\Dql\Fit\Builder;
use Yosmanyga\Component\Dql\Fit\WhereCriteriaFit;

class CategoryManagerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Cubalider\Component\PrepaidCard\Manager\CategoryManager::__construct
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
        $manager = new CategoryManager($em, $builder);

        $this->assertAttributeEquals($em, 'em', $manager);
        $this->assertAttributeEquals($builder, 'builder', $manager);
    }

    /**
     * @covers \Cubalider\Component\PrepaidCard\Manager\CategoryManager::__construct
     */
    public function testConstructorWithDefaultParameters()
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->getMock();
        /** @var \Doctrine\ORM\EntityManager $em */
        $manager = new CategoryManager($em);

        $this->assertAttributeEquals(new Builder($em), 'builder', $manager);
    }

    /**
     * @covers \Cubalider\Component\PrepaidCard\Manager\CategoryManager::collect
     */
    public function testCollect()
    {
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $builder = $this->getMockBuilder('Yosmanyga\Component\Dql\Fit\Builder')
            ->disableOriginalConstructor()
            ->getMock();
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('getResult'))
            ->getMockForAbstractClass();
        /** @var \Doctrine\ORM\EntityManager $em */
        /** @var \Yosmanyga\Component\Dql\Fit\Builder $builder */
        $manager = new CategoryManager($em, $builder);

        /** @var \PHPUnit_Framework_MockObject_MockObject $builder */
        $builder
            ->expects($this->once())
            ->method('build')
            ->with(
                'Cubalider\Component\PrepaidCard\Model\Category'
            )
            ->will($this->returnValue($qb));
        $qb
            ->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));
        $query
            ->expects($this->once())
            ->method('getResult')
            ->will($this->returnValue('foobar'));

        $this->assertEquals('foobar', $manager->collect());
    }

    /**
     * @covers \Cubalider\Component\PrepaidCard\Manager\CategoryManager::add
     */
    public function testAdd()
    {
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $category = new Category();
        /** @var \Doctrine\ORM\EntityManager $em */
        $manager = new CategoryManager($em);

        /** @var \PHPUnit_Framework_MockObject_MockObject $em */
        $em
            ->expects($this->once())->method('persist')
            ->with($category);
        $em
            ->expects($this->once())->method('flush');

        $manager->add($category);
    }

    /**
     * @covers \Cubalider\Component\PrepaidCard\Manager\CategoryManager::pick
     */
    public function testPick()
    {
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $builder = $this->getMockBuilder('Yosmanyga\Component\Dql\Fit\Builder')
            ->disableOriginalConstructor()
            ->getMock();
        $criteria = array('foo' => 'bar');
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('getOneOrNullResult'))
            ->getMockForAbstractClass();
        /** @var \Doctrine\ORM\EntityManager $em */
        /** @var \Yosmanyga\Component\Dql\Fit\Builder $builder */
        $manager = new CategoryManager($em, $builder);

        /** @var \PHPUnit_Framework_MockObject_MockObject $builder */
        $builder
            ->expects($this->once())
            ->method('build')
            ->with(
                'Cubalider\Component\PrepaidCard\Model\Category',
                new WhereCriteriaFit($criteria)
            )
            ->will($this->returnValue($qb));
        $qb
            ->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));
        $query
            ->expects($this->once())
            ->method('getOneOrNullResult')
            ->will($this->returnValue('foobar'));

        $this->assertEquals('foobar', $manager->pick($criteria));
    }

    /**
     * @covers \Cubalider\Component\PrepaidCard\Manager\CategoryManager::pick
     */
    public function testPickWithString()
    {
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $builder = $this->getMockBuilder('Yosmanyga\Component\Dql\Fit\Builder')
            ->disableOriginalConstructor()
            ->getMock();
        $criteria = 'foo';
        $qb = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();
        $query = $this->getMockBuilder('Doctrine\ORM\AbstractQuery')
            ->disableOriginalConstructor()
            ->setMethods(array('getOneOrNullResult'))
            ->getMockForAbstractClass();
        /** @var \Doctrine\ORM\EntityManager $em */
        /** @var \Yosmanyga\Component\Dql\Fit\Builder $builder */
        $manager = new CategoryManager($em, $builder);

        /** @var \PHPUnit_Framework_MockObject_MockObject $builder */
        $builder
            ->expects($this->once())
            ->method('build')
            ->with(
                'Cubalider\Component\PrepaidCard\Model\Category',
                new WhereCriteriaFit(array('strid' => $criteria))
            )
            ->will($this->returnValue($qb));
        $qb
            ->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));
        $query
            ->expects($this->once())
            ->method('getOneOrNullResult')
            ->will($this->returnValue('foobar'));

        $this->assertEquals('foobar', $manager->pick($criteria));
    }

    /**
     * @covers \Cubalider\Component\PrepaidCard\Manager\CategoryManager::remove
     */
    public function testDelete()
    {
        $em = $this->getMock('Doctrine\ORM\EntityManagerInterface');
        $category = new Category();
        /** @var \Doctrine\ORM\EntityManager $em */
        $manager = new CategoryManager($em);

        /** @var \PHPUnit_Framework_MockObject_MockObject $em */
        $em
            ->expects($this->once())->method('remove')
            ->with($category);
        $em
            ->expects($this->once())->method('flush');

        $manager->remove($category);
    }
}