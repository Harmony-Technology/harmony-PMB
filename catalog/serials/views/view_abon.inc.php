<?php
// +-------------------------------------------------+
// | 2002-2007 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: view_abon.inc.php,v 1.4 2017/06/22 10:19:48 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path;
global $include_path;

$bulletins= "<form action='".$base_url."' method='post' name='filter_form'><input type='hidden' name='location' value='$location'/>" ;
require_once($class_path."/abts_abonnements.class.php");

$abonnements=new abts_abonnements($serial_id,$location);
$bulletins.=$abonnements->show_list();

$bulletins.= "</form>" ;
$pages_display = "";
?>
