<?php
// +-------------------------------------------------+
// � 2002-2014 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: nomenclature_instruments.class.php,v 1.4 2019/06/27 13:57:54 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/encoding_normalize.class.php");

/**
 * class nomenclature_instruments
 * Repr�sente tous les instruments 
 */
class nomenclature_instruments{

	/** Aggregations: */

	/** Compositions: */

	 /*** Attributes: ***/
	
	public $instruments;
			
	/**
	 * Constructeur
	 *
	 * @param
	 
	 * @return void
	 * @access public
	 */
	public function __construct() {
		
		$this->fetch_datas();
	} // end of member function __construct

	protected function fetch_datas(){
		global $dbh;
		$this->instruments =array();
		
		$query = "select id_instrument from nomenclature_instruments order by instrument_name";
		$result = pmb_mysql_query($query,$dbh);
		if(pmb_mysql_num_rows($result)){
			while($row = pmb_mysql_fetch_object($result)){
				$this->add_instrument( new nomenclature_instrument($row->id_instrument));				
			}
		}		
	}
	
	public function add_instrument($instrument ) {
		$this->instruments[] = $instrument;
	
	} // end of member function add_instrument
	
	public function get_data($duplicate = false) {
		$data=array();
		
		foreach($this->instruments  as $instrument){
		    $data[]=$instrument->get_data($duplicate);
		}
		return($data);
	}
			
	public function get_json_informations(){
		$data = json_encode(encoding_normalize::utf8_normalize($this->get_data()));
		return $data;
	}	

} // end of nomenclature_instruments