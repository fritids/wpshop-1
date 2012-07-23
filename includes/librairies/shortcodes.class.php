<?php
class wpshop_shortcodes
{
	/*	Define the database table used in the current class	*/
	const dbTable = '';
	/*	Define the url listing slug used in the current class	*/
	const urlSlugListing = WPSHOP_URL_SLUG_SHORTCODES;
	/*	Define the url edition slug used in the current class	*/
	const urlSlugEdition = WPSHOP_URL_SLUG_SHORTCODES;
	/*	Define the current entity code	*/
	const currentPageCode = 'shortcodes';
	/*	Define the page title	*/
	const pageContentTitle = 'Shortcodes';
	/*	Define the page title when adding an attribute	*/
	const pageAddingTitle = 'Add a shortcode';
	/*	Define the page title when editing an attribute	*/
	const pageEditingTitle = 'Shortcode "%s" edit';
	/*	Define the page title when editing an attribute	*/
	const pageTitle = 'Shortcodes list';

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
				$title = sprintf(__(self::pageEditingTitle, 'wpshop'), str_replace("\\", "", $editedItem->frontend_label) . '&nbsp;(' . $editedItem->code . ')');
			}
			elseif($action == 'add'){
				$title = __(self::pageAddingTitle, 'wpshop');
			}
		}
		elseif((self::getEditionSlug() != self::getListingSlug()) && ($_GET['page'] == self::getEditionSlug())){
			$title = __(self::pageAddingTitle, 'wpshop');
		}
		return $title;
	}

	function elementAction(){
	
	}
	
	function shortcode_definition(){
		$shortcodes = array();
		
		/*	Product tab	*/
		$shortcodes['simple_product']['main_title'] = __('Simple product shortcode', 'wpshop');
		$shortcodes['simple_product']['main_code'] = 'wpshop_product';
		$shortcodes['simple_product']['attrs_def']['pid'] = 'ID_DU_PRODUIT';
		$shortcodes['simple_product']['attrs_def']['type'] = 'list|grid';
		$shortcodes['simple_product']['attrs_exemple']['pid'] = '12';
		$shortcodes['simple_product']['attrs_exemple']['type'] = 'list';

		$shortcodes['product_listing']['main_title'] = __('Product listing', 'wpshop');
		$shortcodes['product_listing']['main_code'] = 'wpshop_products';
		$shortcodes['product_listing']['attrs_def']['limit'] = 'NB_MAX_PRODUIT_A_AFFICHER';
		$shortcodes['product_listing']['attrs_def']['order'] = 'title|date|price|random';
		$shortcodes['product_listing']['attrs_def']['sorting'] = 'asc|desc';
		$shortcodes['product_listing']['attrs_def']['display'] = 'normal|mini';
		$shortcodes['product_listing']['attrs_def']['type'] = 'list|grid';
		$shortcodes['product_listing']['attrs_def']['pagination'] = 'NB_PRODUIT_PAR_PAGE';
		$shortcodes['product_listing']['attrs_exemple']['pid'] = '20';
		$shortcodes['product_listing']['attrs_exemple']['order'] = 'price';
		$shortcodes['product_listing']['attrs_exemple']['sorting'] = 'desc';
		$shortcodes['product_listing']['attrs_exemple']['display'] = 'normal';
		$shortcodes['product_listing']['attrs_exemple']['type'] = 'grid';
		$shortcodes['product_listing']['attrs_exemple']['pagination'] = '5';

		$shortcodes['product_listing_specific']['main_title'] = __('Product listing with specific product', 'wpshop');
		$shortcodes['product_listing_specific']['main_code'] = 'wpshop_products';
		$shortcodes['product_listing_specific']['attrs_def']['pid'] = 'ID_DU_PRODUIT_1,ID_DU_PRODUIT_2,ID_DU_PRODUIT_3,...';
		$shortcodes['product_listing_specific']['attrs_def']['type'] = 'list|grid';
		$shortcodes['product_listing_specific']['attrs_exemple']['pid'] = '12,25,4,98';
		$shortcodes['product_listing_specific']['attrs_exemple']['type'] = 'list';

		$shortcodes['product_by_attribute']['main_title'] = __('Product listing for a given attribute value', 'wpshop');
		$shortcodes['product_by_attribute']['main_code'] = 'wpshop_products';
		$shortcodes['product_by_attribute']['attrs_def']['att_name'] = 'ATTRIBUTE_CODE';
		$shortcodes['product_by_attribute']['attrs_def']['att_value'] = 'ATTRIBUTE_VALUE';
		$shortcodes['product_by_attribute']['attrs_def']['type'] = 'list|grid';
		$shortcodes['product_by_attribute']['attrs_exemple']['att_name'] = 'tx_tva';
		$shortcodes['product_by_attribute']['attrs_exemple']['att_value'] = '19.6';
		$shortcodes['product_by_attribute']['attrs_exemple']['type'] = 'list';

		/*	Category tab	*/
		$shortcodes['simple_category']['main_title'] = __('Complete category output', 'wpshop');
		$shortcodes['simple_category']['main_code'] = 'wpshop_category';
		$shortcodes['simple_category']['attrs_def']['cid'] = 'ID_DE_LA_CATEGORIE';
		$shortcodes['simple_category']['attrs_def']['type'] = 'list|grid';
		$shortcodes['simple_category']['attrs_exemple']['cid'] = '12';
		$shortcodes['simple_category']['attrs_exemple']['type'] = 'list';

		/*	Attribute tab	*/
		$shortcodes['simple_attribute']['main_title'] = __('Display an attribute value', 'wpshop');
		$shortcodes['simple_attribute']['main_code'] = 'wpshop_att_val';
		$shortcodes['simple_attribute']['attrs_def']['type'] = 'decimal|varchar';
		$shortcodes['simple_attribute']['attrs_def']['attid'] = 'ID_DE_LATTRIBUT';
		$shortcodes['simple_attribute']['attrs_def']['pid'] = 'ID_DU_PRODUIT';
		$shortcodes['simple_attribute']['attrs_exemple']['type'] = 'decimal';
		$shortcodes['simple_attribute']['attrs_exemple']['attid'] = '3';
		$shortcodes['simple_attribute']['attrs_exemple']['pid'] = '98';

		$shortcodes['attributes_set']['main_title'] = __('Display a complete attribute set', 'wpshop');
		$shortcodes['attributes_set']['main_code'] = 'wpshop_att_group';
		$shortcodes['attributes_set']['attrs_def']['pid'] = 'ID_DU_PRODUIT';
		$shortcodes['attributes_set']['attrs_def']['sid'] = 'ID_DE_LA_SECTION';
		$shortcodes['attributes_set']['attrs_exemple']['pid'] = '98';
		$shortcodes['attributes_set']['attrs_exemple']['sid'] = '2';

		/*	Widget tab	*/
		$shortcodes['widget_cart_full']['main_title'] = __('Display the complete cart', 'wpshop');
		$shortcodes['widget_cart_full']['main_code'] = 'wpshop_cart';

		$shortcodes['widget_cart_mini']['main_title'] = __('Display the cart widget', 'wpshop');
		$shortcodes['widget_cart_mini']['main_code'] = 'wpshop_mini_cart';

		$shortcodes['widget_checkout']['main_title'] = __('Display the checkout page content', 'wpshop');
		$shortcodes['widget_checkout']['main_code'] = 'wpshop_checkout';

		$shortcodes['widget_account']['main_title'] = __('Display the customer account page', 'wpshop');
		$shortcodes['widget_account']['main_code'] = 'wpshop_myaccount';

		$shortcodes['widget_shop']['main_title'] = __('Display the shop page content', 'wpshop');
		$shortcodes['widget_shop']['main_code'] = 'wpshop_products';

		$shortcodes['widget_custom_search']['main_title'] = __('Display a custom search form', 'wpshop');
		$shortcodes['widget_custom_search']['main_code'] = 'wpshop_custom_search';

		return $shortcodes;
	}

	function output_shortcode($shortcode_code, $specific_shorcode = '', $more_class_shortcode_helper = ''){
		$shortcode = ( empty($specific_shorcode) ? self::shortcode_definition() : $specific_shorcode );

		$shortcode_main_title = ( !empty($shortcode[$shortcode_code]['main_title']) ? $shortcode[$shortcode_code]['main_title'] : '' );
		$shorcode_main_code = ( !empty($shortcode[$shortcode_code]['main_code']) ? $shortcode[$shortcode_code]['main_code'] : '' );
		$shorcode_attributes_def = ' ';
		if(!empty($shortcode[$shortcode_code]['attrs_def'])){
			foreach($shortcode[$shortcode_code]['attrs_def'] as $attr_name => $attr_values){
				$shorcode_attributes_def .= $attr_name.'="'.$attr_values.'" ';
			}
		}
		$shorcode_attributes_def = substr($shorcode_attributes_def, 0, -1);
		$shorcode_attributes_exemple = ' ';
		if(!empty($shortcode[$shortcode_code]['attrs_exemple'])){
			foreach($shortcode[$shortcode_code]['attrs_exemple'] as $attr_name => $attr_values){
				$shorcode_attributes_exemple .= $attr_name.'="'.$attr_values.'" ';
			}
		}
		$shorcode_attributes_exemple = substr($shorcode_attributes_exemple, 0, -1);

		include(WPSHOP_TEMPLATES_DIR.'admin/shortcode_help.tpl.php');
	}

	function elementList(){

		$shortcode_list = '';
		ob_start();
?>
<div id="shortcode-tabs" class="wpshop_tabs wpshop_full_page_tabs wpshop_shortcode_tabs" >
	<ul>
		<li><a href="#products"><?php _e('Products', 'wpshop'); ?></a></li>
		<li><a href="#category"><?php _e('Categories', 'wpshop'); ?></a></li>
		<li><a href="#attributs"><?php _e('Attributs', 'wpshop'); ?></a></li>
		<li><a href="#widgets"><?php _e('Widgets', 'wpshop'); ?></a></li>
		<li><a href="#customs_emails"><?php _e('Customs emails', 'wpshop'); ?></a></li>
	</ul>

	<div id="products">
		<div class="wpshop_admin_box wpshop_admin_box_shortcode wpshop_admin_box_shortcode_product" >
			<h3><?php _e('Simple product','wpshop'); ?></h3>
			<?php self::output_shortcode('simple_product'); ?>
			<h3><?php _e('Products listing','wpshop'); ?></h3>
			<?php self::output_shortcode('product_listing'); ?>
			<?php self::output_shortcode('product_listing_specific'); ?>
			<?php self::output_shortcode('product_by_attribute'); ?>
		</div>
	</div>

	<div id="category">
		<div class="wpshop_admin_box wpshop_admin_box_shortcode wpshop_admin_box_shortcode_category" >
			<h3><?php _e('Simple category','wpshop'); ?></h3>
			<?php self::output_shortcode('simple_category'); ?>
		</div>
	</div>

	<div id="attributs">
		<div class="wpshop_admin_box wpshop_admin_box_shortcode wpshop_admin_box_shortcode_attributes" >
			<h3><?php _e('Simple attribute','wpshop'); ?></h3>
			<?php self::output_shortcode('simple_attribute'); ?>
			<h3><?php _e('Attributes set','wpshop'); ?></h3>
			<?php self::output_shortcode('attributes_set'); ?>
		</div>
	</div>

	<div id="widgets">
		<div class="wpshop_admin_box wpshop_admin_box_shortcode wpshop_admin_box_shortcode_widget wpshop_admin_box_shortcode_widget_cart" >
			<h3><?php _e('Cart','wpshop'); ?></h3>
			<?php self::output_shortcode('widget_cart_full'); ?>
			<?php self::output_shortcode('widget_cart_mini'); ?>
		</div>
		<div class="wpshop_admin_box wpshop_admin_box_shortcode wpshop_admin_box_shortcode_widget wpshop_admin_box_shortcode_widget_checkout" >
			<h3><?php _e('Checkout','wpshop'); ?></h3>
			<?php self::output_shortcode('widget_checkout'); ?>
		</div>
		<div class="wpshop_admin_box wpshop_admin_box_shortcode wpshop_admin_box_shortcode_widget wpshop_admin_box_shortcode_widget_customer_account" >
			<h3><?php _e('Account','wpshop'); ?></h3>
			<?php self::output_shortcode('widget_account'); ?>
		</div>
		<div class="wpshop_admin_box wpshop_admin_box_shortcode wpshop_admin_box_shortcode_widget wpshop_admin_box_shortcode_widget_shop" >
			<h3><?php _e('Shop','wpshop'); ?></h3>
			<?php self::output_shortcode('widget_shop'); ?>
		</div>
		<div class="wpshop_admin_box wpshop_admin_box_shortcode wpshop_admin_box_shortcode_widget wpshop_admin_box_shortcode_custom_search" >
			<h3><?php _e('Custom search','wpshop'); ?></h3>
			<?php self::output_shortcode('widget_custom_search'); ?>
		</div>
	</div>

	<div id="customs_emails">
		<div class="wpshop_admin_box wpshop_admin_box_shortcode wpshop_admin_box_shortcode_emails" >
			<h3><?php _e('Available tags for emails cutomization','wpshop'); ?></h3>
			<ul >
				<li><span class="wpshop_customer_tag_name" ><?php _e('Customer first name', 'wpshop'); ?></span><code>[customer_first_name]</code><li>
				<li><span class="wpshop_customer_tag_name" ><?php _e('Customer last name', 'wpshop'); ?></span><code>[customer_last_name]</code><li>
				<li><span class="wpshop_customer_tag_name" ><?php _e('Order id', 'wpshop'); ?></span><code>[order_key]</code><li>
				<li><span class="wpshop_customer_tag_name" ><?php _e('Paypal transaction id', 'wpshop'); ?></span><code>[paypal_order_key]</code><li>
			</ul>
		</div>
	</div>
</div>
<?php
		$shortcode_list = ob_get_contents();
		ob_end_clean();

		return $shortcode_list;
	}

}
