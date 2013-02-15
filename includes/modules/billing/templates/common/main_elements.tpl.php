<?php

ob_start();
?><!DOCTYPE html>
<!--[if IE 8]>
<html xmlns="http://www.w3.org/1999/xhtml" class="ie8 wp-toolbar"  dir="ltr" lang="en-US">
<![endif]-->
<!--[if !(IE 8) ]><!-->
<html xmlns="http://www.w3.org/1999/xhtml" class="wp-toolbar"  dir="ltr" lang="en-US">
<!--<![endif]-->
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
		<title>{WPSHOP_INVOICE_TITLE_PAGE}</title>
		{WPSHOP_INVOICE_CSS}
	</head>
	<body >
		{WPSHOP_INVOICE_MAIN_PAGE}
	</body>
</html><?php
$tpl_element['common']['default']['invoice_page'] = ob_get_contents();
ob_end_clean();


ob_start();
?><style type="text/css" >
	.invoice_main_title_container {
		width: 100%;
	}
	.invoice_main_title {
		font-size: 40px;
	}
	.invoice_main_title_container td {
		width: 100%;
		text-align: right;
	}

	.invoice_part_main_container {
		width: 100%;
		margin-bottom: 30px;
	}
	.invoice_sender_container, .invoice_receiver_container {
		width: 40%;
		padding: 10px;
	}
	.invoice_part_main_container .invoice_emtpy_cell {
		width: 20%;
	}
	.invoice_sender_container {
		background-color: #CCCCCC;
	}
	.invoice_receiver_container {
		border: 1px solid #000000;
	}

	.invoice_lines  {
		border-collapse: collapse;
		border: 1px solid #CCCCCC;
		width: 100%;
	}
	.invoice_lines th, .invoice_lines td {
		border: 1px solid #CCCCCC;
	}
	.invoice_lines th {
		padding: 3px;
	}
	.invoice_lines td {
		padding: 3px 0px;
	}

	.wpshop_alignright {
		text-align: right;
	}
	.wpshop_aligncenter {
		text-align: center;
	}
	.wpshop_cart_variation_details_line {
		clear: both;
		margin: 6px 10px;
		word-wrap: break-word;
	}
	.invoice_line_ref {
		width: 30mm;
		word-wrap: break-word;
		-webkit-hyphens: auto;
		-moz-hyphens: auto;
		-ms-hyphens: auto;
		-o-hyphens: auto;
		hyphens: auto;
	}
	.invoice_line_product_name {
		width: 60mm;
	}
	.wpshop_invoice_summaries_container {
		margin: 30px 0px;
		width: 100%;
		border-collapse: collapse;
	}
	.wpshop_invoice_summaries_container_infos {
		width: 60%;
	}
	.wpshop_invoice_summaries_container_totals {
		width: 40%;
	}
	.invoice_summary {
		width: 100%;
	}

	.invoice_summary_row_title {
		width: 80%;
		text-align: right;
	}
	.invoice_summary_row_amount {
		width: 20%;
		text-align: right;
	}


	.wpshop_invoice_received_payment_container {
		width: 100%;
		float: right;
		margin: 40px 0px;
	}
	.wpshop_invoice_received_payment_container_infos {
		width: 50%;
	}
	.received_payment_list {
		width: 100%;
	}
	.received_payment_list_header th {
		background-color: #CCCCCC;
	}
	.received_payment_list_row td {
		text-align: center;
	}
		</style><?php
$tpl_element['common']['default']['invoice_page_content_css'] = ob_get_contents();
ob_end_clean();


ob_start();
?>		<table class="invoice_main_title_container" >
			<tbody>
				<tr>
					<td class="invoice_main_title" >{WPSHOP_INVOICE_TITLE}</td>
				</tr>
				<tr>
					<td >
						<?php echo sprintf( __('Ref. %s', 'wpshop'), '{WPSHOP_INVOICE_ORDER_INVOICE_REF}' ); ?>
						<br/><br/><?php echo sprintf( __('Order n. %s', 'wpshop'), '{WPSHOP_INVOICE_ORDER_KEY}' ); ?>
						<br/><?php echo sprintf( __('Order date %s', 'wpshop'), '{WPSHOP_INVOICE_ORDER_DATE}') ; ?>
					</td>
				</tr>
			</tbody>
		</table>
		<table class="invoice_part_main_container" >
			<tbody>
				<tr>
					<td class="invoice_sender_title" ><?php _e('Sender', 'wpshop'); ?></td>
					<td class="invoice_emtpy_cell" ></td>
					<td class="invoice_receiver_title" ><?php _e('Customer', 'wpshop'); ?></td>
				</tr>
				<tr>
					<td class="invoice_sender_container" >
						{WPSHOP_INVOICE_SENDER}
					</td>
					<td class="invoice_emtpy_cell" ></td>
					<td class="invoice_receiver_container" >
						{WPSHOP_INVOICE_RECEIVER}
					</td>
				</tr>
			</tbody>
		</table>
		<h4 style="text-align: right; width: 100%; margin: 30px 0px 0px;"><?php echo sprintf( __('Amount are shown in %s', 'wpshop'), wpshop_tools::wpshop_get_currency( true ) ); ?></h4>
		<table class="invoice_lines" >
			<thead>
				{WPSHOP_INVOICE_HEADER}
			</thead>
			<tbody>
				{WPSHOP_INVOICE_ROWS}
			</tbody>
		</table>
		<table class="wpshop_invoice_summaries_container" >
			<tbody>
				<tr>
					<td class="wpshop_invoice_summaries_container_infos" ></td>
					<td class="wpshop_invoice_summaries_container_totals" >
						<table class="invoice_summary" >
							<tbody>
								<tr>
									<td class="invoice_summary_row_title" ><?php _e('Order grand total ET', 'wpshop'); ?></td>
									<td class="invoice_summary_row_amount" >{WPSHOP_INVOICE_ORDER_TOTAL_HT} {WPSHOP_CURRENCY}</td>
								</tr>
								{WPSHOP_INVOICE_SUMMARY_TAXES}
								<tr class="wpshop_invoice_grand_total" >
									<td class="invoice_summary_row_title" ><?php _e('Order grand total ATI', 'wpshop'); ?></td>
									<td class="invoice_summary_row_amount" >{WPSHOP_INVOICE_ORDER_GRAND_TOTAL} {WPSHOP_CURRENCY}</td>
								</tr>
								{WPSHOP_INVOICE_SUMMARY_MORE}
							</tbody>
						</table>
					</td>
				</tr>
			</tbody>
		</table>
		{WPSHOP_RECEIVED_PAYMENT}<?php
$tpl_element['common']['default']['invoice_page_content'] = ob_get_contents();
ob_end_clean();

ob_start();
?>		<table class="wpshop_invoice_received_payment_container" >
			<tbody>
				<tr>
				<td class="wpshop_invoice_received_payment_container_infos" ></td>
				<td class="wpshop_invoice_received_payment_container_list wpshop_invoice_summaries_container_received_payment" >
					<?php _e('Received payment', 'wpshop'); ?>
					<table class="received_payment_list" >
						<thead>
							<tr class="received_payment_list_header" >
								<th><?php _e('Date', 'wpshop'); ?></th>
								<th><?php _e('Amount', 'wpshop'); ?></th>
								<th><?php _e('Method', 'wpshop'); ?></th>
								<th><?php _e('Ref.', 'wpshop'); ?></th>
								<th><?php _e('Invoice ref.', 'wpshop'); ?></th>
							</tr>
						</thead>
						<tbody>
							{WPSHOP_ORDER_RECEIVED_PAYMENT_ROWS}
						</tbody>
					</table>
				</td>
			</tr></tbody>
		</table><?php
$tpl_element['common']['default']['received_payment'] = ob_get_contents();
ob_end_clean();
ob_end_clean();

ob_start();
?><tr class="received_payment_list_row" >
	<td>{WPSHOP_INVOICE_RECEIVED_PAYMENT_DATE}</td>
	<td>{WPSHOP_INVOICE_RECEIVED_PAYMENT_RECEIVED_AMOUNT}</td>
	<td>{WPSHOP_INVOICE_RECEIVED_PAYMENT_METHOD}</td>
	<td>{WPSHOP_INVOICE_RECEIVED_PAYMENT_PAYMENT_REFERENCE}</td>
	<td>{WPSHOP_INVOICE_RECEIVED_PAYMENT_INVOICE_REF}</td>
</tr><?php
$tpl_element['common']['default']['received_payment_row'] = ob_get_contents();
ob_end_clean();

ob_start();
?><tr>
	<th><?php _e('Reference', 'wpshop'); ?></th>
	<th><?php _e('Name', 'wpshop'); ?></th>
	<th><?php _e('Qty', 'wpshop'); ?></th>
	<th><?php _e('Unit price ET', 'wpshop'); ?></th>
	<th><?php _e('Total price ET', 'wpshop'); ?></th>
	<th><?php _e('Taxes amount', 'wpshop'); ?></th>
	<th><?php _e('Total price ATI', 'wpshop'); ?></th>
</tr><?php
$tpl_element['common']['default']['invoice_row_header'] = ob_get_contents();
ob_end_clean();

ob_start();
?><tr>
	<td class="invoice_line_ref" >{WPSHOP_INVOICE_ROW_ITEM_REF}</td>
	<td class="invoice_line_product_name" >
		{WPSHOP_INVOICE_ROW_ITEM_NAME}
		{WPSHOP_INVOICE_ROW_ITEM_DETAIL}
	</td>
	<td class="wpshop_aligncenter" >{WPSHOP_INVOICE_ROW_ITEM_QTY}</td>
	<td class="wpshop_alignright" >{WPSHOP_INVOICE_ROW_ITEM_PU_HT}</td>
	<td class="wpshop_alignright" >{WPSHOP_INVOICE_ROW_ITEM_TOTAL_HT}</td>
	<td class="wpshop_alignright" >{WPSHOP_INVOICE_ROW_ITEM_TVA_AMOUNT} ({WPSHOP_INVOICE_ROW_ITEM_TVA_RATE}%)</td>
	<td class="wpshop_alignright" >{WPSHOP_INVOICE_ROW_ITEM_TOTAL_TTC}</td>
</tr><?php
$tpl_element['common']['default']['invoice_row'] = ob_get_contents();
ob_end_clean();

/*	Product variation detail in cart					Panier detail des variations */
ob_start();
?><span class="wpshop_cart_variation_details_line" >{WPSHOP_VARIATION_NAME} : {WPSHOP_VARIATION_VALUE}</span><br/><?php
$tpl_element['common']['default']['cart_variation_detail'] = ob_get_contents();
ob_end_clean();

ob_start();
?><br/>{WPSHOP_CART_PRODUCT_MORE_INFO}<?php
$tpl_element['common']['default']['invoice_row_item_detail'] = ob_get_contents();
ob_end_clean();


ob_start();
?><tr>
	<td class="invoice_summary_row_title" >{WPSHOP_SUMMARY_ROW_TITLE}</td>
	<td class="invoice_summary_row_amount" >{WPSHOP_SUMMARY_ROW_VALUE}</td>
</tr><?php
$tpl_element['common']['default']['invoice_summary_row'] = ob_get_contents();
ob_end_clean();