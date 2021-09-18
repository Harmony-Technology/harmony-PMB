<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_visits_statistics_ui.class.php,v 1.3 2021/05/26 09:54:22 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path.'/visit_statistics.class.php');

class list_visits_statistics_ui extends list_ui {
	
	protected function _get_query_base() {
		$query = 'select visits_statistics_id from visits_statistics';
		return $query;
	}
	
	protected function get_object_instance($row) {
		return new visit_statistics($row->visits_statistics_id);
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters =
		array('main_fields' =>
				array(
						'types' => 'visits_statistics_types',
						'locations' => 'visits_statistics_locations',
						'date' => 'visits_statistics_dates'
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
				'locations' => array(),
				'date_start' => '',
				'date_end' => '',
		);
		parent::init_filters($filters);
	}
	
	protected function init_default_selected_filters() {
		$this->add_selected_filter('types');
		$this->add_selected_filter('locations');
		$this->add_empty_selected_filter();
		$this->add_selected_filter('date');
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns = 
		array('main_fields' =>
			array(
					'type' => 'visit_statistics_type',
					'date' => 'visit_statistics_date',
					'location' => 'visit_statistics_location',
			)
		);
	}
	
	/**
	 * Initialisation du tri par d�faut appliqu�
	 */
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('date', 'desc');
	}
	
	/**
	 * Tri SQL
	 */
	protected function _get_query_order() {
		
	    if($this->applied_sort[0]['by']) {
			$order = '';
			$sort_by = $this->applied_sort[0]['by'];
			switch($sort_by) {
				case 'id':
					$order .= 'visits_statistics_id';
					break;
				case 'type' :
				case 'location' :
				case 'date':
					$order .= 'visits_statistics_'.$sort_by;
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
		$this->set_filter_from_form('locations');
		$this->set_filter_from_form('date_start');
		$this->set_filter_from_form('date_end');
		parent::set_filters_from_form();
	}
	
	protected function init_default_columns() {
		$this->add_column_selection();
		$this->add_column('type');
		$this->add_column('location');
		$this->add_column('date');
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_column('date', 'datatype', 'datetime');
	}
	
	protected function get_search_filter_types() {
		global $msg;
	
		$options = array();
		$query = "SELECT DISTINCT visits_statistics_type FROM visits_statistics ORDER BY visits_statistics_type";
		$result = pmb_mysql_query($query);
		while ($row = pmb_mysql_fetch_object($result)) {
			$label = visits_statistics::get_label_from_type($row->visits_statistics_type);
			$options[$row->visits_statistics_type] = ($label ? $label : $row->visits_statistics_type);
		}
		return $this->get_multiple_selector('', 'types', $msg['all'], $options);
	}
	
	protected function get_search_filter_locations() {
		global $msg;
	
		$query = "SELECT idlocation AS id, location_libelle AS label FROM docs_location ORDER BY label";
		return $this->get_multiple_selector($query, 'locations', $msg['all_location']);
	}
	
	protected function get_search_filter_date() {
		return $this->get_search_filter_interval_date('date');
	}
	
	/**
	 * Filtre SQL
	 */
	protected function _get_query_filters() {
		$filter_query = '';
		
		$this->set_filters_from_form();
		
		$filters = array();
		if(is_array($this->filters['types']) && count($this->filters['types'])) {
			$filters [] = 'visits_statistics_type IN ("'.implode('","', $this->filters['types']).'")';
		}
		if(is_array($this->filters['locations']) && count($this->filters['locations'])) {
			$filters [] = 'visits_statistics_location IN ("'.implode('","', addslashes_array($this->filters['locations'])).'")';
		}
		if($this->filters['date_start']) {
			$filters [] = 'visits_statistics_date >= "'.$this->filters['date_start'].'"';
		}
		if($this->filters['date_end']) {
			$filters [] = 'visits_statistics_date <= "'.$this->filters['date_end'].' 23:59:59"';
		}
		if($this->filters['ids']) {
			$filters [] = 'visits_statistics_id IN ('.$this->filters['ids'].')';
		}
		if(count($filters)) {
			$filter_query .= ' where '.implode(' and ', $filters);
		}
		return $filter_query;
	}
	
	protected function get_js_sort_script_sort() {
		$display = parent::get_js_sort_script_sort();
		$display = str_replace('!!categ!!', 'visits_statistics', $display);
		$display = str_replace('!!sub!!', '', $display);
		$display = str_replace('!!action!!', 'list', $display);
		return $display;
	}
	
	protected function _get_object_property_type($object) {
		global $msg;
		
		$label = visits_statistics::get_label_from_type($object->get_type());
		if($label) {
			return $label;
		} elseif(isset($msg['dashboard_visits_statistics_'.$object->get_type()])) {
			return $msg['dashboard_visits_statistics_'.$object->get_type()];
		} else {
			return $object->get_type();
		}
	}
	
	protected function _get_object_property_location($object) {
		$docs_location = new docs_location($object->get_location());
		return $docs_location->libelle;
	}
	
	protected function _get_query_human_types() {
		if(!empty($this->filters['types'])) {
			$labels = array();
			foreach ($this->filters['types'] as $type) {
				$labels[] = visits_statistics::get_label_from_type($type);
			}
			return implode(', ', $labels);
		}
	}
	
	protected function _get_query_human_locations() {
		if(!empty($this->filters['locations'])) {
			$labels = array();
			foreach ($this->filters['locations'] as $location) {
				$docs_location = new docs_location($location);
				$labels[] = $docs_location->libelle;
			}
			return implode(', ', $labels);
		}
		return '';
	}
	
	protected function _get_query_human_date() {
		return $this->_get_query_human_interval_date('date');
	}
}