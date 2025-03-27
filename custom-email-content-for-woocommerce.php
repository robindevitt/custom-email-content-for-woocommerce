<?php
/**
 * Plugin Name: Custom Email Content for WooCommerce
 * Plugin URI: https://github.com/robindevitt/custom-email-content-for-woocommerce
 * Description: Add custom email content too WooCommerce emails for specific categories.
 * Version: 1.1.0
 * Author: Robin Devitt
 * Author URI: https://robindevitt.co.za/
 * License: GPL v3 or later
 * License URI: https://www.gnu.org/licenses/gpl-3.0.html
 * Text Domain: custom-email-content-for-woocommerce
 * Domain Path: /languages/
 * Requires Plugins: woocommerce
 *
 * @package custom-email-content-for-woocommerce
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

require_once 'includes/wecfw-email-templates.php';

add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), 'add_settings_link', 10 );
add_filter( 'woocommerce_email_settings', 'wecfw_email_additional_settings' );
add_filter( 'woocommerce_email_before_order_table', 'wecfw_content_before_order_table', 10, 4 );
add_filter( 'woocommerce_email_after_order_table', 'wecfw_content_after_order_table', 10, 4 );

/**
 * Add a settings link to the plugin page.
 *
 * @param array $links The existing links.
 */
function add_settings_link( $links ) {

	$settings_url = admin_url( 'admin.php?page=wc-settings&tab=email' );

	array_unshift(
		$links,
		sprintf( '<a href="%1$s">%2$s</a>', esc_url( $settings_url ), __( 'Settings', 'custom-email-content-for-woocommerce' ) )
	);

	return $links;
}

/**
 * Before the order table,
 *
 * @param WC_Order $order Order object.
 * @param bool     $sent_to_admin Whether it's sent to admin or customer.
 * @param bool     $plain_text Whether it's a plain text email.
 * @param WC_Email $email Email object.
 */
function wecfw_content_before_order_table( $order, $sent_to_admin, $plain_text, $email ) {
	$wecfw_content_location = get_option( 'wecfw_content_location' );
	if ( 'before_order_table' === $wecfw_content_location ) {
		wecfw_global_content_for_email( $order, $sent_to_admin, $plain_text, $email );
	}

	if ( 'before_order_table' === $email->get_option( 'wecfw_content_location' ) ) {
		wecfw_global_content_for_email( $order, $sent_to_admin, $plain_text, $email, true );
	}

	do_action( 'wecfw_add_email_template_content_before', $order, $sent_to_admin, $plain_text, $email );
}

/**
 * Before the order table.
 *
 * @param WC_Order $order Order object.
 * @param bool     $sent_to_admin Whether it's sent to admin or customer.
 * @param bool     $plain_text Whether it's a plain text email.
 * @param WC_Email $email Email object.
 */
function wecfw_content_after_order_table( $order, $sent_to_admin, $plain_text, $email ) {
	do_action( 'wecfw_add_email_template_content_after', $order, $sent_to_admin, $plain_text, $email );
	$wecfw_content_location = get_option( 'wecfw_content_location' );
	if ( 'after_order_table' === $wecfw_content_location ) {
		wecfw_global_content_for_email( $order, $sent_to_admin, $plain_text, $email );
	}

	if ( 'after_order_table' === $email->get_option( 'wecfw_content_location' ) ) {
		wecfw_global_content_for_email( $order, $sent_to_admin, $plain_text, $email, true );
	}
}

/**
 * Add global content before the woocommerce order table in emails.
 *
 * @param WC_Order $order Order object.
 * @param bool     $sent_to_admin Whether it's sent to admin or customer.
 * @param bool     $plain_text Whether it's a plain text email.
 * @param WC_Email $email Email object.
 * @param bool     $template Whether it's a template or not.
 */
function wecfw_global_content_for_email( $order, $sent_to_admin, $plain_text, $email, $template = false ) {

	if ( $template ) {

		$wecfw_cat_one_selected = $email->get_option( 'wecfw_cat_one' );
		$wecfw_cat_two_selected = $email->get_option( 'wecfw_cat_two' );
		$wecfw_cat_one_content  = $email->get_option( 'wecfw_cat_one_content' );
		$wecfw_cat_two_content  = $email->get_option( 'wecfw_cat_two_content' );

	} else {

		$wecfw_enabled_emails = get_option( 'wecfw_enabled_emails' );

		if ( ! in_array( get_class( $email ), $wecfw_enabled_emails, true ) ) {
			return;
		}

		$wecfw_cat_one_selected = get_option( 'wecfw_cat_one' );
		$wecfw_cat_two_selected = get_option( 'wecfw_cat_two' );
		$wecfw_cat_one_content  = get_option( 'wecfw_cat_one_content' );
		$wecfw_cat_two_content  = get_option( 'wecfw_cat_two_content' );
	}

	$show_category_one_content = false;
	$show_category_two_content = false;

	foreach ( $order->get_items() as $item ) {

		$product    = $item->get_product();
		$categories = wp_get_post_terms( $product->get_id(), 'product_cat', array( 'fields' => 'slugs' ) );

		if ( in_array( $wecfw_cat_one_selected, $categories, true ) ) {
			$show_category_one_content = true;
		}

		if ( in_array( $wecfw_cat_two_selected, $categories, true ) ) {
			$show_category_two_content = true;
		}

		if ( null !== $product->get_changes() ) {
			$changes = $product->get_changes();
			if ( isset( $changes['name'] ) && 'Dummy Product' === $changes['name'] ) {
				$show_category_one_content = true;
				$show_category_two_content = true;
			}
		}
	}

	do_action( 'wecfw_add_additional_email_content_before', $order, $sent_to_admin, $plain_text, $email );

	if ( $show_category_one_content ) {
		echo wp_kses_post( $wecfw_cat_one_content );
	}

	if ( $show_category_two_content ) {
		echo wp_kses_post( $wecfw_cat_two_content );
	}

	do_action( 'wecfw_add_additional_email_content_after' );
}


/**
 * Add additional settings to WooCommerce emails.
 *
 * @param array $settings Array of email settings.
 *
 * @return array
 */
function wecfw_email_additional_settings( $settings ) {

	$emails        = wc()->mailer()->emails;
	$email_options = array();
	foreach ( $emails as $email_name => $email_object ) {
		$email_options[ $email_name ] = $email_object->title;
	}
	$email_options = apply_filters( 'wecfw_add_email_options', $email_options );

	// Add the settings section title.
	$settings[] = array(
		'title' => __( 'Global category email content', 'custom-email-content-for-woocommerce' ),
		'type'  => 'title',
		'id'    => 'custom_email_options',
	);

	// Add the selection of which emails this is added too.
	$settings[] = array(
		'title'    => __( 'Email select', 'custom-email-content-for-woocommerce' ),
		'id'       => 'wecfw_enabled_emails',
		'desc_tip' => 'Select which emails should included the category content.',
		'type'     => 'multiselect',
		'class'    => 'wc-enhanced-select',
		'options'  => $email_options,
	);

	// Add the selection of where the content should show.
	$settings[] = array(
		'title'    => __( 'Content location', 'custom-email-content-for-woocommerce' ),
		'id'       => 'wecfw_content_location',
		'desc_tip' => 'Select where the content should show.',
		'type'     => 'select',
		'class'    => 'wc-enhanced-select',
		'options'  => array(
			''                   => __( 'Don\'t display', 'custom-email-content-for-woocommerce' ),
			'before_order_table' => __( 'Before order table', 'custom-email-content-for-woocommerce' ),
			'after_order_table'  => __( 'After order table', 'custom-email-content-for-woocommerce' ),
		),
	);

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
	$category_options[''] = __( 'Disable custom content', 'custom-email-content-for-woocommerce' );

	// Add the selection of category one.
	$settings[] = array(
		'title'    => __( 'Category one select', 'custom-email-content-for-woocommerce' ),
		'id'       => 'wecfw_cat_one',
		'desc_tip' => '',
		'default'  => '',
		'type'     => 'select',
		'class'    => 'wc-enhanced-select',
		'options'  => $category_options,
		'autoload' => false,
	);

	// Add the selection of category one content.
	$settings[] = array(
		'title'    => __( 'Category one HTML content', 'custom-email-content-for-woocommerce' ),
		'id'       => 'wecfw_cat_one_content',
		'type'     => 'textarea',
		'class'    => 'large',
		'autoload' => false,
		'desc_tip' => __( 'Add custom HTML content here.', 'custom-email-content-for-woocommerce' ),
	);

	// Add the selection of category two.
	$settings[] = array(
		'title'    => __( 'Category two select', 'custom-email-content-for-woocommerce' ),
		'id'       => 'wecfw_cat_two',
		'desc_tip' => '',
		'default'  => '',
		'type'     => 'select',
		'class'    => 'wc-enhanced-select',
		'options'  => $category_options,
		'autoload' => false,
	);

	// Add the selection of category two content.
	$settings[] = array(
		'title'    => __( 'Category two HTML content', 'custom-email-content-for-woocommerce' ),
		'id'       => 'wecfw_cat_two_content',
		'type'     => 'textarea',
		'desc_tip' => __( 'Add custom HTML content here.', 'custom-email-content-for-woocommerce' ),
		'autoload' => false,
	);

	$settings = apply_filters( 'wecfw_add_email_additional_settings', $settings );

	// Close the settings.
	$settings[] = array(
		'type' => 'sectionend',
		'id'   => 'custom_email_options',
	);
	return $settings;
}
