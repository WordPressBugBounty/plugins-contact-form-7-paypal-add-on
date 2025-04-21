<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


// hook into contact form 7 form
function cf7pp_editor_panels ( $panels ) {

	$new_page = array(
		'PayPal' => array(
			'title' => __( 'PayPal & Stripe', 'cf7pp' ),
			'callback' => 'cf7pp_admin_after_additional_settings'
		)
	);

	$panels = array_merge($panels, $new_page);

	return $panels;

}
add_filter( 'wpcf7_editor_panels', 'cf7pp_editor_panels' );


function cf7pp_admin_after_additional_settings( $cf7 ) {

	$post_id = sanitize_text_field($_GET['post']);

	$enable = 					get_post_meta($post_id, "_cf7pp_enable", true);
	$enable_stripe = 			get_post_meta($post_id, "_cf7pp_enable_stripe", true);
	$name = 					get_post_meta($post_id, "_cf7pp_name", true);
	$price = 					get_post_meta($post_id, "_cf7pp_price", true);
	$id = 						get_post_meta($post_id, "_cf7pp_id", true);
	$gateway = 					get_post_meta($post_id, "_cf7pp_gateway", true);
	$stripe_email = 			get_post_meta($post_id, "_cf7pp_stripe_email", true);

	if ($enable == "1") { $checked = "CHECKED"; } else { $checked = ""; }
	if ($enable_stripe == "1") { $checked_stripe = "CHECKED"; } else { $checked_stripe = ""; }

	$admin_table_output = "";
	$admin_table_output .= "<h2>" . __("PayPal & Stripe Settings", 'cf7pp') . "</h2>";

	$admin_table_output .= "<div class='mail-field'></div>";
	
	$admin_table_output .= "<table><tr>";
	
	$admin_table_output .= "<td width='195px'><label>" . __("Enable PayPal on this form:", 'cf7pp') . " </label></td>";
	$admin_table_output .= "<td width='250px'><input name='cf7pp_enable' value='1' type='checkbox' $checked></td></tr>";

	$admin_table_output .= "<td><label>" . __("Enable Stripe on this form", 'cf7pp') . "</label></td>";
	$admin_table_output .= "<td><input name='cf7pp_enable_stripe' value='1' type='checkbox' $checked_stripe></td></tr>";

	$admin_table_output .= "<tr><td>" . __("Gateway Code:", 'cf7pp') . " </td>";
	$admin_table_output .= "<td><input type='text' name='cf7pp_gateway' value='$gateway'> </td><td> (" . __("Required to use both Gateways at the same time. Documentation", 'cf7pp') . " <a target='_blank' href='https://wpplugin.org/documentation/paypal-stripe-gateway-code/'>" . __("here", 'cf7pp') . "</a>. " . __("Example: menu-231", 'cf7pp') . ")</td></tr><tr><td>";

	$admin_table_output .= "<tr><td>" . __("Email Code:", 'cf7pp') . " </td>";
	$admin_table_output .= "<td><input type='text' name='cf7pp_stripe_email' value='$stripe_email'> </td><td> (" . __("Optional. Pass email to Stripe. Example: text-105", 'cf7pp') . ")</td></tr><tr><td colspan='3'><br />";


	$admin_table_output .= "<hr></td></tr>";

	$admin_table_output .= "<tr><td>" . __("Item Description:", 'cf7pp') . " </td>";
	$admin_table_output .= "<td><input type='text' name='cf7pp_name' value='$name'> </td><td> (" . __("Optional", 'cf7pp') . ")</td></tr>";

	$admin_table_output .= "<tr><td>" . __("Item Price:", 'cf7pp') . " </td>";
	$admin_table_output .= "<td><input type='text' name='cf7pp_price' value='$price'> </td><td> (" . __("Format: for $2.99, enter 2.99", 'cf7pp') . ")</td></tr>";

	$admin_table_output .= "<tr><td>" . __("Item ID / SKU:", 'cf7pp') . " </td>";
	$admin_table_output .= "<td><input type='text' name='cf7pp_id' value='$id'> </td><td> (" . __("Optional", 'cf7pp') . ")</td></tr>";
	
	$admin_table_output .= "<input type='hidden' name='cf7pp_post' value='" . esc_attr($post_id) . "'>";

	$admin_table_output .= "</td></tr></table>";

	echo $admin_table_output;

}






// hook into contact form 7 admin form save
add_action('wpcf7_after_save', 'cf7pp_save_contact_form');

function cf7pp_save_contact_form( $cf7 ) {
		
		$post_id = sanitize_text_field($_POST['cf7pp_post']);
		
		if (!empty($_POST['cf7pp_enable'])) {
			$enable = sanitize_text_field($_POST['cf7pp_enable']);
			update_post_meta($post_id, "_cf7pp_enable", $enable);
		} else {
			update_post_meta($post_id, "_cf7pp_enable", 0);
		}
		
		if (!empty($_POST['cf7pp_enable_stripe'])) {
			$enable_stripe = sanitize_text_field($_POST['cf7pp_enable_stripe']);
			update_post_meta($post_id, "_cf7pp_enable_stripe", $enable_stripe);
		} else {
			update_post_meta($post_id, "_cf7pp_enable_stripe", 0);
		}
		
		$name = sanitize_text_field($_POST['cf7pp_name']);
		update_post_meta($post_id, "_cf7pp_name", $name);
		
		$price = sanitize_text_field($_POST['cf7pp_price']);
		$price = cf7pp_format_currency($price);
		update_post_meta($post_id, "_cf7pp_price", $price);
		
		$id = sanitize_text_field($_POST['cf7pp_id']);
		update_post_meta($post_id, "_cf7pp_id", $id);
		
		$gateway = sanitize_text_field($_POST['cf7pp_gateway']);
		update_post_meta($post_id, "_cf7pp_gateway", $gateway);
		
		$stripe_email = sanitize_text_field($_POST['cf7pp_stripe_email']);
		update_post_meta($post_id, "_cf7pp_stripe_email", $stripe_email);

}
