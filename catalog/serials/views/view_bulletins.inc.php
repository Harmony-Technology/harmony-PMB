<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: view_bulletins.inc.php,v 1.37 2021/03/18 08:32:18 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

//DG - 12/07/2020 - Attention � la globalisation de variables - fichier inclus dans le contexte d'une fonction
global $msg, $nb_per_page_a_search;
global $pmb_collstate_advanced;
global $serial_id, $bull_date_start, $bull_date_end;

$debut = intval($debut);

// affichage des bulletinages associ�s
// on r�cup�re le nombre de lignes qui vont bien
$bulletins = "
		<script src='javascript/ajax.js'></script>		
		<script type='text/javascript' src='./javascript/bulletin_list.js'></script>
		<script type='text/javascript'>
 			var msg_select_all = '".$msg["notice_expl_check_all"]."';
			var msg_unselect_all = '".$msg["notice_expl_uncheck_all"]."';
 			var msg_have_select_bulletin = '".$msg["bulletin_have_select"]."';
		</script>
		<form action='".$base_url."' method='post' name='filter_form'>
			<input type='hidden' name='location' value='".$location."'/>
			<table>";

$date_debut = get_input_date('bull_date_start', 'bull_date_start', $bull_date_start, false, 'document.filter_form.submit();');
$date_fin = get_input_date('bull_date_end', 'bull_date_end', $bull_date_end, false, 'document.filter_form.submit();');

$bulletins .= "
		<tr>
			<th></th>			
			<th>".$msg[4025]."</th>
			<th>".$msg[4026]."</th>
			<th>".$msg['bulletin_mention_periode']."</th>
			<th>".$msg['bulletin_mention_titre_court']."</th>
			<th>".$msg['bul_articles']."</th>
			<th>".$msg['bul_docnum']."</th>
			<th>".$msg['bul_exemplaires']."</th>";
			
if ($pmb_collstate_advanced) {
	$bulletins .= "<th>".$msg['bul_collstate']."</th>";
}

$bulletins .= "
		</tr>
		<tr>
			<th class='align_left'>
				<input type='checkbox' name='check_all_bulletins_".$serial_id."' value='1' title='".$msg["notice_expl_check_all"]."' 
					onClick=\"check_all_bulletins(this, document.getElementById('bulletins_to_check_".$serial_id."').value);\">
				<input id='bulletins_to_check_".$serial_id."' type='hidden' value='!!bulletins_to_check!!' name='bulletins_to_check_".$serial_id."'>
				<img src='".get_url_icon('basket_small_20x20.gif')."' class='align_middle' alt='basket' title='".$msg[400]."' onClick=\"
					if(check_if_bulletins_checked(document.getElementById('bulletins_to_check_".$serial_id."').value,'cart'))
					openPopUp('./cart.php?object_type=BULL&item=' + get_bulletins_checked(document.getElementById('bulletins_to_check_".$serial_id."').value),
					'cart')\">
			</th>
			<th>
				<input type='text' autfield='f_bull_date_id' completion='bull_num' autocomplete='off' id='bull_num_deb_".$serial_id."' class='saisie-10em' name='aff_bulletins_restrict_numero' onchange='this.form.submit();' value='".htmlentities($aff_bulletins_restrict_numero,ENT_QUOTES, $charset)."'/>
				<input type='hidden' name='f_bull_date_id' id='f_bull_date_id'>
			</th>		
			<th>".$msg["search_bull_start"]." ".$date_debut." ".$msg["search_bull_end"]." ".$date_fin."</th>
			<th><input type='text' class='saisie-10em' name='aff_bulletins_restrict_periode' onchange='this.form.submit();' value='".htmlentities($aff_bulletins_restrict_periode,ENT_QUOTES, $charset)."'/></th>
			<th></th><th></th><th></th><th></th>";
if ($pmb_collstate_advanced) {
	$bulletins .= "<th></th>";
}
$bulletins .="
		</tr>";

$bulletins_to_check=array();
// ici : affichage par page des bulletinages associ�s
// on lance la vraie requette

$clause = ($serial_id ? "and bulletin_notice='$serial_id'" : "")." ".$clause;

$query = "SELECT distinct uni.bulletin_id FROM (";
$query .= "SELECT distinct bulletin_id FROM bulletins ";
if($location) {
	$query .= "JOIN exemplaires ON expl_bulletin=bulletin_id AND expl_location='$location' ";
}
if($clause) {
	$query .= " WHERE 1 ".$clause." ";
}
//UNION sur les documents num�riques avec localisation(s) s�lectionn�es ou TOUTES
$query .= " UNION ";
$query .= "SELECT distinct bulletin_id FROM bulletins ";
if($location) {
	$query .= "JOIN explnum ON explnum_bulletin=bulletin_id LEFT JOIN explnum_location ON explnum_location.num_explnum = explnum.explnum_id ";
}
if($clause) {
    if($location) {
        $query .= " WHERE (explnum_location.num_location='$location' OR explnum_location.num_location IS NULL) ".$clause." ";
    } else {
        $query .= " WHERE 1 ".$clause." ";
    }
}
$query .= ") AS uni JOIN bulletins ON bulletins.bulletin_id = uni.bulletin_id ";

$query .= $filter_date." ORDER BY date_date DESC, bulletin_numero*1 DESC, bulletin_id DESC LIMIT $debut,$nb_per_page_a_search";

$myQuery = pmb_mysql_query($query);
if((pmb_mysql_num_rows($myQuery))) {
	$parity=1;
	while(($bul = pmb_mysql_fetch_object($myQuery))) {
		$collstates = array();
		if ($pmb_collstate_advanced) {
			$query = "SELECT collstate_bulletins_num_collstate, state_collections FROM collstate_bulletins JOIN collections_state ON collections_state.collstate_id = collstate_bulletins.collstate_bulletins_num_collstate WHERE collstate_bulletins_num_bulletin = '".$bul->bulletin_id."'";
			$result = pmb_mysql_query($query);			
			if (pmb_mysql_num_rows($result)) {
				while ($row = pmb_mysql_fetch_object($result)) {
					$collstates[$row->collstate_bulletins_num_collstate] = $row->state_collections;
				}
			}
		}
		$bulletin = new bulletinage($bul->bulletin_id,0,'',$location,false);
		if ($parity % 2) {
			$pair_impair = "even";
		}
		else {
			$pair_impair = "odd";
		}
		$parity += 1;				
		
        $href_start="<a href='".bulletinage::get_permalink($bulletin->bulletin_id)."' style='display:block;'>";        
        $tr_surbrillance = " onmouseover=\"this.className='surbrillance'\" onmouseout=\"this.className='".$pair_impair."'\" ";
		$bulletins .= "<tr class='".$pair_impair."' ".$tr_surbrillance." style='cursor: pointer'><td>";
		$bulletins .= "<input type='checkbox' name='checkbox_bulletin[".$bulletin->bulletin_id."]' id='checkbox_bulletin[".$bulletin->bulletin_id."]' value='1'>";
		$drag="<span id=\"BULL_drag_".$bulletin->bulletin_id."\" dragicon='".get_url_icon('icone_drag_notice.png')."' dragtext=\"".htmlentities($bulletin->bulletin_numero,ENT_QUOTES, $charset)."\" draggable=\"yes\" dragtype=\"notice\" callback_before=\"show_carts\" callback_after=\"\" style=\"padding-left:7px\"><img src=\"".get_url_icon('notice_drag.png')."\"/></span>";
		$bulletins .= "$drag</td><td>".$href_start;
		$bulletins .= $bulletin->bulletin_numero;
		$bulletins .= "</a></td><td>".$href_start;
		$bulletins .= $bulletin->aff_date_date;
		$bulletins .= "</a></td><td>".$href_start;
		$bulletins .= htmlentities($bulletin->mention_date, ENT_QUOTES, $charset);
		$bulletins .= "</a></td><td>".$href_start;
		$bulletins .= htmlentities($bulletin->bulletin_titre, ENT_QUOTES, $charset);
		$bulletins .= "</a></td><td class='center'>" ;
		if ($bulletin->nb_analysis) {
			$bulletins .= $bulletin->nb_analysis."&nbsp;<img src='".get_url_icon('basket_small_20x20.gif')."' class='align_middle' alt='basket' title='".$msg[400]."' onClick=\"openPopUp('./cart.php?object_type=BULL&item=".$bulletin->bulletin_id."&what=DEP', 'cart')\">"; 
		}
		else {
			$bulletins .= "&nbsp;";
		}
		$bulletins .= "</td><td class='center'>".$href_start;
		if (!empty($bulletin->nbexplnum)) {
			$bulletins .= $bulletin->nbexplnum; 
		} else {
			$bulletins .= "&nbsp;";
		}
		$bulletins .= "</a></td><td class='center'>".$href_start;
		if (is_array($bulletin->expl) && !empty($bulletin->expl)) {
			$bulletins .= count($bulletin->expl); 
		} else {
			$bulletins .= "&nbsp;";
		}
		$bulletins .= "</a></td>";
		if ($pmb_collstate_advanced) {
			$bulletins .= "<td>";
			$collstate_list = "";
			foreach($collstates as $id => $collstate) {
				if($collstate_list) {
					$collstate_list.= "<br/>";
				}
				$collstate_list .="<a href='./catalog.php?categ=serials&sub=collstate_bulletins_list&id=".$id."&serial_id=".$serial_id."&bulletin_id=0'>".$collstate."</a>";
				
			}			
			$bulletins .= $collstate_list."</td>";
		}
		
		$bulletins .= "</tr>";
		$bulletins_to_check[]=$bulletin->bulletin_id;
	}
	$bulletins .= "</table></form>";
} else {
	$bulletins .= "</table><br />";
   	if ($aff_bulletins_restrict_periode || $aff_bulletins_restrict_date || $aff_bulletins_restrict_numero) $bulletins .= $msg['perio_restrict_no_bulletin'];
   	else $bulletins .= $msg[4024] ;
}
$bulletins .= "<script type='text/javascript'>ajax_parse_dom();</script>";
$bulletins = str_replace('!!bulletins_to_check!!', implode(',',$bulletins_to_check), $bulletins);
// barre de navigation par page
$pages_display = aff_pagination ($base_url."&location=$location&bull_date_start=$bull_date_start&bull_date_end=$bull_date_end", $nbr_lignes, $nb_per_page_a_search, $page, 10, false, true);
?>
