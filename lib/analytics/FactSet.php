<?php

namespace analytics;

/**
 * A set of FactRecords, allows for typical crud operations
 */
class FactSet {

	public $factNames;

	public $measureNames;

	public $factRecords = array();

	public function __construct(array $factNames, array $measureNames) {
		$this->factNames	= $factNames;
		$this->measureNames	= $measureNames;
	}

	/**
	 * Convenience method for creating a new, blank record that belongs in this FactSet.
	 * NOTE: does not insert
	 * @param int $time
	 * @return FactRecord
	 */
	public function newRecord($time) {
		return new FactRecord($time, $this->factNames, $this->measureNames);
	}

	/**
	 * Insert a FactRecord
	 * @param FactRecord $record
	 */
	public function insert(FactRecord $record) {
		$hash = $record->getHash();
		if (isset($this->factRecords[$hash])) {
			/** @var $existingRecord FactRecord */
			$existingRecord = $this->factRecords[$hash];
			$existingRecord->merge($record);
		}
		$this->factRecords[$hash]	= $record;
	}

	/**
	 * The number of fact records in this FactSet
	 * @return int
	 */
	public function count() {
		return count($this->factRecords);
	}

	/**
	 * Convert to a flat array for json
	 * @return array
	 */
	public function toArray() {
		$data	= array();

		foreach ($this->factRecords as $record) {
			$data[]	= $record->toArray();
		}

		return $data;
	}

	/**
	 * From an array of FactSet data (see FactSet->toArray()), turn into a FactSet
	 * @param array $data
	 * @return FactSet
	 */
	public static function fromArray(array $data) {
		if (empty($data)) {
			return new FactSet(array(), array());
		}

		$first	= reset($data);
		$set	= new FactSet(array_keys($first['dims']), array_keys($first['measures']));

		foreach ($data as $recordData) {
			$record = $set->newRecord($recordData['time']);
			$record->dims		= $recordData['dims'];
			$record->measures	= $recordData['measures'];
			$record->getHash(true);
			$set->insert($record);
		}

		return $set;
	}
}