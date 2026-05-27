<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly


/**
 * Used for testing to make sure the IPN can listen to URL calls.
 * @since 1.8
 * @return string
 */
add_action('template_redirect','cf7pp_ipn_test');
function cf7pp_ipn_test() {

	if (isset($_REQUEST['cf7pp_test'])) {
		echo __("Contact Form 7 - PayPal Add-on - Test Successful", 'contact-form-7-paypal-add-on');
		exit;
	}
}


/**
 * PayPal notify url.
 * @since 1.8
 * @return string or array
 */
function cf7pp_get_paypal_notify_url($return = 'str') {
	$options = cf7pp_free_options();
	$mode_paypal = $options['mode'] == '1' ? 'sandbox' : 'production';

	$namespace = 'paypalipn/v1';
	$route = '/cf7pp_' . $mode_paypal;

	if ($return == 'str') {
		$result = add_query_arg('rest_route', '/' . $namespace . $route, get_site_url());
	} else {
		$result = array(
			'namespace'	=> $namespace,
			'route'		=> $route
		);
	}

	return $result;
}


/**
 * Register PayPal IPN listener.
 * @since 1.8
 */
add_action('rest_api_init', 'cf7pp_paypal_ipn_listener');
function cf7pp_paypal_ipn_listener() {
	$notify_url = cf7pp_get_paypal_notify_url('arr');
    register_rest_route($notify_url['namespace'], $notify_url['route'], array(
        'methods' 				=> 'POST',
        'callback' 				=> 'cf7pp_paypal_ipn_handler',
        'permission_callback'	=> 'cf7pp_paypal_ipn_auth'
    ));
}


/**
 * PayPal IPN permission callback.
 * @since 1.8
 * @return bool
 */
function cf7pp_paypal_ipn_auth() {
	return true; // security done in the handler
}


/**
 * PayPal IPN handler.
 *
 * Note: This handles PayPal Standard IPN only. PayPal Commerce Platform (PPCP)
 * uses a separate flow in ppcp_frontend.php and does not go through this handler.
 *
 * @since 1.8
 */
function cf7pp_paypal_ipn_handler() {
	$payload = file_get_contents('php://input');
	parse_str($payload, $data);

	if (strtolower($data['payment_status']) == 'completed') {
		$options = cf7pp_free_options();
		// Use PayPal's dedicated IPN postback endpoint (ipnpb subdomain)
		$paypal_post_url = 'https://ipnpb.' . ($options['mode'] == '1' ? 'sandbox.' : '') . 'paypal.com/cgi-bin/webscr';

		$data['cmd'] = '_notify-validate';
		$args = array(
			'method'           => 'POST',
			'timeout'          => 45,
			'redirection'      => 5,
			'httpversion'      => '1.1',
			'blocking'         => true,
			'headers'          => array(
				'connection'   => 'close',
				'content-type' => 'application/x-www-form-urlencoded',
			),
			'sslverify'        => true,
			'body'             => 'cmd=_notify-validate&' . $payload
		);
			
		// Get response
		$response = wp_remote_post($paypal_post_url, $args);
		
		$status = is_wp_error($response) || strtolower($response['body']) != 'verified' ? 'failed' : 'completed';
		
		// Capture debug info for display on the payment edit screen
		$debug_info = array(
			'paypal_validation_response' => is_wp_error($response) ? 'WP_ERROR: ' . $response->get_error_message() : $response['body'],
			'status_after_validation' => $status,
			'receiver_email_from_ipn' => isset($data['receiver_email']) ? $data['receiver_email'] : 'NOT SET',
			'receiver_id_from_ipn' => isset($data['receiver_id']) ? $data['receiver_id'] : 'NOT SET',
			'business_from_ipn' => isset($data['business']) ? $data['business'] : 'NOT SET',
			'configured_liveaccount' => isset($options['liveaccount']) ? $options['liveaccount'] : 'NOT SET',
			'configured_sandboxaccount' => isset($options['sandboxaccount']) ? $options['sandboxaccount'] : 'NOT SET',
			'mode' => $options['mode'],
			'full_ipn_data' => $data,
		);
		
		// Security: Verify the payment was actually received by the configured PayPal account.
		// PayPal's "VERIFIED" response only confirms the IPN data is real, not that the payment
		// went to this site's PayPal account. Without this check, an attacker could replay any
		// real PayPal IPN with a forged "invoice" value to mark unrelated orders as paid.
		if ($status == 'completed') {
			$configured_account = $options['mode'] == '1' 
				? (isset($options['sandboxaccount']) ? trim($options['sandboxaccount']) : '')
				: (isset($options['liveaccount']) ? trim($options['liveaccount']) : '');
			
			$debug_info['configured_account_used'] = $configured_account;
			
			// No PayPal account configured - we cannot verify the recipient, so fail closed.
			if (empty($configured_account)) {
				$debug_info['compared_against'] = 'NO_ACCOUNT_CONFIGURED_FAIL_CLOSED';
				$debug_info['final_status'] = 'failed';
				error_log('cf7pp_paypal_ipn_handler: no PayPal account configured; rejecting IPN for order ' . (isset($data['invoice']) ? (int) $data['invoice'] : 0));
				http_response_code(200);
				return;
			}
			
			if (strpos($configured_account, '@') !== false) {
				// Configured account is an email - check against receiver_email (case-insensitive)
				$receiver = isset($data['receiver_email']) ? trim($data['receiver_email']) : '';
				$debug_info['compared_against'] = 'receiver_email';
				$debug_info['comparison_expected'] = $configured_account;
				$debug_info['comparison_received'] = $receiver;
				if (strcasecmp($configured_account, $receiver) !== 0) {
					$debug_info['comparison_result'] = 'MISMATCH';
					$debug_info['final_status'] = 'failed';
					error_log('cf7pp_paypal_ipn_handler: receiver_email mismatch. Expected: ' . $configured_account . ', Got: ' . $receiver);
					http_response_code(200);
					return;
				}
				$debug_info['comparison_result'] = 'MATCH';
			} else {
				// Configured account is a Merchant ID - check against receiver_id
				$receiver = isset($data['receiver_id']) ? trim($data['receiver_id']) : '';
				$debug_info['compared_against'] = 'receiver_id';
				$debug_info['comparison_expected'] = $configured_account;
				$debug_info['comparison_received'] = $receiver;
				if ($configured_account !== $receiver) {
					$debug_info['comparison_result'] = 'MISMATCH';
					$debug_info['final_status'] = 'failed';
					error_log('cf7pp_paypal_ipn_handler: receiver_id mismatch. Expected: ' . $configured_account . ', Got: ' . $receiver);
					http_response_code(200);
					return;
				}
				$debug_info['comparison_result'] = 'MATCH';
			}
		}
		
		// Security: Verify the amount and currency in the IPN match the stored payment record.
		// The receiver check alone does not stop an attacker who makes a genuine low-value payment
		// to the correct PayPal account while pointing "invoice" at a different high-value order.
		// Compare the actual amount paid (mc_gross) and currency (mc_currency) against the values
		// stored on the payment record at checkout creation time.
		if ($status == 'completed' && !empty($data['invoice'])) {
			$order_id        = (int) $data['invoice'];
			$stored_amount   = (float) get_post_meta($order_id, 'amount', true);
			$paid_amount     = isset($data['mc_gross']) ? (float) $data['mc_gross'] : -1;
			$paid_currency   = isset($data['mc_currency']) ? strtoupper(trim($data['mc_currency'])) : '';
			$stored_currency = strtoupper(trim((string) get_post_meta($order_id, 'currency', true)));
			
			$debug_info['amount_check_stored_amount']   = $stored_amount;
			$debug_info['amount_check_paid_amount']     = $paid_amount;
			$debug_info['amount_check_stored_currency'] = $stored_currency;
			$debug_info['amount_check_paid_currency']   = $paid_currency;
			
			// Allow a small float epsilon for rounding; reject if underpaid or currency mismatches.
			$amount_underpaid    = $paid_amount < ($stored_amount - 0.01);
			$currency_mismatch   = !empty($stored_currency) && $paid_currency !== $stored_currency;
			
			if ($amount_underpaid || $currency_mismatch) {
				$debug_info['amount_check_result'] = $amount_underpaid ? 'UNDERPAID' : 'CURRENCY_MISMATCH';
				error_log('cf7pp_paypal_ipn_handler: amount/currency mismatch for order ' . $order_id
					. '. Expected ' . $stored_amount . ' ' . $stored_currency
					. ', got ' . $paid_amount . ' ' . $paid_currency);
				$status = 'failed';
			} else {
				$debug_info['amount_check_result'] = 'MATCH';
			}
		}
		
		$debug_info['final_status'] = $status;
		
		// Save debug info to the payment record so it's visible in the edit screen
		if (!empty($data['invoice'])) {
			update_post_meta((int) $data['invoice'], '_cf7pp_ipn_debug', $debug_info);
		}
		
		cf7pp_complete_payment($data['invoice'], $status, $data['txn_id']);
		
		http_response_code(200);
		
	} else {
		$status = 'failed';
	}
	
}