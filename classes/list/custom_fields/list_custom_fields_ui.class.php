<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_custom_fields_ui.class.php,v 1.29 2021/05/07 08:06:19 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $include_path;
require_once($include_path.'/templates/list/custom_fields/list_custom_fields_ui.tpl.php');

class list_custom_fields_ui extends list_ui {
		
	protected static $prefix;
	
	protected static $option_visibilite;
	
	public static function set_prefix($prefix) {
		static::$prefix = $prefix;
	}
	
	public static function set_option_visibilite($option_visibilite) {
		static::$option_visibilite = $option_visibilite;
	}
	
	protected function _get_query_base() {
		$query = "select idchamp as id, name, titre, type, datatype, multiple, obligatoire, ordre ,search, export,exclusion_obligatoire, opac_sort, comment, custom_classement from
				".static::$prefix."_custom";
		return $query;
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters =
		array('main_fields' =>
				array(
						'input_type' => 'parperso_input_type',
						'data_type' => 'parperso_data_type',
				)
		);
		$this->available_filters['custom_fields'] = array();
	}
	
	/**
	 * Initialisation des filtres de recherche
	 */
	public function init_filters($filters=array()) {
		$this->filters = array(
			'input_type' => array(),
			'data_type' => array()
		);
		parent::init_filters($filters);
	}
	
	protected function init_default_selected_filters() {
		$this->add_selected_filter('input_type');
		$this->add_selected_filter('data_type');
	}
	
	/**
	 * Initialisation du groupement appliqu? ? la recherche
	 */
	public function init_applied_group($applied_group=array()) {
		if(!isset($this->applied_group)) {
			$this->applied_group = array(0 => 'custom_classement');
		}
		parent::init_applied_group($applied_group);
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns =
		array('main_fields' =>
				array(
						'name' => 'parperso_field_name',
						'titre' => 'parperso_field_title',
						'type' => 'parperso_input_type',
						'datatype' => 'parperso_data_type',
						'custom_classement' => 'parperso_field_classement'
				)
		);
		if(isset(static::$option_visibilite["multiple"]) && static::$option_visibilite["multiple"] == "block") {
			$this->available_columns['main_fields']['multiple'] = 'parperso_opac_visibility';
		}
		if(isset(static::$option_visibilite["opac_sort"]) && static::$option_visibilite["opac_sort"] == "block") {
			$this->available_columns['main_fields']['opac_sort'] = 'parperso_opac_sort';
		}
		if(isset(static::$option_visibilite["obligatoire"]) && static::$option_visibilite["obligatoire"] == "block") {
			$this->available_columns['main_fields']['obligatoire'] = 'parperso_mandatory';
		}
		if(isset(static::$option_visibilite["filters"]) && static::$option_visibilite["filters"] == "block") {
			$this->available_columns['main_fields']['filters'] = 'parperso_opac_filters';
		}
		if(isset(static::$option_visibilite["search"]) && static::$option_visibilite["search"] == "block") {
			$this->available_columns['main_fields']['search'] = 'parperso_field_search_tableau';
		}
		if(isset(static::$option_visibilite["export"]) && static::$option_visibilite["export"] == "block") {
			$this->available_columns['main_fields']['export'] = 'parperso_exportable';
		}
		if(isset(static::$option_visibilite["exclusion"]) && static::$option_visibilite["exclusion"] == "block") {
			$this->available_columns['main_fields']['exclusion_obligatoire'] = 'parperso_exclusion_entete';
		}
	}
	
	protected function add_column_dnd() {
		global $msg, $charset;
		
		$this->columns[] = array(
				'property' => 'ordre',
				'label' => $msg['parperso_options_list_order'],
                'html' => "<img src='".get_url_icon('bottom-arrow.png')."' title='".htmlentities($msg['move_bottom_arrow'], ENT_QUOTES, $charset)."' alt='".htmlentities($msg['move_bottom_arrow'], ENT_QUOTES, $charset)."' onClick='document.location=\"".static::get_controller_url_base()."&action=down&id=!!id!!\"' style='cursor:pointer;' />
					<img src='".get_url_icon('top-arrow.png')."' title='".htmlentities($msg['move_top_arrow'], ENT_QUOTES, $charset)."' alt='".htmlentities($msg['move_top_arrow'], ENT_QUOTES, $charset)."' onClick='document.location=\"".static::get_controller_url_base()."&action=up&id=!!id!!\"' style='cursor:pointer;' />",
                'exportable' => false
		);
	}
	
	protected function init_default_columns() {
		$this->add_column_dnd();
		$this->add_column('name');
		$this->add_column('titre');
		$this->add_column('type');
		$this->add_column('datatype');
		if(isset(static::$option_visibilite["multiple"]) && static::$option_visibilite["multiple"] == "block") {
			$this->add_column('multiple');
		}
		if(isset(static::$option_visibilite["opac_sort"]) && static::$option_visibilite["opac_sort"] == "block") {
			$this->add_column('opac_sort');
		}
		if(isset(static::$option_visibilite["obligatoire"]) && static::$option_visibilite["obligatoire"] == "block") {
			$this->add_column('obligatoire');
		}
		if(isset(static::$option_visibilite["filters"]) && static::$option_visibilite["filters"] == "block") {
			$this->add_column('filters');
		}
		if(isset(static::$option_visibilite["search"]) && static::$option_visibilite["search"] == "block") {
			$this->add_column('search');
		}
		if(isset(static::$option_visibilite["export"]) && static::$option_visibilite["export"] == "block") {
			$this->add_column('export');
		}
		if(isset(static::$option_visibilite["exclusion"]) && static::$option_visibilite["exclusion"] == "block") {
			$this->add_column('exclusion_obligatoire');
		}
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('search_form', 'options', true);
		$this->set_setting_display('search_form', 'export_icons', false);
		$this->set_setting_column('default', 'align', 'left');
	}
	
	/**
	 * Initialisation de la pagination par d?faut
	 */
	protected function init_default_pager() {
	    parent::init_default_pager();
	    $this->pager['nb_per_page'] = 100;
	}
	
	/**
	 * Initialisation du tri par d?faut appliqu?
	 */
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('ordre');
	}
	
	/**
	 * Tri SQL
	 */
	protected function _get_query_order() {
	
	    if($this->applied_sort[0]['by']) {
			$order = '';
			$sort_by = $this->applied_sort[0]['by'];
			switch($sort_by) {
				default :
					$order .= $sort_by;
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
		$this->set_filter_from_form('input_type');
		$this->set_filter_from_form('data_type');
		parent::set_filters_from_form();
	}
	
	/**
	 * Liste des types
	 */
	protected function get_search_filter_input_type() {
		global $type_list_empr;
		
		reset($type_list_empr);
		return $this->get_multiple_selector('', 'input_type', '', $type_list_empr);
	}
	
	/**
	 * Liste des types de donn?es
	 */
	protected function get_search_filter_data_type() {
		global $datatype_list;
	
		reset($datatype_list);
		return $this->get_multiple_selector('', 'data_type', '', $datatype_list);
	}
	
	/**
	 * Filtre SQL
	 */
	protected function _get_query_filters() {
		
		$filter_query = '';
		
		$this->set_filters_from_form();
		
		$filters = array();
		if(is_array($this->filters['input_type']) && count($this->filters['input_type'])) {
		    $filters [] = 'type IN ("'.implode('","', $this->filters['input_type']).'")';
		}
		if(is_array($this->filters['data_type']) && count($this->filters['data_type'])) {
		    $filters [] = 'datatype IN ("'.implode('","', $this->filters['data_type']).'")';
		}
		if(count($filters)) {
			$filter_query .= ' where '.implode(' and ', $filters);
		}
		return $filter_query;
	}
		
	/**
	 * Construction dynamique de la fonction JS de tri
	 */
	protected function get_js_sort_script_sort() {
	    global $id_authperso;
	    
		$display = parent::get_js_sort_script_sort();
		$display = str_replace('!!categ!!', 'custom_fields', $display);
		$sub = "&prefix=".static::$prefix."&option_visibilite=".encoding_normalize::json_encode(static::$option_visibilite);
		if (static::$prefix == 'authperso') {
    		$sub = "&prefix=".static::$prefix."&option_visibilite=".encoding_normalize::json_encode(static::$option_visibilite)."&id_authperso=$id_authperso";
		}
		$display = str_replace('!!sub!!', $sub, $display);
		$display = str_replace('!!action!!', 'list', $display);
		return $display;
	}
	
	protected function _get_object_property_type($object) {
		global $type_list_empr;
		return $type_list_empr[$object->type];
	}
	
	protected function _get_object_property_datatype($object) {
		global $datatype_list;
		return $datatype_list[$object->datatype];
	}
	
	protected function get_cell_content($object, $property) {
		global $msg;
		
		$content = '';
		switch($property) {
			case 'name':
				$content .= "<b>".$object->{$property}."</b>";
				break;
			case 'multiple':
			case 'opac_sort':
			case 'obligatoire':
			case 'search':
			case 'export':
			case 'exclusion_obligatoire':
			case 'filters':
				if ($object->{$property}==1) {
					$content .= $msg["40"];
				} else {
					$content .= $msg["39"];
				}
				break;
			default :
				$content .= parent::get_cell_content($object, $property);
				break;
		}
		return $content;
	}
	
	protected function _get_query_human_input_type() {
		global $type_list_empr;
		$labels = array();
		foreach ($this->filters['input_type'] as $input_type) {
			$labels[] = $type_list_empr[$input_type];
		}
		return $labels;
	}
	
	protected function _get_query_human_data_type() {
		global $datatype_list;
		$labels = array();
		foreach ($this->filters['data_type'] as $data_type) {
			$labels[] = $datatype_list[$data_type];
		}
		return $labels;
	}
		
	protected function get_display_cell($object, $property) {
		$attributes = array(
				'onclick' => "window.location=\"".static::get_controller_url_base()."&action=edit&id=".$object->id."\""
		);
		$content = $this->get_cell_content($object, $property);
		$display = $this->get_display_format_cell($content, $property, $attributes);
		return $display;
	}
	
	protected function get_display_left_actions() {
		global $msg;
		
		return "<input type='button' class='bouton' value='".$msg['parperso_new_field']."' onClick='document.location=\"".static::get_controller_url_base()."&action=nouv\"'/>";
	}
	
	public static function get_controller_url_base() {
		global $base_path, $base_auth;
		global $type_field;
		
		if($base_auth == 'FICHES_AUTH') {
			return $base_path.'/fichier.php?categ=gerer&mode=champs';
		}
		return parent::get_controller_url_base().($type_field ? '&type_field='.$type_field : '');
	}
}