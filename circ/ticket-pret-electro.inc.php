<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: ticket-pret-electro.inc.php,v 1.3 2020/11/04 10:40:46 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

require_once("$base_path/circ/pret_func.inc.php");
// liste des pr�ts et r�servations

if (isset($id_groupe)) {
	electronic_ticket_groupe($id_groupe);
} else {
	electronic_ticket($id_empr) ;
}