<?php

/*
 * This file is part of the KtwDatabaseMenuBundle package.
 *
 * (c) Kevin T. Weber <https://github.com/kevintweber/KtwDatabaseMenuBundle/>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace kevintweber\KtwDatabaseMenuBundle\Tests\Entity;

use kevintweber\KtwDatabaseMenuBundle\Entity\MenuItem;
use kevintweber\KtwDatabaseMenuBundle\Tests\BaseTestCase;

class TestMenuItem extends MenuItem {}

/**
 * MenuItem tree tests.
 *
 * Since kevintweber\KtwDatabaseMenuBundle\Entity\MenuItem inherits from
 * Knp\Menu\MenuItem, I have copied many of the tests from
 * KnpMenu/tests/Knp/Menu/Tests/MenuItemTreeTest.php to here.
 * Therefore most of these tests are thanks to stof of KNP Labs.  Thank you.
 */
class MenuItemTreeTest extends BaseTestCase
{
    /**
     * @var kevintweber\KtwDatabaseMenuBundle\Entity\MenuItem
     */
    protected $menu;

    /**
     * @var kevintweber\KtwDatabaseMenuBundle\Entity\MenuItem
     */
    protected $pt1;

    /**
     * @var kevintweber\KtwDatabaseMenuBundle\Entity\MenuItem
     */
    protected $ch1;

    /**
     * @var kevintweber\KtwDatabaseMenuBundle\Entity\MenuItem
     */
    protected $ch2;

    /**
     * @var kevintweber\KtwDatabaseMenuBundle\Entity\MenuItem
     */
    protected $ch3;

    /**
     * @var kevintweber\KtwDatabaseMenuBundle\Entity\MenuItem
     */
    protected $pt2;

    /**
     * @var kevintweber\KtwDatabaseMenuBundle\Entity\MenuItem
     */
    protected $ch4;

    /**
     * @var kevintweber\KtwDatabaseMenuBundle\Entity\MenuItem
     */
    protected $gc1;

    protected function setUp()
    {
        $factory = $this->buildFactory();
        $this->menu = $factory->createItem('Root li', array('childrenAttributes' => array('class' => 'root')));
        $this->pt1 = $this->menu->addChild('Parent 1');
        $this->ch1 = $this->pt1->addChild('Child 1');
        $this->ch2 = $this->pt1->addChild('Child 2');

        // add the 3rd child via addChild with an object
        $this->ch3 = new MenuItem('Child 3', $factory);
        $this->pt1->addChild($this->ch3);

        $this->pt2 = $this->menu->addChild('Parent 2');
        $this->ch4 = $this->pt2->addChild('Child 4');
        $this->gc1 = $this->ch4->addChild('Grandchild 1');
    }

    protected function tearDown()
    {
        $this->menu = null;
        $this->pt1 = null;
        $this->ch1 = null;
        $this->ch2 = null;
        $this->ch3 = null;
        $this->pt2 = null;
        $this->ch4 = null;
        $this->gc1 = null;
    }

    // prints a visual representation of our basic testing tree
    protected function printTestTree()
    {
        print(' Menu Structure '."\n");
        print(' rt '."\n");
        print(' / \ '."\n");
        print(' pt1 pt2 '."\n");
        print(' / | \ | '."\n");
        print(' ch1 ch2 ch3 ch4 '."\n");
        print(' | '."\n");
        print(' gc1 '."\n");
    }

    public function testSampleTreeIntegrity()
    {
        $this->assertCount(2, $this->menu);
        $this->assertCount(3, $this->menu['Parent 1']);
        $this->assertCount(1, $this->menu['Parent 2']);
        $this->assertCount(1, $this->menu['Parent 2']['Child 4']);
        $this->assertEquals('Grandchild 1', $this->menu['Parent 2']['Child 4']['Grandchild 1']->getName());
    }

    public function testGetLevel()
    {
        $this->assertEquals(0, $this->menu->getLevel());
        $this->assertEquals(1, $this->pt1->getLevel());
        $this->assertEquals(1, $this->pt2->getLevel());
        $this->assertEquals(2, $this->ch4->getLevel());
        $this->assertEquals(3, $this->gc1->getLevel());
    }

    public function testGetRoot()
    {
        $this->assertSame($this->menu, $this->menu->getRoot());
        $this->assertSame($this->menu, $this->pt1->getRoot());
        $this->assertSame($this->menu, $this->gc1->getRoot());
    }

    public function testIsRoot()
    {
        $this->assertTrue($this->menu->isRoot());
        $this->assertFalse($this->pt1->isRoot());
        $this->assertFalse($this->ch3->isRoot());
    }

    public function testGetParent()
    {
        $this->assertNull($this->menu->getParent());
        $this->assertSame($this->menu, $this->pt1->getParent());
        $this->assertSame($this->ch4, $this->gc1->getParent());
    }

    public function testMoveSampleMenuToNewRoot()
    {
        $newRoot = new TestMenuItem("newRoot", $this->getMock('Knp\Menu\FactoryInterface'));
        $newRoot->addChild($this->menu);

        $this->assertEquals(1, $this->menu->getLevel());
        $this->assertEquals(2, $this->pt1->getLevel());

        $this->assertSame($newRoot, $this->menu->getRoot());
        $this->assertSame($newRoot, $this->pt1->getRoot());
        $this->assertFalse($this->menu->isRoot());
        $this->assertTrue($newRoot->isRoot());
        $this->assertSame($newRoot, $this->menu->getParent());
    }

    public function testIsFirst()
    {
        $this->assertFalse($this->menu->isFirst(), 'The root item is not considered as first');
        $this->assertTrue($this->pt1->isFirst());
        $this->assertFalse($this->pt2->isFirst());
        $this->assertTrue($this->ch4->isFirst());
    }

    public function testActsLikeFirst()
    {
        $this->ch1->setDisplay(false);
        $this->assertFalse($this->menu->actsLikeFirst(), 'The root item is not considered as first');
        $this->assertFalse($this->ch1->actsLikeFirst(), 'A hidden item does not acts like first');
        $this->assertTrue($this->ch2->actsLikeFirst());
        $this->assertFalse($this->ch3->actsLikeFirst());
        $this->assertTrue($this->ch4->actsLikeFirst());
    }

    public function testActsLikeFirstWithNoDisplayedItem()
    {
        $this->pt1->setDisplay(false);
        $this->pt2->setDisplay(false);
        $this->assertFalse($this->pt1->actsLikeFirst());
        $this->assertFalse($this->pt2->actsLikeFirst());
    }

    public function testIsLast()
    {
        $this->assertFalse($this->menu->isLast(), 'The root item is not considered as last');
        $this->assertFalse($this->pt1->isLast());
        $this->assertTrue($this->pt2->isLast());
        $this->assertTrue($this->ch4->isLast());
    }

    public function testActsLikeLast()
    {
        $this->ch3->setDisplay(false);
        $this->assertFalse($this->menu->actsLikeLast(), 'The root item is not considered as last');
        $this->assertFalse($this->ch1->actsLikeLast());
        $this->assertTrue($this->ch2->actsLikeLast());
        $this->assertFalse($this->ch3->actsLikeLast(), 'A hidden item does not acts like last');
        $this->assertTrue($this->ch4->actsLikeLast());
    }

    public function testActsLikeLastWithNoDisplayedItem()
    {
        $this->pt1->setDisplay(false);
        $this->pt2->setDisplay(false);
        $this->assertFalse($this->pt1->actsLikeLast());
        $this->assertFalse($this->pt2->actsLikeLast());
    }

    public function testArrayAccess()
    {
        $this->menu->addChild('Child Menu');
        $this->assertEquals('Child Menu', $this->menu['Child Menu']->getName());
        $this->assertNull($this->menu['Fake']);

        $this->menu['New Child'] = 'New Label';
        $this->assertEquals('kevintweber\KtwDatabaseMenuBundle\Entity\MenuItem',
                            get_class($this->menu['New Child']));
        $this->assertEquals('New Child', $this->menu['New Child']->getName());
        $this->assertEquals('New Label', $this->menu['New Child']->getLabel());

        unset($this->menu['New Child']);
        $this->assertNull($this->menu['New Child']);
    }

    public function testCountable()
    {
        $this->assertCount(2, $this->menu);

        $this->menu->addChild('New Child');
        $this->assertCount(3, $this->menu);

        unset($this->menu['New Child']);
        $this->assertCount(2, $this->menu);
    }

    public function testGetChildren()
    {
        $children = $this->ch4->getChildren();
        $this->assertCount(1, $children);
        $this->assertEquals($this->gc1->getName(), $children['Grandchild 1']->getName());
    }

    public function testGetFirstChild()
    {
        $this->assertSame($this->pt1, $this->menu->getFirstChild());
        // test for bug in getFirstChild implementation (when internal array pointer is changed getFirstChild returns wrong child)
        foreach ($this->menu->getChildren() as $c);
        $this->assertSame($this->pt1, $this->menu->getFirstChild());
    }

    public function testGetLastChild()
    {
        $this->assertSame($this->pt2, $this->menu->getLastChild());
        // test for bug in getFirstChild implementation (when internal array pointer is changed getLastChild returns wrong child)
        foreach ($this->menu->getChildren() as $c);
        $this->assertSame($this->pt2, $this->menu->getLastChild());
    }

    public function testAddChildDoesNotUseTheFactoryIfItem()
    {
        $factory = $this->getMock('Knp\Menu\FactoryInterface');
        $factory->expects($this->never())
            ->method('createItem');
        $menu = new MenuItem('Root li', $factory);
        $menu->addChild(new MenuItem('Child 3', $factory));
    }

    /**
     * @expectedException LogicException
     */
    public function testAddChildFailsIfInAnotherMenu()
    {
        $factory = $this->getMock('Knp\Menu\FactoryInterface');
        $menu = new MenuItem('Root li', $factory);
        $child = new MenuItem('Child 3', $factory);
        $menu->addChild($child);

        $menu2 = new MenuItem('Second menu', $factory);
        $menu2->addChild($child);
    }

    public function testGetChild()
    {
        $this->assertSame($this->gc1, $this->ch4->getChild('Grandchild 1'));
        $this->assertNull($this->ch4->getChild('nonexistentchild'));
    }

    public function testRemoveChild()
    {
        $gc2 = $this->ch4->addChild('gc2');
        $gc3 = $this->ch4->addChild('gc3');
        $gc4 = $this->ch4->addChild('gc4');
        $this->assertCount(4, $this->ch4);
        $this->ch4->removeChild('gc4');
        $this->assertCount(3, $this->ch4);
        $this->assertTrue($this->ch4->getChild('Grandchild 1')->isFirst());
        $this->assertTrue($this->ch4->getChild('gc3')->isLast());
    }

    public function testRemoveFakeChild()
    {
        $this->menu->removeChild('fake');
        $this->assertCount(2, $this->menu);
    }

    public function testReAddRemovedChild()
    {
        $gc2 = $this->ch4->addChild('gc2');
        $this->ch4->removeChild('gc2');
        $this->menu->addChild($gc2);
        $this->assertCount(3, $this->menu);
        $this->assertTrue($gc2->isLast());
        $this->assertFalse($this->pt2->isLast());
    }

    public function testUpdateChildAfterRename()
    {
        $this->pt1->setName('Temp name');
        $this->assertSame($this->pt1, $this->menu->getChild('Temp name'));
        $this->assertEquals(array('Temp name', 'Parent 2'), array_keys($this->menu->getChildren()));
        $this->assertNull($this->menu->getChild('Parent 1'));
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testRenameToExistingSiblingNameThrowAnException()
    {
        $this->pt1->setName('Parent 2');
    }

    public function testGetUri()
    {
        $this->addChildWithExternalUrl();
        $this->assertNull($this->pt1->getUri());
        $this->assertEquals('http://www.symfony-reloaded.org', $this->menu['child']->getUri());
    }

    public function testCopy()
    {
        $menuCopy = $this->menu->copy();
        $this->assertEquals($menuCopy, $this->menu);
    }

    public function getSliceData()
    {
        $this->setUp();

        return array(
            'numeric offset and numeric length' => array(0, 2, 2, array($this->ch1->getName(), $this->ch2->getName())),
            'numeric offset and no length' => array(0, null, 3, array($this->ch1->getName(), $this->ch2->getName(), $this->ch3->getName())),
            'named offset and no length' => array('Child 2', null, 2, array($this->ch2->getName(), $this->ch3->getName())),
            'child offset and no length' => array($this->ch3, null, 1, array($this->ch3->getName())),
            'numeric offset and named length' => array(0, 'Child 3', 2, array($this->ch1->getName(), $this->ch2->getName())),
            'numeric offset and child length' => array(0, $this->ch3, 2, array($this->ch1->getName(), $this->ch2->getName())),
            );
    }

    public function getSplitData()
    {
        $this->setUp();

        return array(
            'numeric length' => array(1, 1, array($this->ch1->getName())),
            'named length' => array('Child 3', 2, array($this->ch1->getName(), $this->ch2->getName())),
            'child length' => array($this->ch3, 2, array($this->ch1->getName(), $this->ch2->getName())),
            );
    }

    protected function addChildWithExternalUrl()
    {
        $this->menu->addChild('child', array('uri' => 'http://www.symfony-reloaded.org'));
    }
}