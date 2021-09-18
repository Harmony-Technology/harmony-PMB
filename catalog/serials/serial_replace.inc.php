<?php
// +-------------------------------------------------+
// � 2002-2004 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: serial_replace.inc.php,v 1.4 2021/04/23 06:26:18 dgoron Exp $

if (stristr($_SERVER['REQUEST_URI'], ".inc.php")) die("no access");

global $class_path, $serial_id;
require_once($class_path."/entities/entities_serials_controller.class.php");

$entities_serials_controller = new entities_serials_controller($serial_id);
$entities_serials_controller->set_action('replace');
$entities_serials_controller->proceed();

?>