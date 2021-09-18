<?php
// +-------------------------------------------------+
// | 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: list_users_ui.class.php,v 1.13 2021/05/25 11:12:21 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path.'/user.class.php');

class list_users_ui extends list_ui {
	
	protected function _get_query_base() {
		$query = 'SELECT * FROM users';
		return $query;
	}
	
	/**
	 * Initialisation des filtres disponibles
	 */
	protected function init_available_filters() {
		$this->available_filters =
		array('main_fields' =>
				array(
				)
		);
		$this->available_filters['custom_fields'] = array();
	}
	
	/**
	 * Initialisation des filtres de recherche
	 */
	public function init_filters($filters=array()) {
		
		$this->filters = array(
		);
		parent::init_filters($filters);
	}
	
	protected function init_default_selected_filters() {
	    $this->selected_filters = array();
	}
	
	/**
	 * Initialisation des colonnes disponibles
	 */
	protected function init_available_columns() {
		$this->available_columns = 
		array('main_fields' =>
			array(
			)
		);
	}
	
	/**
	 * Initialisation du tri par d�faut appliqu�
	 */
	protected function init_default_applied_sort() {
	    $this->add_applied_sort('username');
	}
	
	protected function init_default_pager() {
	    parent::init_default_pager();
	    $this->pager['nb_per_page'] = 0;
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
					$order .= 'userid';
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
	
	protected function get_button_add() {
		global $msg;
		
		return $this->get_button('add', $msg['85']);
	}
	
	/**
	 * Filtres provenant du formulaire
	 */
	public function set_filters_from_form() {
		parent::set_filters_from_form();
	}
	
	/**
	 * Initialisation des settings par d�faut
	 */
	protected function init_default_settings() {
		parent::init_default_settings();
		$this->set_setting_display('search_form', 'visible', false);
		$this->set_setting_display('search_form', 'export_icons', false);
		$this->set_setting_display('query', 'human', false);
	}
	
	/**
	 * Filtre SQL
	 */
	protected function _get_query_filters() {
		$filter_query = '';
		
		$this->set_filters_from_form();
		
		$filters = array();
		if(count($filters)) {
			$filter_query .= ' where '.implode(' and ', $filters);
		}
		return $filter_query;
	}
	
	protected function get_js_sort_script_sort() {
		$display = parent::get_js_sort_script_sort();
		$display = str_replace('!!categ!!', 'users', $display);
		$display = str_replace('!!sub!!', '', $display);
		$display = str_replace('!!action!!', 'list', $display);
		return $display;
	}
	
	protected function get_display_permission_access($permission_access=0) {
	    if($permission_access) {
	        return '<img src="'.get_url_icon('coche.gif').'" class="align_top" hspace=3>';
	    } else {
	        return '<img src="'.get_url_icon('uncoche.gif').'" class="align_top" hspace=3>';
	    }
	}
	
	protected function get_display_ask_alert_mail($name, $alert_mail=0) {
	    global $msg;
	    global $admin_user_alert_row;
	    
	    if($alert_mail) {
	        return str_replace("!!user_alert!!", $msg[$name].'<img src="'.get_url_icon('tick.gif').'" class="align_top" hspace=3>', $admin_user_alert_row);
	    } else {
	        return '';
	    }
	}
	
	protected function get_display_content_object_list($object, $indice) {
	    global $msg;
	    global $admin_user_list;
	    global $admin_user_link1;
	    
	    $ancre = "";
	    if(!empty($this->object_id) && $this->object_id==$object->userid) {
	    	if(empty($this->ancre)) {
	    		$this->ancre = $this->objects_type."_object_list_ancre";
	    	}
	    	$ancre = "<a name='".$this->ancre."'></a>";
	    }
	    
	    // r�initialisation des cha�nes
	    $dummy = $admin_user_list;
	    $dummy1 = $admin_user_link1;
	    
	    $flag = "<img src='./images/flags/".$object->user_lang.".gif' width='24' height='16' vspace='3'>";
	    
	    $dummy =str_replace('!!user_link!!', $dummy1, $dummy);
	    $dummy =str_replace('!!user_name!!', "$object->prenom $object->nom", $dummy);
	    $dummy =str_replace('!!user_login!!', $object->username, $dummy);
	    
	    $dummy =str_replace('!!nuseradmin!!', $this->get_display_permission_access($object->rights & ADMINISTRATION_AUTH), $dummy);
	    $dummy =str_replace('!!nusercatal!!', $this->get_display_permission_access($object->rights & CATALOGAGE_AUTH), $dummy);
	    $dummy =str_replace('!!nusercirc!!', $this->get_display_permission_access($object->rights & CIRCULATION_AUTH), $dummy);
	    $dummy =str_replace('!!nuserpref!!', $this->get_display_permission_access($object->rights & PREF_AUTH), $dummy);
	    $dummy =str_replace('!!nuseracquisition_account_invoice!!', $this->get_display_permission_access($object->rights & ACQUISITION_ACCOUNT_INVOICE_AUTH), $dummy);
	    $dummy =str_replace('!!nuserauth!!', $this->get_display_permission_access($object->rights & AUTORITES_AUTH), $dummy);
	    $dummy =str_replace('!!nuseredit!!', $this->get_display_permission_access($object->rights & EDIT_AUTH), $dummy);
	    $dummy =str_replace('!!nusereditforcing!!', $this->get_display_permission_access($object->rights & EDIT_FORCING_AUTH), $dummy);
	    $dummy =str_replace('!!nusersauv!!', $this->get_display_permission_access($object->rights & SAUV_AUTH), $dummy);
	    $dummy =str_replace('!!nuserdsi!!', $this->get_display_permission_access($object->rights & DSI_AUTH), $dummy);
	    $dummy =str_replace('!!nuseracquisition!!', $this->get_display_permission_access($object->rights & ACQUISITION_AUTH), $dummy);
	    $dummy =str_replace('!!nuserrestrictcirc!!', $this->get_display_permission_access($object->rights & RESTRICTCIRC_AUTH), $dummy);
	    $dummy =str_replace('!!nuserthesaurus!!', $this->get_display_permission_access($object->rights & THESAURUS_AUTH), $dummy);
	    $dummy =str_replace('!!nusertransferts!!', $this->get_display_permission_access($object->rights & TRANSFERTS_AUTH), $dummy);
	    $dummy =str_replace('!!nuserextensions!!', $this->get_display_permission_access($object->rights & EXTENSIONS_AUTH), $dummy);
	    $dummy =str_replace('!!nuserdemandes!!', $this->get_display_permission_access($object->rights & DEMANDES_AUTH), $dummy);
	    $dummy =str_replace('!!nusercms!!', $this->get_display_permission_access($object->rights & CMS_AUTH), $dummy);
	    $dummy =str_replace('!!nusercms_build!!', $this->get_display_permission_access($object->rights & CMS_BUILD_AUTH), $dummy);
	    $dummy =str_replace('!!nuserfiches!!', $this->get_display_permission_access($object->rights & FICHES_AUTH), $dummy);
	    $dummy =str_replace('!!nusermodifcbexpl!!', $this->get_display_permission_access($object->rights & CATAL_MODIF_CB_EXPL_AUTH), $dummy);
	    $dummy =str_replace('!!nusersemantic!!', $this->get_display_permission_access($object->rights & SEMANTIC_AUTH), $dummy);
	    $dummy =str_replace('!!nuserconcepts!!', $this->get_display_permission_access($object->rights & CONCEPTS_AUTH), $dummy);
	    $dummy =str_replace('!!nusermodelling!!', $this->get_display_permission_access($object->rights & MODELLING_AUTH), $dummy);
	    
	    
        $dummy = str_replace('!!lang_flag!!', $flag, $dummy);
        $dummy = str_replace('!!nuserlogin!!', $object->username, $dummy);
        $dummy = str_replace('!!nuserid!!', $object->userid, $dummy);
              
        $dummy =str_replace('!!user_alert_resamail!!', $this->get_display_ask_alert_mail('alert_resa_user_mail', $object->user_alert_resamail), $dummy);
        $dummy =str_replace('!!user_alert_contribmail!!', $this->get_display_ask_alert_mail('alert_contrib_user_mail', $object->user_alert_contribmail), $dummy);
        $dummy =str_replace('!!user_alert_demandesmail!!', $this->get_display_ask_alert_mail('alert_demandes_user_mail', $object->user_alert_demandesmail), $dummy);
        $dummy =str_replace('!!user_alert_subscribemail!!', $this->get_display_ask_alert_mail('alert_subscribe_user_mail', $object->user_alert_subscribemail), $dummy);
        $dummy =str_replace('!!user_alert_suggmail!!', $this->get_display_ask_alert_mail('alert_sugg_user_mail', $object->user_alert_suggmail), $dummy);
        $dummy =str_replace('!!user_alert_serialcircmail!!', $this->get_display_ask_alert_mail('alert_subscribe_serialcirc_mail', $object->user_alert_serialcircmail), $dummy);
                                                
        $dummy = str_replace('!!user_created_date!!', $msg['user_created_date'].format_date($object->create_dt), $dummy);
                    
        return $ancre.$dummy;
	}
	
	/**
	 * Affiche la recherche + la liste
	 */
	public function get_display_list() {
	    $display = '';
	    //Affichage de la liste des objets
	    if(count($this->objects)) {
	        $display .= $this->get_display_content_list();
	        $display .= $this->add_events_on_objects_list();
	    }
	    if(count($this->get_selection_actions())) {
	        $display .= $this->get_display_selection_actions();
	    }
	    $display .= $this->get_display_others_actions();
	    $display .= $this->pager();
	    $display .= "
		<div class='row'>&nbsp;</div>
		<div class='row'>
			<div class='left'>
			</div>
			<div class='right'>
			</div>
		</div>";
	    return $display;
	}
	
	protected function get_link_action($action, $act) {
	    global $msg;
	    
	    return array(
	        'href' => static::get_controller_url_base()."&action=".$action,
	        'confirm' => ''
	    );
	}
	
	protected function init_default_selection_actions() {
		parent::init_default_selection_actions();
		//Bouton modifier
// 		$link = array();
// 		$this->add_selection_action('edit', $msg['62'], 'b_edit.png', $link);
		
		//Bouton supprimer
// 		$this->add_selection_action('delete', $msg['63'], 'interdit.gif', $this->get_link_action('list_delete', 'delete'));
	}
	
	protected function get_selection_mode() {
	    return 'icon-dialog';
	}
	
	protected function get_display_selection_actions() {
	    $display = parent::get_display_selection_actions();
	    $display .= "<script type='text/javascript'>
            require(['dojo/ready', 'apps/pmb/Users'], function(ready, Users){
                ready(function(){
                    new Users();
                });
            });
       </script>";
	    return $display;
	}
	
	public static function get_controller_url_base() {
		global $base_path;
		
		return $base_path.'/admin.php?categ=users&sub=users';
	}
}