<?php
// +-------------------------------------------------+
//  2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: searcher_authorities_collections.class.php,v 1.2 2018/10/08 13:59:39 vtouchard Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path.'/searcher/searcher_autorities.class.php');

class searcher_authorities_collections extends searcher_autorities {

	public function __construct($user_query){
		$this->authority_type = AUT_TABLE_COLLECTIONS;
		parent::__construct($user_query);
		$this->object_table = "collections";
		$this->object_table_key = "collection_id";
	}
	
	public function _get_search_type(){
		return parent::_get_search_type()."_collections";
	}
	
	public function get_authority_tri() {
		return ' index_coll';
	}

}