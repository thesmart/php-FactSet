<?php

namespace analytics;

require_once __DIR__ . "/../vendor/autoload.php";

/**
 * Test the FactRecord class
 */
class FactSetTest extends \PHPUnit_Framework_TestCase {

	public $factNames = array(
		'gender',
		'ethnicity',
	);

	public $measureNames = array(
		'revenue',
		'sales',
		'clicks'
	);

	public function testNaive() {
		$time	= time();

		$set = new FactSet($this->factNames, $this->measureNames);
		for ($i = 0; $i < 10000; ++$i) {
			if ($i % 100 === 0) {
				// 100 possible time slices
				++$time;
			}

			$record = $set->newRecord($time);

			// two fact groups
			if ($i % 2 === 0) {
				$record->setDim('gender', 'male');
				$record->setDim('ethnicity', 'here');
			} else {
				$record->setDim('gender', 'female');
				$record->setDim('ethnicity', 'there');
			}

			$record->setMeasure('revenue', 10);
			$record->setMeasure('sales', 2);
			$record->setMeasure('clicks', 5);

			$set->insert($record);
		}

		$this->assertEquals(200, $set->count());
	}
}