<?php

/**
 * Enter description here...
 *
 */

Class Dropio_Set  {

  private $_attributes = null;

	function __construct ( Array $items = null, $count = null, $page = null, $per_page = null, $name_key = null) {

		$i = 0;
		
		$this->_attributes = Array('total_count'=>$count, 'count'=>sizeof($items),  'page'=>$page, 'per_page'=>$per_page);
		
		if (is_array($items) && count($items))
		foreach ($items as $key=>$item) {
			$key_val = $name_key?$item->$name_key:$i;
			$this->$key_val = $item;
			$i++;
		}
		
	}
	
	function getPage () {
		return $this->_attributes['page'];
	}
	
	function getTotalCount () {
		return $this->_attributes['total_count'];
	}
	
	function getCount() {
		return $this->_attributes['count'];
	}
	
	function getPerPage () {
		return $this->_attributes['per_page'];
	}
	
	function __toString() {
		return (string) $this->count();
	}

	
}