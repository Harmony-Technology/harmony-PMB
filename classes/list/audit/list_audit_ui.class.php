<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_audit_ui.class.php,v 1.1 2021/06/07 09:52:50 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path.'/audit.class.php');

class list_audit_ui extends list_ui {
	
	protected function _get_query_base() {
		$query = "SELECT user_id, user_name, type_obj, object_id, type_user, type_modif, quand , info 
			FROM audit";
		return $query;
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters =
		array('main_fields' =>
				array(
						'types' => 'audit_types',
						'users' => '25',
						'quand' => 'audit_col_date_heure',
						'action' => 'audit_col_type_action',
				)
		);
		$this->available_filters['custom_fields'] = array();
	}
	
	/**
	 * Initialisation des filtres de recherche
	 */
	public function init_filters($filters=array()) {
		
		$this->filters = array(
				'types' => array(),
				'users' => array(),
				'quand_start' => '',
				'quand_end' => '',
				'action' => '',
		);
		parent::init_filters($filters);
	}
	
	protected function init_default_selected_filters() {
		$this->add_selected_filter('types');
		$this->add_selected_filter('users');
		$this->add_empty_selected_filter();
		$this->add_selected_filter('quand');
		$this->add_selected_filter('action');
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns = 
		array('main_fields' =>
			array(
					'type' => 'audit_col_type',
					'object_label' => '103',
					'name' => 'audit_col_nom',
					'user_name' => 'audit_col_username',
					'action' => 'audit_col_type_action',
					'quand' => 'audit_col_date_heure',
					'info' => 'audit_comment'
			)
		);
	}
	
	/**
	 * Initialisation du tri par d�faut appliqu�
	 */
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('quand', 'desc');
	}
	
	/**
	 * Tri SQL
	 */
	protected function _get_query_order() {
		
	    if($this->applied_sort[0]['by']) {
			$order = '';
			$sort_by = $this->applied_sort[0]['by'];
			switch($sort_by) {
				case 'user_name' :
				case 'quand' :
					$order .= $sort_by;
					break;
				default :
					$order .= parent::_get_query_order();
					break;
			}
			if($order) {
				return $this->_get_query_order_sql_build($order);
			} else {
				return "";
			}
		}	
	}
	
	/**
	 * Filtres provenant du formulaire
	 */
	public function set_filters_from_form() {
		$this->set_filter_from_form('types');
		$this->set_filter_from_form('users');
		$this->set_filter_from_form('quand_start');
		$this->set_filter_from_form('quand_end');
		$this->set_filter_from_form('action');
		parent::set_filters_from_form();
	}
	
	protected function init_default_columns() {
		$this->add_column('type');
		$this->add_column('object_label');
		$this->add_column('name');
		$this->add_column('user_name');
		$this->add_column('action');
		$this->add_column('quand');
		$this->add_column('info');
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_column('quand', 'datatype', 'datetime');
	}
	
	protected function get_selector_query($type) {
		$query = '';
		switch ($type) {
			case 'users':
				$query = 'select userid as id, concat(prenom, " ", nom) as label from users order by label';
				break;
		}
		return $query;
	}
	
	protected static function get_constants() {
		return array(
				'AUDIT_NOTICE', 'AUDIT_EXPL', 'AUDIT_BULLETIN', 'AUDIT_ACQUIS', 'AUDIT_PRET',
				'AUDIT_AUTHOR', 'AUDIT_COLLECTION', 'AUDIT_SUB_COLLECTION', 'AUDIT_INDEXINT', 'AUDIT_PUBLISHER', 'AUDIT_SERIE', 'AUDIT_CATEG', 'AUDIT_TITRE_UNIFORME',
				'AUDIT_DEMANDE', 'AUDIT_ACTION', 'AUDIT_NOTE',
				'AUDIT_EDITORIAL_ARTICLE', 'AUDIT_EDITORIAL_SECTION',
				'AUDIT_EXPLNUM', 'AUDIT_CONCEPT'
		);
	}
	protected function get_search_filter_types() {
		global $msg;
	
		$in_database = array();
		$query = "SELECT DISTINCT type_obj FROM audit";
		$result = pmb_mysql_query($query);
		while ($row = pmb_mysql_fetch_object($result)) {
			$in_database[] = $row->type_obj;
		}
		$options = array();
		$constants = static::get_constants();
		foreach ($constants as $constant_name) {
			if(in_array(constant($constant_name), $in_database)) {
				$options[$constant_name] = audit::get_label_from_type($constant_name);
			}
		}
		return $this->get_multiple_selector('', 'types', $msg['all'], $options);
	}
	
	protected function get_search_filter_users() {
		global $msg;
		return $this->get_multiple_selector($this->get_selector_query('users'), 'users', $msg["all"]);
	}
	
	protected function get_search_filter_quand() {
		return $this->get_search_filter_interval_date('quand');
	}
	
	protected function get_search_filter_action() {
		global $msg;
		
		return "
			<input type='radio' id='".$this->objects_type."_action_0' name='".$this->objects_type."_action' value='0' ".(!$this->filters['action'] ? "checked='checked'" : "")." />
			<label for='".$this->objects_type."_action'>".$msg['all']."</label>
			<input type='radio' id='".$this->objects_type."_action_1' name='".$this->objects_type."_action' value='1' ".($this->filters['action'] == 1 ? "checked='checked'" : "")." />
			<label for='".$this->objects_type."_action_1'>".$msg['audit_type1']."</label>
			<input type='radio' id='".$this->objects_type."_action_2' name='".$this->objects_type."_action' value='2' ".($this->filters['action'] == 2 ? "checked='checked'" : "")." />
			<label for='".$this->objects_type."_action_2'>".$msg['audit_type2']."</label>
			<input type='radio' id='".$this->objects_type."_action_3' name='".$this->objects_type."_action' value='3' ".($this->filters['action'] == 3 ? "checked='checked'" : "")." />
			<label for='".$this->objects_type."_action_3'>".$msg['audit_type3']."</label>";
	}
	
	/**
	 * Filtre SQL
	 */
	protected function _get_query_filters() {
		$filter_query = '';
		
		$this->set_filters_from_form();
		
		$filters = array();
		if(is_array($this->filters['types']) && count($this->filters['types'])) {
			$consts = array();
			foreach ($this->filters['types'] as $type) {
				$consts[] = constant($type);
			}
			$filters [] = 'type_obj IN ("'.implode('","', $consts).'")';
		}
		if(is_array($this->filters['users']) && count($this->filters['users'])) {
			$filters [] = 'user_id IN ("'.implode('","', addslashes_array($this->filters['users'])).'")';
		}
		if($this->filters['quand_start']) {
			$filters [] = 'quand >= "'.$this->filters['quand_start'].'"';
		}
		if($this->filters['quand_end']) {
			$filters [] = 'quand <= "'.$this->filters['quand_end'].' 23:59:59"';
		}
		if($this->filters['action']) {
			$filters [] = 'type_modif = "'.$this->filters['action'].'"';
		}
		if(count($filters)) {
			$filter_query .= ' where '.implode(' and ', $filters);
		}
		return $filter_query;
	}
	
	protected function get_js_sort_script_sort() {
		$display = parent::get_js_sort_script_sort();
		$display = str_replace('!!categ!!', 'audit', $display);
		$display = str_replace('!!sub!!', '', $display);
		$display = str_replace('!!action!!', 'list', $display);
		return $display;
	}
	
	protected function _get_object_property_type($object) {
		$constants = static::get_constants();
		foreach ($constants as $constant_name) {
			if(constant($constant_name) == $object->type_obj) {
				return audit::get_label_from_type($constant_name);
			}
		}
		return $object->type_obj;
	}
	
	protected function _get_object_property_object_label($object) {
		switch ($object->type_obj) {
			case AUDIT_NOTICE:
				return notice::get_notice_title($object->object_id);
		}
	}
	
	protected function _get_object_property_name($object) {
		if($object->type_user == 1) {
			return emprunteur::get_name($object->user_id);
		} else {
			return user::get_name($object->user_id);
		}
	}
	
	protected function _get_object_property_action($object) {
		global $msg;
		return $msg['audit_type'.$object->type_modif];
	}
	
	protected function _get_object_property_info($object) {
		$display = "";
		$info=json_decode($object->info);
		if(is_object($info)){
			if(!empty($info->comment)) {
				$display .= $info->comment."<br>";
			}
			if(!empty($info->fields)){
				foreach($info->fields as $fieldname => $values){
					if(is_object($values)){
						$display .= $fieldname." : ".$values->old." => ".$values->new."<br>";
					}
				}
			}
		}else {
			$display = $object->info;
		}
		return $display;
	}
	
	protected function _get_query_human_types() {
		if(!empty($this->filters['types'])) {
			$labels = array();
			foreach ($this->filters['types'] as $type) {
				$labels[] = audit::get_label_from_type($type);
			}
			return implode(', ', $labels);
		}
	}
	
	protected function _get_query_human_users() {
		if(!empty($this->filters['users'])) {
			$labels = array();
			foreach ($this->filters['users'] as $user) {
				$labels[] = user::get_name($user);
			}
			return implode(', ', $labels);
		}
		return '';
	}
	
	protected function _get_query_human_quand() {
		return $this->_get_query_human_interval_date('quand');
	}
	
	protected function _get_query_human_action() {
		global $msg;
		if(!empty($this->filters['action'])) {
			return $msg['audit_type'.$this->filters['action']];
		}
		return '';
	}
}