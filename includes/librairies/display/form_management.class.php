<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
* Define the different tools for the entire plugin
*
* @author Eoxia <dev@eoxia.com>
* @version 1.1
* @package wpshop
* @subpackage librairies
*/

/**
* Define the different tools for the entire plugin
* @package wpshop
* @subpackage librairies
*/
class wpshop_form_management {

	var $errors = array(); // Stores store errors
	var $messages = array(); // Stores store messages

	/**
	* Add an error
	*/
	function add_error( $error ) {
		$this->errors[] = $error;
	}

	/**
	* Add a message
	*/
	function add_message( $message ) {
		$this->messages[] = $message;
	}

	/**
	* Get error count
	*/
	function error_count() {
		return sizeof($this->errors);
	}

	/**
	* Get message count
	*/
	function message_count() {
		return sizeof($this->messages);
	}

	/**
	* Output the errors and messages
	*/
	function show_messages() {

		if (!empty($this->errors) && $this->error_count()>0) :
			$message = '<div class="error_bloc">'.__('Errors were detected', 'wpshop').' :<ul>';
			foreach($this->errors as $e) { $message .= '<li>'.$e.'</li>'; }
			$message .= '</ul></div>';
			return $message;
		else : return null;
		endif;
	}

	/** Affiche les elements d'un formulaire
	* @param string $key : nom du champ
	* @param array $args : informations sur le champ
	* @param string $value : valeur par defaut pour le champ
	* @return void
	*/
	function display_field($key, $args, $value=null) {
		if (isset($args['type']) && $args['type']=='password') $type = 'password'; else $type = 'text';
		if (!empty($args['required'])) $required = '*'; else $required = '';
		if (isset($args['class']) && in_array('form-row-last', $args['class'])) $after = '<div class="clear"></div>'; else $after = '';
		$value = !empty($_POST[$key]) ? $_POST[$key] : (!empty($value) ? $value : null);

		$string = '
			<p class="formField '.implode(' ', isset($args['class'])?$args['class']:array()).'">
				<label>'.__($args['label'], 'wpshop').' <span class="required">'.$required.'</span></label><br /><input type="'.$type.'" name="'.$key.'" id="'.$key.'" value="'.$value.'" placeholder="'.$args['placeholder'].'" />
			</p>'.$after;

		return $string;
	}

	/** Valide les champs d'un formlaire
	* @param array $array : Champs a lire
	* @return boolean
	*/
	function validateForm($array) {

		foreach($array as $key => $value):
			$value = $_POST[$key];
			// Si le champ est obligatoire
			if(empty($value) && !empty($array[$key]['required'])) {
				$this->add_error(sprintf(__('The field "%s" is required','wpshop'),$array[$key]['label']));
			}
			elseif(!empty($value) && !empty($array[$key]['type'])) {
				switch($array[$key]['type']) {
					case 'email':
						if(!is_email($value)) {
							$this->add_error(sprintf(__('The field "%s" is incorrect','wpshop'),$array[$key]['label']));
						}
					break;
					case 'postcode':
						if(!wpshop_tools::is_postcode($value)) {
							$this->add_error(sprintf(__('The field "%s" is incorrect','wpshop'),$array[$key]['label']));
						}
					break;
					case 'phone':
						if(!wpshop_tools::is_phone($value)) {
							$this->add_error(sprintf(__('The field "%s" is incorrect','wpshop'),$array[$key]['label']));
						}
					break;
				}
			}
		endforeach;

		return ($this->error_count()==0);
	}

}