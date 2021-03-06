<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_configuration_acquisition_lgstat_ui.class.php,v 1.1 2021/01/15 13:26:34 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_configuration_acquisition_lgstat_ui extends list_configuration_acquisition_ui {
	
	protected function _get_query_base() {
		return 'SELECT * FROM lignes_actes_statuts';
	}
	
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('libelle');
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_column('libelle', 'text', array('italic' => true));
		$this->set_setting_column('relance', 'text', array('italic' => true));
	}
	
	protected function get_main_fields_from_sub() {
		return array(
				'libelle' => '103',
				'relance' => 'acquisition_lgstat_arelancer',
		);
	}
	
	protected function get_cell_content($object, $property) {
		global $msg;
		
		$content = '';
		switch($property) {
			case 'libelle':
				if($object->id_statut == 1) {
					$content .= "<strong>".$object->{$property}."</strong>";
				} else {
					$content .= $object->{$property};
				}
				break;
			case 'relance':
				if($object->relance == 1) {
					$content .= $msg[40];
				} else {
					$content .= $msg[39];
				}
				break;
			default :
				$content .= parent::get_cell_content($object, $property);
				break;
		}
		return $content;
	}
	
	protected function get_edition_link($object) {
		return static::get_controller_url_base().'&action=modif&id='.$object->id_statut;
	}
	
	protected function get_label_button_add() {
		global $msg;
		
		return $msg['acquisition_lgstat_add'];
	}
}