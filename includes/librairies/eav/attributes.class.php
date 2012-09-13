<?php

/*	Vérification de l'inclusion correcte du fichier => Interdiction d'acceder au fichier directement avec l'url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
 * Define the different method to manage attributes
 *
 *	Define the different method and variable used to manage attributes
 * @author Eoxia <dev@eoxia.com>
 * @version 1.0
 * @package wpshop
 * @subpackage librairies
 */

/**
 * Define the different method to manage attributes
 * @package wpshop
 * @subpackage librairies
 */
class wpshop_attributes{
	/*	Define the database table used in the current class	*/
	const dbTable = WPSHOP_DBT_ATTRIBUTE;
	/*	Define the url listing slug used in the current class	*/
	const urlSlugListing = WPSHOP_URL_SLUG_ATTRIBUTE_LISTING;
	/*	Define the url edition slug used in the current class	*/
	const urlSlugEdition = WPSHOP_URL_SLUG_ATTRIBUTE_LISTING;
	/*	Define the current entity code	*/
	const currentPageCode = 'attributes';
	/*	Define the page title	*/
	const pageContentTitle = 'Attributes';
	/*	Define the page title when adding an attribute	*/
	const pageAddingTitle = 'Add an attribute';
	/*	Define the page title when editing an attribute	*/
	const pageEditingTitle = 'Attribute "%s" edit';
	/*	Define the page title when editing an attribute	*/
	const pageTitle = 'Attributes list';

	/*	Define the path to page main icon	*/
	public $pageIcon = '';
	/*	Define the message to output after an action	*/
	public $pageMessage = '';

	/**
	 *	Get the url listing slug of the current class
	 *
	 *	@return string The table of the class
	 */
	function setMessage($message){
		$this->pageMessage = $message;
	}
	/**
	 *	Get the url listing slug of the current class
	 *
	 *	@return string The table of the class
	 */
	function getListingSlug(){
		return self::urlSlugListing;
	}
	/**
	 *	Get the url edition slug of the current class
	 *
	 *	@return string The table of the class
	 */
	function getEditionSlug(){
		return self::urlSlugEdition;
	}
	/**
	 *	Get the database table of the current class
	 *
	 *	@return string The table of the class
	 */
	function getDbTable(){
		return self::dbTable;
	}
	/**
	 *	Define the title of the page
	 *
	 *	@return string $title The title of the page looking at the environnement
	 */
	function pageTitle(){
		$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action']) : '';
		$objectInEdition = isset($_REQUEST['id']) ? wpshop_tools::varSanitizer($_REQUEST['id']) : '';

		$title = __(self::pageTitle, 'wpshop' );
		if($action != ''){
			if(($action == 'edit') || ($action == 'delete')){
				$editedItem = self::getElement($objectInEdition);
				$title = sprintf(__(self::pageEditingTitle, 'wpshop'), str_replace("\\", "", $editedItem->frontend_label));
			}
			elseif($action == 'add')
				$title = __(self::pageAddingTitle, 'wpshop');
		}
		elseif((self::getEditionSlug() != self::getListingSlug()) && ($_GET['page'] == self::getEditionSlug()))
			$title = __(self::pageAddingTitle, 'wpshop');

		return $title;
	}

	/**
	 *	Define the different message and action after an action is send through the element interface
	 */
	function elementAction(){
		global $wpdb, $initialEavData;

		$pageMessage = $actionResult = '';
		$attribute_undeletable = unserialize(WPSHOP_ATTRIBUTE_UNDELETABLE);

		/*	Start definition of output message when action is doing on another page	*/
		/************		CHANGE THE FIELD NAME TO TAKE TO DISPLAY				*************/
		/****************************************************************************/
		$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'add';
		$saveditem = isset($_REQUEST['saveditem']) ? wpshop_tools::varSanitizer($_REQUEST['saveditem']) : '';
		$set_section = !empty($_REQUEST[self::getDbTable()]['set_section']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable()]['set_section']) : '';
		if ( !empty($_REQUEST[self::getDbTable()]['set_section']) ) unset($_REQUEST[self::getDbTable()]['set_section']);
		if(!empty($action) && ($action=='activate') && (!empty($_REQUEST['id']))){
			$query = $wpdb->update(self::getDbTable(), array('status'=>'moderated'), array('id'=>$_REQUEST['id']));
			wpshop_tools::wpshop_safe_redirect(admin_url('edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES.'&page=' . self::getListingSlug() . "&action=edit&id=" . $_REQUEST['id']));
		}
		if(($action != '') && ($action == 'saveok') && ($saveditem > 0)){
			$editedElement = self::getElement($saveditem);
			$pageMessage = '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('%s succesfully saved', 'wpshop'), '<span class="bold" >' . $editedElement->code . '</span>');
		}
		elseif(($action != '') && ($action == 'deleteok') && ($saveditem > 0)){
			$editedElement = self::getElement($saveditem, "'deleted'");
			$pageMessage = '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('%s succesfully deleted', 'wpshop'), '<span class="bold" >' . $editedElement->code . '</span>');
		}

		if(!isset($_REQUEST[self::getDbTable()]['status'])){
			$_REQUEST[self::getDbTable()]['status'] = 'moderated';
		}
		if(!isset($_REQUEST[self::getDbTable()]['is_historisable'])){
			$_REQUEST[self::getDbTable()]['is_historisable'] = 'no';
		}
		if(!isset($_REQUEST[self::getDbTable()]['is_intrinsic'])){
			$_REQUEST[self::getDbTable()]['is_intrinsic'] = 'no';
		}
		if(!isset($_REQUEST[self::getDbTable()]['is_requiring_unit'])){
			$_REQUEST[self::getDbTable()]['is_requiring_unit'] = 'no';
		}
		if(!isset($_REQUEST[self::getDbTable()]['is_visible_in_front'])){
			$_REQUEST[self::getDbTable()]['is_visible_in_front'] = 'no';
		}
		if(!isset($_REQUEST[self::getDbTable()]['is_used_for_sort_by'])){
			$_REQUEST[self::getDbTable()]['is_used_for_sort_by'] = 'no';
		}
		if(!isset($_REQUEST[self::getDbTable()]['is_visible_in_advanced_search'])){
			$_REQUEST[self::getDbTable()]['is_visible_in_advanced_search'] = 'no';
		}
		if(!isset($_REQUEST[self::getDbTable()]['is_user_defined'])){
			$_REQUEST[self::getDbTable()]['is_user_defined'] = 'no';
		}

		/*	Check frontend input and data type	*/
		if(!empty($_REQUEST[self::getDbTable()]['backend_input'])){
			switch($_REQUEST[self::getDbTable()]['backend_input']){
				case 'short_text':
						$_REQUEST[self::getDbTable()]['backend_input'] = 'text';
						$_REQUEST[self::getDbTable()]['data_type'] = 'varchar';
					break;
				case 'select':
						$_REQUEST[self::getDbTable()]['backend_input'] = 'select';
						$_REQUEST[self::getDbTable()]['data_type'] = 'integer';
					break;
				case 'multiple-select':
						$_REQUEST[self::getDbTable()]['backend_input'] = 'multiple-select';
						$_REQUEST[self::getDbTable()]['data_type'] = 'integer';
					break;
				case 'float_field':
						$_REQUEST[self::getDbTable()]['backend_input'] = 'text';
						$_REQUEST[self::getDbTable()]['data_type'] = 'decimal';
					break;
				case 'date_field':
						$_REQUEST[self::getDbTable()]['backend_input'] = 'text';
						$_REQUEST[self::getDbTable()]['data_type'] = 'datetime';
					break;
				case 'textarea':
						$_REQUEST[self::getDbTable()]['backend_input'] = 'textarea';
						$_REQUEST[self::getDbTable()]['data_type'] = 'text';
					break;
			}
		}
		else{
			$_REQUEST[self::getDbTable()]['backend_input'] = 'text';
		}

		/*	Check if the checkbox for ajax activation is checked for data update	*/
		// if(!isset($_REQUEST[self::getDbTable()]['use_ajax_for_filling_field']) || empty($_REQUEST[self::getDbTable()]['use_ajax_for_filling_field'])){
			// $_REQUEST[self::getDbTable()]['use_ajax_for_filling_field']='no';
		// }
		$_REQUEST[self::getDbTable()]['use_ajax_for_filling_field'] = 'yes';

		/*	Define the database operation type from action launched by the user	 */
		$_REQUEST[self::getDbTable()]['default_value'] = isset($_REQUEST[self::getDbTable()]['default_value'])?str_replace('"', "'", $_REQUEST[self::getDbTable()]['default_value']):'';
		/*****************************		GENERIC				**************************/
		/*************************************************************************/
		$pageAction = (!empty($_REQUEST[self::getDbTable()]['frontend_label']) && isset($_REQUEST[self::getDbTable() . '_action'])) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable() . '_action']) : ((!empty($_GET['action']) && ($_GET['action']=='delete')) ? $_GET['action'] : '');
		$id = isset($_REQUEST[self::getDbTable()]['id']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable()]['id']) : ((!empty($_GET['id'])) ? $_GET['id'] : '');
		if(($pageAction != '') && (($pageAction == 'edit') || ($pageAction == 'editandcontinue'))){
			if(current_user_can('wpshop_edit_attributes')){
				$_REQUEST[self::getDbTable()]['last_update_date'] = date('Y-m-d H:i:s');
				if($pageAction == 'delete'){
					$attribute_code = $_REQUEST[self::getDbTable()]['code'];
					if(!isset($_REQUEST[self::getDbTable()]['code']) || ($_REQUEST[self::getDbTable()]['code'] == '')){
						$attribute = self::getElement($id, "'valid', 'moderated', 'notused'", 'id');
						$attribute_code = $attribute->code;
					}
					if(!in_array($attribute_code, $attribute_undeletable)){
						if(current_user_can('wpshop_delete_attributes')){
							$_REQUEST[self::getDbTable()]['status'] = 'deleted';
						}
						else{
							$actionResult = 'userNotAllowedForActionDelete';
						}
					}
					else{
						$actionResult = 'unDeletableAtribute';
					}
				}
				$actionResult = wpshop_database::update($_REQUEST[self::getDbTable()], $id, self::getDbTable());
			}
			else{
				$actionResult = 'userNotAllowedForActionEdit';
			}
		}
		elseif(($pageAction != '') && (($pageAction == 'delete'))){
			$attribute_code = '';
			if (empty($_REQUEST[self::getDbTable()]['code'])) {
				$attribute = self::getElement($id, "'valid', 'moderated', 'notused', 'deleted'", 'id');
				$attribute_code = $attribute->code;
			}
			if (!in_array($attribute_code, $attribute_undeletable)) {
				if(current_user_can('wpshop_delete_attributes')){
					$_REQUEST[self::getDbTable()]['last_update_date'] = date('Y-m-d H:i:s');
					$_REQUEST[self::getDbTable()]['status'] = 'deleted';
					$actionResult = wpshop_database::update($_REQUEST[self::getDbTable()], $id, self::getDbTable());
				}
				else
					$actionResult = 'userNotAllowedForActionDelete';
			}
			else
				$actionResult = 'unDeletableAtribute';
		}
		elseif(($pageAction != '') && (($pageAction == 'save') || ($pageAction == 'saveandcontinue') || ($pageAction == 'add'))){
			if(current_user_can('wpshop_add_attributes')){
				$_REQUEST[self::getDbTable()]['creation_date'] = date('Y-m-d H:i:s');
				if(trim($_REQUEST[self::getDbTable()]['code']) == ''){
					$_REQUEST[self::getDbTable()]['code'] = $_REQUEST[self::getDbTable()]['frontend_label'];
				}
				$_REQUEST[self::getDbTable()]['code'] = wpshop_tools::slugify(str_replace("\'", "_", str_replace('\"', "_", $_REQUEST[self::getDbTable()]['code'])), array('noAccent', 'noSpaces', 'lowerCase', 'noPunctuation'));
				$code_exists = self::getElement($_REQUEST[self::getDbTable()]['code'], "'valid', 'moderated', 'deleted'", 'code');
				if((is_object($code_exists) || is_array($code_exists)) && (count($code_exists) > 0)){
					$_REQUEST[self::getDbTable()]['code'] = $_REQUEST[self::getDbTable()]['code'] . '_' . (count($code_exists) + 1);
				}
				$actionResult = wpshop_database::save($_REQUEST[self::getDbTable()], self::getDbTable());
				$id = $wpdb->insert_id;
			}
			else{
				$actionResult = 'userNotAllowedForActionAdd';
			}
		}

		/*	When an action is launched and there is a result message	*/
		/************		CHANGE THE FIELD NAME TO TAKE TO DISPLAY				*************/
		/************		CHANGE ERROR MESSAGE FOR SPECIFIC CASE					*************/
		/****************************************************************************/
		if($actionResult != ''){
			$elementIdentifierForMessage = __('the attribute', 'wpshop');
			if(!empty($_REQUEST[self::getDbTable()]['name']))$elementIdentifierForMessage = '<span class="bold" >' . $_REQUEST[self::getDbTable()]['frontend_label'] . '</span>';
			if($actionResult == 'error')
			{/*	CHANGE HERE FOR SPECIFIC CASE	*/
				$pageMessage .= '<img src="' . WPSHOP_ERROR_ICON . '" alt="action error" class="wpshopPageMessage_Icon" />' . sprintf(__('An error occured while saving %s', 'wpshop'), $elementIdentifierForMessage);
				if(WPSHOP_DEBUG_MODE){
					$pageMessage .= '<br/>' . $wpdb->last_error;
				}
			}
			elseif(($actionResult == 'done') || ($actionResult == 'nothingToUpdate'))
			{/*	CHANGE HERE FOR SPECIFIC CASE	*/
				/*****************************************************************************************************************/
				/*************************			CHANGE FOR SPECIFIC ACTION FOR CURRENT ELEMENT				****************************/
				/*****************************************************************************************************************/
				/*	Add the different option for the attribute that are set to combo box for frontend input	*/
				$done_options_value = array();
				$default_value = $_REQUEST[self::getDbTable()]['default_value'];
				$i = 1;
				if(isset($_REQUEST['optionsUpdate'])){
					/**
					 *	Check if there is an attribute code into sended request or if we have to get the code from database (Bug fix) 
					 */
					if(empty($_REQUEST[self::getDbTable()]['code'])){
						$attribute = self::getElement($id, "'valid', 'moderated', 'notused'", 'id');
						$attribute_code = $attribute->code;
					}
					else{
						$attribute_code = $_REQUEST[self::getDbTable()]['code'];
					}

					foreach ($_REQUEST['optionsUpdate'] as $option_key => $option_label){
						$option_value = !empty($_REQUEST['optionsUpdateValue'][$option_key]) ? str_replace(",", ".", $_REQUEST['optionsUpdateValue'][$option_key]) : '';

						if(!in_array($option_value, $done_options_value)){
							/*	Update an existing value only if the value does not exist into existing list	*/
							$label = (($option_label != '') ? $option_label : str_replace(",", ".", $option_value));
							$value = str_replace(",", ".", $option_value);
							if( !WPSHOP_DISPLAY_VALUE_FOR_ATTRIBUTE_SELECT ) {
								$label = $option_label;
								$value = sanitize_title($label);
							}
							$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('last_update_date' => current_time('mysql', 0), 'position' => $i, 'label' => $label, 'value' => $value), array('id' => $option_key));
							$done_options_value[] = str_replace(",", ".", $option_value);

							/*	Check if this value is used for price calculation and make update on the different product using this value	*/
							if($attribute_code == WPSHOP_PRODUCT_PRICE_TAX){
								$query = $wpdb->prepare("SELECT entity_id FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER . " WHERE attribute_id = %d AND value = %d", $id, $option_key);
								$entity_liste_using_this_option_value = $wpdb->get_results($query);

								$query = $wpdb->prepare("
										SELECT
										(SELECT data_type
										FROM " . WPSHOP_DBT_ATTRIBUTE . "
										WHERE code = %s) AS WPSHOP_PRODUCT_PRICE_HT_TYPE,
										(SELECT data_type
										FROM " . WPSHOP_DBT_ATTRIBUTE . "
										WHERE code = %s) AS WPSHOP_PRODUCT_PRICE_TTC_TYPE,
										(SELECT data_type
										FROM " . WPSHOP_DBT_ATTRIBUTE . "
										WHERE code = %s) AS WPSHOP_PRODUCT_PRICE_TAX_AMOUNT_TYPE,
										(SELECT id
										FROM " . WPSHOP_DBT_ATTRIBUTE . "
										WHERE code = %s) AS WPSHOP_PRODUCT_PRICE_HT_ID,
										(SELECT id
										FROM " . WPSHOP_DBT_ATTRIBUTE . "
										WHERE code = %s) AS WPSHOP_PRODUCT_PRICE_TTC_ID,
										(SELECT id
										FROM " . WPSHOP_DBT_ATTRIBUTE . "
										WHERE code = %s) AS WPSHOP_PRODUCT_PRICE_TAX_AMOUNT_ID
										", WPSHOP_PRODUCT_PRICE_HT, WPSHOP_PRODUCT_PRICE_TTC, WPSHOP_PRODUCT_PRICE_TAX_AMOUNT, WPSHOP_PRODUCT_PRICE_HT, WPSHOP_PRODUCT_PRICE_TTC, WPSHOP_PRODUCT_PRICE_TAX_AMOUNT);
								$attribute_types = $wpdb->get_row($query);

								if(is_array($entity_liste_using_this_option_value) && (count($entity_liste_using_this_option_value) > 0)){
									foreach($entity_liste_using_this_option_value as $entity){
										$query = $wpdb->prepare("
												SELECT
												(SELECT value
												FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attribute_types->WPSHOP_PRODUCT_PRICE_HT_TYPE . "
												WHERE attribute_id = %d
												AND entity_id = %d) AS PRICE_HT,
												(SELECT value
												FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attribute_types->WPSHOP_PRODUCT_PRICE_TTC_TYPE . "
												WHERE attribute_id = %d
												AND entity_id = %d) AS PRICE_TTC,
												(SELECT value
												FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attribute_types->WPSHOP_PRODUCT_PRICE_TAX_AMOUNT_TYPE . "
												WHERE attribute_id = %d
												AND entity_id = %d) AS PRICE_TAX_AMOUNT", $attribute_types->WPSHOP_PRODUCT_PRICE_HT_ID, $entity->entity_id, $attribute_types->WPSHOP_PRODUCT_PRICE_TTC_ID, $entity->entity_id, $attribute_types->WPSHOP_PRODUCT_PRICE_TAX_AMOUNT_ID, $entity->entity_id);
										$product_price_info = $wpdb->get_row($query);

										$ht_amount = $ttc_amount = $tax_amount = 0;
										$tax_rate = 1 + (str_replace(",", ".", $option_value) / 100);
										$ht_amount = str_replace(',', '.', $product_price_info->PRICE_HT);
										$ttc_amount = str_replace(',', '.', $product_price_info->PRICE_TTC);
										if(WPSHOP_PRODUCT_PRICE_PILOT == 'HT'){
											$ttc_amount = $ht_amount * $tax_rate;
											$tax_amount = $ttc_amount - $ht_amount;
											$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attribute_types->WPSHOP_PRODUCT_PRICE_TTC_TYPE, array('value' => $ttc_amount), array('entity_id' => $entity->entity_id, 'attribute_id' => $attribute_types->WPSHOP_PRODUCT_PRICE_TTC_ID));
											$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attribute_types->WPSHOP_PRODUCT_PRICE_TAX_AMOUNT_TYPE, array('value' => $tax_amount), array('entity_id' => $entity->entity_id, 'attribute_id' => $attribute_types->WPSHOP_PRODUCT_PRICE_TAX_AMOUNT_ID));
										}
										if(WPSHOP_PRODUCT_PRICE_PILOT == 'TTC'){
											$ht_amount = $ttc_amount / $tax_rate;
											$tax_amount = $ttc_amount - $ht_amount;
											$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attribute_types->WPSHOP_PRODUCT_PRICE_HT_TYPE, array('value' => $ht_amount), array('entity_id' => $entity->entity_id, 'attribute_id' => $attribute_types->WPSHOP_PRODUCT_PRICE_HT_ID));
											$wpdb->update(WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attribute_types->WPSHOP_PRODUCT_PRICE_TAX_AMOUNT_TYPE, array('value' => $tax_amount), array('entity_id' => $entity->entity_id, 'attribute_id' => $attribute_types->WPSHOP_PRODUCT_PRICE_TAX_AMOUNT_ID));
										}
									}
								}
							}
						}

						if($default_value == $option_key) {
							/*	Update an existing a only if the value does not exist into existing list	*/
							$wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('last_update_date' => current_time('mysql', 0), 'default_value' => $option_key), array('id' => $id));
							$done_options_value[] = str_replace(",", ".", $option_value);
						}
						$i++;
					}
				}
				if(isset($_REQUEST['options'])){
					foreach ( $_REQUEST['options'] as $option_key => $option_label ) {
						$option_value = !empty($_REQUEST['optionsValue'][$option_key]) ? str_replace(",", ".", $_REQUEST['optionsValue'][$option_key]) : sanitize_title($option_label);

						/*	Check what value to use for the new values	*/
						$label = (!empty($option_label) ? $option_label : str_replace(",", ".", $option_value));
						if( !WPSHOP_DISPLAY_VALUE_FOR_ATTRIBUTE_SELECT && empty($option_value) ) {
							$label = $option_label;
							$option_value = sanitize_title($label);
						}

						// If the optionsUpdateValue is empty, set it a empty array to avoid error calling the in_array() function
						$_REQUEST['optionsUpdateValue'] = !empty($_REQUEST['optionsUpdateValue']) ? $_REQUEST['optionsUpdateValue'] : array();

						if (!in_array($option_value, $done_options_value) && !in_array($option_value, $_REQUEST['optionsUpdateValue']) ) {

							$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS, array('creation_date' => current_time('mysql', 0), 'status' => 'valid', 'attribute_id' => $id, 'position' => $i, 'label' => $label, 'value' => $option_value));
							$done_options_value[] = str_replace(",", ".", $option_value);
							$last_insert_id = $wpdb->insert_id;

							if(empty($default_value)){
								/*	Update an existing a only if the value does not exist into existing list	*/
								$wpdb->update(WPSHOP_DBT_ATTRIBUTE, array('last_update_date' => current_time('mysql', 0), 'default_value' => $last_insert_id), array('id' => $id));
								$done_options_value[] = str_replace(",", ".", $option_value);
							}

						}
						$i++;
					}
				}

				// If the is_used_for_sort_by is mark as yes, we have to get out some attributes and save it separately
				if(!empty($_REQUEST[self::getDbTable()]['is_used_for_sort_by']) && ($_REQUEST[self::getDbTable()]['is_used_for_sort_by'] == 'yes')){
					$data = query_posts(array('posts_per_page' => -1, 'post_type' => WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT));
					$attribute_code = $_REQUEST[self::getDbTable()]['code'];
					if(!isset($_REQUEST[self::getDbTable()]['code']) || ($_REQUEST[self::getDbTable()]['code'] == '')){
						$attribute = self::getElement($id, "'valid', 'moderated', 'notused'", 'id');
						$attribute_code = $attribute->code;
					}
					foreach($data as $post){
						$postmeta = get_post_meta($post->ID, '_wpshop_product_metadata', true);
						if(!empty($postmeta[$attribute_code])) {
							update_post_meta($post->ID, '_'.$attribute_code, $postmeta[$attribute_code]);
						}
					}
					wp_reset_query();
				}

				if ( $pageAction != 'delete' ) {/*	Add the new attribute in the additionnal informations attribute group	*/
					if ( !empty($set_section) ) {
						$choosen_set_section = explode('_', $set_section);
						$set_id = $choosen_set_section[0];
						$group_id = $choosen_set_section[1];
					}
					else{
						$attribute_current_attribute_set = 0;
						$query = $wpdb->prepare("
								SELECT id
								FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " AS ATTRIBUTE_SET_DETAILS
								WHERE ATTRIBUTE_SET_DETAILS.status = 'valid'
								AND ATTRIBUTE_SET_DETAILS.attribute_id = %d
								AND ATTRIBUTE_SET_DETAILS.entity_type_id = %d", $id, $_REQUEST[self::getDbTable()]['entity_id']);
						$attribute_current_attribute_set = $wpdb->get_var($query);

						if($attribute_current_attribute_set <= 0){
							$query = $wpdb->prepare(
									"SELECT
									(SELECT ATTRIBUTE_SET.id
									FROM " . WPSHOP_DBT_ATTRIBUTE_SET . " AS ATTRIBUTE_SET
									WHERE ATTRIBUTE_SET.entity_id = %d
									AND ATTRIBUTE_SET.default_set = 'yes' ) AS attribute_set_id,
									(SELECT ATTRIBUTE_GROUP.id
									FROM " . WPSHOP_DBT_ATTRIBUTE_GROUP . " AS ATTRIBUTE_GROUP
									INNER JOIN " . WPSHOP_DBT_ATTRIBUTE_SET . " AS ATTRIBUTE_SET ON ((ATTRIBUTE_SET.id = ATTRIBUTE_GROUP.attribute_set_id) AND (ATTRIBUTE_SET.entity_id = %d))
									WHERE ATTRIBUTE_GROUP.default_group = 'yes') AS attribute_group_id"
									, $_REQUEST[self::getDbTable()]['entity_id']
									, $_REQUEST[self::getDbTable()]['entity_id']
									, $_REQUEST[self::getDbTable()]['entity_id']
							);
							$wpshop_default_group = $wpdb->get_row($query);

							$set_id = $wpshop_default_group->attribute_set_id;
							$group_id = $wpshop_default_group->attribute_group_id;
						}
					}

					if(!empty($set_id) && !empty($group_id)){
						$query = $wpdb->prepare(
								"SELECT (MAX(position) + 1) AS position
								FROM " . WPSHOP_DBT_ATTRIBUTE_DETAILS . "
								WHERE attribute_set_id = %s
								AND attribute_group_id = %s
								AND entity_type_id = %s ",
								$set_id,
								$group_id,
								$_REQUEST[self::getDbTable()]['entity_id']
						);
						$wpshopAttributePosition = $wpdb->get_var($query);
						if($wpshopAttributePosition == 0)$wpshopAttributePosition = 1;
						$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_DETAILS, array('status' => 'valid', 'creation_date' => current_time('mysql', 0), 'entity_type_id' => $_REQUEST[self::getDbTable()]['entity_id'], 'attribute_set_id' => $set_id, 'attribute_group_id' => $group_id, 'attribute_id' => $id, 'position' => $wpshopAttributePosition));
					}
				}

				/*************************			GENERIC				****************************/
				/*************************************************************************/
				$pageMessage .= '<img src="' . WPSHOP_SUCCES_ICON . '" alt="action success" class="wpshopPageMessage_Icon" />' . sprintf(__('%s succesfully saved', 'wpshop'), $elementIdentifierForMessage);
				/* if(($pageAction == 'edit') || ($pageAction == 'save')){
					wpshop_tools::wpshop_safe_redirect(admin_url('edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES.'&page=' . self::getListingSlug() . "&action=saveok&saveditem=" . $id));
				}
				else */
				if ( $pageAction == 'add' )
					wpshop_tools::wpshop_safe_redirect(admin_url('edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES.'&page=' . self::getListingSlug() . "&action=edit&id=" . $id));
				elseif ( $pageAction == 'delete' )
					wpshop_tools::wpshop_safe_redirect(admin_url('edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES.'&page=' . self::getListingSlug() . "&action=deleteok&saveditem=" . $id));
			}
			elseif(($actionResult == 'userNotAllowedForActionEdit') || ($actionResult == 'userNotAllowedForActionAdd') || ($actionResult == 'userNotAllowedForActionDelete')){
				$pageMessage .= '<img src="' . WPSHOP_ERROR_ICON . '" alt="action error" class="wpshopPageMessage_Icon" />' . __('You are not allowed to do this action', 'wpshop');
			}
			elseif(($actionResult == 'unDeletableAtribute')){
				$pageMessage .= '<img src="' . WPSHOP_ERROR_ICON . '" alt="action error" class="wpshopPageMessage_Icon" />' . __('This attribute could not be deleted due to configuration', 'wpshop');
			}

			if(empty($_REQUEST[self::getDbTable()]['frontend_label']) && ($pageAction!='delete')){
				$pageMessage .= __('Please enter an label for the attribut', 'wpshop');
			}
		}

		self::setMessage($pageMessage);
	}

	/**
	 *	Return the list page content, containing the table that present the item list
	 *
	 *	@return string $listItemOutput The html code that output the item list
	 */
	function elementList(){
		//Create an instance of our package class...
		$wpshop_list_table = new wpshop_attributes_custom_List_table();
		//Fetch, prepare, sort, and filter our data...
		$status="'valid'";
		if(!empty($_REQUEST['attribute_status'])){
			switch($_REQUEST['attribute_status']){
				case 'unactive':
					$status="'moderated', 'notused'";
					if(empty($_REQUEST['orderby']) && empty($_REQUEST['order'])){
						$_REQUEST['orderby']='status';
						$_REQUEST['order']='asc';
					}
					break;
				default:
					$status="'".$_REQUEST['attribute_status']."'";
					break;
			}
		}
		$attr_set_list = self::getElement('', $status);
		$i=0;
		$attribute_set_list=array();
		foreach($attr_set_list as $attr_set){
			if(!empty($attr_set->id) && ($attr_set->code != 'product_attribute_set_id') ){
				$attribute_set_list[$i]['id'] = $attr_set->id;
				$attribute_set_list[$i]['name'] = $attr_set->frontend_label;
				$attribute_set_list[$i]['status'] = $attr_set->status;
				$attribute_set_list[$i]['entity'] = $attr_set->entity;
				$attribute_set_list[$i]['code'] = $attr_set->code;
				$i++;
			}
		}
		$wpshop_list_table->prepare_items($attribute_set_list);

		ob_start();
		?>
<div class="wrap">
	<!-- Forms are NOT created automatically, so you need to wrap the table in one to use features like bulk actions -->
	<?php $wpshop_list_table->views() ?>
	<form id="attributes_filter" method="get">
		<!-- For plugins, we also need to ensure that the form posts back to our current page -->
		<input type="hidden" name="page"
			value="<?php echo $_REQUEST['page']; ?>" />
		<!-- Now we can render the completed list table -->
		<?php $wpshop_list_table->display() ?>
	</form>
</div>
<?php
$element_output = ob_get_contents();
ob_end_clean();

return $element_output;
	}

	/**
	 *	Return the page content to add a new item
	 *
	 *	@return string The html code that output the interface for adding a nem item
	 */
	function elementEdition($itemToEdit = ''){
		global $attribute_displayed_field, $attribute_options_group;
		$dbFieldList = wpshop_database::fields_to_input(self::getDbTable());

		$editedItem = '';
		if($itemToEdit != '')
			$editedItem = self::getElement($itemToEdit);

		$the_form_content_hidden = $the_form_general_content = '';
		$the_form_option_content_list = array();
		foreach($dbFieldList as $input_key => $input_def){
			if(!isset($attribute_displayed_field) || !is_array($attribute_displayed_field) || in_array($input_def['name'], $attribute_displayed_field)){
				$input_def['label'] = $input_def['name'];
				$input_def_id=$input_def['id']='wpshop_' . self::currentPageCode . '_edition_table_field_id_'.$input_def['label'];

				$pageAction = isset($_REQUEST[self::getDbTable() . '_action']) ? wpshop_tools::varSanitizer($_REQUEST[self::getDbTable() . '_action']) : '';
				$requestFormValue = isset($_REQUEST[self::currentPageCode][$input_def['label']]) ? wpshop_tools::varSanitizer($_REQUEST[self::currentPageCode][$input_def['label']]) : '';
				$currentFieldValue = $input_def['value'];
				if(is_object($editedItem))
					$currentFieldValue = $editedItem->$input_def['label'];
				elseif(($pageAction != '') && ($requestFormValue != ''))
					$currentFieldValue = $requestFormValue;

				if($input_def['label'] == 'status'){
					if(in_array('notused', $input_def['possible_value'])){
						$key = array_keys($input_def['possible_value'], 'notused');
						unset($input_def['possible_value'][$key[0]]);
					}
					if(in_array('dbl', $input_def['possible_value'])){
						$key = array_keys($input_def['possible_value'], 'dbl');
						unset($input_def['possible_value'][$key[0]]);
					}

					$input_def['type'] = 'checkbox';
					$input_def['label'] = __('Use this attribute', 'wpshop');
					$input_def['possible_value'] = array('valid');
					$input_def_id.='_valid';
					$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box for using this attribute', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
				}

				if(substr($input_def['label'], 0, 3) == 'is_'){
					$input_def['type'] = 'checkbox';
					$input_def['possible_value'] = 'yes';
					switch($input_def['label']){
						case 'is_requiring_unit':
							$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box for using unit with this attribute', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
						break;
						case 'is_visible_in_front':
							$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box for displaying this attribute in shop', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
						break;
						case 'is_used_for_sort_by':
							$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box for displaying this attribute into sortbar', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
						break;
						case 'is_visible_in_advanced_search':
							$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box for using in advanced search form', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
						break;
						case 'is_historisable':
							$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box if you want to save the different value this attribute, each time it is modified', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
						break;
						case 'is_intrinsic':
							$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box if this attribute is intrinsic for a product', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
						break;
						break;
						case 'is_user_defined':
							$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box if this attribute is used for variation. It means that the user would be able to choose a value in frontend', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
						break;
					}
				}

				$input_def['value'] = $currentFieldValue;
				if($input_def['label'] == 'code')
					$input_def['type'] = 'hidden';
				elseif($input_def['label'] == 'entity_id'){
					$input_def['possible_value'] = wpshop_entities::get_entity();
					$input_def['valueToPut'] = 'index';
					$input_def['type'] = 'select';

					$i=0;
					foreach($input_def['possible_value'] as $entity_id => $entity_name) {
						if($i <= 0){
							$current_entity_id = $entity_id;
						}
						$i++;
					}
				}
				elseif($input_def['label'] == '_unit_group_id'){
					$input_def['possible_value'] = wpshop_attributes_unit::get_unit_group();
					$input_def['type'] = 'select';
				}
				elseif($input_def['label'] == '_default_unit'){
					$unit_group_list = wpshop_attributes_unit::get_unit_group();
					$input_def['possible_value'] = wpshop_attributes_unit::get_unit_list_for_group(!empty($editedItem->_unit_group_id)?$editedItem->_unit_group_id:(!empty($unit_group_list)?$unit_group_list[0]->id:''));
					$input_def['type'] = 'select';
				}
				elseif($input_def['label'] == 'backend_input'){
					$new_possible_value = array();
					$new_possible_value[__('Text field', 'wpshop')] = 'short_text';
					$new_possible_value[__('select', 'wpshop')] = 'select';
					$new_possible_value[__('multiple-select', 'wpshop')] = 'multiple-select';
					$new_possible_value[__('Number field', 'wpshop')] = 'float_field';
					$new_possible_value[__('Date field', 'wpshop')] = 'date_field';
					$new_possible_value[__('Textarea field', 'wpshop')] = 'textarea';
					$input_def['possible_value'] = $new_possible_value;

					if ( !empty($editedItem->backend_input) ) {
						switch ( $editedItem->backend_input ) {
							case 'text':
								switch ( $editedItem->data_type ) {
									case 'varchar':
										$input_def['value'] = 'short_text';
									break;
									case 'decimal':
										$input_def['value'] = 'float_field';
									break;
									case 'datetime':
										$input_def['value'] = 'date_field';
									break;
								}
							break;
							default:
								$input_def['value'] = $editedItem->backend_input;
							break;
						}
					}
				}

				if(is_object($editedItem) && (($input_def['label'] == 'code') || ($input_def['label'] == 'data_type') || ($input_def['label'] == 'entity_id') || ($input_def['label'] == 'backend_input'))){
					// $input_def['type'] = 'hidden';
					$input_def['option'] = ' disabled="disabled" ';
					$the_form_content_hidden .= '<input type="hidden" name="' . self::getDbTable() . '[' . $input_def['name'] . ']" value="' . $input_def['value'] . '" />';
					$input_def['label'] = $input_def['name'];
				}

				$input_def['value'] = str_replace("\\", "", $input_def['value']);

				$the_input = wpshop_form::check_input_type($input_def, self::getDbTable());
				if ( $input_def['label'] == 'default_value' ) {
					if ( !empty($editedItem->backend_input) ) {
						switch ( $editedItem->backend_input ) {
							case 'text':
								$input_def['type'] = 'text';
								switch ( $editedItem->data_type ) {
									case 'datetime':
										$input_def['label'] = __('Use the date of the day as default value','wpshop');
										$input_def['type'] = 'checkbox';
										$input_def['possible_value'] = 'date_of_current_day';
										$input_def['options']['label']['custom'] = '<a href="#" title="'.__('Check this box for using date of the day as value when editing a product', 'wpshop').'" class="wpshop_infobulle_marker">?</a>';
									break;
								}
								$the_input = wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE);
							break;
							case 'select':
							case 'multiple-select':
								$input_def['label'] = __('Options list for attribute','wpshop') . '
<div class="alignright wpshop_change_select_data_type" >
	+' . __('Change data type for this attribute', 'wpshop') . '
</div>';
								$the_input = wpshop_attributes::get_select_options_list($itemToEdit, $editedItem->data_type_to_use);
							break;
							case 'textarea':
								$input_def['type'] = 'textarea';
								$the_input = wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE);
							break;
						}
					}
					else {
						$input_def['type']='text';
						$the_input = wpshop_form::check_input_type($input_def, self::getDbTable());
					}
				}

				if($input_def['type'] != 'hidden'){
					if ( $input_def['label'] == 'entity_id' ) {
						$the_input .= '<br/><span class="wpshop_duplicate_attribute" >' . __('Duplicate this attribute to another entity', 'wpshop') . '</span>';
					}
					$input = '
		<tr class="wpshop_' . self::currentPageCode . '_edition_table_line wpshop_' . self::currentPageCode . '_edition_table_line_'.$input_def['name'].'" >
			<td class="wpshop_' . self::currentPageCode . '_edition_table_cell wpshop_' . self::currentPageCode . '_edition_table_field_label wpshop_' . self::currentPageCode . '_edition_table_field_label_'.$input_def['name'].'" ><label for="'.$input_def_id.'" >' . __($input_def['label'], 'wpshop') . '</label></td>
			<td class="wpshop_' . self::currentPageCode . '_edition_table_cell wpshop_' . self::currentPageCode . '_edition_table_field_input wpshop_' . self::currentPageCode . '_edition_table_field_input_'.$input_def['name'].'" >' . $the_input . '</td>
		</tr>';
					if ( (substr($input_def['label'], 0, 3) == 'is_') || (substr($input_def['label'], 0, 1) == '_') )
						$the_form_option_content_list[$input_def['label']] = $input;
					else {
						$the_form_general_content .= $input;
						if ( ($input_def['label'] == 'backend_input') && !is_object($editedItem) ) {

							$the_input = wpshop_attributes_set::get_attribute_set_complete_list($current_entity_id,  self::getDbTable(), self::currentPageCode);

							$input = '
		<tr class="wpshop_' . self::currentPageCode . '_edition_table_line wpshop_' . self::currentPageCode . '_edition_table_line_set_section" >
			<td class="wpshop_' . self::currentPageCode . '_edition_table_cell wpshop_' . self::currentPageCode . '_edition_table_field_label wpshop_' . self::currentPageCode . '_edition_table_field_label_set_section" ><label for="'.self::currentPageCode.'_set_section" >' . __('Affect this new attribute to the set section', 'wpshop') . '</label></td>
			<td class="wpshop_' . self::currentPageCode . '_edition_table_cell wpshop_' . self::currentPageCode . '_edition_table_field_input wpshop_' . self::currentPageCode . '_edition_table_field_input_set_section" >' . $the_input . '</td>
		</tr>';
							$the_form_general_content .= $input;
						}
					}
				}
				else{
					$the_form_content_hidden .= '
				' . $the_input;
				}
			}
		}

		$section_legend = '';
		$section_page_code = self::currentPageCode;
		$section_content = $the_form_general_content;
		ob_start();
		include(WPSHOP_TEMPLATES_DIR.'admin/admin_box_section.tpl.php');
		$the_form_general_content = ob_get_contents();
		ob_end_clean();

		if (!empty($the_form_option_content_list)) {
			$the_form_option_content_section='';
			foreach ($attribute_options_group as $group_name => $group_content) {
				$section_content = '';
				foreach ($group_content as $group_code) {
					if (array_key_exists($group_code, $the_form_option_content_list)) {
						$section_content .= $the_form_option_content_list[$group_code];
						unset($the_form_option_content_list[$group_code]);
					}
				}
				$section_legend = __($group_name,'wpshop');
				$section_page_code = self::currentPageCode;

				ob_start();
				include(WPSHOP_TEMPLATES_DIR.'admin/admin_box_section.tpl.php');
				$the_form_option_content_section .= ob_get_contents();
				ob_end_clean();
			}

			/*	Check there are other attributes to display not in defined group	*/
			if (!empty($the_form_option_content_list)) {
				$section_legend = __('General options','wpshop');
				$section_content = implode('', $the_form_option_content_list);
				$section_page_code = self::currentPageCode;

				ob_start();
				include(WPSHOP_TEMPLATES_DIR.'admin/admin_box_section.tpl.php');
				$the_form_option_content = ob_get_contents();
				ob_end_clean();

				$the_form_option_content .= $the_form_option_content_section;
			}
		}

		/*	Default content for the current page	*/
		$bloc_list[self::currentPageCode]['main_info']['title']=__('Main informations', 'wpshop');
		$bloc_list[self::currentPageCode]['main_info']['content']=$the_form_general_content;

		$bloc_list[self::currentPageCode]['options']['title']=__('Options', 'wpshop');
		$bloc_list[self::currentPageCode]['options']['content']=$the_form_option_content;

		$the_form = '
<form name="' . self::getDbTable() . '_form" id="' . self::getDbTable() . '_form" method="post" action="#" >
	' . wpshop_form::form_input(self::getDbTable() . '_action', self::getDbTable() . '_action', (isset($_REQUEST['action']) && ($_REQUEST['action'] != '') ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'save') , 'hidden') . '
	' . wpshop_form::form_input(self::currentPageCode . '_form_has_modification', self::currentPageCode . '_form_has_modification', 'no' , 'hidden') . $the_form_content_hidden . wpshop_display::custom_page_output_builder($bloc_list, WPSHOP_ATTRIBUTE_EDITION_PAGE_LAYOUT) . '
</form>
<div title="' . __('Change data type for selected attribute', 'wpshop') . '" id="wpshop_dialog_change_select_data_type" ><div id="wpshop_dialog_change_select_data_type_container" ></div></div>';
		$input_def['possible_value'] = wpshop_entities::get_entity();
		unset($input_def['possible_value'][$current_entity_id]);
		$input_def['valueToPut'] = 'index';
		$input_def['type'] = 'select';
		$input_def['name'] = 'wpshop_entity_to_duplicate_to';
		$input_def['id'] = 'wpshop_entity_to_duplicate_to';
		$the_form .= '
<div title="' . __('Duplicate attribute to another entity', 'wpshop') . '" id="wpshop_dialog_duplicate_attribute" >
	' . __('Choose an entity to copy the selected attribute to', 'wpshop') . '
	' . wpshop_form::check_input_type($input_def) . '
</div>';

		$the_form .= '
<script type="text/javascript" >
	wpshop(document).ready(function(){
		wpshopMainInterface("'.self::getDbTable().'", "' . __('Are you sure you want to quit this page? You will loose all current modification', 'wpshop') . '", "' . __('Are you sure you want to delete this attributes group?', 'wpshop') . '");

		jQuery("#wpshop_dialog_duplicate_attribute").dialog({
			autoOpen: false,
			width: 500,
			height: 100,
			modal: true,
			buttons:{
				"'.__('Duplicate', 'wpshop').'": function(){
					var data = {
						action: "wpshop_duplicate_attribute",
						wpshop_ajax_nonce: "' . wp_create_nonce("wpshop_duplicate_attribute") . '",
						attribute_id: jQuery("#wpshop_attributes_edition_table_field_id_id").val(),
						entity: jQuery("#wpshop_entity_to_duplicate_to").val()
					};
					jQuery.post(ajaxurl, data, function(response) {
						if (response[0]) {
							jQuery("#wpshop_dialog_duplicate_attribute").append(response[1]);
						}
						else {
							alert(response[1]);
						}
					}, "json");	
				},
				"'.__('Cancel', 'wpshop').'": function(){
					jQuery(this).dialog("close");
					jQuery(".wpshop_duplicate_attribute_result").remove();
				}
			}
		});
		jQuery(".wpshop_duplicate_attribute").live("click", function(){
			jQuery("#wpshop_dialog_duplicate_attribute").dialog("open");
		});

		jQuery("#wpshop_dialog_change_select_data_type").dialog({
			autoOpen: false,
			width: 800,
			height: 200,
			modal: true,
			buttons:{
				"'.__('Change type', 'wpshop').'": function(){
					var delete_entity = false;
					if(jQuery("#delete_entity").is(":checked")){
						var delete_entity = true;
					}
					var delete_items_of_entity = false;
					if(jQuery("#delete_items_of_entity").is(":checked")){
						var delete_items_of_entity = true;
					}
					var data = {
						action: "attribute_select_data_type_change",
						wpshop_ajax_nonce: "' . wp_create_nonce("wpshop_attribute_change_select_data_type_change") . '",
						attribute_id: jQuery("#wpshop_attributes_edition_table_field_id_id").val(),
						internal_data: jQuery("#internal_data").val(),
						data_type: jQuery("#wpshop_attribute_change_data_type_new_type").val(),
						delete_entity: delete_entity,
						delete_items_of_entity: delete_items_of_entity
					};
					jQuery.post(ajaxurl, data, function(response) {
						jQuery(".wpshop_attributes_edition_table_field_input_default_value").html( response );
						jQuery("#wpshop_dialog_change_select_data_type").dialog("close");
					}, "json");	
				},
				"'.__('Cancel', 'wpshop').'": function(){
					jQuery(this).dialog("close");
				}
			}
		});

		jQuery(".wpshop_attribute_change_select_data_type_deletion_input").live("click",function() {
			var display = false;
			if (jQuery(".wpshop_attribute_change_select_data_type_deletion_input_item").is(":checked") ) {
				display = true;
			}
			if (jQuery(".wpshop_attribute_change_select_data_type_deletion_input_entity").is(":checked") ) {
				display = true;
			}
			if (display) {
				jQuery(".wpshop_attribute_change_data_type_alert").show();
			}
			else {
				jQuery(".wpshop_attribute_change_data_type_alert").hide();
			}
		});

		jQuery(".wpshop_change_select_data_type").live("click",function(){
			jQuery("#wpshop_dialog_change_select_data_type_container").html(jQuery("#wpshopLoadingPicture").html());
			jQuery("#wpshop_dialog_change_select_data_type").dialog("open");	
	
			var data = {
				action: "attribute_select_data_type",
				current_attribute: jQuery("#wpshop_attributes_edition_table_field_id_id").val(),
				wpshop_ajax_nonce: "' . wp_create_nonce("wpshop_attribute_change_select_data_type") . '"
			};
			jQuery.post(ajaxurl, data, function(response) {
				jQuery("#wpshop_dialog_change_select_data_type_container").html( response );
			}, "json");
			
		});
		jQuery("#wpshop_attributes_edition_table_field_id__unit_group_id").change(function(){
			change_unit_list();
		});
		jQuery("#wpshop_attributes_edition_table_field_id_backend_input").change(function(){
			jQuery(".wpshop_attributes_edition_table_field_input_default_value").html(jQuery("#wpshopLoadingPicture").html());
	
			var data = {
				action: "attribute_output_type",
				current_type: jQuery(this).val(),
				wpshop_ajax_nonce: "' . wp_create_nonce("wpshop_attribute_output_type_selection") . '"
			};
			jQuery.getJSON(ajaxurl, data, function(response) {
				jQuery(".wpshop_attributes_edition_table_field_input_default_value").html((response[0]));
				jQuery(".wpshop_attributes_edition_table_field_label_default_value label").html((response[1]));
			});
		});
		jQuery("#wpshop_attributes_edition_table_field_id_entity_id").change(function(){
			jQuery(".wpshop_attributes_edition_table_field_input_set_section").html(jQuery("#wpshopLoadingPicture").html());

			var data = {
				action: "attribute_entity_set_selection",
				current_entity_id: jQuery(this).val(),
				wpshop_ajax_nonce: "' . wp_create_nonce("wpshop_attribute_entity_set_selection") . '"
			};
			jQuery.post(ajaxurl, data, function(response) {
				jQuery(".wpshop_attributes_edition_table_field_input_set_section").html( response );
			}, "json"); 
		});
	});
	function change_unit_list(){
		jQuery(".wpshop_attributes_edition_table_field_input__default_unit").html(jQuery("#wpshopLoadingPicture").html());
		jQuery(".wpshop_attributes_edition_table_field_input__default_unit").load(WPSHOP_AJAX_FILE_URL,{
			"post": "true",
			"elementCode": "attribute_unit_management",
			"action": "load_attribute_unit_list",
			"current_group": jQuery("#wpshop_attributes_edition_table_field_id__unit_group_id").val()
		});
	}
</script>';

		return $the_form;
	}
	/**
	 *	Return the different button to save the item currently being added or edited
	 *
	 *	@return string $currentPageButton The html output code with the different button to add to the interface
	 */
	function getPageFormButton($element_id = 0){
		$action = isset($_REQUEST['action']) ? wpshop_tools::varSanitizer($_REQUEST['action']) : 'add';
		$currentPageButton = '';

		// $currentPageButton .= '<h2 class="cancelButton alignleft" ><a href="' . admin_url('edit.php?post_type='.WPSHOP_NEWTYPE_IDENTIFIER_ENTITIES.'&amp;page=' . self::getListingSlug()) . '" class="button add-new-h2" >' . __('Back', 'wpshop') . '</a></h2>';

		if(($action == 'add') && (current_user_can('wpshop_add_attributes')))
			$currentPageButton .= '<input type="button" class="button-primary" id="add" name="add" value="' . __('Add', 'wpshop') . '" />';
		elseif(current_user_can('wpshop_edit_attributes'))
		$currentPageButton .= '<input type="button" class="button-primary" id="save" name="save" value="' . __('Save', 'wpshop') . '" />';

		$attribute_undeletable = unserialize(WPSHOP_ATTRIBUTE_UNDELETABLE);
		$attribute = self::getElement($element_id, "'valid', 'moderated', 'notused'", 'id');
		$attribute_code = !empty($attribute->code)?$attribute->code:'';
		if(current_user_can('wpshop_delete_attributes') && ($action != 'add') && !in_array($attribute_code, $attribute_undeletable))
			$currentPageButton .= '<input type="button" class="button-secondary wpshop_delete_element_button wpshop_delete_element_button_'.self::currentPageCode.'" id="delete" name="delete" value="' . __('Delete', 'wpshop') . '" />';

		return $currentPageButton;
	}

	/**
	 *	Get the existing attribute list into database
	 *
	 *	@param integer $element_id optionnal The attribute identifier we want to get. If not specify the entire list will be returned
	 *	@param string $element_status optionnal The status of element to get into database. Default is set to valid element
	 *	@param mixed $field_to_search optionnal The field we want to check the row identifier into. Default is to set id
	 *
	 *	@return object $element_list A wordpress database object containing the attribute list
	 */
	function getElement($element_id = '', $element_status = "'valid', 'moderated', 'notused'", $field_to_search = 'id', $list = false){
		global $wpdb;
		$element_list = array();
		$moreQuery = "";

		if($element_id != ''){
			$moreQuery .= "
					AND CURRENT_ELEMENT." . $field_to_search . " = '" . $element_id . "' ";
		}
		if(!empty($_REQUEST['orderby']) && !empty($_REQUEST['order'])){
			$moreQuery .= "
					ORDER BY " . $_REQUEST['orderby'] . "  " . $_REQUEST['order'];
		}

		$query = $wpdb->prepare(
				"SELECT CURRENT_ELEMENT.*, ENTITIES.post_name as entity
				FROM " . self::getDbTable() . " AS CURRENT_ELEMENT
				INNER JOIN " . $wpdb->posts . " AS ENTITIES ON (ENTITIES.ID = CURRENT_ELEMENT.entity_id)
				WHERE CURRENT_ELEMENT.status IN (".$element_status.") " . $moreQuery
		);

		/*	Get the query result regarding on the function parameters. If there must be only one result or a collection	*/
		if(($element_id == '') || $list){
			$element_list = $wpdb->get_results($query);
		}
		else{
			$element_list = $wpdb->get_row($query);
		}

		return $element_list;
	}


	/**
	 *	Save the different value for attribute of a given entity type and entity
	 *
	 *	@param array $attributeToSet The list of attribute with each value to set
	 *	@param integer $entityTypeId The entity type identifier (products/categories/...)
	 *	@param integer $entityId The entity identifier we want to save attribute for (The specific product/category/...)
	 *	@param string $language The language to set the value for into database
	 *
	 */
	function saveAttributeForEntity($attributeToSet, $entityTypeId, $entityId, $language = 'fr_FR', $from = '') {
		global $wpdb;
		
		/* Recuperation de l'identifiant de l'utilisateur connecte */
		$user_id = function_exists('is_user_logged_in') && is_user_logged_in() ? get_current_user_id() : '0';
		$sent_attribute_list = array();

		foreach ($attributeToSet as $attributeType => $attributeTypeDetails) {

			/* Preparation des parametres permettant de supprimer les bonnes valeurs des attributs suivant la configuration de la boutique et de la methode de mise a jour */
			$delete_current_attribute_values_params = array(
					'entity_id' => $entityId, 
					'entity_type_id' => $entityTypeId
				);
			if(WPSHOP_ATTRIBUTE_VALUE_PER_USER){
				$delete_current_attribute_values_params['user_id'] = $user_id;
			}

			if(!empty($attributeTypeDetails) && is_array($attributeTypeDetails)) {
				
				foreach($attributeTypeDetails as $attribute_code => $attributeValue) {

					$more_query_params_values = array();
					if($attribute_code != 'unit') {
						
						$unit_id = 0;
						if(isset($attributeTypeDetails['unit'][$attribute_code])){
							$unit_id = $attributeTypeDetails['unit'][$attribute_code];
						}

						$currentAttribute = self::getElement($attribute_code, "'valid'", 'code');
						$sent_attribute_list[] = $currentAttribute->id;

						if ( $currentAttribute->is_unique == 'yes' ) {
							$query = $wpdb->prepare("SELECT value FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX.$attributeType . " WHERE attribute_id = %d AND value = %s", $currentAttribute->id, $attributeValue);
							$attr_existing_value = $wpdb->get_results($query);
							if (count($attr_existing_value) > 0) {
								
							}
						}

						/*	Enregistrement de la valeur actuelle de l'attribut dans la table d'historique si l'option historique est activee sur l'attribut courant	*/
						if ($currentAttribute->is_historisable == 'yes') {
							$query = $wpdb->prepare("SELECT * FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attributeType . " WHERE entity_type_id = %d AND attribute_id = %d AND entity_id = %d", $entityTypeId, $currentAttribute->id, $entityId);
							$attribute_histo = $wpdb->get_results($query);
							if(!empty($attribute_histo)){
								$attribute_histo_content['status'] = 'valid';
								$attribute_histo_content['creation_date'] = current_time('mysql', 0);
								$attribute_histo_content['creation_date_value'] = $attribute_histo[0]->creation_date_value;
								$attribute_histo_content['original_value_id'] = $attribute_histo[0]->value_id;
								$attribute_histo_content['entity_type_id'] = $attribute_histo[0]->entity_type_id;
								$attribute_histo_content['attribute_id'] = $attribute_histo[0]->attribute_id;
								$attribute_histo_content['entity_id'] = $attribute_histo[0]->entity_id;
								$attribute_histo_content['unit_id'] = $attribute_histo[0]->unit_id;
								$attribute_histo_content['language'] = $attribute_histo[0]->language;
								$attribute_histo_content['value'] = $attribute_histo[0]->value;
								$attribute_histo_content['value_type'] = WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attributeType;
								$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_HISTO, $attribute_histo_content);
							}
						}
						$attributeValue = str_replace("\\", "", $attributeValue);

						$wpdb->delete(WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX.$attributeType, array_merge($delete_current_attribute_values_params, array('attribute_id' => $currentAttribute->id)));

						/*	Insertion de la nouvelle valeur de l'attribut dans la base	*/
						$query_params = array(
								'value_id' => '',
								'entity_type_id' => $entityTypeId,
								'attribute_id' => $currentAttribute->id,
								'entity_id' => $entityId,
								'unit_id' => $unit_id,
								'language' => $language,
								'user_id' => $user_id,
								'creation_date_value' => current_time('mysql', 0)
							);
						/*	Si l'attribut courant est contenu dans un tableau (exemple: select multiple) on lit tout le tableau et on enregistre chaque valeur separement	*/
						if(is_array($attributeValue)){
							foreach($attributeValue as $a){
								$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX.$attributeType, array_merge($query_params, array('value' => $a)));
							}
						}
						else{
							$wpdb->insert(WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX.$attributeType, array_merge($query_params, array('value' => $attributeValue)));
						}

						/*	Dans le cas ou l'attribut courant est utilise dans l'interface permettant de trier les produits (option de l'attribut) on defini une meta specifique	*/
						if ($currentAttribute->is_used_for_sort_by && !empty($attributeValue)) :
							update_post_meta($entityId, '_'.$attribute_code, $attributeValue);
						endif;

						/*	Enregistrement de toutes les valeurs des attributs dans une meta du produit	*/
						if (!empty($_POST['attribute_option'][$attribute_code])) {
							$value = self::get_attribute_type_select_option_info($attributeTypeDetails[$attribute_code], 'value');
							if (strtolower($value) == 'yes') :
								update_post_meta($entityId, 'attribute_option_'.$attribute_code, $_POST['attribute_option'][$attribute_code]);
							else :
								delete_post_meta($entityId, 'attribute_option_'.$attribute_code);
							endif;
						}
					}
				}

				if (empty($from)) {
					$query = $wpdb->prepare("SELECT value_id FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX.$attributeType . " WHERE attribute_id NOT IN ('" . implode("', '", $sent_attribute_list) . "') AND entity_id = %d AND entity_type_id = %d", $entityId, $entityTypeId);
					$attr_to_delete = $wpdb->get_results($query);
					if(!empty($attr_to_delete)){
						foreach ($attr_to_delete as $value) {
							$wpdb->delete(WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX.$attributeType, array_merge($delete_current_attribute_values_params, array('value_id' => $value->value_id)));
						}
					}
				}
			}
		}
// 					exit;
	}

	/**
	 *	Return the value for a given attribute of a given entity type and a given entity
	 *
	 *	@param string $attributeType The extension of the database table to get the attribute value in
	 *	@param integer $attributeId The attribute identifier we want to get the value for
	 *	@param integer $entityTypeId The entity type identifier we want to get the attribute value for (example: product = 1)
	 	*	@param integer $entityId The entity id we want the attribute value for
	 *
	 *	@return object $attributeValue A wordpress database object containing the value of the attribute for the selected entity
	 */
	function getAttributeValueForEntityInSet($attributeType, $attributeId, $entityTypeId, $entityId, $atribute_params = array()){
		global $wpdb;
		$attributeValue = '';

		$query_params = "";
		$query_params_values = array($attributeId, $entityTypeId, $entityId);
		if(WPSHOP_ATTRIBUTE_VALUE_PER_USER && (isset($atribute_params['intrinsic']) && ($atribute_params['intrinsic'] != 'yes'))){
			$query_params = "
						AND user_id = %d";
			$query_params_values[] = get_current_user_id();
		}
		$query = $wpdb->prepare(
			"SELECT value, unit_id, user_id
			FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_PREFIX . $attributeType . "
			WHERE attribute_id = %d
				AND entity_type_id = %d
				AND entity_id = %d" . $query_params . " 
			ORDER BY creation_date_value ASC",
			$query_params_values
		);

		$attributeValue = $wpdb->get_results($query);

		if(((count($attributeValue)<=1) && (!empty($attributeValue[0]))) && (empty($atribute_params['backend_input']) || $atribute_params['backend_input'] != 'multiple-select')) $attributeValue = $attributeValue[0];
// 		if(!WPSHOP_ATTRIBUTE_VALUE_PER_USER && (count($attributeValue) > 1)){
// 			$attributeValue = $attributeValue[0];
// 		}

		return $attributeValue;
	}

	/**
	 *	Get the existing element list into database
	 *
	 *	@param integer $elementId optionnal The element identifier we want to get. If not specify the entire list will be returned
	 *	@param string $elementStatus optionnal The status of element to get into database. Default is set to valid element
	 *
	 *	@return object $elements A wordpress database object containing the element list
	 */
	function getElementWithAttributeAndValue($entityId, $elementId, $language, $keyForArray = '', $outputType = ''){
		$elements = array();
		$elementsWithAttributeAndValues = self::get_attribute_list_for_item($entityId, $elementId, $language, $keyForArray, $outputType);

		foreach($elementsWithAttributeAndValues as $elementDefinition){
			$arrayKey = $elementDefinition->attribute_id;
			if($keyForArray == 'code'){
				$arrayKey = $elementDefinition->attribute_code;
			}
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['code'] = $elementDefinition->attribute_set_section_code;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['data_type'] = $elementDefinition->data_type;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['backend_table'] = $elementDefinition->backend_table;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['backend_input'] = $elementDefinition->backend_input;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['frontend_label'] = $elementDefinition->frontend_label;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['attribute_code'] = $elementDefinition->attribute_code;
			$attributeValueField = 'attribute_value_' . $elementDefinition->data_type;

			// Manage the value differently if it is an array or not
			if(!empty($elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['value'])) {
					
				if(is_array($elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['value'])) {
					$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['value'][] = $elementDefinition->$attributeValueField;
				}
				else {
					$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['value'] = array($elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['value'],$elementDefinition->$attributeValueField);
				}
			}
			else {
				$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['value'] = $elementDefinition->$attributeValueField;
			}

			if($elementDefinition->backend_input == 'select' || $elementDefinition->backend_input == 'multiple-select'){
				$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['select_value'] = self::get_attribute_type_select_option_info($elementId, 'value');
			}

			$attributeUnitField = 'attribute_unit_' . $elementDefinition->data_type;
			$elements[$elementId][$elementDefinition->attribute_set_section_name]['attributes'][$arrayKey]['unit'] = $elementDefinition->$attributeUnitField;
		}

		return $elements;
	}

	function get_attribute_list_for_item($entityId, $elementId, $language = 'fr_FR', $keyForArray = '', $outputType = ''){
		global $wpdb;
		$elementsWithAttributeAndValues = array();
		$moreQuery = "";

		if($outputType == 'frontend'){
			$moreQuery .= "
				AND ATTR.is_visible_in_front = 'yes'";
/*
			AND ATTRIBUTE_GROUP.display_on_frontend = 'yes' */
		}

		$query = $wpdb->prepare(
				"SELECT POST_META.*,
					ATTR.id as attribute_id, ATTR.data_type, ATTR.backend_table, ATTR.backend_input, ATTR.frontend_label, ATTR.code AS attribute_code, ATTR.is_recordable_in_cart_meta, ATTR.default_value as default_value,
					ATTR_VALUE_VARCHAR.value AS attribute_value_varchar, ATTR_UNIT_VARCHAR.unit AS attribute_unit_varchar,
					ATTR_VALUE_DECIMAL.value AS attribute_value_decimal, ATTR_UNIT_DECIMAL.unit AS attribute_unit_decimal,
					ATTR_VALUE_TEXT.value AS attribute_value_text, ATTR_UNIT_TEXT.unit AS attribute_unit_text,
					ATTR_VALUE_INTEGER.value AS attribute_value_integer, ATTR_UNIT_INTEGER.unit AS attribute_unit_integer,
					ATTR_VALUE_DATETIME.value AS attribute_value_datetime, ATTR_UNIT_DATETIME.unit AS attribute_unit_datetime,
					ATTRIBUTE_GROUP.code AS attribute_set_section_code, ATTRIBUTE_GROUP.name AS attribute_set_section_name
				FROM " . WPSHOP_DBT_ATTRIBUTE . " AS ATTR
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " AS EAD ON (EAD.attribute_id = ATTR.id)
					INNER JOIN " . $wpdb->postmeta . " AS POST_META ON ((POST_META.post_id = %d) AND (POST_META.meta_key = '_wpshop_product_attribute_set_id') AND (POST_META.meta_value = EAD.attribute_set_id))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_GROUP . " AS ATTRIBUTE_GROUP  ON (ATTRIBUTE_GROUP.id = EAD.attribute_group_id)
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR . " AS ATTR_VALUE_VARCHAR ON ((ATTR_VALUE_VARCHAR.entity_type_id = '" . $entityId . "') AND (ATTR_VALUE_VARCHAR.attribute_id = ATTR.id) AND (ATTR_VALUE_VARCHAR.entity_id = %d) AND (ATTR_VALUE_VARCHAR.language = '" . $language . "'))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_UNIT . " AS ATTR_UNIT_VARCHAR ON ((ATTR_UNIT_VARCHAR.id = ATTR_VALUE_VARCHAR.unit_id) AND (ATTR_UNIT_VARCHAR.status = 'valid'))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL . " AS ATTR_VALUE_DECIMAL ON ((ATTR_VALUE_DECIMAL.entity_type_id = '" . $entityId . "') AND (ATTR_VALUE_DECIMAL.attribute_id = ATTR.id) AND (ATTR_VALUE_DECIMAL.entity_id = %d) AND (ATTR_VALUE_DECIMAL.language = '" . $language . "'))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_UNIT . " AS ATTR_UNIT_DECIMAL ON ((ATTR_UNIT_DECIMAL.id = ATTR_VALUE_DECIMAL.unit_id) AND (ATTR_UNIT_DECIMAL.status = 'valid'))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT . " AS ATTR_VALUE_TEXT ON ((ATTR_VALUE_TEXT.entity_type_id = '" . $entityId . "') AND (ATTR_VALUE_TEXT.attribute_id = ATTR.id) AND (ATTR_VALUE_TEXT.entity_id = %d) AND (ATTR_VALUE_TEXT.language = '" . $language . "'))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_UNIT . " AS ATTR_UNIT_TEXT ON ((ATTR_UNIT_TEXT.id = ATTR_VALUE_TEXT.unit_id) AND (ATTR_UNIT_TEXT.status = 'valid'))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER . " AS ATTR_VALUE_INTEGER ON ((ATTR_VALUE_INTEGER.entity_type_id = '" . $entityId . "') AND (ATTR_VALUE_INTEGER.attribute_id = ATTR.id) AND (ATTR_VALUE_INTEGER.entity_id = %d) AND (ATTR_VALUE_INTEGER.language = '" . $language . "'))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_UNIT . " AS ATTR_UNIT_INTEGER ON ((ATTR_UNIT_INTEGER.id = ATTR_VALUE_INTEGER.unit_id) AND (ATTR_UNIT_INTEGER.status = 'valid'))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME . " AS ATTR_VALUE_DATETIME ON ((ATTR_VALUE_DATETIME.entity_type_id = '" . $entityId . "') AND (ATTR_VALUE_DATETIME.attribute_id = ATTR.id) AND (ATTR_VALUE_DATETIME.entity_id = %d) AND (ATTR_VALUE_DATETIME.language = '" . $language . "'))
					LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_UNIT . " AS ATTR_UNIT_DATETIME ON ((ATTR_UNIT_DATETIME.id = ATTR_VALUE_DATETIME.unit_id) AND (ATTR_UNIT_DATETIME.status = 'valid'))
				WHERE
					ATTR.status = 'valid'
					AND EAD.status = 'valid'
					AND ATTRIBUTE_GROUP.status = 'valid'
					AND EAD.entity_type_id = '" . $entityId . "' " . $moreQuery . "
			ORDER BY ATTRIBUTE_GROUP.position", 
		$elementId, $elementId, $elementId, $elementId, $elementId, $elementId);
		$elementsWithAttributeAndValues = $wpdb->get_results($query);

		return $elementsWithAttributeAndValues;
	}

	/**
	 * Traduit le shortcode et affiche la valeur d'un attribut donnï¿½
	 * @param array $atts : tableau de paramï¿½tre du shortcode
	 * @return mixed
	 **/
	function wpshop_att_val_func($atts) {
		global $wpdb;
		global $wp_query;

		$att_type = array(
				'datetime'	=>	WPSHOP_DBT_ATTRIBUTE_VALUES_DATETIME,
				'decimal'	=>	WPSHOP_DBT_ATTRIBUTE_VALUES_DECIMAL,
				'integer'	=>	WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER,
				'text'		=>	WPSHOP_DBT_ATTRIBUTE_VALUES_TEXT,
				'varchar'	=>	WPSHOP_DBT_ATTRIBUTE_VALUES_VARCHAR
		);
		if(empty($atts['pid'])) $atts['pid']=$wp_query->posts[0]->ID;
		if(in_array($atts['type'], array_keys($att_type))) {
			$query = 'SELECT value FROM '.$att_type[$atts['type']].' WHERE entity_id='.$atts['pid'].' AND attribute_id='.$atts['attid'].'';
			$data = $wpdb->get_results($query);
			return $data[0]->value;
		}
	}

	/**
	 *	Return the output for attribute list in advanced search
	 */
	function getAttributeForAdvancedSearch() {
		global $wpdb;

		$attributeSetStatus = '"valid"';

		$query = $wpdb->prepare(
				"SELECT ATTRIBUTE_GROUP.id AS attr_group_id, ATTRIBUTE_GROUP.backend_display_type AS backend_display_type, ATTRIBUTE_GROUP.code AS attr_group_code, ATTRIBUTE_GROUP.position AS attr_group_position, ATTRIBUTE_GROUP.name AS attr_group_name,
				ATTRIBUTE.*, ATTRIBUTE_DETAILS.position AS attr_position_in_group, ATTRIBUTE_GROUP.id as attribute_detail_id, ATTRIBUTE_GROUP.default_group
				FROM " . WPSHOP_DBT_ATTRIBUTE_GROUP . " AS ATTRIBUTE_GROUP
				INNER JOIN " . self::getDbTable() . " AS ATTRIBUTE_SET ON (ATTRIBUTE_SET.id = ATTRIBUTE_GROUP.attribute_set_id)
				LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_DETAILS . " AS ATTRIBUTE_DETAILS ON ((ATTRIBUTE_DETAILS.attribute_group_id = ATTRIBUTE_GROUP.id) AND (ATTRIBUTE_DETAILS.attribute_set_id = ATTRIBUTE_SET.id) AND (ATTRIBUTE_DETAILS.status = 'valid'))
				LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE . " AS ATTRIBUTE ON (ATTRIBUTE.id = ATTRIBUTE_DETAILS.attribute_id AND ATTRIBUTE.status = 'valid')
				WHERE ATTRIBUTE_SET.status IN (" . $attributeSetStatus . ")
				AND ATTRIBUTE_GROUP.status IN (" . $attributeSetStatus . ")
				AND ATTRIBUTE.is_visible_in_advanced_search = 'yes'
				ORDER BY ATTRIBUTE_GROUP.position, ATTRIBUTE_DETAILS.position");
			
		$attributeListDetails = $wpdb->get_results($query);

		$attributeSetDetailsGroups=array();
		foreach($attributeListDetails as $attributeGroup){
			$attributeSetDetailsGroups[$attributeGroup->attr_group_id]['id'] = $attributeGroup->attribute_detail_id;
			$attributeSetDetailsGroups[$attributeGroup->attr_group_id]['code'] = $attributeGroup->attr_group_code;
			$attributeSetDetailsGroups[$attributeGroup->attr_group_id]['name'] = $attributeGroup->attr_group_name;
			$attributeSetDetailsGroups[$attributeGroup->attr_group_id]['is_default_group'] = $attributeGroup->default_group;
			$attributeSetDetailsGroups[$attributeGroup->attr_group_id]['backend_display_type'] = $attributeGroup->backend_display_type;
			$attributeSetDetailsGroups[$attributeGroup->attr_group_id]['attribut'][$attributeGroup->attr_position_in_group] = $attributeGroup;
			$validAttributeList[] = $attributeGroup->id;
		}

		$inputs = '';
		$currentPageCode = 'advanced_search';
		$itemToEdit=0;

		/*	Read the attribute list in order to output	*/
		foreach($attributeSetDetailsGroups as $productAttributeSetDetail){
			if(count($productAttributeSetDetail['attribut']) >= 1){
				foreach($productAttributeSetDetail['attribut'] as $attribute){
					if(!empty($attribute->id)){

						$input_def['option'] = '';
						$attributeInputDomain = $currentPageCode . '_attribute[' . $attribute->data_type . ']';
						$input_def['id'] = $currentPageCode . '_' . $itemToEdit . '_attribute_' . $attribute->id;
						$input_def['intrinsec'] = $attribute->is_intrinsic;
						$input_def['name'] = $attribute->code;
						$input_def['type'] = wpshop_tools::defineFieldType($attribute->data_type);
						$input_label = $attribute->frontend_label;
						$input_def['value'] = $attribute->default_value;

						if($attribute->data_type == 'datetime'){
							if((($input_def['value'] == '') || ($input_def['value'] == 'date_of_current_day')) && ($attribute->default_value == 'date_of_current_day')){
								$input_def['value'] = date('Y-m-d');
							}
							$input_more_class .= ' wpshop_input_datetime ';
							$input_options = '<script type="text/javascript" >wpshop(document).ready(function(){wpshop("#' . $input_def['id'] . '").val("' . str_replace(" 00:00:00", "", $input_def['value']) . '")});</script>';
						}
						if(($attribute->backend_input == 'select') OR ($attribute->backend_input == 'multiple-select')){

							$input_def['type'] = $attribute->backend_input; // 'select' or 'multiple-select'

							$query = $wpdb->prepare("SELECT id, label, value, '' as name FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " WHERE attribute_id = %d AND status = 'valid' ORDER BY position", $attribute->id);
							$attribute_select_options = $wpdb->get_results($query);
							$attribute_select_options_list = $attribute_select_options;

							$select_value = '';
							foreach($attribute_select_options as $index => $option){
								if(($option->label != '') && ($option->label != $option->value) && (str_replace(',', '.', $option->label) != $option->value)){
									$attribute_select_options_list[$index]->name = $option->label . '&nbsp;(' . $option->value . ')';
								}
								else{
									$attribute_select_options_list[$index]->name = $option->value;
								}
								if(str_replace("\\", "", $input_def['value']) == $option->id){
									$select_value = $option->value;
								}
								//$more_input .= '<input type="hidden" value="' . str_replace("\\", "", $option->value) . '" name="wpshop_product_attribute_' . $attribute->code . '_value_' . $option->id . '" id="wpshop_product_attribute_' . $attribute->code . '_value_' . $option->id . '" />';
								unset($attribute_select_options_list[$index]->label);
								unset($attribute_select_options_list[$index]->value);
							}
							$input_def['possible_value'] = $attribute_select_options_list;
						}

						$inputs .= '<label>'.__($input_label, 'wpshop').' : '.wpshop_form::check_input_type($input_def, $attributeInputDomain) . $more_input.'</label><br />';
					}
				}
			}
		}
		return $inputs;
	}

	/**
	 *	Return the output for attribute list for a given attribute set and a given item to edit
	 *
	 *	@param integer $attributeSetId The attribute set to get the attribute for
	 *	@param string $currentPageCode Define on wich page we want to get the attribute
	 *	@param integer $itemToEdit The item identifier we are working on and we want to get attributes and attributes value for
	 *
	 *	@return array $box An array with the different content to output: box and box content
	 */
	function getAttributeFieldOutput($attributeSetId, $currentPageCode, $itemToEdit, $outputType = 'box'){
		global $wpdb;
		$box = $box['box'] = $box['boxContent'] = $box['generalTabContent'] = array();
		$wpshop_price_attributes = unserialize(WPSHOP_ATTRIBUTE_PRICES);
		$wpshop_weight_attributes = unserialize(WPSHOP_ATTRIBUTE_WEIGHT);

		/*	Get the attribute set details in order to build the product interface	*/
		$productAttributeSetDetails = wpshop_attributes_set::getAttributeSetDetails($attributeSetId, "'valid'");

		if(count($productAttributeSetDetails) > 0){
			/*	Read the attribute list in order to output	*/
			$shortcodes_attr = '';
			$shortcodes_to_display = false;
			$attribute_set_id_is_present = false;
			foreach($productAttributeSetDetails as $productAttributeSetDetail){
				$shortcodes = '';
				$currentTabContent = '';
				$output_nb = 0;
				$price_done = false;
				if(count($productAttributeSetDetail['attribut']) >= 1){
					foreach($productAttributeSetDetail['attribut'] as $attribute){
						if(!empty($attribute->id)){
							if($attribute->code == 'product_attribute_set_id'){
								$attribute_set_id_is_present = true;
							}
							$input_def['option'] = '';
							$attributeInputDomain = $currentPageCode . '_attribute[' . $attribute->data_type . ']';
							$input_def['id'] = $currentPageCode . '_' . $itemToEdit . '_attribute_' . $attribute->id;
							$input_def['intrinsec'] = $attribute->is_intrinsic;
							$input_def['name'] = $attribute->code;
							$input_def['type'] = wpshop_tools::defineFieldType($attribute->data_type);
							$input_label = $attribute->frontend_label;
							$input_def['value'] = $attribute->default_value;
							$attributeValue = wpshop_attributes::getAttributeValueForEntityInSet($attribute->data_type, $attribute->id, wpshop_entities::get_entity_identifier_from_code($currentPageCode), $itemToEdit, array('intrinsic' => $attribute->is_intrinsic, 'backend_input' => $attribute->backend_input));

							if(is_array($attributeValue) && !empty($attributeValue)){
								$input_def['value'] = $attributeValue;
							}
							elseif(!empty($attributeValue->value)){
								$input_def['value'] = $attributeValue->value;
							}

							/*	Manage specific field as the attribute_set_id in product form	*/
							if($input_def['name'] == 'product_attribute_set_id'){
								$product_attribute_set = get_post_meta($itemToEdit, WPSHOP_PRODUCT_ATTRIBUTE_SET_ID_META_KEY, true);
								$input_def['value'] = !empty($product_attribute_set) ? $product_attribute_set : $attributeSetId;
								$input_def['type'] = 'hidden';
							}

							$input_options = '';
							$input_more_class = '';
							if($attribute->data_type == 'datetime'){
								if((($input_def['value'] == '') || ($input_def['value'] == 'date_of_current_day')) && ($attribute->default_value == 'date_of_current_day')){
									$input_def['value'] = date('Y-m-d');
								}
								$input_more_class .= ' wpshop_input_datetime ';
								$input_options = '<script type="text/javascript" >wpshop(document).ready(function(){wpshop("#' . $input_def['id'] . '").val("' . str_replace(" 00:00:00", "", $input_def['value']) . '")});</script>';
							}

							$label = 'for="' . $input_def['id'] . '"';
							$more_input = '';
							if (($attribute->backend_input == 'select') OR ($attribute->backend_input == 'multiple-select')) {
								$input_more_class .= ' chosen_select ';
								$input_def['type'] = $attribute->backend_input;
								$input_def['valueToPut'] = 'index';

								$select_display = self::get_select_output($attribute);
								$more_input .= $select_display['more_input'];
								$input_def['possible_value'] = $select_display['possible_value'];

								$more_input .= '<input type="hidden" value="' . str_replace("\\", "", $input_def['value']) . '" name="wpshop_product_attribute_' . $attribute->code . '_current_value" id="wpshop_product_attribute_' . $attribute->code . '_current_value" />';
							}
							if(($input_def['type'] == 'radio') || ($input_def['type'] == 'checkbox')){
								$label = '';
							}
							if((WPSHOP_PRODUCT_PRICE_PILOT == 'HT') && ($attribute->code == WPSHOP_PRODUCT_PRICE_TTC) ){
								$input_def['option'] .= ' readonly="readonly" ';
								$input_more_class = ' wpshop_prices_readonly';
							}
							elseif((WPSHOP_PRODUCT_PRICE_PILOT == 'TTC') && ($attribute->code == WPSHOP_PRODUCT_PRICE_HT) ){
								$input_def['option'] .= ' readonly="readonly" ';
								$input_more_class = ' wpshop_prices_readonly';
							}
							if ($attribute->code == WPSHOP_PRODUCT_PRICE_TAX_AMOUNT) {
								$input_def['option'] .= ' readonly="readonly" ';
								$input_more_class = ' wpshop_prices_readonly';
							}
							$input_label = str_replace("\\", "", $input_label);
							$input_def['value'] = str_replace("\\", "", $input_def['value']);
							$input_def['option'] .= ' class="wpshop_product_attribute_' . $attribute->code . ' alignleft' . $input_more_class . '" ';

							if(($attribute->is_intrinsic == 'yes') && ((!empty($input_def['value'])) || ($input_def['value'] > 0))){
								$input_def['option'] .= ' readonly="readonly" ';
							}

							$input = wpshop_form::check_input_type($input_def, $attributeInputDomain) . $more_input;

							/*	Add the unit to the attribute if attribute configuration is set to yes	*/
							if($attribute->is_requiring_unit == 'yes'){
								if ( in_array($attribute->code, $wpshop_price_attributes) ) {
									$input .= '&nbsp;<span class="alignleft attribute_currency" id="attribute_currency_' . $attribute->id . '" >' . wpshop_tools::wpshop_get_currency() . '</span>';
								}
								elseif ( in_array($attribute->code, $wpshop_weight_attributes) ) {
									$input .= __('Kilogram', 'wpshop');
								}
								else{
									$unit_input_def['possible_value'] = wpshop_attributes_unit::get_unit_list_for_group($attribute->_unit_group_id);
									$unit_input_def['type'] = 'select';
									$unit_input_def['option'] = ' class="wpshop_attribute_unit_input chosen_select" ';
									$unit_input_def['id'] = $currentPageCode . '_' . $itemToEdit . '_unit_attribute_' . $attribute->id;
									$unit_input_def['name'] = $attribute->code;
									$unit_input_def['value'] = (!empty($attributeValue->unit_id)?$attributeValue->unit_id:'');
									if($unit_input_def['value'] == ''){
										if($attribute->_default_unit > 0){
											$unit_input_def['value'] = $attribute->_default_unit;
										}
										else{
											$unit_input_def['value'] = wpshop_attributes_unit::get_default_unit_for_group($attribute->_unit_group_id);
										}
									}
									$input .= wpshop_form::check_input_type($unit_input_def, $attributeInputDomain .= '[unit]');
								}
							}

							/*	Add indication on postage cost tax	*/
							if ( $attribute->code == WPSHOP_COST_OF_POSTAGE ) {
								$input .= '&nbsp;<div class="attribute_currency alignleft" >' . __('ATI', 'wpshop') . '</div>';
							}

							if(($input_def['type'] != 'hidden')){
								// Test if the option value is "activated" in order to hide/display additionnal fields
								$attribute_option_display = $attribute->backend_input=='select' && strtolower(self::get_attribute_type_select_option_info($input_def['value'], 'value'))=='yes' ? 'block' : 'none';
								
								$content = ($input_def['name']=='quotation_allowed') ? (WPSHOP_ADDONS_QUOTATION ? $input.$input_options : __('Quotation addon isn\'t activated','wpshop')) : $input.$input_options;

								$price_tab = unserialize(WPSHOP_ATTRIBUTE_PRICES);
								unset($price_tab[array_search(WPSHOP_COST_OF_POSTAGE, $price_tab)]);

								$currentTabContent .= '
<div class="clear" >
	<div class="wpshop_form_label ' . $currentPageCode . '_' . $input_def['name'] . '_label ' . (in_array($attribute->code, $price_tab) ? $currentPageCode . '_prices_label ' : '') . ' alignleft" >
		<label ' . $label . ' >' . __($input_label, 'wpshop') . ($attribute->is_required == 'yes' ? ' <span class="wpshop_required" >*</span>' : '') . '</label>
	</div>
	<div class="wpshop_form_input_element ' . $currentPageCode . '_' . $input_def['name'] . '_input ' . (in_array($attribute->code, $price_tab) ? $currentPageCode . '_prices_input ' : '') . ' alignleft" >
		' . $content . '
	</div>
	<div class="attribute_option_'.$attribute->code.'" style="display:'.$attribute_option_display.'">'.self::get_attribute_option_fields($itemToEdit,$attribute->code).'</div>
</div>';

									$shortcode_code_def=array();
									$shortcode_code_def['attribute_'.str_replace('-', '_', sanitize_title($input_label))]['main_code'] = 'wpshop_att_val';
									$shortcode_code_def['attribute_'.str_replace('-', '_', sanitize_title($input_label))]['attrs_exemple']['type'] = $attribute->data_type;
									$shortcode_code_def['attribute_'.str_replace('-', '_', sanitize_title($input_label))]['attrs_exemple']['attid'] = $attribute->id;
									$shortcode_code_def['attribute_'.str_replace('-', '_', sanitize_title($input_label))]['attrs_exemple']['pid'] = $itemToEdit;
									ob_start();
										wpshop_shortcodes::output_shortcode('attribute_'.str_replace('-', '_', sanitize_title($input_label)), $shortcode_code_def, 'wpshop_product_shortcode_display wpshop_product_attribute_shortcode_display wpshop_product_attribute_shortcode_display_'.str_replace('-', '_', sanitize_title($input_label)).' clear');
										$shortcodes .= '<li class="clear" >'.sprintf(__('Insertion code for the attribute %s for this product', 'wpshop'), '<span>'.__($input_label, 'wpshop').'</span>').ob_get_contents().'</li>';
									ob_end_clean();
							}
							else {
								$currentTabContent .= $input;
							}
							$output_nb++;
						}
					}

					$shortcode_code['attributes_set']['main_code'] = 'wpshop_att_group';
					$shortcode_code['attributes_set']['attrs_exemple']['pid'] = $itemToEdit;
					$shortcode_code['attributes_set']['attrs_exemple']['sid'] = $productAttributeSetDetail['id'];
					ob_start();
						wpshop_shortcodes::output_shortcode('attributes_set', $shortcode_code, 'wpshop_product_shortcode_display wpshop_product_attribute_group_shortcode_display wpshop_product_attribute_group_shortcode_display_'.str_replace('-', '_', sanitize_title($productAttributeSetDetail['name'])).' clear');
						$attribute_group_display = sprintf(__('Insertion code for attribute group %s for this product', 'wpshop'), '<span>'.$productAttributeSetDetail['name'].'</span>').ob_get_contents().'<ul class="" >'.$shortcodes.'</ul>';
					ob_end_clean();
					
					if( WPSHOP_PRODUCT_SHORTCODE_DISPLAY_TYPE == 'each-box' )
						$currentTabContent .= '<div class="clear" ><strong>'.__('Shortcodes','wpshop').'</strong> - <a href="#" class="show-hide-shortcodes">Afficher</a><div class="wpshop_product_shortcode_display wpshop_product_shortcode_display_container wpshopHide" >' . $attribute_group_display . '</div></div>';
					else
						$shortcodes_attr .= $attribute_group_display;
		
					if ( $output_nb <= 0 ) {
						$currentTabContent = __('Nothing avaiblable here. You can go in attribute management interface in order to add content here.', 'wpshop');
					}
				}

				if($output_nb > 0){
					$shortcodes_to_display = true;
					if($outputType == 'box'){
						$box['box'][$productAttributeSetDetail['code']] = $productAttributeSetDetail['name'];
						$box['box'][$productAttributeSetDetail['code'].'_backend_display_type'] = $productAttributeSetDetail['backend_display_type'];
						$box['boxContent'][$productAttributeSetDetail['code']] = '
		<div id="wpshop_' . $currentPageCode . '_' . wpshop_tools::slugify($productAttributeSetDetail['code'], array('noAccent')) . '_form" >' . $currentTabContent . '
						</div><div class="clear" ></div>';
					}
					elseif($outputType == 'column'){
						$currentTabContent = str_replace('wpshop_form_input_element', 'wpshop_form_input_column', $currentTabContent);
						$currentTabContent = str_replace('wpshop_form_label', 'wpshop_form_label_column', $currentTabContent);

						$box['columnTitle'][$productAttributeSetDetail['code']] = __($productAttributeSetDetail['name'], 'wpshop');
						$box['columnContent'][$productAttributeSetDetail['code']] = $currentTabContent;
					}
				}
			}

			if( !$attribute_set_id_is_present ){
				unset($input_def);
				$input_def['id'] = 'product_attribute_set_id';
				$input_def['name'] = 'product_attribute_set_id';
				$input_def['value'] = $attributeSetId;
				$input_def['type'] = 'hidden';
				$box['boxMore'] = wpshop_form::check_input_type($input_def, 'product_attribute[integer]');
			}

			/*	Ajout de la boite permettant d'ajouter des valeurs aux attributs de type liste deroulante a la volee	*/
			$dialog_title = __('New value for attribute', 'wpshop');
			$dialog_identifier = 'wpshop_new_attribute_option_value_add';
			$dialog_input_identifier = 'wpshop_new_attribute_option_value';
			ob_start();
			include(WPSHOP_TEMPLATES_DIR.'admin/add_new_element_dialog.tpl.php');
			$box['boxMore'] .= ob_get_contents();
			ob_end_clean();
			$box['boxMore'] .= '<input type="hidden" name="wpshop_attribute_type_select_code" value="" id="wpshop_attribute_type_select_code" />';
	
			if ( $shortcodes_to_display ) {
				switch ( WPSHOP_PRODUCT_SHORTCODE_DISPLAY_TYPE ) {
					case 'fixed-tab':
					case 'movable-tab':
						if($outputType == 'box'){
							$box['box']['shortcode'] = __('Product Shortcodes', 'wpshop');
							$box['boxContent']['shortcode'] = $shortcodes_attr;
							$box['box']['shortcode_backend_display_type'] = WPSHOP_PRODUCT_SHORTCODE_DISPLAY_TYPE;
						}
						else{
							$box['columnTitle']['shortcode'] = __('Product Shortcodes', 'wpshop');
							$box['columnContent']['shortcode'] = $shortcodes_attr;
						}
					break;
				}
			}
		}

		return $box;
	}

	/**
	 * Affichage de la liste deroulante correspondante a un attribut
	 * 
	 * @param object $attribute La definition complete de l'attribut que l'on souhaite afficher
	 * @return array $output Retourne un tableau contenant les differentes valeurs et options pour la liste deroulante a afficher
	 */
	function get_select_output($attribute, $provenance = array()) {
		global $wpdb;
		$ouput = array();
		$ouput['more_input'] = '';

		if ( $attribute->data_type_to_use == 'custom') {
			$query = $wpdb->prepare("SELECT id, label, value, '' as name FROM " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " WHERE attribute_id = %d AND status = 'valid' ORDER BY position", $attribute->id);
			$attribute_select_options = $wpdb->get_results($query);

			/*	Lecture de la liste des elements existant pour la construction de la liste des options pour l'attribut	*/
			foreach ($attribute_select_options as $index => $option) :
				$attribute_select_options_list[$option->id] = $option->label;
	
				$ouput['more_input'] .= '<input type="hidden" value="' . (WPSHOP_DISPLAY_VALUE_FOR_ATTRIBUTE_SELECT ? str_replace("\\", "", $option->value) : str_replace("\\", "", $option->label)) . '" name="wpshop_product_attribute_' . $attribute->code . '_value_' . $option->id . '" id="wpshop_product_attribute_' . $attribute->code . '_value_' . $option->id . '" />';
			endforeach;
		}
		elseif ( $attribute->data_type_to_use == 'internal')  {
			switch ($attribute->default_value) {
				case 'users':
					$users = get_users('orderby=nicename');
					foreach($users as $user){
						$attribute_select_options_list[$user->ID] = $user->display_name;
					}
					break;
				default:
					wp_reset_query();
					$wpshop_attr_custom_post_query = new WP_Query(array(
						'post_type' => $attribute->default_value
					));

					if($wpshop_attr_custom_post_query->have_posts()):
						foreach($wpshop_attr_custom_post_query->posts as $post){
							$attribute_select_options_list[$post->ID] = $post->post_title;
						}
					endif;
					wp_reset_query();
					break;
			}
		}

		/*	Si il n'existe aucune valeur pour cet attribut	*/
		if (empty($attribute_select_options_list)) :
			$ouput['more_input'].=__('Nothing found for this field','wpshop');
		else:
			/*	On ajoute dans la liste des options en premiere position une option vide	*/
			$ouput['possible_value'][] = __('Choose...', 'wpshop');
			foreach ( $attribute_select_options_list as $option_key => $option_value ) {
				$ouput['possible_value'][$option_key] = $option_value;
			}
		endif;

		/*	On ajoute la possiblite d'ajouter une valeur a la liste existante des options pour cette liste	*/
		$ouput['more_input'] .= '<img src="'.WPSHOP_MEDIAS_ICON_URL.'add.png" rel="' . $attribute->data_type_to_use . '_' . $attribute->code . '" alt="'.__('Add a new value for this attribute', 'wpshop').'" title="'.__('Add a new value for this attribute', 'wpshop').'" class="wpshop_icons wpshop_icons_add_new_value_to_option_list wpshop_icons_add_new_value_to_option_list_'.$attribute->code.'" />';

		return $ouput;
	}

	function get_attribute_option_output($item, $attr_code, $attr_option, $additionnal_params = ''){
		switch($attr_code){
			case 'is_downloadable_':
				$option = get_post_meta($item['item_id'], 'attribute_option_'.$attr_code, true);
				switch($attr_option){
					case 'file_url':
						if(in_array($additionnal_params['order_status'], array('completed', 'shipped')) && (!empty($item['item_'.$attr_code]) && (strtolower($item['item_'.$attr_code])=='yes'))) {
							$file_url = isset($option[$attr_option]) ? $option[$attr_option] : false;
							return $file_url;
						}
						return false;
						break;
					/*case 'manage_stock':
						if(isset($item['item_'.$attr_code]) && strtolower($item['item_'.$attr_code])=='yes') {
							return isset($option[$attr_option]) && $option[$attr_option]=="true";
						}
						return true;
						break;*/
				}
				break;
		}
	}

	function get_attribute_option_fields($postid, $code) {

		switch($code){
			case 'is_downloadable_':
				$data = get_post_meta($postid, 'attribute_option_'.$code, true);
				$data['file_url'] = !empty($data['file_url'])?$data['file_url']:__('No file selected', 'wpshop');

				$fields = '<div class="wpshop_form_label alignleft">&nbsp;</div>
						<div class="wpshop_form_input_element alignleft"><br /><br />
						<form></form>
						<form action="'.WPSHOP_AJAX_FILE_URL.'" method="post" enctype="multipart/form-data" id="wpshop_uploadForm">
						<input type="file" name="wpshop_file" style="width:auto;" />
						<input type="hidden" name="post" value="true" />
						<input type="hidden" name="elementCode" value="ajaxUpload" />
						<input type="submit" value="'.__('Upload File','wpshop').'" class="button" /> <img src="' . admin_url('images/loading.gif') . '" alt="loading..." class="wpshop_loading" style="display:none;" />
						</form>
						<div class="statut">'.basename($data['file_url']).'</div>
						</div>';

				$fields .= '<div class="wpshop_form_label alignleft"><label>'.__('File url','wpshop').'</label></div>
						<div class="wpshop_form_input_element alignleft">
						<input type="hidden" name="attribute_option[is_downloadable_][file_url]" value="'.$data['file_url'].'" /><br /><br />
						</div>';

				/*$fields .= '<div class="wpshop_form_label alignleft">&nbsp;</div>
						<div class="wpshop_form_input_element alignleft">
						<input type="checkbox" name="attribute_option[is_downloadable_][manage_stock]" value="true" '.(!empty($data['manage_stock'])?'checked="checked"':null).' />
						<label>'.__('Manage stock','wpshop').'</label><br /><br />
						</div>';*/

				$fields .= '<div class="wpshop_form_label alignleft">&nbsp;</div>
						<div class="wpshop_form_input_element alignleft">
						<input type="checkbox" name="attribute_option[is_downloadable_][allow_presale]" value="true" '.(!empty($data['allow_presale'])?'checked="checked"':null).' />
						<label>'.__('Allow pre-sale','wpshop').'</label><br /><br />
						</div>';

				return $fields;
				break;
					
			default:
				return '';
				break;
		}

	}

	/**
	 *	Return content informations about a given attribute
	 *
	 *	@param string $attribute_code The code of attribute to get (Not the id because if another system is using eav model it could have some conflict)
	 *	@param integer $entity_id The current entity we want to have the attribute value for
	 *	@param string $entity_type The current entity type code we want to have the attribute value for
	 *
	 *	@return object $attribute_value_content The attribute content
	 */
	function get_attribute_value_content($attribute_code, $entity_id, $entity_type){
		$attribute_value_content = '';

		$atributes = self::getElement($attribute_code, "'valid'", 'code');
		$attribute_value_content = self::getAttributeValueForEntityInSet($atributes->data_type, $atributes->id,  wpshop_entities::get_entity_identifier_from_code($entity_type), $entity_id);

		return $attribute_value_content;
	}


	/**
	 * Met a jour un ou plusieurs attributes concernant un produit
	 * @param integer $entityId Id du produit
	 * @param array $values Valeurs d'attributs
	 * @return array
	 */
	function setAttributesValuesForItem($entityId, $values=array(), $defaultValueForOthers=false) {
		global $wpdb;
	
		$message='';
		$attribute_available = array();
		$attribute_final = array();
		$data = self::get_attribute_list_for_item(wpshop_entities::get_entity_identifier_from_code(wpshop_products::currentPageCode), $entityId);
		foreach($data as $d) $attribute_available[$d->attribute_code] = array('attribute_id' => $d->attribute_id, 'data_type' => $d->data_type);
	
		// Creation d'un array "propre" et valide pour la fonction self::saveAttributeForEntity
		foreach ( $values as $key => $value ) {
			if ( in_array( $key, array_keys( $attribute_available ) ) ) {
				$attribute_final[$attribute_available[$key]['data_type']][$key] = $value;
			} else $message .= sprintf(__('Impossible to set "%s" attribute', 'wpshop'), $key)."\n";
		}
	
		// Pour les autres attributs non donnÃ© on leur affecte leur valeur par dÃ©faut
		if ($defaultValueForOthers) {
			$codes = array_keys($values);
			foreach ($data as $d) {
				if (!in_array($d->attribute_code, $codes)) {
					$attribute_final[$d->data_type][$d->attribute_code] = $d->default_value;
				}
			}
		}
	
		/*	Save the attributes values into wordpress post metadata database in order to have a backup and to make frontend search working	*/
		$productMetaDatas = array();
		foreach ($attribute_final as $attributeType => $attributeValues) {
			foreach ($attributeValues as $attributeCode => $attributeValue) {
				$productMetaDatas[$attributeCode] = $attributeValue;
			}
		}
		$current = get_post_meta($entityId, WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, true);
		$current = empty($current) ? array() : $current;
		$productMetaDatas = array_merge($current, $productMetaDatas);
		update_post_meta($entityId, WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, $productMetaDatas);
	
		if (!empty($attribute_final)) {
			self::saveAttributeForEntity($attribute_final, wpshop_entities::get_entity_identifier_from_code(wpshop_products::currentPageCode), $entityId, get_locale(), 'webservice');
		}
	
		return array('status' => empty($message), 'message' => $message);
	}


	/**
	 * Recupere les informations concernant une option donnees dans la liste d'un attribut de type liste deroulante
	 * 
	 * @param integer $option_id L'identifiant de l'option dont on veut recuperer les informations
	 * @param string $field optionnal Le champs correspondant a l'information que l'on souhaite recuperer
	 * @return string $info L'information que l'on souhaite
	 */
	function get_attribute_type_select_option_info ($option_id, $field = 'label') {
		global $wpdb;

		$query = $wpdb->prepare("SELECT " . $field . " FROM ".WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS." WHERE id=%d LIMIT 1", $option_id);
		$info = $wpdb->get_var($query);

		return $info;
	}
	function get_select_option_list_ ($attribute_id){
		global $wpdb;
		$query = $wpdb->prepare("
			SELECT ATTRIBUTE_COMBO_OPTION.id, ATTRIBUTE_COMBO_OPTION.label as name, ATTRIBUTE_COMBO_OPTION.value , ATTRIBUTE_VALUE_INTEGER.value_id
			, ATT.default_value, ATT.data_type_to_use, ATT.use_ajax_for_filling_field
			FROM " . WPSHOP_DBT_ATTRIBUTE . " AS ATT
				LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS . " AS ATTRIBUTE_COMBO_OPTION ON ((ATTRIBUTE_COMBO_OPTION.attribute_id = ATT.id) AND (ATTRIBUTE_COMBO_OPTION.status = 'valid'))
				LEFT JOIN " . WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER . " AS ATTRIBUTE_VALUE_INTEGER ON ((ATTRIBUTE_VALUE_INTEGER.attribute_id = ATTRIBUTE_COMBO_OPTION.attribute_id) AND (ATTRIBUTE_VALUE_INTEGER.value = ATTRIBUTE_COMBO_OPTION.id))
			WHERE ATT.id = %d
				AND ATT.status = 'valid'
			GROUP BY ATTRIBUTE_COMBO_OPTION.value
			ORDER BY ATTRIBUTE_COMBO_OPTION.position", $attribute_id);
		return $wpdb->get_results($query);
	}
	/**
	 * Recupere la liste des options pour les attributs de type liste deroulante suivant le type de donnees choisi (personnalise ou interne a wordpress)
	 * 
	 * @param integer $attribute_id L'identifiant de l'attribut pour lequel on souhaite recuperer la liste des options
	 * @param string $data_type optionnal Le type de donnees choisi pour cet attribut (custom | internal)
	 * @return string Le resultat sous forme de code html pour la liste des options
	 */
	function get_select_options_list($attribute_id, $data_type='custom') {
		global $wpdb;
		$output = '';

		$attribute_select_options = self::get_select_option_list_($attribute_id);

		/*	Add possibily to choose datat type to use with list	*/
		if(empty($attribute_id) || (!empty($attribute_select_options) && empty($attribute_select_options[0]->data_type_to_use))){
			unset($input_def);$input_def=array();
			$input_def['label'] = __('Type of data for list', 'wpshop');
			$input_def['type'] = 'radio';
			$input_def['name'] = 'data_type_to_use';
			$input_def['valueToPut'] = 'index';
			$input_def['possible_value'] = unserialize(WPSHOP_ATTR_SELECT_TYPE);
			$input_def['option'] = 'class="clear wpshop_attr_combo_data_type"';
			$input_def['value'] = $data_type.'_data';
			$input_def['options']['label']['original'] = true;
			$output = wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE);
		}

		if(!empty($attribute_id) || !empty($data_type)){
			if((($data_type == 'custom') && empty($attribute_select_options)) || (!empty($attribute_select_options) && !empty($attribute_select_options[0]->data_type_to_use) && ($attribute_select_options[0]->data_type_to_use == 'custom'))){
				$sub_output = '';
				if ( count($attribute_select_options) > 0 ) {
					foreach($attribute_select_options as $options){
						$option_id=$options->id;
						$option_default_value=$options->default_value;
						$option_value_id=$options->value_id;
						$option_name=$options->name;
						$options_value=$options->value;
						ob_start();
						include(WPSHOP_TEMPLATES_DIR.'admin/attribute_option_value.tpl.php');
						$sub_output .= ob_get_contents();
						ob_end_clean();
					}
				}
				$add_button = $add_dialog_box = $user_more_script = '';
				if( current_user_can('wpshop_add_attributes_select_values') ) {

					$dialog_title = __('New value for attribute', 'wpshop');
					$dialog_identifier = 'wpshop_new_attribute_option_value_add';
					$dialog_input_identifier = 'wpshop_new_attribute_option_value';
					ob_start();
					include(WPSHOP_TEMPLATES_DIR.'admin/add_new_element_dialog.tpl.php');
					$add_dialog_box = ob_get_contents();
					ob_end_clean();

					$add_button_text = __('Add a value for this attribute', 'wpshop');
					$add_button_parent_class = 'wpshop_attribute_option_value_add';
					$add_button_name = 'wpshop_add_option_to_select';
					ob_start();
					include(WPSHOP_TEMPLATES_DIR.'admin/add_new_element_with_dialog.tpl.php');
					$add_button = ob_get_contents();
					ob_end_clean();

					$user_more_script = '
			jQuery("#'.$dialog_identifier.'").dialog({
				modal: true,
				autoOpen:false,
				show: "blind",
				buttons:{
					"'.__('Add', 'wpshop').'": function(){
						var data = {
							action: "new_option_for_select",
							wpshop_ajax_nonce: "' . wp_create_nonce("wpshop_new_option_for_attribute_creation") . '",
							attribute_new_label: jQuery("#'.$dialog_input_identifier.'").val(),
							attribute_identifier: "' . $attribute_id . '"
						};
						jQuery.getJSON(ajaxurl, data, function(response) {
							if( response[0] ) {
								jQuery("#sortable_attribute li:last-child").before(response[1]);
								jQuery("#wpshop_new_attribute_option_value_add").dialog("close");
							}
							else {
								alert(response[1]);		
							}
							jQuery("#wpshop_new_attribute_option_value_add").children("img").hide();
						});

						jQuery(this).children("img").show();
					},
					"'.__('Cancel', 'wpshop').'": function(){
						jQuery(this).dialog("close");
					}
				},
				close:function(){
					jQuery("#'.$dialog_input_identifier.'").val("");
				}
			});
			jQuery(".'.$add_button_parent_class.' input").click(function(){
				jQuery("#'.$dialog_identifier.'").dialog("open");
			});';

				}
				$output .= $add_dialog_box . '
	<ul id="sortable_attribute" class="clear" >'.(count($attribute_select_options)>5 ? $add_button : '').$sub_output.$add_button.'
	</ul>
	<input type="hidden" value="' . wp_create_nonce("wpshop_new_option_for_attribute_deletion") . '" name="wpshop_new_option_for_attribute_deletion_nonce" id="wpshop_new_option_for_attribute_deletion_nonce" />
	<script type="text/javascript" >
		wpshop(document).ready(function(){
			jQuery("#sortable_attribute").sortable({
				revert: true,
				items: "li:not(.ui-state-disabled)"
			});
			' . $user_more_script . '
			jQuery(".wpshop_attr_combo_data_type").live("click", function(){
				if(jQuery(this).is(":checked")){
					jQuery(".wpshop_attributes_edition_table_field_input_default_value").html(jQuery("#wpshopLoadingPicture").html());
										
					var data = {
						action: "attribute_output_type",
						current_type: jQuery("#wpshop_attributes_edition_table_field_id_backend_input").val(),
						elementIdentifier: "'.$attribute_id.'",
						data_type_to_use: jQuery(this).val(),
						wpshop_ajax_nonce: "' . wp_create_nonce("wpshop_attribute_output_type_selection") . '"
					};
					jQuery.getJSON(ajaxurl, data, function(response) {
						jQuery(".wpshop_attributes_edition_table_field_input_default_value").html(response[0]);
						jQuery(".wpshop_attributes_edition_table_field_label_default_value label").html(response[1]);
					});
				}
			});
		});
	</script>';
			}
			elseif((($data_type == 'internal') && empty($attribute_select_options)) || (!empty($attribute_select_options) && !empty($attribute_select_options[0]->data_type_to_use) && ($attribute_select_options[0]->data_type_to_use == 'internal'))){
				$sub_output='';
				$wp_types = unserialize(WPSHOP_INTERNAL_TYPES);
				unset($input_def);$input_def=array();
				$input_def['label'] = __('Type of data for list', 'wpshop');
				$input_def['type'] = 'select';
				$input_def['name'] = 'default_value';
				$input_def['valueToPut'] = 'index';
				$input_def['possible_value'] = $wp_types;
				$input_def['value'] = !empty($attribute_select_options[0]->default_value) ? $attribute_select_options[0]->default_value : null;
				$combo_wp_type = wpshop_form::check_input_type($input_def, WPSHOP_DBT_ATTRIBUTE);
				$output .= '<div class="clear">'.$combo_wp_type.'</div>';
			}
		}

		return $output;
	}

}
