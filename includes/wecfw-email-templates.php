<?php
/**
 * Add custom email content to WooCommerce emails.
 *
 * @package custom-email-content-for-woocommerce
 */

add_action( 'woocommerce_email_classes', 'wecfw_email_templates_additional_settings' );

/**
 * Add additional settings to WooCommerce emails.
 *
 * @param array $email_class_list Array of email classes.
 * @return array
 */
function wecfw_email_templates_additional_settings( $email_class_list ) {
	foreach ( $email_class_list as $email_class ) {
		add_action( 'woocommerce_settings_api_form_fields_' . $email_class->id, 'add_my_custom_email_setting', 10, 1 );
	}

	return $email_class_list;
}

/**
 * Add additional settings to WooCommerce emails.
 *
 * @param array $form_fields Array of email settings.
 * @return array
 */
function add_my_custom_email_setting( $form_fields ) {

	// Retrieve WooCommerce Categories.
	$terms = get_terms(
		array(
			'taxonomy'   => 'product_cat',
			'hide_empty' => false,
			'orderby'    => 'term_order',
		)
	);

	// Setup the options that are available for categories.
	$category_options = array();
	foreach ( $terms as $term ) {
		$category_options[ $term->slug ] = ( 0 === $term->parent ? '' : '- ' ) . $term->name;
	}
	$category_options[''] = __( 'Disable Custom Content', 'custom-email-content-for-woocommerce' );

	// Add the selection of where the content should show.
	$form_fields['wecfw_content_location'] = array(
		'title'    => __( 'Content location', 'custom-email-content-for-woocommerce' ),
		'desc_tip' => 'Select where the content should show.',
		'type'     => 'select',
		'class'    => 'wc-enhanced-select',
		'options'  => array(
			''                   => __( 'Don\'t display', 'custom-email-content-for-woocommerce' ),
			'before_order_table' => __( 'Before order table', 'custom-email-content-for-woocommerce' ),
			'after_order_table'  => __( 'After order table', 'custom-email-content-for-woocommerce' ),
		),
	);

	// Add the selection of category one.
	$form_fields['wecfw_cat_one'] = array(
		'title'    => __( 'Category one select', 'custom-email-content-for-woocommerce' ),
		'desc_tip' => '',
		'default'  => '',
		'type'     => 'select',
		'class'    => 'wc-enhanced-select',
		'options'  => $category_options,
		'autoload' => false,
	);

	// Add the selection of category one content.
	$form_fields['wecfw_cat_one_content'] = array(
		'title'    => __( 'Category one HTML content', 'custom-email-content-for-woocommerce' ),
		'type'     => 'textarea',
		'class'    => 'large',
		'autoload' => false,
		'desc_tip' => __( 'Add custom HTML content here.', 'custom-email-content-for-woocommerce' ),
		'css'      => 'max-width: 400px;',
	);

	// Add the selection of category two.
	$form_fields['wecfw_cat_two'] = array(
		'title'    => __( 'Category two select', 'custom-email-content-for-woocommerce' ),
		'desc_tip' => '',
		'default'  => '',
		'type'     => 'select',
		'class'    => 'wc-enhanced-select',
		'options'  => $category_options,
		'autoload' => false,
	);

	// Add the selection of category two content.
	$form_fields['wecfw_cat_two_content'] = array(
		'title'    => __( 'Category two HTML content', 'custom-email-content-for-woocommerce' ),
		'type'     => 'textarea',
		'desc_tip' => __( 'Add custom HTML content here.', 'custom-email-content-for-woocommerce' ),
		'autoload' => false,
		'css'      => 'max-width: 400px;',
	);

	return $form_fields;
}
