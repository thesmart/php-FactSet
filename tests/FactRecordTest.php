<?php

namespace analytics;

require_once __DIR__ . "/../vendor/autoload.php";

/**
 * Test the FactRecord class
 */
class FactRecordTest extends \PHPUnit_Framework_TestCase {

	public $factNames = array(
		'gender',
		'ethnicity',
		'income'
	);

	public $measureNames = array(
		'revenue',
		'sales',
		'clicks'
	);

	public function testNaive() {
		$time	= time();

		$a = new FactRecord($time, $this->factNames, $this->measureNames);
		$b = new FactRecord($time, $this->factNames, $this->measureNames);

		$this->assertEquals($a->getHash(), $b->getHash());
		$this->assertEquals($a->getHash(), $b->getHash());
		$this->assertEquals(0, FactRecord::compare($a, $b));
		$this->assertEquals(0, FactRecord::compare($b, $a));

		$a->setDim('gender', 'male');
		$this->assertNotEquals($a->getHash(), $b->getHash());
		$this->assertEquals(0, FactRecord::compare($a, $b));

		$c = new FactRecord($time + 1, $this->factNames, $this->measureNames);
		$c->setDim('gender', 'male');
		$this->assertNotEquals($a->getHash(), $c->getHash());
		$this->assertEquals(-1, FactRecord::compare($a, $c));

		$isMerge = $a->merge($c);
		$this->assertFalse($isMerge);

		$b->setDim('gender', 'male');
		$a->setMeasure('revenue', 10);
		$b->setMeasure('revenue', 5);
		$isMerge = $a->merge($b);
		$this->assertTrue($isMerge);
		$this->assertEquals(15, $a->measures['revenue']);
		$this->assertEquals(5, $b->measures['revenue']);

		$arr = $a->toArray();
		$this->assertInternalType('array', $arr);
	}
}