<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_items_ui.class.php,v 1.5 2021/04/19 07:10:06 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_items_ui extends list_ui {
		
	protected function _get_query_base() {
		$query = "SELECT * FROM exemplaires
			JOIN docs_location ON docs_location.idlocation = exemplaires.expl_location
			JOIN docs_section ON docs_section.idsection = exemplaires.expl_section
			JOIN docs_statut ON docs_statut.idstatut = exemplaires.expl_statut
			JOIN docs_type ON docs_type.idtyp_doc = exemplaires.expl_typdoc
			JOIN docs_codestat ON docs_codestat.idcode = exemplaires.expl_codestat";
		return $query;
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters =
		array('main_fields' =>
				array(
						'expl_codestat' => 'editions_datasource_expl_codestat',
						'expl_codestats' => 'editions_datasource_expl_codestats',
						'expl_section' => 'editions_datasource_expl_section',
						'expl_sections' => 'editions_datasource_expl_sections',
						'expl_statut' => 'editions_datasource_expl_statut',
						'expl_statuts' => 'editions_datasource_expl_statuts',
						'expl_type' => 'editions_datasource_expl_type',
						'expl_types' => 'editions_datasource_expl_types',
						'expl_cote' => '296',
				)
		);
		$this->available_filters['custom_fields'] = array();
	}
	
	/**
	 * Initialisation des filtres de recherche
	 */
	public function init_filters($filters=array()) {
		$this->filters = array(
				'expl_id' => 0,
				'expl_codestat' => '',
				'expl_codestats' => array(),
				'expl_section' => '',
				'expl_sections' => array(),
				'expl_statut' => '',
				'expl_statuts' => array(),
				'expl_type' => '',
				'expl_types' => array(),
				'expl_cote' => '',
				'expl_location' => '',
				'expl_locations' => array(),
				'expl_group' => 0,
				'expl_groups' => array(),
		);
		parent::init_filters($filters);
	}
	
	/**
	 * Initialisation du tri par d�faut appliqu�
	 */
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('expl_id');
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		global $pmb_sur_location_activate;
		
		$this->available_columns =
		array('main_fields' =>
				array(
						'expl_cb' => '293',
						'record_header' => '',
						'location_libelle' => '298',
						'section_libelle' => '295',
						'expl_cote' => '296',
						'statut_libelle' => '297',
						'tdoc_libelle' => '294',
						'lender_libelle' => '651'
				)
		);
		if($pmb_sur_location_activate){
			$this->available_columns['main_fields']['sur_loc_libelle'] = 'sur_location_expl';
		}
		$this->available_columns['custom_fields'] = array();
	}
	
	protected function init_default_columns() {
	}
	
	/**
	 * Filtres provenant du formulaire
	 */
	public function set_filters_from_form() {
		$this->set_filter_from_form('expl_codestat', 'integer');
		$this->set_filter_from_form('expl_codestats');
		$this->set_filter_from_form('expl_section', 'integer');
		$this->set_filter_from_form('expl_sections');
		$this->set_filter_from_form('expl_statut', 'integer');
		$this->set_filter_from_form('expl_statuts');
		$this->set_filter_from_form('expl_type', 'integer');
		$this->set_filter_from_form('expl_types');
		$this->set_filter_from_form('expl_cote');
		$this->set_filter_from_form('expl_location', 'integer');
		$this->set_filter_from_form('expl_locations');
		$this->set_filter_from_form('expl_group', 'integer');
		$this->set_filter_from_form('expl_groups');
		parent::set_filters_from_form();
	}
	
	protected function get_selector_query($type) {
		$query = '';
		switch ($type) {
			case 'docs_codestat':
				$query = 'select idcode as id, codestat_libelle as label from docs_codestat order by label';
				break;
			case 'docs_section':
				$query = 'select idsection as id, section_libelle as label from docs_section order by label';
				break;
			case 'docs_statut':
				$query = 'select idstatut as id, statut_libelle as label from docs_statut order by label';
				break;
			case 'docs_type':
				$query = 'select idtyp_doc as id, tdoc_libelle as label from docs_type order by label';
				break;
			case 'docs_location':
				$query = 'select idlocation as id, location_libelle as label from docs_location order by label';
				break;
			case 'docs_groups':
				$query = 'select id_groupexpl as id, groupexpl_name as label from groupexpl order by label';
				break;
		}
		return $query;
	}
	
	protected function get_search_filter_expl_cote() {
		global $charset;
		return "<input type='text' class='saisie-20em' name='".$this->objects_type."_expl_cote' value='".htmlentities($this->filters['expl_cote'], ENT_QUOTES, $charset)."' />";
	}
	
	protected function get_search_filter_expl_codestat() {
		global $msg;
		
		return $this->get_simple_selector($this->get_selector_query('docs_codestat'), 'expl_codestat', $msg['all']);
	}
	
	protected function get_search_filter_expl_codestats() {
		global $msg;
		
		return $this->get_multiple_selector($this->get_selector_query('docs_codestat'), 'expl_codestats', $msg['all']);
	}
	
	protected function get_search_filter_expl_section() {
		global $msg;
		
		return $this->get_simple_selector($this->get_selector_query('docs_section'), 'expl_section', $msg['all']);
	}
	
	protected function get_search_filter_expl_sections() {
		global $msg;
		
		return $this->get_multiple_selector($this->get_selector_query('docs_section'), 'expl_sections', $msg['all']);
	}
	
	protected function get_search_filter_expl_statut() {
		global $msg;
		
		return $this->get_simple_selector($this->get_selector_query('docs_statut'), 'expl_statut', $msg['all']);
	}
	
	protected function get_search_filter_expl_statuts() {
		global $msg;
		
		return $this->get_multiple_selector($this->get_selector_query('docs_statut'), 'expl_statuts', $msg['all']);
	}
	
	protected function get_search_filter_expl_type() {
		global $msg;
		
		return $this->get_simple_selector($this->get_selector_query('docs_type'), 'expl_type', $msg['all']);
	}
	
	protected function get_search_filter_expl_types() {
		global $msg;
		
		return $this->get_multiple_selector($this->get_selector_query('docs_type'), 'expl_types', $msg['all']);
	}
	
	protected function get_search_filter_expl_location() {
		global $msg;
		
		return $this->get_simple_selector($this->get_selector_query('docs_location'), 'expl_location', $msg['all']);
	}
	
	protected function get_search_filter_expl_locations() {
		global $msg;
		
		return $this->get_multiple_selector($this->get_selector_query('docs_location'), 'expl_locations', $msg['all']);
	}
	
	protected function get_search_filter_expl_groups() {
		global $msg;
		
		return $this->get_multiple_selector($this->get_selector_query('docs_groups'), 'expl_groups', $msg['all']);
	}
	
	/**
	 * Filtre SQL
	 */
	protected function _get_query_filters() {
		
		$filter_query = '';
		
		$this->set_filters_from_form();
		
		$filters = array();
		if($this->filters['expl_id']) {
			$filters [] = 'expl_id = "'.$this->filters['expl_id'].'"';
		}
		if($this->filters['expl_group']) {
			$filters [] = 'groupexpl_num = "'.$this->filters['expl_group'].'"';
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
		global $categ, $sub;
		
		$display = parent::get_js_sort_script_sort();
		$display = str_replace('!!categ!!', $categ, $display);
		$display = str_replace('!!sub!!', $sub, $display);
		$display = str_replace('!!action!!', 'list', $display);
		return $display;
	}
	
	protected function _get_object_property_sur_loc_libelle($object) {
		$sur_loc = sur_location::get_info_surloc_from_location($object->expl_location);
		return $sur_loc->libelle;
	}
	
	protected function get_cell_content($object, $property) {
		global $msg, $cart_link_non;
	
		$content = '';
		switch($property) {
			case 'expl_cb':
				$content .= "<a href='./circ.php?categ=visu_ex&form_cb_expl=".rawurlencode($object->expl_cb)."'>".$object->expl_cb."</a>";
				break;
		    case 'record_header':
		    	if($object->expl_notice) {
		    		if (SESSrights & CATALOGAGE_AUTH) {
		    			$notice = new mono_display($object->expl_notice, 1, notice::get_permalink($object->expl_notice), 0);
		    		} else {
		    			$notice = new mono_display($object->expl_notice, 1, "", 0);
		    		}
		    		$content .= $notice->header;
		    	} elseif ($object->expl_bulletin) {
		    		$bl = new bulletinage_display($object->expl_bulletin);
		    		if ($cart_link_non) {
		    			$content .= $bl->header;
		    		} else {
		    			$content .= "<a href='".bulletinage::get_permalink($object->expl_bulletin)."'>".$bl->header."</a>";
		    		}
		    	}
		    	break;
		    case 'add_cart':
		    	$content .= "<img src='".get_url_icon('basket_small_20x20.gif')."' alt='basket' title='".$msg[400]."' onclick=\"openPopUp('./cart.php?object_type=EXPL&item=".$object->expl_id."', 'cart')\" class='align_middle'>";
		    	break;
			default :
				$content .= parent::get_cell_content($object, $property);
				break;
		}
		return $content;
	}
	
	protected function _get_query_property_filter($property) {
		switch ($property) {
			case 'expl_codestat':
				return "select codestat_libelle from docs_codestat where idcode = ".$this->filters[$property];
			case 'expl_codestats':
				return "select codestat_libelle from docs_codestat where idcode IN (".implode(',', $this->filters[$property]).")";
			case 'expl_section':
				return "select section_libelle from docs_section where idsection = ".$this->filters[$property];
			case 'expl_sections':
				return "select section_libelle from docs_section where idsection IN (".implode(',', $this->filters[$property]).")";
			case 'expl_statut':
				return "select statut_libelle from docs_statut where idstatut = ".$this->filters[$property];
			case 'expl_statuts':
				return "select statut_libelle from docs_statut where idstatut IN (".implode(',', $this->filters[$property]).")";
			case 'expl_type':
				return "select tdoc_libelle from docs_type where idtyp_doc = ".$this->filters[$property];
			case 'expl_types':
				return "select tdoc_libelle from docs_type where idtyp_doc IN (".implode(',', $this->filters[$property]).")";
			case 'expl_group':
				return "select groupexpl_name from groupexpl where id_groupexpl = ".$this->filters[$property];
			case 'expl_groups':
				return "select groupexpl_name from groupexpl where id_groupexpl IN (".implode(',', $this->filters[$property]).")";
		}
		return '';
	}
}