<?php
/**
 * Test the {@link DataObjectSet} class.
 * 
 * @package sapphire
 * @subpackage tests
 */
class DataObjectSetTest extends SapphireTest {
	
	static $fixture_file = 'sapphire/tests/DataObjectTest.yml';
	
	function testArrayAccessExists() {
		$set = new DataObjectSet(array(
			$one = new DataObject(array('Title' => 'one')),
			$two = new DataObject(array('Title' => 'two')),
			$three = new DataObject(array('Title' => 'three'))
		));
		$this->assertEquals(count($set), 3);
		$this->assertTrue(isset($set[0]), 'First item in the set is set');
		$this->assertEquals($one, $set[0], 'First item in the set is accessible by array notation');
	}
	
	function testArrayAccessUnset() {
		$set = new DataObjectSet(array(
			$one = new DataObject(array('Title' => 'one')),
			$two = new DataObject(array('Title' => 'two')),
			$three = new DataObject(array('Title' => 'three'))
		));
		unset($set[0]);
		$this->assertEquals(count($set), 2);
	}
	
	function testArrayAccessSet() {
		$set = new DataObjectSet();
		$this->assertEquals(0, count($set));
		$set['testing!'] = $test = new DataObject(array('Title' => 'I\'m testing!'));
		$this->assertEquals($test, $set['testing!'], 'Set item is accessible by the key we set it as');
	}
	
	function testIterator() {
		$set = new DataObjectSet(array(
			$one = new DataObject(array('Title'=>'one')),
			$two = new DataObject(array('Title'=>'two')),
			$three = new DataObject(array('Title'=>'three')),
			$four = new DataObject(array('Title'=>'four'))
		));
		
		// test Pos() with foreach()
		$i = 0;
		foreach($set as $item) {
			$i++;
			$this->assertEquals($i, $item->Pos(), "Iterator position is set correctly on ViewableData when iterated with foreach()");
		}
		
		// test Pos() manually
		$this->assertEquals(1, $one->Pos());
		$this->assertEquals(2, $two->Pos());
		$this->assertEquals(3, $three->Pos());
		$this->assertEquals(4, $four->Pos());
		
		// test DataObjectSet->Count()
		$this->assertEquals(4, $set->Count());
		
		// test DataObjectSet->First()
		$this->assertSame($one, $set->First());
		
		// test DataObjectSet->Last()
		$this->assertSame($four, $set->Last());
		
		// test ViewableData->First()
		$this->assertTrue($one->First());
		$this->assertFalse($two->First());
		$this->assertFalse($three->First());
		$this->assertFalse($four->First());
		
		// test ViewableData->Last()
		$this->assertFalse($one->Last());
		$this->assertFalse($two->Last());
		$this->assertFalse($three->Last());
		$this->assertTrue($four->Last());
		
		// test ViewableData->Middle()
		$this->assertFalse($one->Middle());
		$this->assertTrue($two->Middle());
		$this->assertTrue($three->Middle());
		$this->assertFalse($four->Middle());
		
		// test ViewableData->Even()
		$this->assertFalse($one->Even());
		$this->assertTrue($two->Even());
		$this->assertFalse($three->Even());
		$this->assertTrue($four->Even());
		
		// test ViewableData->Odd()
		$this->assertTrue($one->Odd());
		$this->assertFalse($two->Odd());
		$this->assertTrue($three->Odd());
		$this->assertFalse($four->Odd());
	}

	public function testMultipleOf() {
		$comments = DataObject::get('PageComment', '', "\"ID\" ASC");
		$commArr = $comments->toArray();
		$multiplesOf3 = 0;
		
		foreach($comments as $comment) {
			if($comment->MultipleOf(3)) {
				$comment->IsMultipleOf3 = true;
				$multiplesOf3++;
			} else {
				$comment->IsMultipleOf3 = false;
			}
		}
		
		$this->assertEquals(3, $multiplesOf3);
		
		$this->assertTrue($commArr[0]->IsMultipleOf3);
		$this->assertFalse($commArr[1]->IsMultipleOf3);
		$this->assertFalse($commArr[2]->IsMultipleOf3);
		$this->assertTrue($commArr[3]->IsMultipleOf3);
		$this->assertFalse($commArr[4]->IsMultipleOf3);
		$this->assertFalse($commArr[5]->IsMultipleOf3);
		$this->assertTrue($commArr[6]->IsMultipleOf3);
	}

	/**
	 * Test {@link DataObjectSet->Count()}
	 */
	function testCount() {
		$comments = DataObject::get('PageComment', '', "\"ID\" ASC");
		
		/* There are a total of 8 items in the set */
		$this->assertEquals($comments->Count(), 8, 'There are a total of 8 items in the set');
	}

	/**
	 * Test {@link DataObjectSet->First()}
	 */
	function testFirst() {
		$comments = DataObject::get('PageComment', '', "\"ID\" ASC");
		
		/* The first object is Joe's comment */
		$this->assertEquals($comments->First()->Name, 'Joe', 'The first object has a Name field value of "Joe"');
	}
	
	/**
	 * Test {@link DataObjectSet->Last()}
	 */
	function testLast() {
		$comments = DataObject::get('PageComment', '', "\"ID\" ASC");
		
		/* The last object is Dean's comment */
		$this->assertEquals($comments->Last()->Name, 'Dean', 'The last object has a Name field value of "Dean"');
	}
	
	/**
	 * Test {@link DataObjectSet->map()}
	 */
	function testMap() {
		$comments = DataObject::get('PageComment', '', "\"ID\" ASC");

		/* Now we get a map of all the PageComment records */
		$map = $comments->map('ID', 'Title', '(Select one)');
		
		$expectedMap = array(
			'' => '(Select one)',
			1 => 'Joe',
			2 => 'Jane',
			3 => 'Bob',
			4 => 'Bob',
			5 => 'Ernie',
			6 => 'Jimmy',
			7 => 'Dean',
			8 => 'Dean'
		);
		
		/* There are 9 items in the map. 8 are records. 1 is the empty value */
		$this->assertEquals(count($map), 9, 'There are 9 items in the map. 8 are records. 1 is the empty value');
		
		/* We have the same map as our expected map, asserted above */
		
		/* toDropDownMap() is an alias of map() - let's make a map from that */
		$map2 = $comments->toDropDownMap('ID', 'Title', '(Select one)');
		
		/* There are 9 items in the map. 8 are records. 1 is the empty value */
		$this->assertEquals(count($map), 9, 'There are 9 items in the map. 8 are records. 1 is the empty value.');
	}
	
}
?>