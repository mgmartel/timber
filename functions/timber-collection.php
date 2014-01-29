<?php
/*
* @class TimberCollection
*
* I don't know if it is more efficent to use array_map with a copy, then create a new
* instance, or if using the iterator would be better...
*
*/
class TimberCollection extends \ArrayObject implements \SplSubject {

	protected $comparator = null;
	protected $autosort = false;
	private   $observers = array("add" => array(), "remove" => array());

	public function setComparator($func) {
		return $this->comparator = $func;
	}

	public function setAutosort($bool) {
		$this->autosort = $bool;
		$this->_sort();
		return $this->autosort;
	}

	public function getLength() {
		return $this->count();
	}

	public function getSize() {
		return $this->count();
	}

	public function push() {
		$newArray = array_merge($this->getArrayCopy(), func_get_args());
		$this->exchangeArray($newArray);
		$this->_sort();
		return $this;
	}

	public function add($obj){
		return $this->push($obj);
	}

	public function pop($amount = 1) {
		$results = array();
		$index = $this->count() - 1;
		while($amount > 0) {
			array_unshift($results, $this->offsetPull($index));
			--$amount;
			--$index;
		}
		$this->_sort();
		return (count($results) > 1 ? $results : $results[0]);
	}

	public function shift($amount = 1) {
		$results = array();
		for($i = 0; $amount > $i; $i++) {
			$results[] = $this->offsetPull($i);
		}
		$this->_sort();
		return (count($results) > 1 ? $results : $results[0]);
	}

	public function unshift() {
		$newArray = array_merge(func_get_args(), $this->getArrayCopy());
		$this->exchangeArray($newArray);
		$this->_sort();
		return $this;
	}

	public function slice($begin, $length = null) {
		$length = ($length ? $length : $this->length);
		return new static(array_slice($this->getArrayCopy(), $begin, $length));
	}

	public function mSlice($begin, $length = null) {
		$length = ($length ? $length : $this->length);
		$this->exchangeArray( array_slice($this->getArrayCopy(), $begin, $length) );
		return $this;
	}

	public function map($func) {
		return new static( $this->_map($func) );
	}

	public function mMap($func) {
		$newArray = $this->_map($func);
		$this->exchangeArray($newArray);
		return $this;
	}

	public function pluck($attribute) {
		return $this->_map( function($val) use (&$attribute) {
			return $val->{$attribute};
		});
	}

	public function reduce($func, $initial = null) {
		return array_reduce($this->getArrayCopy(), $func, $initial);
	}

	public function filter($func) {
		$result = new static;
		$this->_map(function($val) use($func, $result) {
			$func($val) ? $result->push($val) : null;
		});
		return $result;
	}

	public function reject($func) {
		$result = new static;
		$this->_map(function($val) use($func, $result) {
			$func($val) ? null: $result->push($val);
		});
		return $result;
	}

	public function where($attributes, $limit = null) {
		$result = new static;

		foreach ($this as $obj) {
			foreach ($attributes as $attr => $val) {
				$add = ( $obj->{$attr} === $val ? true : false);
			}
			$add ? $result->push($obj) : null;
			if ($limit && $result->length >= $limit) {
				break;
			}
		}
		return $result;
	}

	public function __get($name) {
    	if (method_exists($this, ($method = "get".ucwords($name) ))) {
      		return $this->$method();
    	}
    	else return;
  	}

  	public function attach(\SplObserver $observer) {

  	}

  	public function detach(\SplObserver $observer) {

  	}

  	public function notify() {

  	}

  	protected function offsetPull($index) {
  		$val = $this->offsetGet($index);
		$this->offsetUnset($index);
		return $val;
  	}

  	protected function _map($func) {
  		return array_map( $func, $this->getArrayCopy() );
  	}

  	protected function _sort() {
  		if($this->autosort && $this->comparator) {
			$this->uasort($this->comparator);
		}
  	}
}