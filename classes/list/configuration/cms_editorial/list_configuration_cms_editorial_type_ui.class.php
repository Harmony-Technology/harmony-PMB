<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_configuration_cms_editorial_type_ui.class.php,v 1.2 2021/05/06 13:01:45 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path;
require_once($class_path."/list/configuration/cms_editorial/list_configuration_cms_editorial_ui.class.php");
require_once($class_path."/cms/cms_editorial_type.class.php");

class list_configuration_cms_editorial_type_ui extends list_configuration_cms_editorial_ui {
	
	protected function _get_query_base() {
		return 'SELECT * FROM cms_editorial_types';
	}
	
	public function init_filters($filters=array()) {
		
		$this->filters = array(
				'element' => '',
		);
		parent::init_filters($filters);
	}
	
	protected function get_object_instance($row) {
		return new cms_editorial_type($row->id_editorial_type);
	}
	
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('label');
	}
	
	protected function _get_query_filters() {
		$filter_query = '';
		
		$this->set_filters_from_form();
		
		$filters = array();
		if($this->filters['element']) {
			$filters[] = '(editorial_type_element = "'.$this->filters['element'].'_generic" OR editorial_type_element = "'.$this->filters['element'].'")';
		}
		if(count($filters)) {
			$filter_query .= ' where '.implode(' and ', $filters);
		}
		return $filter_query;
	}
	
	protected function get_main_fields_from_sub() {
		return array(
				'label' => 'editorial_content_type_label',
				'comment' => 'editorial_content_type_comment',
				'type_fields' => 'editorial_content_type_fields',
		);
	}
	
	protected function init_no_sortable_columns() {
		$this->no_sortable_columns = array(
				'type_fields',
		);
	}
	
	protected function init_default_settings() {
		parent::init_default_settings();
	}
	
	protected function get_display_content_object_list($object, $indice) {
		$this->is_editable_object_list = false;
		return parent::get_display_content_object_list($object, $indice);
	}
	
	protected function _compare_objects($a, $b) {
		if(strpos($a->get_element(), "generic") !== false){
			return -1;
		} elseif(strpos($b->get_element(), "generic") !== false){
			return 1;
		} else {
			return parent::_compare_objects($a, $b);
		}
	}
	
	protected function get_cell_content($object, $property) {
		global $msg, $charset;
		global $type_list_empr;
		
		$content = '';
		switch($property) {
			case 'label':
				if(strpos($object->get_element(), "generic") === false){
					$content .= $object->get_label();
				} else {
					$content .= "<b>".$object->get_label()."</b>";
				}
				break;
			case 'comment':
				$content .= nl2br($object->get_comment());
				break;
			case 'type_fields':
				foreach($object->fields as $field){
					$content.= htmlentities($field['TITRE'],ENT_QUOTES,$charset)." (<i>".$type_list_empr[$field['TYPE']]."</i>)<br />";
				}
				$content .= "<input type='button' class='bouton' value=' ".$msg['cms_editorial_type_fieldlist_edit']." ' onclick='document.location=\"".static::get_controller_url_base()."&quoi=fields&type_id=".$object->get_id()."\"'/>";
				break;
			default :
				$content .= parent::get_cell_content($object, $property);
				break;
		}
		return $content;
	}
	
	protected function get_display_cell($object, $property) {
		switch ($property) {
			case 'type_fields':
				$attributes = array();
				break;
			default:
				$attributes = array(
						'onclick' => "document.location=\"".$this->get_edition_link($object)."\""
				);
				break;
		}
		$content = $this->get_cell_content($object, $property);
		$display = $this->get_display_format_cell($content, $property, $attributes);
		return $display;
	}
	
	protected function get_edition_link($object) {
		return static::get_controller_url_base().'&action=edit&id='.$object->get_id();
	}
	
	protected function get_label_button_add() {
		global $msg;
		
		return $msg['editorial_content_type_add'];
	}
	
	public static function get_controller_url_base() {
		global $elem;
		
		return parent::get_controller_url_base().'&elem='.$elem;
	}
}