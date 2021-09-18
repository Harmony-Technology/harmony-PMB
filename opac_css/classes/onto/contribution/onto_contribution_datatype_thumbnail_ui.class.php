<?php
// +-------------------------------------------------+
// � 2002-2011 PMB Services / www.sigb.net pmb@sigb.net et contributeurs (voir www.sigb.net)
// +-------------------------------------------------+
// $Id: onto_contribution_datatype_thumbnail_ui.class.php,v 1.3 2021/04/15 14:26:10 jlaurent Exp $

if (stristr($_SERVER['REQUEST_URI'], ".class.php")) die("no access");

require_once($include_path.'/templates/onto/contribution/onto_contribution_datatype_ui.tpl.php');

/**
 * class onto_common_datatype_small_text_ui
 * 
 */
class onto_contribution_datatype_thumbnail_ui extends onto_common_datatype_file_ui {

	/** Aggregations: */

	/** Compositions: */

	 /*** Attributes: ***/


	/**
	 * 
	 *
	 * @param property property la propri�t� concern�e
	 * @param restriction $restrictions le tableau des restrictions associ�es � la propri�t� 
	 * @param array datas le tableau des datatypes
	 * @param string instance_name nom de l'instance
	 * @param string flag Flag

	 * @return string
	 * @static
	 * @access public
	 */
	public static function get_form($item_uri,$property, $restrictions,$datas, $instance_name,$flag) {
		global $msg,$charset,$ontology_tpl;

		$form = parent::get_form($item_uri, $property, $restrictions, $datas, $instance_name, $flag);
		$form.= $ontology_tpl['onto_contribution_datatype_docnum_file_script'];

		//recomposition des infos pour mettre dans le champ cach�
		$info = $datas[0]->get_value(true);
		$data = urlencode(json_encode(["name"=>  $info->name, "id_upload_directory" => $info->id_upload_directory, "path" => $info->path]));
		
		$form = str_replace('!!onto_contribution_file_template!!', self::get_template($datas), $form);
		$form = str_replace('!!onto_row_content_file_data!!', $data, $form);
		$form = str_replace('!!instance_name!!', $instance_name, $form);
		$form = str_replace('!!property_name!!', $property->pmb_name, $form);
		
		return $form;
	} // end of member function get_form
	
	
	/**
	 * Retourne le template pour une ligne
	 *
	 * @param string $item_uri
	 * @param onto_common_property $property
	 * @param string $range
	 * @param string|int $order
	 * @param array $data
	 * @param string|boolean $is_draft
	 * @return mixed
	 */
	private static function get_template($datas)
	{
	    if ($datas[0]->get_value(true)->thumbnail){
    	    return "<img src=data:image/png;base64,".$datas[0]->get_value(true)->thumbnail."></img>";
	    }
	    return '';
	}
	
} // end of onto_common_datatype_ui