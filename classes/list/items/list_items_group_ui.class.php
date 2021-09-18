<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_items_group_ui.class.php,v 1.1 2021/03/10 07:35:33 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

class list_items_group_ui extends list_items_ui {
	
	protected function _get_query_base() {
		$query = parent::_get_query_base();
		$query .= "
			JOIN groupexpl_expl ON groupexpl_expl.groupexpl_expl_num = exemplaires.expl_id
			JOIN groupexpl ON groupexpl.id_groupexpl = groupexpl_expl.groupexpl_num";
		return $query;
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('search_form', 'visible', false);
		$this->set_setting_display('search_form', 'export_icons', false);
		$this->set_setting_display('query', 'human', false);
		$this->set_setting_column('default', 'align', 'left');
	}
	
	protected function init_no_sortable_columns() {
		$this->no_sortable_columns = array(
				'expl_cb', 'record_header', 'sur_loc_libelle', 'location_libelle', 'section_libelle',
				'expl_cote', 'statut_libelle', 'main_item', 'pointed'
		);
		$this->no_sortable_columns[] = 'raz';
		$this->no_sortable_columns[] = 'del_expl';
	}
	
	protected function init_available_columns() {
		parent::init_available_columns();
		$this->available_columns['main_fields']['main_item'] = 'groupexpl_form_resp_expl';
		$this->available_columns['main_fields']['pointed'] = 'groupexpl_see_form_checked_title';
		$this->available_columns['main_fields']['raz'] = 'groupexpl_form_raz_button';
		$this->available_columns['main_fields']['del_expl'] = '';
	}
	
	public function get_display_search_form() {
		//Ne pas retourner le formulaire car d�j� inclu dans un autre
		return '';
	}
	
	protected function pager() {
		return '';
	}
	
	protected function get_cell_content($object, $property) {
		$content = '';
		switch ($property) {
			case 'main_item':
				$content .= "<input type='radio' value='".$object->expl_id."' ".($object->groupexpl_expl_num == $object->groupexpl_resp_expl_num ? "checked='checked'" : "")." name='resp_expl_num'>";
				break;
			case 'pointed':
				if($object->groupexpl_checked) {
					$content .= "x";
				}
				break;
			case 'raz':
				$content .= "<input type='button' class='bouton_small align_middle' value='X' onclick=\"document.location='".static::get_controller_url_base()."&action=raz_check&id=".$object->id_groupexpl."&form_cb_expl=".rawurlencode($object->expl_cb)."'; \" />";
				break;
			case 'del_expl':
				$content .= "<input type='button' class='bouton_small align_middle' value='X' onclick=\"document.location='".static::get_controller_url_base()."&action=del_expl&id=".$object->id_groupexpl."&form_cb_expl=".rawurlencode($object->expl_cb)."'; \" />";
				break;
			default :
				$content .= parent::get_cell_content($object, $property);
				break;
		}
		return $content;
	}
	
	public static function get_controller_url_base() {
		global $base_path;
		global $categ;
		
		return $base_path.'/circ.php?categ='.$categ;
	}
}