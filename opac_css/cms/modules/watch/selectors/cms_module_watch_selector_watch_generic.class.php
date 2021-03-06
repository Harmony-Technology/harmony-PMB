<?php
// +-------------------------------------------------+
// © 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: cms_module_watch_selector_watch_generic.class.php,v 1.4 2015/03/10 14:45:55 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class cms_module_watch_selector_watch_generic extends cms_module_common_selector{
	
	public function __construct($id=0){
		parent::__construct($id);
		if (!is_array($this->parameters)) $this->parameters = array();
		$this->once_sub_selector = true;
	}
	
	public function get_sub_selectors(){
		return array(
			"cms_module_watch_selector_watch",
			"cms_module_common_selector_env_var",
			"cms_module_watch_selector_watch_by_item",
			"cms_module_common_selector_type_article_generic",
			"cms_module_common_selector_type_article",
			"cms_module_common_selector_type_section_generic",
			"cms_module_common_selector_type_section"
		);
	}	
	
	/*
	 * Retourne la valeur sélectionné
	 */
	public function get_value(){
		if(!$this->value){
			if($this->parameters['sub_selector']){
				$sub_selector = new $this->parameters['sub_selector']($this->get_sub_selector_id($this->parameters['sub_selector']));
				$this->value = $sub_selector->get_value();
			}else{
				$this->value = "";
			}
			if(is_array($this->value)){
				$this->value = array_shift($this->value);
			}
		}
		return $this->value;
	}
}