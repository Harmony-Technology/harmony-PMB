<?php
// +-------------------------------------------------+
// © 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: flux_rss.tpl.php,v 1.4 2017/10/19 14:42:59 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".tpl.php")) die("no access");

global $tpl_liste_item_tableau;
global $tpl_liste_item_tableau_ligne;

$tpl_liste_item_tableau = "
<table>
	<tr>
		<th>".$this->msg["selection_opac"]."</th>
		<th>".$msg['dsi_menu_flux']."</th>
	</tr>
	!!lignes_tableau!!
</table>
";

$tpl_liste_item_tableau_ligne = "
	<tr class='!!pair_impair!!' '!!tr_surbrillance!!' >
		<td><input value='1' id='flux_rss_selected_!!id!!' name='flux_rss_selected_!!id!!' !!selected!! type='checkbox'></td>
		<td !!td_javascript!! >!!name!!</td>
	</tr>
";
?>
