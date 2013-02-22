<?php

/*	Check if file is include. No direct access possible with file url	*/
if ( !defined( 'WPSHOP_VERSION' ) ) {
	die( __('Access is not allowed by this way', 'wpshop') );
}

/**
 * Wpshop Payment Gateway
 *
 * @class 		wpshop_payment
 * @package		WP-Shop
 * @category	Payment Gateway
 * @author		Eoxia
 */
class wpshop_payment {

	public function __construct() {
		global $wpshop;

		$wpshop_paypal = new wpshop_paypal();
		// If the CIC payment method is active
		$wpshop_paymentMethod = get_option('wpshop_paymentMethod');
		if(WPSHOP_PAYMENT_METHOD_CIC || !empty($wpshop_paymentMethod['cic'])) {
			$wpshop_cic = new wpshop_CIC();
		}
		wpshop_tools::create_custom_hook ('wpshop_bankserver_reponse');

	}

	function get_success_payment_url() {
		$default_url = get_permalink(get_option('wpshop_payment_return_page_id'));
		$url = get_option('wpshop_payment_return_url',$default_url);
		return self::construct_url_parameters($url, 'paymentResult', 'success');
	}

	function get_cancel_payment_url() {
		$default_url = get_permalink(get_option('wpshop_payment_return_page_id'));
		$url = get_option('wpshop_payment_return_url',$default_url);
		return self::construct_url_parameters($url, 'paymentResult', 'cancel');
	}

	function construct_url_parameters($url, $param, $value) {
		$interoguation_marker_pos = strpos($url, '?');
		if($interoguation_marker_pos===false)
			return $url.'?'.$param.'='.$value;
		else return $url.'&'.$param.'='.$value;
	}

	/**
	 * Shortcode : Manage payment result
	 */
	function wpshop_payment_result() {
		global $wpdb;
		$user_ID = get_current_user_id();
		$query = $wpdb->prepare('SELECT MAX(ID) FROM ' .$wpdb->posts. ' WHERE post_type = %s AND post_author = %d', WPSHOP_NEWTYPE_IDENTIFIER_ORDER, $user_ID);
		$order_post_id = $wpdb->get_var( $query );
		if ( !empty($order_post_id) ) {
			$order_postmeta = get_post_meta($order_post_id , '_wpshop_order_status', true);
			if ( !empty($order_postmeta) ) {
				switch ( $order_postmeta ) {
					case 'awaiting_payment':
						echo __('We wait your payment.','wpshop');
					break;
					case 'completed':
						echo __('Thank you ! Your payment has been recorded.','wpshop');
					break;
					case 'partially_paid':
						echo __('Thank you ! Your first payment has been recorded.','wpshop');
					break;
					default:
						echo __('Your payment and your order has been cancelled.','wpshop');
					break;
				}
			}
		}
// 		if(!empty($_GET['paymentResult'])) {
// 			if($_GET['paymentResult']=='success') {
// 				echo __('Thank you ! Your payment has been recorded.','wpshop');
// 			}
// 			elseif($_GET['paymentResult']=='cancel') {
// 				echo __('Your payment and your order has been cancelled.','wpshop');
// 			}
// 		}
	}

	/**
	 * Display the list of payment methods available
	 *
	 * @param integer $order_id The order id if existing - Useful when user does not finish its order and want to validateit later
	 * @return string The different payment method
	 */
	function display_payment_methods_choice_form($order_id=0, $cart_type = 'cart') {
		$output = '';
		/**	Get available payment method	*/
		$paymentMethod = get_option('wpshop_paymentMethod', array());

		if(!empty($order_id) && is_numeric($order_id)) {
			$output .= '<input type="hidden" name="order_id" value="'.$order_id.'" />';
		}

		if ($cart_type == 'cart') {
			if(!empty($paymentMethod['paypal'])) {
				$tpl_component = array();
				$tpl_component['CHECKOUT_PAYMENT_METHOD_STATE_CLASS'] = ' active';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_INPUT_STATE'] = ' checked="checked"';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_IDENTIFIER'] = 'paypal';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_ICON'] = WPSHOP_TEMPLATES_URL . 'wpshop/medias/paypal.png';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_NAME'] = __('Paypal', 'wpshop');
				$tpl_component['CHECKOUT_PAYMENT_METHOD_EXPLANATION'] = __('<strong>Tips</strong> : If you have a Paypal account, by choosing this payment method, you will be redirected to the secure payment site Paypal to make your payment. Debit your PayPal account, immediate booking products.','wpshop');
				$output .= wpshop_display::display_template_element('wpshop_checkout_page_payment_method_bloc', $tpl_component, array('type' => 'payment_method', 'id' => 'paypal'));
				unset($tpl_component);
			}

			if(!empty($paymentMethod['checks'])) {
				$current_payment_method_state = (!empty($paymentMethod['paypal']) && $paymentMethod['paypal']) ? false : true;
				$tpl_component = array();
				$tpl_component['CHECKOUT_PAYMENT_METHOD_STATE_CLASS'] = !$current_payment_method_state ? '' : ' active';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_INPUT_STATE'] = !$current_payment_method_state ? '' : ' checked="checked"';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_IDENTIFIER'] = 'check';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_ICON'] = WPSHOP_TEMPLATES_URL . 'wpshop/medias/cheque.png';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_NAME'] = __('Check', 'wpshop');
				$tpl_component['CHECKOUT_PAYMENT_METHOD_EXPLANATION'] = __('Reservation of products upon receipt of the check.','wpshop');
				$output .= wpshop_display::display_template_element('wpshop_checkout_page_payment_method_bloc', $tpl_component, array('type' => 'payment_method', 'id' => 'check'));
				unset($tpl_component);
			}

			if(!empty($paymentMethod['banktransfer'])) {
				$current_payment_method_state = (!empty($paymentMethod['paypal']) && $paymentMethod['paypal']) ? false : true;
				$tpl_component = array();
				$tpl_component['CHECKOUT_PAYMENT_METHOD_STATE_CLASS'] = !$current_payment_method_state ? '' : ' active';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_INPUT_STATE'] = !$current_payment_method_state ? '' : ' checked="checked"';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_IDENTIFIER'] = 'banktransfer';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_ICON'] = WPSHOP_TEMPLATES_URL . 'wpshop/medias/cheque.png';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_NAME'] = __('Bank transfer', 'wpshop');
				$tpl_component['CHECKOUT_PAYMENT_METHOD_EXPLANATION'] = __('Reservation of product receipt of payment.','wpshop');
				$output .= wpshop_display::display_template_element('wpshop_checkout_page_payment_method_bloc', $tpl_component, array('type' => 'payment_method', 'id' => 'banktransfer'));
				unset($tpl_component);
			}

			$wpshop_paymentMethod = get_option('wpshop_paymentMethod');
			if(WPSHOP_PAYMENT_METHOD_CIC || !empty($wpshop_paymentMethod['cic'])) {
				$current_payment_method_state = false;
				$tpl_component = array();
				$tpl_component['CHECKOUT_PAYMENT_METHOD_STATE_CLASS'] = !$current_payment_method_state ? '' : ' active';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_INPUT_STATE'] = !$current_payment_method_state ? '' : ' checked="checked"';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_IDENTIFIER'] = 'cic';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_ICON'] = WPSHOP_TEMPLATES_URL . 'wpshop/medias/cic_payment_logo.png';
				$tpl_component['CHECKOUT_PAYMENT_METHOD_NAME'] = __('Credit card', 'wpshop');
				$tpl_component['CHECKOUT_PAYMENT_METHOD_EXPLANATION'] = __('Reservation of products upon confirmation of payment.','wpshop');
				$output .= wpshop_display::display_template_element('wpshop_checkout_page_payment_method_bloc', $tpl_component, array('type' => 'payment_method', 'id' => 'cic'));
				unset($tpl_component);
			}
		}

		return array( $output, $paymentMethod );
	}

	/**
	* Reduce the stock regarding the order
	*/
	function the_order_payment_is_completed($order_id, $txn_id = null) {
		// Donn�es commande
		$order = get_post_meta($order_id, '_order_postmeta', true);
		$order_info = get_post_meta($order_id, '_order_info', true);

		if(!empty($order) && !empty($order_info) && empty($order['order_invoice_ref'])) {
			$email = (!empty($order_info['billing']['address']['address_user_email']) ? $order_info['billing']['address']['address_user_email'] : '' );
			$first_name = ( !empty($order_info['billing']['address']['address_first_name']) ? $order_info['billing']['address']['address_first_name'] : '' );
			$last_name = ( !empty($order_info['billing']['address']['address_last_name']) ? $order_info['billing']['address']['address_last_name'] : '' );

			// Envoie du message de confirmation de paiement au client
			switch($order['payment_method']) {
				case 'check':
					wpshop_messages::wpshop_prepared_email($email, 'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE', array('order_key' => $order['order_key'], 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_date' => $order['order_date']));
				break;

				case 'paypal':
					wpshop_messages::wpshop_prepared_email($email, 'WPSHOP_PAYPAL_PAYMENT_CONFIRMATION_MESSAGE', array('paypal_order_key' => $txn_id, 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_date' => $order['order_date']));
				break;

				default:
					wpshop_messages::wpshop_prepared_email($email, 'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE', array('order_key' => $order['order_key'], 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_date' => $order['order_date']));
				break;
			}
		}
	}


	function setOrderPaymentStatus( $order_id, $payment_status ) {
		/**	Get order main information	*/
		$order = get_post_meta($order_id, '_order_postmeta', true);
		$send_email = false;

		if ( !empty($order) ) {
			/**	Change order status to given status	*/
			$order['order_status'] = strtolower($payment_status);
			/**	Put order status into a single meta, allowing to use it easily later	*/
			update_post_meta($order_id, '_wpshop_order_status', $order['order_status']);

			/**	In case the set status is completed, make specific treatment: add the completed date	*/
			if ( $payment_status == 'completed' ) {
				/**	Read order items list, if not empty and check if each item is set to manage stock or not */
				if (!empty($order['order_items'])) {
					foreach ($order['order_items'] as $o) {
						$product = wpshop_products::get_product_data( $o['item_id'] );
						if (!empty($product) && !empty($product['manage_stock']) && $product['manage_stock']=='yes') {
							wpshop_products::reduce_product_stock_qty($o['item_id'], $o['item_qty']);
						}
					}
				}

				/** Add information about the order completed date */
				update_post_meta($order_id, '_' . WPSHOP_NEWTYPE_IDENTIFIER_ORDER . '_completed_date', current_time('mysql', 0));

				/**	Set a variable to know when send an email to the customer	*/
				$send_email = true;
			}

			/**	Send email to customer when specific case need it	*/
			if ( $send_email ) {
				/**	Get information about customer that make the order	*/
				$order_info = get_post_meta($order_id, '_order_info', true);
				$mail_tpl_component = array('order_key' => $order['order_key'], 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_date' => $order['order_date']);
			}

			/**	Update order with new informations	*/
			update_post_meta($order_id, '_order_postmeta', $order);
		}
	}

	/**
	* Get payment method
	*/
	function get_payment_method($post_id){

		$order_postmeta = get_post_meta($post_id, '_order_postmeta', true);
		switch($order_postmeta['payment_method']){
			case 'check':
				$pm = __('Check','wpshop');
			break;
			case 'paypal':
				$pm = __('Paypal','wpshop');
			break;
			case 'banktransfer':
				$pm = __('Bank transfer','wpshop');
			break;
			case 'cic':
				$pm = __('Credit card','wpshop');
			break;
			default:
				$pm = __('Nc','wpshop');
			break;
		}

		return $pm;
	}

	/**
	* Set payment transaction number
	*/
	function display_payment_receiver_interface($post_id) {
		$payment_validation = '';
		$display_button = false;

		$order_postmeta = get_post_meta($post_id, '_order_postmeta', true);

		$transaction_indentifier = self::get_payment_transaction_number($post_id);

		$paymentMethod = get_option('wpshop_paymentMethod', array());
		$payment_validation .= '
<div id="order_payment_method_'.$post_id.'" class="wpshop_cls wpshopHide" >
	<input type="hidden" id="used_method_payment_'.$post_id.'" value="' . (!empty($order_postmeta['payment_method']) ? $order_postmeta['payment_method'] : 'no_method') . '"/>
	<input type="hidden" id="used_method_payment_transaction_id_'.$post_id.'" value="' . (!empty($transaction_indentifier) ? $transaction_indentifier : 0) . '"/>';

		if(!empty($order_postmeta['payment_method'])){
			$payment_validation .= sprintf(__('Selected payment method: %s', 'wpshop'), __($order_postmeta['payment_method'], 'wpshop')) . '<br/>';
		}

		if(!empty($paymentMethod['paypal']) && empty($order_postmeta['payment_method'])) {
			$payment_validation .= '<input type="radio" class="payment_method" name="payment_method" value="paypal" id="payment_method_paypal" /><label for="payment_method_paypal" >' . __('Paypal', 'wpshop') . '</label><br/>';
			$display_button = true;
		}

		if(!empty($paymentMethod['checks']) && empty($order_postmeta['payment_method'])) {
			$payment_validation .= '<input type="radio" class="payment_method" name="payment_method" value="check" id="payment_method_check" /><label for="payment_method_check" >' . __('Check', 'wpshop') . '</label><br/>';
			$display_button = true;
		}

		$wpshop_paymentMethod = get_option('wpshop_paymentMethod');
		if((WPSHOP_PAYMENT_METHOD_CIC || !empty($wpshop_paymentMethod['cic'])) && empty($order_postmeta['payment_method'])) {
			$payment_validation .= '<input type="radio" class="payment_method" name="payment_method" value="cb" id="payment_method_cb" /><label for="payment_method_cb" >' . __('Credit card', 'wpshop') . '</label><br/>';
			$display_button = true;
		}

		if(empty($payment_transaction)){
			$payment_validation .= '<hr/>' . __('Transaction number', 'wpshop') . '&nbsp;:&nbsp;<input type="text" value="" name="payment_method_transaction_number" id="payment_method_transaction_number_'.$post_id.'" />';
			$display_button = true;
		}

		if($display_button){
			$payment_validation .= '
		<br/><br/><a class="button payment_method_validate order_'.$post_id.' wpshop_clear" >'.__('Validate payment method', 'wpshop').'</a>';
		}

		$payment_validation .= '
</div>';

		return $payment_validation;
	}

	/**
	 * Allows to inform customer that he would pay a partial amount on this order
	 *
	 * @param float $current_order_total The current order total to pay before partial amount calcul
	 * @return array The amount to pay / A html output with amount to pay and different information
	 */
	function partial_payment_calcul( $current_order_total ) {
		$output = '';
		$tpl_component = array();

		/**	Get current configuration	*/
		$partial_payment_configuration = get_option('wpshop_payment_partial', array('for_all' => array()));
		if ( !empty($partial_payment_configuration['for_all']) && (!empty($partial_payment_configuration['for_all']['activate'])) && ($partial_payment_configuration['for_all']['activate'] == 'on') ) {
			$amount_of_partial_payment = 0;
			if ( !empty($partial_payment_configuration['for_all']['value']) ) {
				$amount_of_partial_payment = $partial_payment_configuration['for_all']['value'];
			}

			$partial_amount_to_pay = 0;
			$type_of_partial_payment = null;
			if (!empty($partial_payment_configuration['for_all']) && !empty($partial_payment_configuration['for_all']['type']) ) {
				switch ($partial_payment_configuration['for_all']['type']) {
					case 'percentage':
						$type_of_partial_payment = '%';
						$partial_amount_to_pay = (($current_order_total * $amount_of_partial_payment) / 100);
					break;
					case 'amount':
						$type_of_partial_payment = wpshop_tools::wpshop_get_currency();
						$partial_amount_to_pay = ($current_order_total - $amount_of_partial_payment);
					break;
					default:
						$type_of_partial_payment = wpshop_tools::wpshop_get_currency();
						$partial_amount_to_pay = ($current_order_total - $amount_of_partial_payment);
					break;
				}
			}
			$output['amount_of_partial_payment'] = $amount_of_partial_payment;
			$output['type_of_partial_payment'] = $type_of_partial_payment;
			$output['amount_to_pay'] = $partial_amount_to_pay;

			$tpl_component['CURRENT_ORDER_TOTAL_AMOUNT'] = $current_order_total;
			$tpl_component['PARTIAL_PAYMENT_CONFIG_AMOUNT'] = !empty($amount_of_partial_payment) ? $amount_of_partial_payment : '';
			$tpl_component['PARTIAL_PAYMENT_CONFIG_TYPE'] = !empty($type_of_partial_payment) ? $type_of_partial_payment : '';
			$tpl_component['PARTIAL_PAYMENT_AMOUNT'] = $partial_amount_to_pay;

			$output['display'] = wpshop_display::display_template_element('wpshop_partial_payment_display', $tpl_component);
			unset($tpl_component);
		}

		return $output;
	}

	/**
	 * Return the new transaction reference for an order payment
	 * @since 1.3.3.7
	 *
	 * @param integer $order_id The order identifer we want to get payment reference for
	 *
	 * @return mixed The payment reference for current order
	 */
	function get_payment_transaction_number($order_id, $payment_index = 0) {
		$order_postmeta = get_post_meta($order_id, '_order_postmeta', true);
		$transaction_indentifier = '';

		if (!empty($order_meta['order_payment']['received']) && !empty($order_meta['order_payment']['received'][$payment_index]) && !empty($order_meta['order_payment']['received'][$payment_index]['payment_reference'])) {
			$transaction_indentifier = $order_meta['order_payment']['received'][$payment_index]['payment_reference'];
		}

		return $transaction_indentifier;
	}

	/**
	 * Set the transaction identifier for a given order
	 *
	 * @param integer $order_id
	 * @param mixed $transaction_number The identifier of transaction. Used for all the payment method
	 */
	function set_payment_transaction_number($order_id, $transaction_number) {
		$order_postmeta = get_post_meta($order_id, '_order_postmeta', true);

		if ( !empty($order_postmeta['order_payment']['received']) ) {
			if (count($order_postmeta['order_payment']['received']) == 1) {
				$order_postmeta['order_payment']['received'][0]['payment_reference'] = $transaction_number;
			}
		}

		update_post_meta($order_id, '_order_postmeta', $order_postmeta);
	}

	/**
	 * Save the payment data returned by the payment server
	 *
	 * @param integer $order_id
	 */
	function save_payment_return_data( $order_id ) {
		$data = wpshop_tools::getMethode();

		update_post_meta($order_id, '_wpshop_payment_return_data', $data);
	}

	/**
	 * Add a new payment to a given order
	 *
	 * @param array $order_meta The complete order meta informations
	 * @param integer $payment_index The payment to add/update data for
	 * @param array $params : infos sended by the bank, array structure : ('method', 'waited amount', 'status', 'author', 'payment reference', 'date', 'received amount')
	 * @return array The order new meta informations
	 */
	function add_new_payment_to_order( $order_id, $order_meta, $payment_index, $params ) {

		$order_meta['order_payment']['received'][$payment_index]['method'] = ( !empty($params['method']) ) ? $params['method'] : null;
		$order_meta['order_payment']['received'][$payment_index]['waited_amount'] = ( !empty($params['waited_amount']) ) ? $params['waited_amount'] : null;
		$order_meta['order_payment']['received'][$payment_index]['status'] = ( !empty($params['status']) ) ? $params['status'] : null;
		$order_meta['order_payment']['received'][$payment_index]['author'] = ( !empty($params['author']) ) ? $params['author'] : get_current_user_id();
		$order_meta['order_payment']['received'][$payment_index]['payment_reference'] =( !empty($params['payment_reference']) ) ? $params['payment_reference'] : null;
		$order_meta['order_payment']['received'][$payment_index]['date'] = ( !empty($params['date']) ) ? $params['date'] : null;
		$order_meta['order_payment']['received'][$payment_index]['received_amount'] = ( !empty($params['received_amount']) ) ? $params['received_amount'] : null;
		$order_meta['order_payment']['received'][$payment_index]['comment'] = '';

		/**	Generate an invoice number for the current payment. Check if the payment is complete or not	*/
		$order_meta['order_payment']['received'][$payment_index]['invoice_ref'] = wpshop_modules_billing::generate_invoice_number( $order_id );

		$order_info = get_post_meta($order_id, '_order_info', true);
		if(!empty($order_meta) && !empty($order_info)) {
			$email = (!empty($order_info['billing']['address']['address_user_email']) ? $order_info['billing']['address']['address_user_email'] : '' );
			$first_name = ( !empty($order_info['billing']['address']['address_first_name']) ? $order_info['billing']['address']['address_first_name'] : '' );
			$last_name = ( !empty($order_info['billing']['address']['address_last_name']) ? $order_info['billing']['address']['address_last_name'] : '' );

			wpshop_messages::wpshop_prepared_email($email, 'WPSHOP_OTHERS_PAYMENT_CONFIRMATION_MESSAGE', array('order_key' => $order_meta['order_key'], 'customer_first_name' => $first_name, 'customer_last_name' => $last_name, 'order_date' => $order_meta['order_date']));
		}

		return $order_meta['order_payment']['received'][$payment_index];
	}

	/**
	 * Return the array id of the last waited paylent for an payment method
	 * @param integer $oid
	 * @param string $payment_method
	 * @return integer $key : array id of [order_payment][received] in the order postmeta
	 */
	function get_order_waiting_payment_array_id ( $oid, $payment_method ) {
		$key = 0;
		$order_meta = get_post_meta( $oid, '_order_postmeta', true);
		if ( !empty($order_meta) ) {
			$key = count( $order_meta['order_payment']['received'] );
			foreach ( $order_meta['order_payment']['received'] as $k => $payment_test) {
				if ( !array_key_exists('received_amount', $payment_test) /* && $order_meta['order_payment']['received'][$k]['method'] == $payment_method */ ) {
					$key = $k;
				}
			}
		}
		return $key;
	}

	function display_payment_list( $order_id, $order_postmeta, $display_last = true ) {
		$output = '';

		/**	Received payment for current order	*/
		$waited_amount_sum = $received_amount_sum = 0;
		if (!empty($order_postmeta['order_payment']['received'])) {
			foreach ( $order_postmeta['order_payment']['received'] as $payment_index => $payment_information) {
				if ( !empty($payment_information) && !empty($payment_information['status']) && ($payment_information['status'] == 'payment_received') ) {
					$sub_tpl_component = array();

					foreach ($payment_information as $payment_info_name => $payment_info_value) {
						$value_to_display = $payment_info_value;
						$sub_tpl_component['ADMIN_ORDER_RECEIVED_PAYMENT_UNSTYLED_' . strtoupper($payment_info_name)] = __($value_to_display, 'wpshop');
						if ( strpos($payment_info_name, 'amount') ) {
							$value_to_display = wpshop_display::format_field_output('wpshop_product_price', $payment_info_value) . ' ' . wpshop_tools::wpshop_get_currency();
						}
						elseif ( strpos($payment_info_name, 'date') || ($payment_info_name == 'date') ) {
							$value_to_display = str_replace(' 00:00:00', '', mysql2date('d M Y H:i:s', $payment_info_value, true));
						}
						$sub_tpl_component['ADMIN_ORDER_RECEIVED_PAYMENT_' . strtoupper($payment_info_name)] = __($value_to_display, 'wpshop');
					}

					if ( !empty($payment_information['waited_amount']) ) {
						$waited_amount_sum += $payment_information['waited_amount'];
					}
					if ( !empty($payment_information['received_amount']) ) {
						$received_amount_sum += $payment_information['received_amount'];
					}

					$sub_tpl_component['ADMIN_ORDER_PAYMENT_RECEIVED_LINE_CLASSES'] = '';
					$sub_tpl_component['ADMIN_ORDER_INVOICE_DOWNLOAD_LINK'] = WPSHOP_TEMPLATES_URL . 'invoice.php?order_id=' . $order_id . '&invoice_ref=' . $payment_information['invoice_ref'];
					$sub_tpl_component['PAYMENT_INVOICE_DOWNLOAD_LINKS'] = wpshop_display::display_template_element('wpshop_admin_order_payment_received_invoice_download_links', $sub_tpl_component, array(), 'admin');;
					if ( $display_last || (!$display_last && ($payment_information['invoice_ref'] != $order_postmeta['order_invoice_ref'])) ) {
						$output .= wpshop_display::display_template_element('wpshop_admin_order_payment_received', $sub_tpl_component, array(), 'admin');
					}
					unset($sub_tpl_component);
				}
				else {
					$output .= '';
				}
			}
		}

		return array($output, $waited_amount_sum, $received_amount_sum);
	}

	/**
	 * Update th receive payment part in order postmeta and return "Complete" if the shop have received the total amount of the order
	 * @param int $order_id
	 * @param array $params_array
	 * @return string
	 */
	function check_order_payment_total_amount($order_id, $params_array, $bank_response) {
		global $wpshop_payment;

		$order_meta = get_post_meta( $order_id, '_order_postmeta', true);

		if ( !empty($order_meta) ) {
			$key = self::get_order_waiting_payment_array_id( $order_id, $params_array['method']);
			$order_grand_total = $order_meta['order_grand_total'];
			$total_received = $params_array['received_amount'];
			foreach ( $order_meta['order_payment']['received'] as $received ) {
				$total_received += ( ( !empty($received['received_amount']) ) ? $received['received_amount'] : 0 );
			}
			$order_meta['order_amount_to_pay_now'] = $order_grand_total - $total_received;
			$order_meta['order_payment']['received'][$key] = self::add_new_payment_to_order( $order_id, $order_meta, $key, $params_array );

			if ($bank_response == 'completed') {
				if ( $total_received >= $order_grand_total) {
					$payment_status = 'completed';

					$order_meta['order_invoice_ref'] = $order_meta['order_payment']['received'][$key]['invoice_ref']; //wpshop_modules_billing::generate_invoice_number( $order_id );
					$order_meta['order_invoice_date'] = current_time('mysql', 0);

					if (!empty($order_meta['order_items'])) {
						foreach ($order_meta['order_items'] as $o) {
							$product = wpshop_products::get_product_data( $o['item_id'] );
							if (!empty($product) && !empty($product['manage_stock']) && $product['manage_stock']=='yes') {
								wpshop_products::reduce_product_stock_qty($o['item_id'], $o['item_qty']);
							}
						}
					}

					/** Add information about the order completed date */
					update_post_meta($order_id, '_' . WPSHOP_NEWTYPE_IDENTIFIER_ORDER . '_completed_date', current_time('mysql', 0));
				}
				else {
					$payment_status = 'partially_paid';
				}
			}
			else {
				$payment_status = $bank_response;
			}

			$order_meta['order_status'] = $payment_status;
			update_post_meta( $order_id, '_order_postmeta', $order_meta);
			update_post_meta( $order_id, '_wpshop_order_status', $payment_status);
		}
	}

	/**
	 * Return the transaction of an order payment transaction.
	 *
	 * @deprecated deprecated since version 1.3.3.7
	 *
	 * @param integer $order_id The order identifier we want to get the old transaction reference for
	 * @return integer
	 */
	function get_payment_transaction_number_old_way($order_id){
		$order_postmeta = get_post_meta($order_id, '_order_postmeta', true);
		$transaction_indentifier = 0;
		if(!empty($order_postmeta['payment_method'])){
			switch($order_postmeta['payment_method']){
				case 'check':
					$transaction_indentifier = get_post_meta($order_id, '_order_check_number', true);
					break;
				case 'paypal':
					$transaction_indentifier = get_post_meta($order_id, '_order_paypal_txn_id', true);
					break;
				case 'cic':
					$transaction_indentifier = get_post_meta($order_id, '_order_cic_txn_id', true);
					break;
				default:
					$transaction_indentifier = 0;
					break;
			}
		}

		return $transaction_indentifier;
	}
}

?>