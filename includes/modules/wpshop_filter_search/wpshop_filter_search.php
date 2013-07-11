<?php
/**
 * Plugin Name: WP-Shop-filter_search
 * Plugin URI: http://www.wpshop.fr/documentations/presentation-wpshop/
 * Description: WpShop Filter Search
 * Version: 0.1
 * Author: Eoxia
 * Author URI: http://eoxia.com/
 */

/**
 * WPShop Filter Search bootstrap file
 * @author Jérôme ALLEGRE - Eoxia dev team <dev@eoxia.com>
 * @version 0.1
 * @package includes
 * @subpackage modules
 *
 */
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __("You are not allowed to use this service.", 'wpshop') );
}
if ( !class_exists("wpshop_filter_search") ) {
	class wpshop_filter_search {
		function __construct() {
			add_filter( 'wpshop_custom_template', array( &$this, 'custom_template_load' ) );
			add_shortcode('wpshop_filter_search', array(&$this, 'display_filter_search'));
			
			/** CSS Include **/
			wp_register_style( 'wpshop_filter_search_css', plugins_url('templates/wpshop/css/wpshop_filter_search.css', __FILE__) );
			wp_enqueue_style( 'wpshop_filter_search_css' );
			
			/** JS Include **/
			if ( !is_admin() ) {
				wp_enqueue_script( 'wpshop_filter_search_js', plugins_url('templates/wpshop/js/wpshop_filter_search.js', __FILE__) );
				wp_enqueue_script( 'wpshop_filter_search_chosen', WPSHOP_JS_URL.'jquery-libs/chosen.jquery.min.js' );
			}
			
			/** Ajax action **/
			add_action('wp_ajax_update_filter_product_display',array(&$this, 'wpshop_ajax_update_filter_product_display'));
			add_action('wp_ajax_nopriv_update_filter_product_display',array(&$this, 'wpshop_ajax_update_filter_product_display'));
			add_action('wp_ajax_filter_search_action',array(&$this, 'wpshop_ajax_filter_search_action'));
			add_action('wp_ajax_nopriv_filter_search_action',array(&$this, 'wpshop_ajax_filter_search_action'));
			
			add_action('save_post', array(&$this, 'save_displayed_price_meta'));
			add_action('save_post', array(&$this, 'stock_values_for_attribute'));
		}
		
		/** Load module/addon automatically to existing template list
		 *
		 * @param array $templates The current template definition
		 *
		 * @return array The template with new elements
		 */
		function custom_template_load( $templates ) {
			include('templates/wpshop/main_elements.tpl.php');
			$templates = wpshop_display::add_modules_template_to_internal( $tpl_element, $templates );
			unset($tpl_element);
		
			return $templates;
		}
		
		function display_filter_search () {
			global $wp_query;
			$output = '';
			if ( !empty($wp_query) && !empty($wp_query->queried_object_id) ) {
				$category_id = $wp_query->queried_object_id;
				$category_option =  get_option('wpshop_product_category_'.$category_id);
				if ( !empty($category_option) && !empty($category_option['wpshop_category_filterable_attributes']) ) {
					$output = $this->construct_wpshop_filter_search_interface( $category_id );
				}
			}
			return $output;
		}
		
		/**
		 * Return a filter search interface for the current category
		 * @param integer $category_id
		 * @return string
		 */
		function construct_wpshop_filter_search_interface ( $category_id ) {
			global $wpdb;
			$tpl_component = array();
			$tpl_component['CATEGORY_ID'] = $category_id;
			$filter_search_interface = $tpl_component['FILTER_SEARCH_ELEMENT'] = '';
			if ( !empty($category_id) ) {
				$category_option =  get_option('wpshop_product_category_'.$category_id);
				if ( !empty($category_option) && !empty($category_option['wpshop_category_filterable_attributes']) && is_array($category_option['wpshop_category_filterable_attributes']) ) {
					foreach ( $category_option['wpshop_category_filterable_attributes'] as $k => $attribute ) {
						$attribute_def = wpshop_attributes::getElement($k);
						$tpl_component['FILTER_SEARCH_ELEMENT'] .= $this->construct_element( $attribute_def, $category_id );
						$unity = '';
						if ( !empty($attribute_def->_default_unit) ) {
							$query = $wpdb->prepare('SELECT unit FROM ' .WPSHOP_DBT_ATTRIBUTE_UNIT. ' WHERE id= %d', $attribute_def->_default_unit);
							$unity = $wpdb->get_var( $query );
						}
						
						$tpl_component['DEFAULT_UNITY'.'_'.$attribute_def->code] = $unity;
					}
				}
			}
			$filter_search_interface = wpshop_display::display_template_element('wpshop_filter_search_interface', $tpl_component, array(), 'wpshop');
			return $filter_search_interface;
		}
		
		function construct_element ( $attribute_def, $category_id ) {
			global $wpdb;
			$current_category_children = array();
			$args = array(
					'type'		=> WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
					'taxonomy'  => WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES,
					'child_of'  => $category_id
			);
			$current_category_children = get_categories($args);
			
			if ( !empty( $attribute_def ) ) {
				switch ( $attribute_def->frontend_input ) {
					case 'text' : 
						if ( $attribute_def->data_type == 'decimal' || $attribute_def->data_type == 'integer') {
							return $this->get_filter_element_for_integer_data( $attribute_def, $category_id, $current_category_children );
						}
						else {
							return $this->get_filter_element_for_text_data( $attribute_def, $category_id, $current_category_children );
						}
					break;
					
					case 'select' : case 'checkbox' : case 'radio' : case 'multiple-select' :
						return $this->get_filter_element_for_list_data ( $attribute_def, $category_id, $current_category_children, $attribute_def->frontend_input);
					break;
					
				}
			}
		}
		
		
		/**
		 * Construct the element when it's a decimal Data
		 * @param StdObject $attribute_def
		 * @return string
		 */
		function get_filter_element_for_integer_data ( $attribute_def, $category_id, $current_category_children  ) {
 			$min_value = $max_value = 0;
 			$sub_tpl_component = array();
 			$output = '';
 			$first  = true;
 			/** Get allproducts of category **/
 			$category_product_ids = wpshop_categories::get_product_of_category( $category_id );
 			$category_option = get_option('wpshop_product_category_'.$category_id );
 			if ( !empty($category_option) && !empty($category_option['wpshop_category_filterable_attributes']) && !empty($category_option['wpshop_category_filterable_attributes'][$attribute_def->id]) ) {
	 			$sub_tpl_component['FILTER_SEARCH_ATTRIBUTE_TITLE'] = __($attribute_def->frontend_label, 'wpshop');
	 			$sub_tpl_component['FILTER_SEARCH_FILTER_LIST_NAME'] = $attribute_def->code;
	 			$sub_tpl_component['FILTER_SEARCH_MIN_DATA'] = ( !empty($category_option['wpshop_category_filterable_attributes'][$attribute_def->id]['min']) ) ? number_format($category_option['wpshop_category_filterable_attributes'][$attribute_def->id]['min'],2, '.', '') : 0;
	 			$sub_tpl_component['FILTER_SEARCH_MAX_DATA'] = ( !empty($category_option['wpshop_category_filterable_attributes'][$attribute_def->id]['max']) ) ? number_format($category_option['wpshop_category_filterable_attributes'][$attribute_def->id]['max'],2, '.', '') : 0;
	 			
	 			$output = wpshop_display::display_template_element('wpshop_filter_search_element_for_integer_data', $sub_tpl_component, array(), 'wpshop');
	 			unset($sub_tpl_component);
 			}
			return $output;
		}
		
		/**
		 * Construct the element when it's a text Data
		 * @param StdObject $attribute_def
		 * @return string
		 */
		function get_filter_element_for_text_data( $attribute_def, $category_id, $current_category_child ) {
			global $wpdb;
			$output = '';
			$category_option = get_option('wpshop_product_category_'.$category_id);
			$list_values = array();
			$sub_tpl_component = array();
			$sub_tpl_component['FILTER_SEARCH_ATTRIBUTE_TITLE'] = __($attribute_def->frontend_label, 'wpshop');
			$sub_tpl_component['FILTER_SEARCH_FILTER_LIST_NAME'] = $attribute_def->code;
			$sub_tpl_component['FILTER_SEARCH_LIST_VALUE'] = '';
			
			if ( !empty($category_option) && !empty($category_option['wpshop_category_filterable_attributes']) && !empty($category_option['wpshop_category_filterable_attributes'][$attribute_def->id]) && is_array($category_option['wpshop_category_filterable_attributes'][$attribute_def->id]) ) {
				foreach( $category_option['wpshop_category_filterable_attributes'][$attribute_def->id] as $attribute_value ) {
					$sub_tpl_component['FILTER_SEARCH_LIST_VALUE'] .= '<option value="' .$attribute_value. '">' .$attribute_value. '</option>';
				}
				$output = wpshop_display::display_template_element('wpshop_filter_search_element_for_text_data', $sub_tpl_component, array(), 'wpshop');
				unset($sub_tpl_component);
			}
			return $output;
		}
		
		/**
		 * Construct the element when it's a list Data
		 * @param StdObject $attribute_def
		 * @return string
		 */
		function get_filter_element_for_list_data ( $attribute_def, $category_id, $current_category_child, $field_type) {
			global $wpdb;
			$output = '';
			$category_option = get_option('wpshop_product_category_'.$category_id);
			if ( !empty( $attribute_def) ){
				$sub_tpl_component['FILTER_SEARCH_ATTRIBUTE_TITLE'] = __($attribute_def->frontend_label, 'wpshop');
				$sub_tpl_component['FILTER_SEARCH_FILTER_LIST_NAME'] = $attribute_def->code;
				$sub_tpl_component['FILTER_SEARCH_LIST_VALUE'] = '';
				if ( !empty($category_option) && !empty($category_option['wpshop_category_filterable_attributes']) && isset($category_option['wpshop_category_filterable_attributes'][$attribute_def->id]) ) {
					$available_attribute_values = $category_option['wpshop_category_filterable_attributes'][$attribute_def->id];
				}
				$stored_available_attribute_values = array();
				/** Store options for the attribute **/
				$query = $wpdb->prepare( 'SELECT * FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS .' WHERE attribute_id = %d', $attribute_def->id); 
				$attributes_options = $wpdb->get_results( $query );
				if ( $attribute_def->data_type_to_use == 'internal') {
					if ( !empty( $attribute_def->default_value ) ) {
						$attribute_default_value = $attribute_def->default_value;
						$attribute_default_value = unserialize($attribute_default_value);

						
						$query = $wpdb->prepare( 'SELECT * FROM ' .$wpdb->posts. ' WHERE post_type = %s', $attribute_default_value['default_value']);
						$elements = $wpdb->get_results( $query );
						
						if ( !empty( $elements) ) {
							
						}
					}
					
					foreach ( $elements as $element ) {
						if ( in_array($element->post_title, $available_attribute_values) ) {
							$stored_available_attribute_values[$element->menu_order] = array( 'option_id' => $element->ID, 'option_label' => $element->post_title );
						}
					}
				}
				else {
					foreach ( $attributes_options as $attributes_option ) {
						if ( in_array($attributes_option->label, $available_attribute_values) ) {
							$key_value = array_search( $attributes_option->label, $available_attribute_values);
							$stored_available_attribute_values[$attributes_option->position] = array('option_id' => $key_value, 'option_label' => $attributes_option->label );
						}
					}
				}
				ksort( $stored_available_attribute_values);
				if ( !empty($stored_available_attribute_values) && is_array($stored_available_attribute_values) ) {
					foreach( $stored_available_attribute_values as $stored_available_attribute_value ) {
						$sub_tpl_component['FILTER_SEARCH_LIST_VALUE'] .= '<option value="' .$stored_available_attribute_value['option_id']. '">' .$stored_available_attribute_value['option_label']. '</option>';
					}
					if ( $field_type == 'multiple-select' || $field_type == 'checkbox' ) {
						$output = wpshop_display::display_template_element('wpshop_filter_search_element_for_multiselect_data', $sub_tpl_component, array(), 'wpshop');
					}
					else {
						$output = wpshop_display::display_template_element('wpshop_filter_search_element_for_text_data', $sub_tpl_component, array(), 'wpshop');
					}
					unset( $sub_tpl_component );
				}
			}
			return $output;
		}
		
		
		/**
		 * Pick up all filter search element type
		 * @param integer $category_id
		 * @return array
		 */
		function pick_up_filter_search_elements_type ( $category_id ) {
			$filter_search_elements = array();
			if ( !empty($category_id) ) {
				$category_option =  get_option('wpshop_product_category_'.$category_id);
				if ( !empty($category_option) && !empty($category_option['wpshop_category_filterable_attributes']) ) {
					foreach ( $category_option['wpshop_category_filterable_attributes'] as $k => $attribute ) {
						$attribute_def = wpshop_attributes::getElement($k);
						if ( !empty($attribute_def) ) {
							if ( $attribute_def->frontend_input == 'text' ) {
								$filter_search_elements['_'.$attribute_def->code] = array('type' => 'fork_values');
							}
							elseif( in_array( $attribute_def->frontend_input, array('checkbox', 'multiple-select', 'radio', 'select') ) ) {
								$filter_search_elements['_'.$attribute_def->code] = array('type' => 'multiple_select_value');
							}
							elseif ( !in_array($attribute_def->frontend_input, array('hidden', 'textarea', 'password') ) )  {
								$filter_search_elements['_'.$attribute_def->code] = array('type' => 'select_value');
							}
						}
					}
				}
			}
			return $filter_search_elements;
		}
		
		/**
		 * Ajax function which construct, execute and display the filter search request
		 */
		function wpshop_ajax_filter_search_action () {
			global $wpdb;
			$category_id =  !empty($_POST['wpshop_filter_search_category_id']) ? wpshop_tools::varSanitizer($_POST['wpshop_filter_search_category_id']) : 0;
			$filter_search_elements = $this->pick_up_filter_search_elements_type($category_id);
			$page_id = ( !empty( $_POST['wpshop_filter_search_current_page_id']) ) ? wpshop_tools::varSanitizer( $_POST['wpshop_filter_search_current_page_id'] ) : 1;
			$request_cmd = '';
			$status = false;
			$data = array();
			foreach ( $filter_search_elements as $k=>$filter_search_element) {
				$datatype_element = array( 'select_value', 'multiple_select_value', 'fork_values');
				if (  (in_array($filter_search_element['type'], $datatype_element) && (isset($_REQUEST['filter_search'.$k]) && $_REQUEST['filter_search'.$k] == 'all_attribute_values' ) ) || ( ($filter_search_element['type'] == 'select_value' || $filter_search_element['type'] == 'multiple_select_value' ) &&  !isset($_REQUEST['filter_search'.$k]) ) || ( $filter_search_element['type'] == 'fork_values' && ( !isset($_REQUEST['amount_min'.$k]) || !isset($_REQUEST['amount_max'.$k]) ) ) ) {
					unset( $filter_search_elements[$k]);
				}
			}	
			/** SQL request Construct for pick up all product with one of filter search element value **/
			if ( !empty($filter_search_elements) && !empty($_REQUEST) ) {
				$request_cmd = '';
				$first = true;
				$i = 1;
				$filter_search_elements_count = count($filter_search_elements);
				
				/** Get subcategories **/
				$current_category_children = array();
				$args = array(
						'type'		=> WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
						'taxonomy'  => WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES,
						'child_of'  => $category_id
				);
				$current_category_children = get_categories($args);
				/** Construct the array for SELECT query IN **/
				$categories_id = array();
				$categories_id[] = $category_id;
				if ( !empty($current_category_children) ) {
					foreach ( $current_category_children as $current_category_child ) {
						$categories_id[] = $current_category_child->term_taxonomy_id;
					}
				}
				
				/** Make the array **/
				$array_for_query = implode(',', $categories_id);

				foreach ( $filter_search_elements as $k=>$filter_search_element ) {
					if ( !empty($filter_search_element['type']) && !empty($_REQUEST['filter_search'.$k]) && $filter_search_element['type'] == 'select_value' && $_REQUEST['filter_search'.$k] != 'all_attribute_values') {
						$request_cmd .= 'SELECT meta_key, post_id FROM ' .$wpdb->postmeta. ' INNER JOIN ' .$wpdb->posts. ' ON  post_id = ID WHERE (meta_key = "'.$k.'" AND meta_value = "'.wpshop_tools::varSanitizer($_REQUEST['filter_search'.$k]).'") AND post_type = "'.WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT.'" ';
						$request_cmd .= ' AND post_id IN (SELECT object_id FROM '.$wpdb->term_relationships.' WHERE term_taxonomy_id IN ('.$array_for_query.') ) ';
					}
					else if($filter_search_element['type'] == 'fork_values') {
						$request_cmd .= 'SELECT meta_key, post_id FROM ' .$wpdb->postmeta. ' INNER JOIN ' .$wpdb->posts. ' ON  post_id = ID WHERE (meta_key = "'.( ( !empty($k) && $k == '_product_price' ) ? '_wpshop_displayed_price' : $k).'" AND meta_value BETWEEN '.wpshop_tools::varSanitizer($_REQUEST['amount_min'.$k]).' AND '.wpshop_tools::varSanitizer($_REQUEST['amount_max'.$k]).') AND post_type = "'.WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT.'"';
						$request_cmd .= ' AND post_id IN (SELECT object_id FROM '.$wpdb->term_relationships.' WHERE term_taxonomy_id IN ('.$array_for_query.') ) ';
					}
					else if( $filter_search_element['type'] == 'multiple_select_value' ) {
						/** Check the attribute id **/
						$attribute_def = wpshop_attributes::getElement(substr($k, 1), "'valid'", 'code');
						if ( !empty($attribute_def) ) {
							$request_cmd .= 'SELECT CONCAT("_", code) AS meta_key, ATT_INT.entity_id AS post_id FROM ' .WPSHOP_DBT_ATTRIBUTE. ', '.WPSHOP_DBT_ATTRIBUTE_VALUES_INTEGER.' AS ATT_INT WHERE attribute_id = id AND attribute_id = '.$attribute_def->id;
							$first = true;
							if ( !empty($_REQUEST['filter_search'.$k]) && is_array($_REQUEST['filter_search'.$k]) ){
								foreach ( $_REQUEST['filter_search'.$k] as $r ) {
									if ( $first) {
										$request_cmd .= ' AND (value ="' . wpshop_tools::varSanitizer($r). '"';
										$first = false;
									}
									else {
										$request_cmd .= ' OR value ="' . wpshop_tools::varSanitizer($r). '"';
									}
								}
								$request_cmd .= ')';
							}
							elseif(  !empty($_REQUEST['filter_search'.$k]) )  {
								$request_cmd .= ' AND (value ="' . wpshop_tools::varSanitizer($_REQUEST['filter_search'.$k]). '" )';
							}
							$request_cmd .= ' AND ATT_INT.entity_id IN (SELECT object_id FROM '.$wpdb->term_relationships.' WHERE term_taxonomy_id IN ('.$array_for_query.') ) ';
							
								
						}
					}
					
					
					if ($i < count($filter_search_elements) ) {
						$request_cmd .= ' UNION ';
					}
					$i++;
				}
				/** SQL Request execution **/
				$query = $wpdb->prepare($request_cmd, ''); 
				$results = $wpdb->get_results($query);
				
				$first = true;
				$final_result = array();
				
				$temp_result = array();
				$first_key = null;
				
				$last = '';
				/** Transform the query result array **/
				foreach ( $results as $result ) {
					$result->meta_key = ( !empty($result->meta_key) && $result->meta_key == '_wpshop_displayed_price' ) ? '_product_price' : $result->meta_key;
					if ( $last != $result->meta_key ){
						$filter_search_elements[$result->meta_key]['count'] = 1;
						$last = $result->meta_key;
					}
					else
						$filter_search_elements[$result->meta_key]['count']++;
					
					$filter_search_elements[$result->meta_key]['values'][$result->post_id] = $result->post_id;
				}
				
				
				/** Check the smaller array of attributes **/
				$smaller_array = '';
				$smaller_array_count = -1;
				foreach ( $filter_search_elements as $k=>$filter_search_element ) {
					if ( empty($filter_search_element['count']) ) {
						$smaller_array_count = 0;
						$smaller_array = $k;
					}
					elseif( $smaller_array_count == -1 || $filter_search_element['count'] <= $smaller_array_count ) {
						$smaller_array_count = $filter_search_element['count'];
						$smaller_array = $k;
					}
					
				}
				
				/** Compare the smaller array with the others **/
				if ( !empty($smaller_array_count) ) {
					$temp_tab = $filter_search_elements[$smaller_array]['values'];
					foreach ( $filter_search_elements as $filter_search) { 
						foreach ( $temp_tab as $value ) {
							if ( !in_array($value, $filter_search['values']) ) {
								/** If value don't exist in the smaller array, delete it **/
								$key = array_key_exists($value, $temp_tab);
								if ( $key ) {
									unset($temp_tab[$value]);
								}
							}
						}
					}
					/** Final result to display the products **/
					if ( !empty( $temp_tab) ) {
						$final_result = $temp_tab;
					}
				}
				else {
					$final_result = array();
				}
				
				$products_count = count($final_result);
				$products_count = sprintf(__('%s products corresponds to your search.', 'wpshop'),$products_count) ;
				
				/** If there is products for this filter search **/
				$status = true;
				if ( !empty($final_result) ) {
					$data['status'] = true;
					$data['result']  = do_shortcode( '[wpshop_products pid="' . implode(',', $final_result) . '" container="no" ]' ) ;
					$data['products_count'] = $products_count;
				}
				else {
					$data['status'] = false;
					$data['result'] = '<div class="container_product_listing">'.__('There is no product for this filter search.', 'wpshop').'</div>';
					$data['products_count'] = __('No products corresponds to your search', 'wpshop');
				}
				
			}
			echo json_encode( $data );
			die();
		}
		
		/**
		 * Return the result of filter search
		 * @param array $product_id_list
		 * @return string
		 */
		function display_ajax_filter_search_action ( $product_id_list, $filter_search_element_recap ) {
			$result_product_display = $products_list = '';
			$display_options = get_option('wpshop_display_option');
			$display_type = ( !empty($display_options) && !empty($display_options['wpshop_display_list_type']) ) ? $display_options['wpshop_display_list_type'] : 'grid';
			$element_per_line = (!empty($display_options) && !empty($display_options['wpshop_display_grid_element_number'])) ? $display_options['wpshop_display_grid_element_number'] : 3;
			$elements_per_page = ( !empty( $display_options) && !empty( $display_options['wpshop_display_element_per_page']) ) ? $display_options['wpshop_display_element_per_page'] : 20;
			
			
			if ( !empty($product_id_list) ) {
				$tpl_component = array();
				$current_element_position = 1;
				foreach ( $product_id_list as $product ) {
			
					$cats = get_the_terms($product, WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES);
					$cats = !empty($cats) ? array_values($cats) : array();
					$cat_id = empty($cats) ? 0 : $cats[0]->term_id;
					$products_list .= wpshop_products::product_mini_output( $product, $cat_id, $display_type, $current_element_position, $element_per_line);
					$current_element_position  ++;
				}
				$tpl_component = array();
				$tpl_component['PRODUCT_CONTAINER_TYPE_CLASS'] = ($display_type == 'grid' ? ' ' . $display_type . '_' . $element_per_line : '') . ' '. $display_type .'_mode';
				$tpl_component['PRODUCT_LIST'] = $products_list;
				$tpl_component['CROSSED_OUT_PRICE'] = '';
				$tpl_component['LOW_STOCK_ALERT_MESSAGE'] = '';
				$result_product_display = count($product_id_list).'<br/>'.wpshop_display::display_template_element('product_list_container', $tpl_component);
				unset( $tpl_component);
					
				$page_number = 1;
				$total_max_pages = round( (count($product_id_list) / (int)$elements_per_page) , 0, PHP_ROUND_HALF_UP);
					
				/** Pagination managment **/
				$paginate = paginate_links(array(
						'base' => '#',
						'current' => $page_number,
						'total' => $total_max_pages,
						'type' => 'array',
						'prev_next' => false
				));
					
				if(!empty($paginate)) {
					$result_product_display .= '<ul id="pagination_filter_search" >';
					foreach($paginate as $p) {
						$result_product_display .= '<li>'.$p.'</li>';
					}
					$result_product_display .= '</ul>';
				}
					
				
					
			}
			else {
				$result_product_display = __('Sorry ! No product correspond to your filter search request', 'wpshop');
			}
			return $result_product_display;
		}
		
		/** 
		 * Save the price which is displayed on website
		 */
		function save_displayed_price_meta() {
			if ( !empty($_POST) && !empty($_POST['ID']) && !empty($_POST['post_type']) && $_POST['post_type'] == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) {
				$price_piloting = get_option('wpshop_shop_price_piloting');
				$product_data = wpshop_products::get_product_data($_POST['ID']);
				$price_infos = wpshop_prices::check_product_price($product_data);

				if ( !empty($price_infos) ) {
					if ( !empty($price_infos['discount']) &&  !empty($price_infos['discount']['discount_exist']) ) {
						$displayed_price = ( !empty($price_piloting) && $price_piloting == 'HT') ? $price_infos['discount']['discount_et_price'] : $price_infos['discount']['discount_ati_price'];
					}
					else if( !empty($price_infos['fork_price']) && !empty($price_infos['fork_price']['have_fork_price']) ) {
						$displayed_price = $price_infos['fork_price']['min_product_price'];
					}
					else {
						$displayed_price = ( !empty($price_piloting) && $price_piloting == 'HT') ? $price_infos['et'] : $price_infos['ati'];
					}
				}
				update_post_meta($_POST['ID'], '_wpshop_displayed_price', number_format($displayed_price,2, '.','') );
			}
		}
		
		
		
		/**
		 * Save values for attributes
		 * @param unknown_type $values
		 */
		function stock_values_for_attribute( $categories_id = array() ) {
			@set_time_limit( 900 );
			if (  !empty($_POST['tax_input']) && !empty($_POST['tax_input']['wpshop_product_category']) && !empty($_POST['post_type']) && $_POST['post_type'] == WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT ) {
				$categories_id = $_POST['tax_input']['wpshop_product_category'];
			}

			if ( !empty( $categories_id )  ) {
				if ( $categories_id && is_array($categories_id) ) {
					foreach( $categories_id as $taxonomy_id ) {
						if ( $taxonomy_id != 0 ) {
						$current_category_children = array();
						$args = array(
								'type'		=> WPSHOP_NEWTYPE_IDENTIFIER_PRODUCT,
								'taxonomy'  => WPSHOP_NEWTYPE_IDENTIFIER_CATEGORIES,
								'child_of'  => $taxonomy_id
						);
						$current_category_children = get_categories($args);

							$category_option = get_option('wpshop_product_category_'.$taxonomy_id);
							if ( !empty($category_option) && !empty($category_option['wpshop_category_filterable_attributes']) && is_array($category_option['wpshop_category_filterable_attributes']) ) {
								foreach ( $category_option['wpshop_category_filterable_attributes'] as $k => $filterable_attribute ) {
									$attribute_def = wpshop_attributes::getElement($k);
									if ( !empty( $attribute_def) ) {
										switch ( $attribute_def->frontend_input ) {
											case 'text' :
												if ( $attribute_def->data_type == 'decimal' || $attribute_def->data_type == 'integer') {
													$this->save_values_for_integer_filterable_attribute( $taxonomy_id, $attribute_def, $current_category_children );
												}
 												else {
 													$this->save_values_for_text_filterable_attribute( $taxonomy_id, $attribute_def, $current_category_children );
 												}
											break;
													
											case 'select' : case 'checkbox' : case 'radio' : case 'multiple-select' :
												$this->save_values_for_list_filterable_attribute( $taxonomy_id, $attribute_def, $current_category_children );
											break;
													
										}
									}
								}
							}
						}
					}	
				}
			}
		}
		
		
		/**
		 * Save Products attribute values for integer attribute data for a products category
		 * @param integer $category_id
		 * @param std_object $attribute_def
		 * @param array $current_category_child
		 */	
		function save_values_for_integer_filterable_attribute ( $category_id, $attribute_def, $current_category_child ) {
			$first = true;
			$category_option = get_option('wpshop_product_category_'.$category_id);
			$category_product_ids = wpshop_categories::get_product_of_category( $category_id );
			$min_value = $max_value = 0;
			/** If there are sub-categories take all products of sub-categories **/
			if ( !empty($current_category_children) ) {
				foreach ( $current_category_children as $current_category_child ) {
					$sub_categories_product_ids = wpshop_categories::get_product_of_category( $current_category_child->term_taxonomy_id );
					if ( !empty($sub_categories_product_ids) ) {
						foreach ( $sub_categories_product_ids as $sub_categories_product_id ) {
							if ( !in_array($sub_categories_product_id, $category_product_ids) ) {
								$category_product_ids[] = $sub_categories_product_id;
							}
						}
					}
				}
			}
			
			/** For each product of category check the value **/
			if ( !empty( $category_product_ids ) ) {
				$price_piloting_option = get_option('wpshop_shop_price_piloting');
				foreach ($category_product_ids as $category_product_id) {
			
					if ( $attribute_def->code == WPSHOP_PRODUCT_PRICE_TTC || $attribute_def->code == WPSHOP_PRODUCT_PRICE_HT ) {
							
						$product_infos = wpshop_products::get_product_data($category_product_id);
						$product_price_infos = wpshop_prices::check_product_price($product_infos);
						if (!empty($product_price_infos) && !empty($product_price_infos['fork_price']) && !empty($product_price_infos['fork_price']['have_fork_price']) && $product_price_infos['fork_price']['have_fork_price'] ) {
								
								
							$max_value = ( !empty($product_price_infos['fork_price']['max_product_price']) && $product_price_infos['fork_price']['max_product_price'] > $max_value ) ? $product_price_infos['fork_price']['max_product_price'] : $max_value;
							$min_value = (!empty($product_price_infos['fork_price']['min_product_price']) && ( ( $product_price_infos['fork_price']['min_product_price'] < $min_value) || $first ) ) ?  $product_price_infos['fork_price']['min_product_price'] : $min_value;
						}
						else {
							if (!empty($product_price_infos) && !empty($product_price_infos['discount']) && !empty($product_price_infos['discount']['discount_exist'] ) && $product_price_infos['discount']['discount_exist'] ) {
								$product_data = (!empty($price_piloting_option) &&  $price_piloting_option == 'HT')  ? $product_price_infos['discount']['discount_et_price'] : $product_price_infos['discount']['discount_ati_price'];
			
							}
							else {
									
								$product_data = (!empty($price_piloting_option) &&  $price_piloting_option == 'HT')  ? $product_price_infos['et'] : $product_price_infos['ati'];
							}
							$max_value = ( !empty($product_data) && $product_data > $max_value ) ? $product_data : $max_value;
							$min_value = (!empty($product_data) && ( ( $product_data < $min_value) || $first )  ) ?  $product_data : $min_value;
						}
					}
					else {
						$product_postmeta = get_post_meta($category_product_id, WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, true);
						$product_data = $product_postmeta[$attribute_def->code];
						$max_value = ( !empty($product_data) && $product_data > $max_value ) ? $product_data : $max_value;
						$min_value = (!empty($product_data) && ( ( $product_data < $min_value) || $first ) ) ?  $product_data : $min_value;
					}
					$first = false;
				}
				$category_option['wpshop_category_filterable_attributes'][$attribute_def->id] = array('min' => $min_value, 'max' => $max_value);
			}
			/** Update the category option **/
			update_option('wpshop_product_category_'.$category_id, $category_option);
		}
		
		
		/**
		 * Save Products attribute values for Text attribute data for a products category
		 * @param integer $category_id
		 * @param std_object $attribute_def
		 * @param array $current_category_child
		 */	
		function save_values_for_text_filterable_attribute ( $category_id, $attribute_def, $current_category_child ) {
			$category_option = get_option('wpshop_product_category_'.$category_id);
			$category_product_ids = wpshop_categories::get_product_of_category( $category_id );
			/** If there are sub-categories take all products of sub-categories **/
			$list_values = array();
			if ( !empty($current_category_children) ) {
				foreach ( $current_category_children as $current_category_child ) {
					$sub_categories_product_ids = wpshop_categories::get_product_of_category( $current_category_child->term_taxonomy_id );
					if ( !empty($sub_categories_product_ids) ) {
						foreach ( $sub_categories_product_ids as $sub_categories_product_id ) {
							if ( !in_array($sub_categories_product_id, $category_product_ids) ) {
								$category_product_ids[] = $sub_categories_product_id;
							}
						}
					}
				}
			}
			if ( !empty($category_option) && !empty($category_option['wpshop_category_filterable_attributes']) && !empty($category_option['wpshop_category_filterable_attributes'][$attribute_def->id]) ) {	
				if ( !empty( $category_product_ids ) ) {
					$product_data = '';
					foreach ( $category_product_ids as $category_product_id ) {
						$product_postmeta = get_post_meta($category_product_id, WPSHOP_PRODUCT_ATTRIBUTE_META_KEY, true);
						$product_data = ( !empty($product_postmeta[$attribute_def->code]) ) ? $product_postmeta[$attribute_def->code] : '';
						if ( !in_array( $product_data,  $list_values) ) {
							$list_values[] = $product_data;
							if ( !empty($product_data) ) {
								$category_option['wpshop_category_filterable_attributes'][$attribute_def->id][] = $product_data;
							}
						}
					}
				}
			}
			update_option('wpshop_product_category_'.$category_id, $category_option);
		}
		
		/**
		 * Save Products attribute values for List attribute data for a products category
		 * @param integer $category_id
		 * @param std_object $attribute_def
		 * @param array $current_category_child
		 */
		function save_values_for_list_filterable_attribute( $category_id, $attribute_def, $current_category_children ) {
			global $wpdb;
			$category_option = get_option('wpshop_product_category_'.$category_id);
			$products = wpshop_categories::get_product_of_category( $category_id );
			/** If there are sub-categories take all products of sub-categories **/
			if ( !empty($current_category_children) ) {
				foreach ( $current_category_children as $current_category_child ) {
					$sub_categories_product_ids = wpshop_categories::get_product_of_category( $current_category_child->term_taxonomy_id );
					if ( !empty($sub_categories_product_ids) ) {
						foreach ( $sub_categories_product_ids as $sub_categories_product_id ) {
							if ( !in_array($sub_categories_product_id, $products) ) {
								$products[] = $sub_categories_product_id;
							}
						}
					}
				}
			}
			
			
			if ( !empty($category_option) && !empty($category_option['wpshop_category_filterable_attributes']) && !empty($category_option['wpshop_category_filterable_attributes'][$attribute_def->id]) ) {
				$category_option['wpshop_category_filterable_attributes'][$attribute_def->id] = array();
			}
			
			
			if ( !empty( $attribute_def) ){
				$available_attribute_values = array();
				$test = array();
				foreach ( $products as $product ) {
					$available_attribute_values = array_merge( $available_attribute_values, wpshop_attributes::get_affected_value_for_list( $attribute_def->code, $product, $attribute_def->data_type_to_use) ) ;
				}

				$available_attribute_values = array_flip($available_attribute_values);
				$data_to_save = array();
				if ( !empty($available_attribute_values) ) {
					$data_to_save = array();
					foreach( $available_attribute_values as $k => $available_attribute_value ) {
						if (  $attribute_def->data_type_to_use == 'internal' ) {
							$attribute_name = get_the_title( $k );
						}
						else {
							$query = $wpdb->prepare( 'SELECT label FROM ' .WPSHOP_DBT_ATTRIBUTE_VALUES_OPTIONS. ' WHERE attribute_id = %d AND id = %d', $attribute_def->id, $k);
							
							$attribute_name = $wpdb->get_var( $query );
						}
						if (!empty($attribute_name) && !empty($k) ) {
							if ( !empty($category_option) && !empty($category_option['wpshop_category_filterable_attributes']) && isset($category_option['wpshop_category_filterable_attributes'][$attribute_def->id]) ) {			
								$data_to_save[$k] = $attribute_name;
								$category_option['wpshop_category_filterable_attributes'][$attribute_def->id] = $data_to_save;
							}
							//$sub_tpl_component['FILTER_SEARCH_LIST_VALUE'] .= '<option value="' .$k. '">' .$attribute_name. '</option>';
						}
					}
				}
			}
			update_option('wpshop_product_category_'.$category_id, $category_option);
			//exit;
		}
		
		
		
		
		
	}
}
if ( class_exists("wpshop_filter_search") ) {
	$wpshop_filter_search = new wpshop_filter_search();
}