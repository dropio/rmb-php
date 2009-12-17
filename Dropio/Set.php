<?php

/**
 * Dropio set is an internally used generic object used to return sets of data, 
 * such as comments, assets, and subscriptions.
 *
 */

Class Dropio_Set  {

  private $_attributes = null;

  /**
   * Creates the set from an array of items sent from the API.
   *
   * @param array $items
   * @param integer $count
   * @param integer $page
   * @param integer $per_page
   * @param string $name_key
   */
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
	
	/**
	 * Returns the current page
	 *
	 * @return integer
	 */
	
	function getPage () {
		return $this->_attributes['page'];
	}
	
	/**
	 * Returns the total number of items.  This differs from the number of items 
	 * in the current set (->getCount())
	 *
	 * @return integer
	 */
	
	function getTotalCount () {
		return $this->_attributes['total_count'];
	}
	
	/**
	 * Get the number of items in the current set.
	 *
	 * @return integer
	 */
	
	function getCount() {
		return $this->_attributes['count'];
	}
	
	/**
	 * How many items per page are returned in the current set.
	 *
	 * @return integer
	 */
	
	function getPerPage () {
		return $this->_attributes['per_page'];
	}
	
	/**
	 * If requested as a string, return the count in string form.
	 *
	 * @return string
	 */
	
	function __toString() {
		return (string) $this->count();
	}

	
}