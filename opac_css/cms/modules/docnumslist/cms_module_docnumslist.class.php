<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_docnumslist.class.php,v 1.2 2015/10/07 14:36:00 arenou Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_docnumslist extends cms_module_common_module {
	
	public function __construct($id=0){
		$this->module_path = str_replace(basename(__FILE__),"",__FILE__);
		parent::__construct($id);
	}
}