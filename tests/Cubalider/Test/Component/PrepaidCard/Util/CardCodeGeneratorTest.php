<?php

namespace Cubalider\Test\Component\PrepaidCard\Util;

use Cubalider\Component\PrepaidCard\Model\Card;
use Cubalider\Component\Util\CodeGenerator;
use Cubalider\Component\PrepaidCard\Util\CardCodeGenerator;
use Yosmanyga\Component\Dql\Fit\Builder;

class CardCodeGeneratorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @covers \Cubalider\Component\PrepaidCard\Util\CardCodeGenerator::__construct
     */
    public function testConstructor()
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->getMock();
        $codeGenerator = $this->getMockBuilder('Cubalider\Component\Util\CodeGenerator')
            ->getMock();
        /** @var \Yosmanyga\Component\Dql\Fit\Builder $builder */
        $builder = $this->getMockBuilder('Yosmanyga\Component\Dql\Fit\Builder')
            ->setConstructorArgs(array($em))
            ->getMock();
        /** @var \Doctrine\ORM\EntityManager $em */
        /** @var \Yosmanyga\Component\Dql\Fit\Builder $builder */
        /** @var \Cubalider\Component\Util\CodeGenerator $codeGenerator */
        $manager = new CardCodeGenerator($em, $builder, $codeGenerator);

        $this->assertAttributeEquals($em, 'em', $manager);
        $this->assertAttributeEquals($builder, 'builder', $manager);
        $this->assertAttributeEquals($codeGenerator, 'codeGenerator', $manager);
    }

    /**
     * @covers \Cubalider\Component\PrepaidCard\Util\CardCodeGenerator::__construct
     */
    public function testConstructorWithDefaultParameters()
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getMockBuilder('Doctrine\ORM\EntityManagerInterface')
            ->getMock();
        $codeGenerator = $this->getMockBuilder('Cubalider\Component\Util\CodeGenerator')
            ->getMock();
        /** @var \Doctrine\ORM\EntityManager $em */
        $manager = new CardCodeGenerator($em);

        $this->assertAttributeEquals(new Builder($em), 'builder', $manager);
        $this->assertAttributeEquals(new CodeGenerator(), 'codeGenerator', $manager);
    }

    /**
     * @covers \Cubalider\Component\PrepaidCard\Util\CardCodeGenerator::generateCode
     */
    public function testGenerateCode()
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
            ->setMethods(array('getOneOrNullResult'))
            ->getMockForAbstractClass();
        $codeGenerator = $this->getMock('Cubalider\Component\Util\CodeGenerator');
        /** @var \Doctrine\ORM\EntityManager $em */
        /** @var \Yosmanyga\Component\Dql\Fit\Builder $builder */
        /** @var \Cubalider\Component\Util\CodeGenerator $codeGenerator */
        $manager = new CardCodeGenerator($em, $builder, $codeGenerator);
        $code = '1234-1234-1234';

        /** @var \PHPUnit_Framework_MockObject_MockObject $codeGenerator */
        $codeGenerator
            ->expects($this->once())
            ->method('generate')
            ->will($this->returnValue($code));

        /** @var \PHPUnit_Framework_MockObject_MockObject $builder */
        $builder
            ->expects($this->once())
            ->method('build')
            ->with(
                'Cubalider\Component\PrepaidCard\Model\Card'
            )
            ->will($this->returnValue($qb));
        $qb
            ->expects($this->once())
            ->method('getQuery')
            ->will($this->returnValue($query));
        $query
            ->expects($this->once())
            ->method('getOneOrNullResult')
            ->will($this->returnValue(null));

        $this->assertEquals($code, $manager->generateCode());
    }

    /**
     * @covers \Cubalider\Component\PrepaidCard\Util\CardCodeGenerator::generateCode
     */
    public function testGenerateCodeWithDuplicateCode()
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
            ->setMethods(array('getOneOrNullResult'))
            ->getMockForAbstractClass();
        $codeGenerator = $this->getMock('Cubalider\Component\Util\CodeGenerator');
        /** @var \Doctrine\ORM\EntityManager $em */
        /** @var \Yosmanyga\Component\Dql\Fit\Builder $builder */
        /** @var \Cubalider\Component\Util\CodeGenerator $codeGenerator */
        $manager = new CardCodeGenerator($em, $builder, $codeGenerator);
        $code = '1234-1234-1234';
        $card = new Card();

        /** @var \PHPUnit_Framework_MockObject_MockObject $codeGenerator */
        $codeGenerator
            ->expects($this->at(0))
            ->method('generate')
            ->will($this->returnValue('1111-1111-1111'));

        /** @var \PHPUnit_Framework_MockObject_MockObject $builder */
        $builder
            ->expects($this->at(0))
            ->method('build')
            ->with(
                'Cubalider\Component\PrepaidCard\Model\Card'
            )
            ->will($this->returnValue($qb));
        $qb
            ->expects($this->at(0))
            ->method('getQuery')
            ->will($this->returnValue($query));
        $query
            ->expects($this->at(0))
            ->method('getOneOrNullResult')
            ->will($this->returnValue('foobar'));

        /** @var \PHPUnit_Framework_MockObject_MockObject $builder */
        $builder
            ->expects($this->at(1))
            ->method('build')
            ->with(
                'Cubalider\Component\PrepaidCard\Model\Card'
            )
            ->will($this->returnValue($qb));
        $qb
            ->expects($this->at(1))
            ->method('getQuery')
            ->will($this->returnValue($query));
        $query
            ->expects($this->at(1))
            ->method('getOneOrNullResult')
            ->will($this->returnValue(null));

        $codeGenerator
            ->expects($this->at(1))
            ->method('generate')
            ->will($this->returnValue($code));

        $this->assertEquals($code, $manager->generateCode());
    }
}