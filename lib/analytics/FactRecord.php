<?php

namespace analytics;

/**
 * A FactRecord, is a single data-type of dims, measures, and a time.
 */
class FactRecord {
	/**
	 * The computed hash
	 * @var string
	 */
	protected $hash		= null;

	/**
	 * A unit of time, minus modulo of the "grain" (e.g. hour, day, etc)
	 * @var int|null
	 */
	protected $time		= null;

	/**
	 * The measures (integers or floats) for this record
	 * @var array
	 */
	public $measures	= array();

	/**
	 * The dimensions (fixed, limited number of values) that define this record
	 * @var array
	 */
	public $dims		= array();

	public function __construct($time = null, array $factNames, array $measureNames) {
		$this->time			= is_null($time) ? null : intval($time);
		$this->dims			= array_fill_keys($factNames, null);
		$this->measures		= array_fill_keys($measureNames, 0);
	}

	/**
	 * Set a dimension
	 * @param string $name
	 * @param mixed $val
	 */
	public function setDim($name, $val) {
		if (isset($this->hash)) {
			$this->hash	= null;
		}

		$this->dims[$name] = $val;
	}

	/**
	 * Set a measure
	 * @param string $name
	 * @param int|float $val
	 */
	public function setMeasure($name, $val) {
		$this->measures[$name]	= $val;
	}

	/**
	 * Get a unique Hash for identifying this FactRecord
	 * @param bool $forceUpdate		Set true to force computation of the hash
	 * @return string
	 */
	public function getHash($forceUpdate = false) {
		if (!$forceUpdate && !is_null($this->hash)) {
			return $this->hash;
		}

		$baseStr	= array();
		if (!is_null($this->time)) {
			$baseStr[]	= $this->time;
		}

		foreach ($this->dims as $k => $v) {
			$baseStr[]	= "{$k}={$v}";
		}

		return $this->hash = md5(implode('', $baseStr));
	}

	/**
	 * Convert to a flat array for json
	 * @return array
	 */
	public function toArray() {
		$data	= array(
			'hash'		=> $this->getHash(),
			'dims'		=> $this->dims,
			'measures'	=> $this->measures
		);

		if (!is_null($this->time)){
			$data['time']	= $this->time;
		}

		return $data;
	}

	/**
	 * Combine two FactRecord objects because their time and dimensions match.
	 *
	 * @param FactRecord $record
	 * @return bool		False if not merged
	 */
	public function merge(FactRecord $record) {
		if ($this->time != $record->time || $this->getHash() != $record->getHash()) {
			return false;
		}

		// sum up all the matching measures
		foreach ($record->measures as $key => $val) {
			if (isset($this->measures[$key])) {
				$this->measures[$key]	+= $val;
			} else {
				$this->measures[$key]	= $val;
			}
		}

		return true;
	}

	/**
	 * Compare two fact records for sorting
	 * @static
	 * @param FactRecord $a
	 * @param FactRecord $b
	 * @return int
	 */
	public static function compare(FactRecord $a, FactRecord $b) {
		return $a->time - $b->time;
	}
}