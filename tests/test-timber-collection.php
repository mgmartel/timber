<?php
class Person {
	public $name;
	public $greeting;

	public function __construct($name, $greeting) {
		$this->name = $name;
		$this->greeting = $greeting;
	}
}

class TimberPostsCollectionTest extends WP_UnitTestCase {

    public function setUp() {
		$this->collection = new TimberCollection;

		$this->walter = new Person("Walter", "I am the danger.");
		$this->jesse  = new Person("Jesse",  "Yeah, Science!");
		$this->skylar = new Person("Skylar", "Walter?");
		$this->junior = new Person("Junior", "Hey Guys!");
		$this->jesse2 = new Person("Jesse", "Yo b****");
		$this->people = new TimberCollection( array($this->walter, $this->jesse, $this->skylar));
	}

	protected function getComparatorCollection() {
		$this->people->setComparator(function($a, $b) {
			if ($a->greeting == $b->greeting) {
    		    return 0;
    		}
    		return ($a->greeting < $b->greeting) ? -1 : 1;
		});
		$this->people->setAutosort = true;
		return $this->people;
	}

	/**
     * @covers ::getLength
     */
	public function testLength() {
		$this->assertEquals($this->collection->length, 0);
		$this->collection->append($this->junior);
		$this->assertEquals($this->collection->length, 1);
	}

	/**
	 * @covers ::setComparator
	 */
	public function testSetComparator() {
		$testFunc = $this->collection->setComparator(function() {
			if ($a == $b) { return 0; }
		    return ($a < $b) ? -1 : 1;
		});
		$this->assertEquals(function() {
			if ($a == $b) { return 0; }
		    return ($a < $b) ? -1 : 1;
		}, $testFunc);

	}

	/**
	 * @covers ::setAutosort
	 */
	public function testSetAutosort() {
		$this->assertTrue($this->collection->setAutosort(true));
		$this->assertFalse($this->collection->setAutosort(false));
	}

	/**
	 * @covers ::add
	 */
	public function testAdd() {
		$this->markTestSkipped();
		$people = $this->getComparatorCollection();
		$people->add(new Person("Combo", "Dude, I got shot!"));
		$this->assertEquals("Dude, I got shot!", $people[0]->greeting);
	}

	/**
	 * @covers ::remove
	 */
	public function testRemove() {
		$this->markTestSkipped();
	}

	/**
     * @covers ::push
     */
	public function testPush() {
		$this->collection->push($this->junior);
		$this->assertEquals($this->collection[0], $this->junior);
	}

	/**
	 * @covers ::push
     * @depends testPush
     */
	public function testPushMultiple() {
		$this->collection->push($this->junior, $this->walter, $this->jesse, $this->skylar);
		$this->assertEquals($this->collection[2], $this->jesse);
		$this->assertEquals($this->collection[3], $this->skylar);
	}

	/**
	 * @covers ::push
     * @depends testPush
     */
	public function testPushReturnsSelf() {
		$collection = $this->collection->push($this->junior, $this->walter);
		$this->assertEquals($this->collection, $collection);
	}

	/**
     * @covers ::pop
     */
	public function testPop() {
		$val = $this->people->pop();
		$this->assertEquals($this->skylar, $val);
	}

	/**
	 * @covers ::pop
     * @depends testPop
     */
	public function testPopMultiple() {
		$val = $this->people->pop(2);
		$this->assertEquals(array($this->jesse, $this->skylar), $val);
	}

	/**
     * @covers ::shift
     */
	public function testShift() {
		$val = $this->people->shift();
		$this->assertEquals($this->walter, $val);
	}

	/**
	 * @covers ::shift
     * @depends testShift
     */
	public function testShiftMultiple() {
		$val = $this->people->shift(2);
		$this->assertEquals(array($this->walter, $this->jesse), $val);
	}

	/**
     * @covers ::unshift
     */
	public function testUnshift() {
		$this->people->unshift($this->junior);
		$this->assertEquals($this->junior, $this->people[0]);
	}

	/**
	 * @covers ::unshift
     * @depends testUnshift
     */
	public function testUnshiftMultiple() {
		$this->collection->unshift($this->junior, $this->walter, $this->jesse, $this->skylar);
		$this->assertEquals($this->jesse, $this->collection[2]);
		$this->assertEquals($this->skylar, $this->collection[3]);
	}

	/**
	 * @covers ::unshift
     * @depends testUnshift
     */
	public function testUnshiftReturnsSelf() {
		$people = $this->people->unshift($this->junior);
		$this->assertEquals($this->people, $people);
	}

	/**
     * @covers ::map
    */
	public function testMap() {
		$mapped = $this->people->map(function($val) {
			return "Hey ".$val->name;
		});
		$this->assertEquals("Hey Walter", $mapped[0]);
		$this->assertEquals("Hey Jesse",  $mapped[1]);
	}

	/**
     * @covers ::mMap
    */
	public function testMMap() {
		$this->people->mMap(function($val) {
			return new Person("Mr. ".$val->name, "Yo");
		});
		$this->assertEquals("Mr. Walter", $this->people[0]->name);
		$this->assertEquals("Mr. Jesse",  $this->people[1]->name);
	}

	/**
	 * @covers ::reduce
	 */
	public function testReduce() {
		$data = $this->people->reduce(function(&$result, $item){
			return $result .= " ".$item->name;
		}, "Whoa!");
		$this->assertEquals("Whoa! Walter Jesse Skylar",$data);
	}

	/**
	 * @covers ::reduce
	 */
	public function testReduceWithoutInitialParam() {
		$data = $this->people->reduce(function($result, $item){
			return $result .= " ".$item->name;
		});
		$this->assertEquals(" Walter Jesse Skylar",$data);
	}

	/**
	 * @covers ::pluck
	 * @depends testMap
	 */
	public function testPluck() {
		$names = $this->people->pluck("name");
		$greetings = $this->people->pluck("greeting");

		$this->assertContains("Walter", $names);
		$this->assertContains("Jesse",  $names);
		$this->assertContains("Skylar", $names);
		$this->assertEquals(array("I am the danger.", "Yeah, Science!", "Walter?"), $greetings);
	}

	/**
	 * @covers ::shuffle
	 */
	public function testShuffle() {
		$this->markTestSkipped();
		$this->people->shuffle();
		$this->assertContains($this->walter, $this->people[0]);
		$this->assertContains($this->jesse, $this->people[1]);
		$this->assertContains($this->skylar, $this->people[2]);
		$this->assertFalse();
	}

	/**
	 * @covers ::slice
	 */
	public function testSlice() {
		$collection1 = $this->people->slice(0,2);
		$collection2 = $this->people->slice(1);
		$this->assertEquals(new TimberCollection(array($this->walter, $this->jesse)), $collection1);
		$this->assertEquals(new TimberCollection(array($this->jesse, $this->skylar)), $collection2);
	}

	/**
	 * @covers ::mSlice
	 */
	public function testMSlice() {
		$this->people->mSlice(0,2);
		$this->assertEquals(new TimberCollection(array($this->walter, $this->jesse)), $this->people);
	}

	/**
	 * @depends testPush
	 * @covers ::where
	 */
	public function testWhere() {
		$this->people->push($this->jesse2);
		$collection1 = $this->people->where(array(
			"name" => "Walter"
		));
		$collection2 = $this->people->where(array(
			"name"     => "Jesse",
			"greeting" => "Yeah, Science!"
		));
		$this->assertEquals(new TimberCollection(array($this->walter)),$collection1);
		$this->assertEquals(new TimberCollection(array($this->jesse)),$collection2);
	}

	/**
	 * @depends testPush
	 * @covers ::where
	 */
	public function testMultipleWhere() {
		$this->people->push($this->jesse2);
		$collection1 = $this->people->where(array(
			"name" => "Jesse"
		));
		$this->assertEquals(new TimberCollection(array($this->jesse,$this->jesse2)),$collection1);
	}

	/**
	 * @depends testPush
	 * @covers ::where
	 */
	public function testLimitedWhere() {
		$this->people->push($this->jesse2);
		$collection1 = $this->people->where(array(
			"name" => "Jesse"
		), 1);
		$this->assertEquals(new TimberCollection(array($this->jesse)),$collection1);
	}

	/**
	 * @covers ::filter
	 */
	public function testFilter() {
		$collection1 = $this->people->filter(function($item) {
			return strlen($item->name) < 6;
		});
		$this->assertEquals(new TimberCollection(array($this->jesse)), $collection1);
	}

	/**
	 * @covers ::reject
	 */
	public function testReject() {
		$collection1 = $this->people->reject(function($item) {
			return strlen($item->name) < 6;
		});
		$this->assertEquals(new TimberCollection(array($this->walter, $this->skylar)), $collection1);
	}

	/**
	 * @covers ::mReject
	 */
	public function testMReject() {
		$this->markTestSkipped();
	}

	/**
	 * @covers ::unique
	 */
	public function testUnique() {
		$this->markTestSkipped();
	}

	/**
	 * @covers ::mUnique
	 */
	public function testMUnique() {
		$this->markTestSkipped();
	}

	/**
	 * @covers ::sample
	 */
	public function testSample() {
		$this->markTestSkipped();
	}

	/**
	 * @covers ::toJSON
	 */
	public function testToJSON() {
		$this->markTestSkipped();
	}
}