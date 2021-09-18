<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: abts_periodicite.class.php,v 1.3 2021/02/19 13:53:19 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path, $include_path;
require_once($include_path."/templates/abts_abonnements.tpl.php");
require_once($class_path."/serial_display.class.php");
require_once($include_path."/abts_func.inc.php");
require_once($include_path."/misc.inc.php");
require_once($class_path."/abts_pointage.class.php");
require_once($class_path."/serialcirc_diff.class.php");
require_once($class_path."/serialcirc.class.php");
require_once($class_path."/abts_status.class.php");
require_once($class_path.'/translation.class.php');

class abts_periodicite {
	public $id; //Num�ro
	public $libelle; //Libell�
	public $duree;
	public $unite;
	public $retard_periodicite;
	public $seuil_periodicite;
	public $consultation_duration;
	
	public function __construct($id=0) {		
		$this->id = intval($id);		
		$this->getData();
	}
	
	public function getData() {
		$this->libelle = '';
		$this->duree = 0;
		$this->unite = 0;
		$this->seuil_periodicite = 0;
		$this->retard_periodicite = 0;
		$this->consultation_duration = 0;
		if ($this->id) {
			$requete="select * from abts_periodicites where periodicite_id=".$this->id;
			$resultat=pmb_mysql_query($requete);
			if (pmb_mysql_num_rows($resultat)) {
				$r=pmb_mysql_fetch_object($resultat);
				$this->libelle = $r->libelle;
				$this->duree = $r->duree;
				$this->unite = $r->unite;
				$this->seuil_periodicite = $r->seuil_periodicite;
				$this->retard_periodicite = $r->retard_periodicite;
				$this->consultation_duration = $r->consultation_duration;
			} else {
				pmb_error::get_instance(static::class)->add_message("not_found", "not_found_object");
			}
		}				
	}
	
	public function get_form() {
		global $msg;
		global $admin_abonnements_periodicite_content_form;
		global $charset;
		
		$content_form = $admin_abonnements_periodicite_content_form;
		$content_form = str_replace('!!id!!', $this->id, $content_form);
		
		$interface_form = new interface_admin_form('typdocform');
		if(!$this->id){
			$interface_form->set_label($msg['abonnements_ajouter_une_periodicite']);
		}else{
			$interface_form->set_label($msg['118']);
		}
		$content_form = str_replace('!!libelle!!', htmlentities($this->libelle, ENT_QUOTES, $charset), $content_form);
		$content_form = str_replace('!!duree!!', htmlentities($this->duree,ENT_QUOTES, $charset), $content_form);
		$selected=array();
		$selected[0]=$selected[1]=$selected[2]='';
		$selected[$this->unite]= "selected='selected'";
		$str_unite="
       <select id='unite' name='unite'>
        <option value='0'$selected[0]>".$msg['abonnements_periodicite_unite_jour']."</option>
        <option value='1'$selected[1]>".$msg['abonnements_periodicite_unite_mois']."</option>
        <option value='2'$selected[2]>".$msg['abonnements_periodicite_unite_annee']."</option>
        </select>";
		$content_form = str_replace('!!unite!!', $str_unite, $content_form);
		
		$content_form = str_replace('!!seuil_periodicite!!', htmlentities($this->seuil_periodicite,ENT_QUOTES, $charset), $content_form);
		
		$content_form = str_replace('!!retard_periodicite!!', htmlentities($this->retard_periodicite,ENT_QUOTES, $charset), $content_form);
		$content_form = str_replace('!!consultation_duration!!', htmlentities($this->consultation_duration,ENT_QUOTES, $charset), $content_form);
		
		$interface_form->set_object_id($this->id)
		->set_confirm_delete_msg($msg['confirm_suppr_de']." ".$this->libelle." ?")
		->set_content_form($content_form)
		->set_table_name('abts_periodicites')
		->set_field_focus('libelle');
		return $interface_form->get_display();
	}
	
	public function set_properties_from_form() {
		global $libelle, $duree, $unite, $seuil_periodicite, $retard_periodicite, $consultation_duration;
		
		$this->libelle = stripslashes($libelle);
		$this->duree = stripslashes($duree);
		$this->unite = stripslashes($unite);
		$this->seuil_periodicite = intval($seuil_periodicite);
		$this->retard_periodicite = intval($retard_periodicite);
		$this->consultation_duration = intval($consultation_duration);
	}
	
	public function get_query_if_exists() {
		return " SELECT count(1) FROM abts_periodicites WHERE (libelle='".addslashes($this->libelle)."' AND periodicite_id!='".$this->id."' )";
	}
	
	public function save() {
		if ($this->id) {
			$requete = "UPDATE abts_periodicites SET libelle='".addslashes($this->libelle)."',duree='".addslashes($this->duree)."',unite='".addslashes($this->unite)."', seuil_periodicite='".$this->seuil_periodicite."', retard_periodicite='".$this->retard_periodicite."', consultation_duration='".$this->consultation_duration."' WHERE periodicite_id='".$this->id."' ";
			pmb_mysql_query($requete);
		} else {
			$requete = "INSERT INTO abts_periodicites SET libelle='".addslashes($this->libelle)."',duree='".addslashes($this->duree)."',unite='".addslashes($this->unite)."', seuil_periodicite='".$this->seuil_periodicite."', retard_periodicite='".$this->retard_periodicite."' , consultation_duration='".$this->consultation_duration."' ";
			pmb_mysql_query($requete);
		}
	}
	
	public static function check_data_from_form() {
		global $libelle, $retard_periodicite, $seuil_periodicite;
		
		if(empty($libelle)) {
			return false;
		}
		if (($retard_periodicite>=$seuil_periodicite)||($retard_periodicite==0)) {
			//Le param�trage est bon
		} else {
			pmb_error::get_instance(static::class)->add_message("retard_rapport_seuil", "retard_rapport_seuil");
			return false;
		}
		return true;
	}
	
	public static function delete($id=0) {
		$id = intval($id);
		if ($id) {
			$total = 0;
			$total = pmb_mysql_result(pmb_mysql_query("select count(1) from abts_modeles where num_periodicite ='".$id."' "), 0, 0);
			if ($total==0) {
				$requete = "DELETE FROM abts_periodicites WHERE periodicite_id='$id' ";
				pmb_mysql_query($requete);
				$requete = "OPTIMIZE TABLE abts_periodicites ";
				pmb_mysql_query($requete);
				return true;
			} else {
				pmb_error::get_instance(static::class)->add_message("noti_statut_noti", "noti_statut_used");
				return false;
			}
		}
		return true;
	}
}