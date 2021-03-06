<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: stat_view.class.php,v 1.28 2021/05/04 06:41:28 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

global $class_path, $include_path;
require_once("$include_path/templates/stat_opac.tpl.php");
require_once ($class_path . "/parse_format.class.php");
require_once("$include_path/misc.inc.php");
require_once("$include_path/user_error.inc.php");
require_once ($class_path . "/consolidation.class.php");
require_once ($class_path . "/stat_query.class.php");

class stat_view {
	
	public $action='';
	public $section='';
	
	/**
	 * Constructeur
	 */
	public function __construct($section='',$act=''){
		$this->action = $act;
		$this->section = $section;
	}
	
	/**
	 * Execution des différentes actions
	 */
	public function proceed(){
		global $msg, $id_col, $col_name, $expr_col, $expr_filtre, $view_name, $view_comment, $id_view; 
		global $id, $id_req, $move, $conso, $date_deb,$date_fin,$date_ech, $list_ck,$remove_data, $remove_data_interval, $remove_data_interval_date_deb, $remove_data_interval_date_fin;
		
		if($id)
			$id_req=$id;
		
		switch($this->section){
			case 'view_list':
				switch($this->action){
					case 'save_view':
						//Enregistrement/Insertion d'une vue
						$this->save_view($id_view,$view_name,$view_comment);
						print $this->do_form();
					break;
					case 'suppr_view':
						//Suppression d'une vue
						$this->delete_view($id_view);
						print $this->do_form();
					break;
					case 'consolide_view':
						if($date_deb>$date_fin)
							error_form_message($msg['stat_wrong_date_interval']);
						elseif(!$list_ck)
							error_form_message($msg['stat_no_view_selected']);
						else { 
							$consolidation = new consolidation($conso,$date_deb,$date_fin,$date_ech, $list_ck,$remove_data, $remove_data_interval);
							if(!empty($remove_data_interval_date_deb)) {
								$consolidation->set_remove_data_interval_date_debut($remove_data_interval_date_deb);
							}
							if(!empty($remove_data_interval_date_fin)) {
								$consolidation->set_remove_data_interval_date_fin($remove_data_interval_date_fin);
							}
							$consolidation->make_consolidation();
						}
						print $this->do_form();
					break;
					case 'reinit':
						//Réinitialisation de la vue
						$this->reinitialiser_view($id_view);
						print $this->do_form();
					break;
					//Actions liées aux requêtes
					case 'configure':
					case 'update_config':				
					case 'update_request':				
					case 'exec_req':
					case 'final':
						//Actions liées aux requêtes
						$stq = new stat_query($id_req,$this->action,$id_view);
						$stq->proceed();
						break;
					case 'save_request':				
					case 'suppr_request':
						$stq = new stat_query($id_req,$this->action,$id_view);
						$stq->proceed();
						print $this->do_form();
						break;
					default:
						print $this->do_form();
					break;
				}
				
			break;	
			case 'view_gestion':
				switch($this->action){
					case 'add_view':
						//ajout d'une vue
						//print $this->do_addview_form();
						break;					
					case 'update_view':
						//MaJ vue
						switch($move){
							case 'up':
								//Déplacer un élément dans la liste des colonnes
								$this->monter_element($id_col);
							break;
							case 'down':
								//Déplacer un élément dans la liste des colonnes
								$this->descendre_element($id_col);
							break;
						}	
					break;
					case 'save_col':
						//Enregistrement/Insertion d'une colonne
						$this->save_col($id_col,$col_name,$expr_col,$expr_filtre,$id_view);
					break;
					case 'suppr_col':
						//Suppression d'une colonne
						$this->delete_col($id_col);
					break;	
				}
				print $this->do_addview_form($id_view);
			break;
			case 'colonne':
				switch($this->action){
					case 'add_col':
						//ajout d'une colonne
						print $this->do_col_form();
					break;
					case 'save_col':
						//Enregistrement/Insertion d'une colonne
						$this->save_col($id_col,$col_name,$expr_col,$expr_filtre,$id_view);
						print $this->do_addview_form($id_view);
					break;
					case 'update_col':
						//MaJ colonne
						print $this->do_col_form($id_col);
					break;
					case 'suppr_col':
						//Suppression d'une colonne
						$this->delete_col($id_col);
						print $this->do_addview_form($id_view);
					break;	
				}
			break;
			case 'query':
				//Actions liées aux requêtes
				$stq = new stat_query($id_req,$this->action,$id_view);
				$stq->proceed();
			break;
			case 'import':
				//Formulaire import de requete
				print $this->do_import_req_form($id_view);
			break;
			case 'importsuite':
				//Import de requete
				$this->do_import_req($id_view);
			break;
			default:
			break;
		}
	}
	
	/**
	 * On fait appel au formulaire qui affiche la liste des vues
	 */
	public function do_form(){
		global $msg;
		global $alert_consolid;
		
		$display = list_statopac_ui::get_instance()->get_display_list();
		if ($alert_consolid) {
			$display.=display_notification($msg["stat_import_consolide"]);
		}
		return $display;
	}
	
	/**
	 * On fait appel au formulaire d'ajout d'une vue
	 */
	public function do_addview_form($vue_id=''){
		global $stat_view_addview_form;
		global $msg, $charset;
		
		if(!$vue_id){
			$stat_view_addview_form=str_replace("!!name_view!!",'',$stat_view_addview_form);
			$stat_view_addview_form=str_replace("!!view_comment!!",'',$stat_view_addview_form);
			$stat_view_addview_form=str_replace("!!table_colonne!!",'',$stat_view_addview_form);
			$stat_view_addview_form=str_replace("!!bouton_add_col!!",'',$stat_view_addview_form);
			$stat_view_addview_form=str_replace("!!bouton_reinit_view!!",'',$stat_view_addview_form);
			$stat_view_addview_form=str_replace("!!btn_suppr!!",'',$stat_view_addview_form);
			$stat_view_addview_form=str_replace("!!view_title!!",$msg["stat_view_create_title"],$stat_view_addview_form);
			$stat_view_addview_form=str_replace("!!id_view!!",'',$stat_view_addview_form);
						
			return $stat_view_addview_form;
			
		} else {
			$btn_add_col = "<input class='bouton' type='submit'  value=\"".$msg['stat_add_col']."\" onClick='this.form.act.value=\"add_col\"; document.addview.action=\"./admin.php?categ=opac&sub=stat&section=colonne&action=addcol\";'/>";
			$bouton_reinit_view="<input class='bouton' type='submit'  value=\"".$msg['stat_reinit_view']."\" onClick='this.form.act.value=\"reinit\";'/>";
			$btn_suppr = "<input class='bouton' type='submit'  value='$msg[63]' onClick='if(confirm_delete()) this.form.act.value=\"suppr_view\";'/>";
			
			$requete = "select nom_vue, comment from statopac_vues where id_vue='".addslashes($vue_id)."'";
			$resultat = pmb_mysql_query($requete);
			while(($vue=pmb_mysql_fetch_object($resultat))){
				$stat_view_addview_form=str_replace("!!name_view!!",htmlentities($vue->nom_vue,ENT_QUOTES,$charset),$stat_view_addview_form);
				$stat_view_addview_form=str_replace("!!view_comment!!",htmlentities($vue->comment,ENT_QUOTES, $charset),$stat_view_addview_form);
			}			
			$stat_view_addview_form=str_replace("!!bouton_add_col!!",$btn_add_col,$stat_view_addview_form);
			$stat_view_addview_form=str_replace("!!bouton_reinit_view!!",$bouton_reinit_view,$stat_view_addview_form);
			$stat_view_addview_form=str_replace("!!btn_suppr!!",$btn_suppr,$stat_view_addview_form);
			$stat_view_addview_form=str_replace("!!id_view!!",$vue_id,$stat_view_addview_form);
				
			$res="";		
			$requete="select id_col, nom_col, expression, filtre, ordre, datatype from statopac_vues_col where num_vue='".$vue_id."' order by ordre";
			$resultat=pmb_mysql_query($requete);
			
			if(pmb_mysql_num_rows($resultat) == 0){
				$res="<div class='row'>".$msg["stat_no_col_associate"]."</div>";
				$stat_view_addview_form=str_replace("!!table_colonne!!",$res,$stat_view_addview_form);		
				$stat_view_addview_form=str_replace("!!view_title!!",$msg["stat_view_modif_title"],$stat_view_addview_form);
				return $stat_view_addview_form;
			} else {
				$res="<table style='width:100%'>\n";
				$res.="<tr><th>".$msg["stat_col_order"]."</th><th>".$msg["stat_col_name"]."</th><th>".$msg["stat_col_expr"]."</th><th>".$msg["stat_col_filtre"]."</th><th>".$msg['stat_col_type']."</th>";
				$parity=1;
				$n=0;
				while ($r=pmb_mysql_fetch_object($resultat)) {
					if ($parity % 2) {
						$pair_impair = "even";
					} else {
						$pair_impair = "odd";
					}
		
					$parity+=1;
					$tr_javascript=" onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='$pair_impair'\"  ";
					$action_td=" onmousedown=\"document.location='./admin.php?categ=opac&sub=stat&section=colonne&act=update_col&id_col=$r->id_col&id_view=$vue_id';\" ";
					$res.="<tr class='$pair_impair' style='cursor: pointer' $tr_javascript>";
					$res.="<td class='center'>";
				    $res.="<input type='button' class='bouton_small' value='-' onClick='document.location=\"./admin.php?categ=opac&sub=stat&section=view_gestion&act=update_view&move=down&id_col=".$r->id_col."&id_view=$vue_id\"'/></a>";
				    $res .= "<input type='button' class='bouton_small' value='+' onClick='document.location=\"./admin.php?categ=opac&sub=stat&section=view_gestion&act=update_view&move=up&id_col=".$r->id_col."&id_view=$vue_id\"'/>";
					$res.="</td>";
					$res.="<td $action_td class='center'><b>".htmlentities($r->nom_col,ENT_QUOTES,$charset)."</b></td>
						<td $action_td class='center'>".htmlentities($r->expression,ENT_QUOTES,$charset)."</td>
						<td $action_td class='center'>".htmlentities($r->filtre,ENT_QUOTES,$charset)."</td>
						<td $action_td class='center'>".htmlentities($r->datatype,ENT_QUOTES,$charset)."</td>";
				}
				$res.="</tr></table>";
				$stat_view_addview_form=str_replace("!!table_colonne!!",$res,$stat_view_addview_form);
				$stat_view_addview_form=str_replace("!!view_title!!",$msg["stat_view_modif_title"],$stat_view_addview_form);
			}
		}
		return $stat_view_addview_form;
	}
	
	/**
	 * On fait appel au formulaire d'ajout de colonne
	 */
	public function do_col_form($id_col=''){
		global $stat_view_addcol_form, $msg, $charset, $id_view; 
		
		$datatype_list=array("small_text"=>"Texte","text"=>"Texte large","integer"=>"Entier","date"=>"Date","datetime"=>"Date/Heure","float"=>"Nombre &agrave; virgule");
		if(!$id_col)	{
			$stat_view_addcol_form=str_replace("!!col_name!!",'',$stat_view_addcol_form);
			$stat_view_addcol_form=str_replace("!!expr_col!!",'',$stat_view_addcol_form);
			$stat_view_addcol_form=str_replace("!!btn_suppr!!",'',$stat_view_addcol_form);
			$stat_view_addcol_form=str_replace("!!expr_filtre!!",'',$stat_view_addcol_form);
			$stat_view_addcol_form=str_replace("!!id_view!!",$id_view,$stat_view_addcol_form);
			$stat_view_addcol_form=str_replace("!!id_col!!",$id_col,$stat_view_addcol_form);
			$stat_view_addcol_form=str_replace("!!col_title!!",$msg["stat_col_create_title"],$stat_view_addcol_form);
						
			//liste des type de données
			$t_list="<select name='datatype'>\n";
			reset($datatype_list);
			foreach ($datatype_list as $key=>$val){
				$t_list.="<option value='".$key."'";
				$t_list.=">".$val."</option>\n";
			}
			$t_list.="</select>\n";
			$stat_view_addcol_form=str_replace("!!datatype!!",$t_list,$stat_view_addcol_form);
			
			return $stat_view_addcol_form;
		} else {
			$requete="select nom_col, expression, filtre, datatype from statopac_vues_col where id_col='".$id_col."'";
			$resultat=pmb_mysql_query($requete);
			while (($col=pmb_mysql_fetch_object($resultat))){
				$col_name = htmlentities($col->nom_col,ENT_QUOTES,$charset);
				$expr = htmlentities($col->expression,ENT_QUOTES,$charset);
				$filtre = htmlentities($col->filtre,ENT_QUOTES,$charset);
				$datatype = htmlentities($col->datatype,ENT_QUOTES,$charset);
			}
			$stat_view_addcol_form=str_replace("!!col_name!!",$col_name,$stat_view_addcol_form);
			$stat_view_addcol_form=str_replace("!!expr_col!!",$expr,$stat_view_addcol_form);
			$stat_view_addcol_form=str_replace("!!expr_filtre!!",$filtre,$stat_view_addcol_form);
			$btn_suppr = "<input class='bouton' type='submit'  value='$msg[63]' onClick='if(confirm_delete()) this.form.act.value=\"suppr_col\"';/>";
			$stat_view_addcol_form=str_replace("!!btn_suppr!!",$btn_suppr,$stat_view_addcol_form);
			$stat_view_addcol_form=str_replace("!!col_title!!",$msg["stat_col_modif_title"],$stat_view_addcol_form);
			$stat_view_addcol_form=str_replace("!!id_view!!",$id_view,$stat_view_addcol_form);
			$stat_view_addcol_form=str_replace("!!id_col!!",$id_col,$stat_view_addcol_form);
			
			//liste des types de données
			$t_list="<select name='datatype'>\n";
			reset($datatype_list);
			foreach ($datatype_list as $key=>$val){
				$t_list.="<option value='".$key."'";
				if ($datatype==$key) $t_list.=" selected";
				$t_list.=">".$val."</option>\n";
			}
			$t_list.="</select>\n";
			$stat_view_addcol_form=str_replace("!!datatype!!",$t_list,$stat_view_addcol_form);
			
		}
		
		return $stat_view_addcol_form;
	}
	
	/**
	 * On insere ou enregistre une colonne
	 */
	public function save_col($id_col='', $col_name='',$expr_col='',$expr_filtre='', $vue_id=''){
		global $datatype;
		
		if((!$id_col) && $vue_id){
			$req_ordre = "select max(ordre) from statopac_vues_col where num_vue='".addslashes($vue_id)."'";
			$resultat = pmb_mysql_query($req_ordre);
			if($resultat) $order = pmb_mysql_result($resultat,0,0);
			else $order=0;
			$ordre = $order+1;
			$req = "INSERT INTO statopac_vues_col(nom_col,expression,filtre,num_vue, ordre,datatype) VALUES ('".$col_name."', '".$expr_col."','".$expr_filtre."','".$vue_id."','".$ordre."', '".$datatype."')";
			$resultat=pmb_mysql_query($req);
		} else {
			$rqt="select * from statopac_vues_col where nom_col='".$col_name."' and expression='".$expr_col."' and num_vue='".$vue_id."' and filtre='".$expr_filtre."' and datatype='".$datatype."'";
			$res_exist = pmb_mysql_query($rqt);
			if(pmb_mysql_num_rows($res_exist)){
				$modif=0;
			} else $modif=1;
			$req = "UPDATE statopac_vues_col SET nom_col='".$col_name."', expression='".$expr_col."', num_vue='".$vue_id."', filtre='".$expr_filtre."', datatype='".$datatype."', maj_flag=$modif  WHERE id_col='".$id_col."'";
			$resultat=pmb_mysql_query($req);
		}
	} 
	
	/**
	 * On insere ou enregistre une vue
	 */
	public function save_view($vue_id='', $view_name='',$view_comment=''){
		if(!$vue_id){
			$req = "INSERT INTO statopac_vues(nom_vue,comment) VALUES ('".$view_name."', '".$view_comment."')";
			pmb_mysql_query($req);
		} else {
			$req = "UPDATE statopac_vues SET nom_vue='".$view_name."', comment='".$view_comment."' WHERE id_vue='".$vue_id."'";
			pmb_mysql_query($req);
		}
	}
	
	/**
	 * Supprime une vue et ces colonnes associées
	 */
	public function delete_view($vue_id){
		if($vue_id){
			$req="DELETE FROM statopac_vues where id_vue='".$vue_id."'";
			pmb_mysql_query($req);
			$req="DELETE FROM statopac_vues_col where num_vue='".$vue_id."'";
			pmb_mysql_query($req);
			$req="DELETE FROM statopac_request where num_vue='".$vue_id."'";
			pmb_mysql_query($req);
			$req="DROP TABLE statopac_vue_".$vue_id;
			pmb_mysql_query($req);
		}
	}
	
	/**
	 * Réinitialise la vue à zéro
	 */
	public function reinitialiser_view($vue_id=''){
		if($vue_id){
			$req="DELETE FROM statopac_vues_col where num_vue='".$vue_id."'";
			pmb_mysql_query($req);
			$req="DELETE FROM statopac_request where num_vue='".$vue_id."'";
			pmb_mysql_query($req);
			$req="DELETE FROM statopac_vue_".$vue_id;
			pmb_mysql_query($req);
			$req="update statopac_vues set date_consolidation='0000-00-00 00:00:00', date_debut_log='0000-00-00 00:00:00', date_fin_log='0000-00-00 00:00:00' where num_vue='".$vue_id."'";
			pmb_mysql_query($req);
		}
	}
	
	/**
	 * Supprime une colonne
	 */
	public function delete_col($id_col){
		if($id_col){
			$req="SELECT nom_col,num_vue FROM statopac_vues_col WHERE id_col='".$id_col."'";
			$res=pmb_mysql_query($req);
			if(pmb_mysql_num_rows($res)){
				//On supprime la colonne de la vue
				$id_vue=pmb_mysql_result($res,0,1);
				pmb_mysql_query("ALTER TABLE statopac_vue_".$id_vue." DROP `".pmb_mysql_result($res,0,0)."`");
				$req="DELETE FROM statopac_vues_col where id_col='".$id_col."'";
				pmb_mysql_query($req);
				//On recalcule l'ordre des colonnes
				$req="SELECT id_col FROM statopac_vues_col WHERE num_vue ='".$id_vue."' ORDER BY ordre";
				$res=pmb_mysql_query($req);
				if(pmb_mysql_num_rows($res)){
					$ordre=1;
					while ($ligne=pmb_mysql_fetch_object($res)) {
						pmb_mysql_query("UPDATE statopac_vues_col SET ordre='".$ordre."' WHERE id_col='".$ligne->id_col."'");
						$ordre++;
					}
				}
			}	
		}
	}

	/**
	 * Changer l'ordre dans la liste en montant un élément
	 */
	public function monter_element($col_id=''){
		$requete="select ordre from statopac_vues_col where id_col='".$col_id."'";
		$resultat=pmb_mysql_query($requete);
		$ordre=pmb_mysql_result($resultat,0,0);
		$requete="select max(ordre) as ordre from statopac_vues_col where ordre<".addslashes($ordre);
		$resultat=pmb_mysql_query($requete);
		$ordre_max=@pmb_mysql_result($resultat,0,0);
		if ($ordre_max) {
			$requete="select id_col from statopac_vues_col where ordre='".addslashes($ordre_max)."' limit 1";
			$resultat=pmb_mysql_query($requete);
			$idcol_max=pmb_mysql_result($resultat,0,0);
			$requete="update statopac_vues_col set ordre='".addslashes($ordre_max)."' where id_col='".$col_id."'";
			pmb_mysql_query($requete); 
			$requete="update statopac_vues_col set ordre='".addslashes($ordre)."' where id_col='".addslashes($idcol_max)."'";
			pmb_mysql_query($requete);
		}
	}
	
	/**
	 * Changer l'ordre dans la liste en descendant un élément
	 */
	public function descendre_element($col_id=''){
		$requete="select ordre from statopac_vues_col where id_col='".$col_id."'";
		$resultat=pmb_mysql_query($requete);
		$ordre=pmb_mysql_result($resultat,0,0);
		$requete="select min(ordre) as ordre from statopac_vues_col where ordre>".addslashes($ordre);
		$resultat=pmb_mysql_query($requete);
		$ordre_min=@pmb_mysql_result($resultat,0,0);
		if ($ordre_min) {
			$requete="select id_col from statopac_vues_col where ordre='".addslashes($ordre_min)."' limit 1";
			$resultat=pmb_mysql_query($requete);
			$idcol_min=pmb_mysql_result($resultat,0,0);
			$requete="update statopac_vues_col set ordre='".addslashes($ordre_min)."'  where id_col='".$col_id."'";
			pmb_mysql_query($requete);
			$requete="update statopac_vues_col set ordre='".addslashes($ordre)."'  where id_col='".addslashes($idcol_min)."'";
			pmb_mysql_query($requete);
		}
	}
	
	/**
	 * Verification de la presence et de la syntaxe des parametres de la requete
	 * retourne true si OK, le nom du parametre entre parentheses sinon
	 */
	public function check_param($requete) {
		$query_parameters=array();
		//S'il y a des termes !!*!! dans la requête alors il y a des paramètres
		if (preg_match_all("|!!(.*)!!|U",$requete,$query_parameters)) {
			for ($i=0; $i<count($query_parameters[1]); $i++) {
				if (!preg_match("/^[A-Za-z][A-Za-z0-9_]*$/",$query_parameters[1][$i])) {
					return "(".$query_parameters[1][$i].")";
				}
			}
		}
		return true;
	}
	
	/**
	 * On fait appel au formulaire d'ajout d'une requete à la vue
	 */
	public function do_import_req_form($vue_id=''){
		global $stat_view_import_req_form;
		
		$action="./admin.php?categ=opac&sub=stat&section=importsuite&id_view=".$vue_id;
		$stat_view_import_req_form=str_replace("!!action!!",$action,$stat_view_import_req_form);
		
		return $stat_view_import_req_form;
	}

	/**
	 * On importe la requête à la vue
	 */
	public function do_import_req($vue_id=''){
		global $msg, $charset, $current_module;
		
		if($vue_id){
			$erreur=0;
			$userfile_name = $_FILES['f_fichier']['name'];
			$userfile_temp = $_FILES['f_fichier']['tmp_name'];
			$userfile_moved = basename($userfile_temp);
			
			$userfile_name = preg_replace("/ |'|\\|\"|\//m", "_", $userfile_name);
			
			// création
			if (move_uploaded_file($userfile_temp,'./temp/'.$userfile_moved)) {
				$fic=1;
			}
			
			if (!$fic) {
				$erreur=$erreur+10;
			}else{
				$fp = fopen('./temp/'.$userfile_moved , "r" );
				$contenu = fread ($fp, filesize('./temp/'.$userfile_moved));
				if (!$fp || $contenu=="") $erreur=$erreur+100; ;
				fclose ($fp) ;
			}
			
			//Vérification du contenu du fichier
			$arrayCols=array();
			$tmpLignes=explode("\n",$contenu);
			foreach ($tmpLignes as $ligne){
				if(preg_match('`^\#col=(.+)`',$ligne,$out)){
					$arrayCols[]=unserialize($out[1]);
				}
			}
			if(!count($arrayCols)){
				$erreur=5;
			}
			
			if(!$erreur){
				
				//Traitement encodage fichier
				if(strpos($contenu,'#charset=iso-8859-1')!==false && $charset=='utf-8'){
					//mise à jour de l'encodage du contenu
					$contenu = utf8_encode($contenu);
					//mise à jour de l'entête des paramètres
					$contenu = str_replace('<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>', '<?xml version=\"1.0\" encoding=\"utf-8\"?>', $contenu) ;
				}elseif(strpos($contenu,'#charset=utf-8')!==false && $charset=='iso-8859-1'){
					//mise à jour de l'encodage du contenu
					$contenu = utf8_decode($contenu);
					//mise à jour de l'entête des paramètres
					$contenu = str_replace('<?xml version=\"1.0\" encoding=\"utf-8\"?>', '<?xml version=\"1.0\" encoding=\"iso-8859-1\"?>', $contenu) ;
				}
				//On distingue les différentes parties, la requête se trouve en $arrayFichier[2]
				preg_match('`(.+requete=\')(.+)(\', comment=\'.+)`s',$contenu,$arrayFichier);
				unset($arrayFichier[0]);
				//On va vérifier les colonnes de la vue existante
				$nbColAjout=0;
				foreach ($arrayCols as $col){
					$res = pmb_mysql_query("SELECT * FROM statopac_vues_col WHERE num_vue=".$vue_id." AND expression='".addslashes($col[1])."'");
					if($res){
						if(!pmb_mysql_num_rows($res)){
							//on va ajouter une colonne, on vérifie qu'il n'y a pas déjà une colonne avec le même nom
							$ok = false;
							$suffixe=0;
							while(!$ok){
								$res2 = pmb_mysql_query("SELECT * FROM statopac_vues_col WHERE num_vue=".$vue_id." AND nom_col='".addslashes($col[0]).($suffixe?$suffixe:"")."'");
								if($res2){
									if(!pmb_mysql_num_rows($res2)){
										$ok=true;
										if($suffixe){
											$arrayFichier[2] = preg_replace('`(?<=\W)'.$col[0].'(?<!\W)`',$col[0].$suffixe,$arrayFichier[2]);
											$col[0]=$col[0].$suffixe;
										}
									}
								}else{
									echo pmb_mysql_error()."<br />";
								}
								$suffixe++;
							}
							pmb_mysql_query("INSERT INTO statopac_vues_col
										SET nom_col='".addslashes($col[0])."',
										expression='".addslashes($col[1])."',
										filtre='".addslashes($col[2])."',
										datatype='".addslashes($col[3])."',
										num_vue=".$vue_id);
							$nbColAjout++;
						}else{
							//une colonne existe déjà avec la même fonction : on adapte la requête qu'on importe
							$row=pmb_mysql_fetch_object($res);
							$arrayFichier[2] = str_replace('`(?<=\W)'.$col[0].'(?<!\W)`',$row->nom_col,$arrayFichier[2]);
						}
					}else{
						echo pmb_mysql_error()."<br />";
					}
				}
				
				//Ajout requete
				$contenu=implode("",$arrayFichier);
				pmb_mysql_query($contenu) ;
				if (pmb_mysql_error()) {
					echo pmb_mysql_error()."<br /><br />".htmlentities($contenu,ENT_QUOTES, $charset)."<br /><br />" ;
				}else{
					$idStat = pmb_mysql_insert_id();

					//maj num_vue sur requete
					pmb_mysql_query("UPDATE statopac_request SET num_vue=".$vue_id." WHERE idproc=".$idStat);
					
					$add_url='';
					if($nbColAjout){
						$add_url='&alert_consolid=1';
					}
					print "<script type=\"text/javascript\">document.location='./admin.php?categ=opac&sub=stat&section=view_list&open_view=".$vue_id.$add_url."';</script>";
				}
			
			} else {
				print "<h1>".$msg['stat_import_invalide']."</h1>
						<form class='form-$current_module' name=\"dummy\" method=\"post\" action=\"./admin.php?categ=opac&sub=stat&section=import&id_view=".$vue_id."\" >
						Error code = $erreur
						<input type='submit' class='bouton' name=\"id_form\" value=\"Ok\" />
						</form>";
			}
			print "</div>";
			
			//On efface le fichier temporaire
			if ($userfile_name) {
				unlink('./temp/'.$userfile_moved);
			}
		}else{
			$erreur=1;
			print "<h1>".$msg['stat_import_invalide']."</h1>
			<form class='form-$current_module' name=\"dummy\" method=\"post\" action=\"./admin.php?categ=opac&sub=stat&section=import&id_view=".$vue_id."\" >
			Error code = $erreur
			<input type='submit' class='bouton' name=\"id_form\" value=\"Ok\" />
			</form>";
		}

	}
	
	public static function get_id_from_label($label) {
		$query = "SELECT id_vue FROM statopac_vues WHERE nom_vue='".addslashes($label)."'";
		$result = pmb_mysql_query($query);
		if (pmb_mysql_num_rows($result)) {
			return pmb_mysql_result($result, 0, 'id_vue');
		}
		return 0;
	}
}
?>