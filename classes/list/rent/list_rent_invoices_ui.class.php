<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_rent_invoices_ui.class.php,v 1.9 2021/05/25 11:12:21 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_rent_invoices_ui extends list_rent_ui {
		
	protected $marclist_rent_destination;
	
	protected function _get_query_base() {
		$query = "SELECT distinct id_invoice FROM rent_invoices 
			JOIN rent_accounts_invoices ON account_invoice_num_invoice = id_invoice
			JOIN rent_accounts ON id_account = account_invoice_num_account";
		return $query;
	}
	
	protected function get_object_instance($row) {
		return new rent_invoice($row->id_invoice);
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters =
		array('main_fields' =>
				array(
						'entity' => 'acquisition_coord_lib',
						'exercice' => 'acquisition_budg_exer',
						'type' => 'acquisition_account_type_name',
						'num_publisher' => 'acquisition_account_num_publisher',
						'num_supplier' => 'acquisition_account_num_supplier',
						'status' => 'acquisition_invoice_status',
						'date' => 'acquisition_invoice_date',
				)
		);
		$this->available_filters['custom_fields'] = array();
	}
	
	/**
	 * Initialisation des filtres de recherche
	 */
	public function init_filters($filters=array()) {
		$id_entity = entites::getSessionBibliId();
		$query = exercices::listByEntite($id_entity);
		$result = pmb_mysql_query($query);
		$id_exercice = 0;
		if($result && pmb_mysql_num_rows($result)) {
			$id_exercice = pmb_mysql_result($result, 0, 'id_exercice');
		}
		$this->filters = array(
				'entity' => $id_entity,
				'exercice' => $id_exercice,
				'type' => '',
				'num_publisher' => '',
				'num_supplier' => '',
				'num_pricing_system' => '',
				'status' => 0,
				'date_start' => '',
				'date_end' => ''
		);
		parent::init_filters($filters);
	}
	
	protected function init_default_selected_filters() {
		$this->add_selected_filter('entity');
		$this->add_selected_filter('exercice');
		$this->add_selected_filter('type');
		$this->add_selected_filter('num_publisher');
		$this->add_selected_filter('num_supplier');
		$this->add_selected_filter('status');
		$this->add_selected_filter('date');
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns =
		array('main_fields' =>
				array(
						'id' => 'acquisition_invoice_id',
						'num_user' => 'acquisition_invoice_num_user',
						'date' => 'acquisition_invoice_date',
						'num_publisher' => 'acquisition_invoice_num_publisher',
						'num_supplier' => 'acquisition_invoice_num_supplier',
						'status' => 'acquisition_invoice_status',
						'valid_date' => 'acquisition_invoice_valid_date',
						'destination_name' => 'acquisition_invoice_destination_name',
				)
		);
		$this->available_columns['custom_fields'] = array();
	}
	
	protected function init_default_columns() {
		$this->add_column_selection();
		$this->add_column('id');
		$this->add_column('num_user');
		$this->add_column('date');
		$this->add_column('num_publisher');
		$this->add_column('num_supplier');
		$this->add_column('status');
		$this->add_column('valid_date');
		$this->add_column('destination_name');
	}
	
	/**
	 * Initialisation des settings par d?faut
	 */
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_column('status', 'align', 'center');
		$this->set_setting_column('valid_date', 'align', 'center');
		$this->set_setting_column('id', 'datatype', 'integer');
	}
	
	/**
	 * Filtres provenant du formulaire
	 */
	public function set_filters_from_form() {
		$this->set_filter_from_form('status', 'integer');
		parent::set_filters_from_form();
	}
	
	protected function get_search_filter_status() {
		global $msg;
		
		$options = array(
				0 => $msg['acquisition_account_type_select_all'],
				1 => $msg['acquisition_invoice_status_new'],
				2 => $msg['acquisition_invoice_status_validated'],
		);
		return $this->get_simple_selector('', 'status', '', $options);
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
		$filters[] = 'account_num_exercice = "'.$this->filters['exercice'].'"';
		
		if($this->filters['type']) {
			$filters [] = 'account_type = "'.addslashes($this->filters['type']).'"';
		}
		if($this->filters['num_publisher']) {
			$filters [] = 'account_num_publisher = "'.$this->filters['num_publisher'].'"';
		}
		if($this->filters['num_supplier']) {
			$filters [] = 'account_num_supplier = "'.$this->filters['num_supplier'].'"';
		}
		if($this->filters['num_pricing_system']) {
			$filters [] = 'account_num_pricing_system = "'.$this->filters['num_pricing_system'].'"';
		}
		if($this->filters['status']) {
			$filters [] = 'invoice_status = "'.$this->filters['status'].'"';
		}
		if($this->filters['date_start']) {
			$filters [] = 'invoice_date >= "'.$this->filters['date_start'].'"';
		}
		if($this->filters['date_end']) {
			$filters [] = 'invoice_date <= "'.$this->filters['date_end'].' 23:59:59"';
		}
		if(count($filters)) {
			$filter_query .= ' where '.implode(' and ', $filters);		
		}
		return $filter_query;
	}
	
	protected function _get_object_property_num_publisher($object) {
		$accounts = $object->get_accounts();
		if(count($accounts)) {
			if(isset($accounts[0]->get_publisher()->display)) {
				return $accounts[0]->get_publisher()->display;
			}
		}
		return '';
	}
	
	protected function _get_object_property_num_supplier($object) {
		$accounts = $object->get_accounts();
		if(count($accounts)) {
			if(isset($accounts[0]->get_supplier()->raison_sociale)) {
				return $accounts[0]->get_supplier()->raison_sociale;
			}
		}
		return '';
	}
	
	protected function _get_object_property_num_user($object) {
		return $object->get_user()->prenom.' '.$object->get_user()->nom;
	}
	
	protected function _get_object_property_status($object) {
		return $object->get_status_label();
	}
	
	protected function _get_object_property_destination_name($object) {
		if(!isset($this->marclist_rent_destination)) {
			$this->marclist_rent_destination = new marc_list('rent_destination');
		}
		return $this->marclist_rent_destination->table[$object->get_destination()];
	}

	protected function _get_query_human_status() {
		global $msg;
		if($this->filters['status'] == 1) {
			return $msg['acquisition_invoice_status_new'];
		} elseif($this->filters['status'] == 2){
			return $msg['acquisition_invoice_status_validated'];
		}
		return '';
	}
	
	protected function _get_query_human() {
		$humans = $this->_get_query_human_main_fields();
		return $this->get_display_query_human($humans);
	}
	
	protected function get_display_cell($object, $property) {
		global $id_bibli;
		
		$attributes = array();
		$attributes['onclick'] = "window.location=\"".static::get_controller_url_base()."&action=edit&id_bibli=".$id_bibli."&id=".$object->get_id()."\"";
		$content = $this->get_cell_content($object, $property);
		$display = $this->get_display_format_cell($content, $property, $attributes);
		return $display;
	}
	
	protected function init_default_selection_actions() {
		global $msg, $base_path;
		
		parent::init_default_selection_actions();
		$gen_invoices_link = array(
				'openPopUp' => $base_path."/pdf.php?pdfdoc=account_invoice",
				'openPopUpTitle' => 'lettre'
		);
		$this->add_selection_action('gen_invoices', $msg['acquisition_invoice_generate'], '', $gen_invoices_link);
		
		$validate_invoices_link = array(
				'href' => static::get_controller_url_base()."&action=validate"
		);
		$this->add_selection_action('validate_invoices', $msg['acquisition_invoice_validate'], '', $validate_invoices_link);
	}
}