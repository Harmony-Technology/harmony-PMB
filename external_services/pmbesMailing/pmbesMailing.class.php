<?php
// +-------------------------------------------------+
// | 2002-2012 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: pmbesMailing.class.php,v 1.8 2020/10/01 08:48:40 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($class_path."/external_services.class.php");

class pmbesMailing extends external_services_api_class {
	
	public function restore_general_config() {
		
	}
	
	public function form_general_config() {
		return false;
	}
	
	public function save_general_config() {
		
	}
	
	public function sendMailingCaddie($id_caddie_empr, $id_tpl, $email_cc = '', $attachments = array(), $associated_campaign=0) {
		$id_caddie_empr += 0;
		if (!$id_caddie_empr)
			throw new Exception("Missing parameter: id_caddie_empr");
		$id_tpl +=0;
		if (!$id_tpl)
			throw new Exception("Missing parameter: id_tpl");
			
			return $this->sendMailing($id_caddie_empr, $id_tpl, $email_cc, mailing_empr::TYPE_CADDIE, $attachments, $associated_campaign);
	}
	
	public function sendMailingSearchPerso($id_search_perso, $id_tpl, $email_cc = '', $attachments = array(), $associated_campaign=0) {
	    $id_search_perso += 0;
	    if (!$id_search_perso)
			throw new Exception("Missing parameter: id_search_perso");
		$id_tpl +=0;
		if (!$id_tpl)
			throw new Exception("Missing parameter: id_tpl");
		
			return $this->sendMailing($id_search_perso, $id_tpl, $email_cc, mailing_empr::TYPE_SEARCH_PERSO, $attachments, $associated_campaign);
	}
	
	private function sendMailing($id_list, $id_tpl, $email_cc, $type, $attachments = array(), $associated_campaign=0) {
	    global $charset;
	    
		$result = array();
	    if (SESSrights & CIRCULATION_AUTH) {
	        if ($id_list && $id_tpl) {
        	    $mailtpl = new mailtpl($id_tpl);
        	    $objet_mail = $mailtpl->info['objet'];
        	    $message = html_entity_decode($mailtpl->info['tpl'], ENT_QUOTES, $charset);
        	    
        	    $mailing = new mailing_empr($id_list, $email_cc, $type);
        	    $mailing->associated_campaign  = intval($associated_campaign);
        	    $mailing->send($objet_mail, $message, 0, $attachments);
        	    
        	    $result["name"] = $mailtpl->info['name'];
        	    $result["object_mail"] = $objet_mail;
        	    $result["nb_mail"] = $mailing->total;
        	    $result["nb_mail_sended"] = $mailing->total_envoyes;
        	    $result["nb_mail_failed"] = $mailing->envoi_KO;
	        }
	    }
	    return $result;	    
	}
}