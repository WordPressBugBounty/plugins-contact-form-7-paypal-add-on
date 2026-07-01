<?php
if (!defined('ABSPATH')) exit; // Exit if accessed directly

/**
 * Create Stripe webhook if not created yet when any redirect happens.
 * @since 1.8
 * @return associative array Plugin settings
 */
add_action('wpcf7_before_send_mail', 'cf7pp_insert_webhook_data_to_plugin_settings');
function cf7pp_insert_webhook_data_to_plugin_settings() {

	$options = cf7pp_free_options();

	switch ($options['mode_stripe']) {
		case '1':
			if (!empty($options['sec_key_test'])) {
				
				$webhook_data_test_orig = $options['webhook_data_test']['id'];
				
				$options['webhook_data_test'] = cf7pp_maybe_create_stripe_webhook($options['webhook_data_test'], $options['sec_key_test']);
				
				if ($webhook_data_test_orig != $options['webhook_data_test']['id']) {
					array_merge($options, $options['webhook_data_test']);
					cf7pp_free_options_update( $options );
				}
				break;
			}
			
		case '2':
			if (!empty($options['sec_key_live'])) {
				
				$webhook_data_live_orig = $options['webhook_data_live']['id'];
				
				$options['webhook_data_live'] = cf7pp_maybe_create_stripe_webhook($options['webhook_data_live'], $options['sec_key_live']);
				
				if ($webhook_data_live_orig != $options['webhook_data_live']['id']) {
					array_merge($options, $options['webhook_data_live']);
					cf7pp_free_options_update( $options );
				}
				break;
			}
	}
	
}

/**
 * Check $webhook_data to see if we need to create a new webhook and remove old
 * @since 1.8
 * @return string Stripe webhook id
 */
function cf7pp_maybe_create_stripe_webhook($webhook_data, $sk) {
	
	try {
	    $stripe = new \Stripe\StripeClient($sk);
	} catch (Exception $e) {
		error_log($e->getMessage());
		return $webhook_data;
	}

	$result = '';

	$url = cf7pp_get_stripe_webhook_url();
	
	$events = array(
		'checkout.session.completed'
	);
	
	// check if webhook exists
	if (!empty($webhook_data['id']) && !empty($webhook_data['secret'])) {
		try {
			$webhook = $stripe->webhookEndpoints->retrieve($webhook_data['id']);
			if (!empty($webhook->url) && $webhook->url == $url) {
				if (is_array($webhook->enabled_events) && $webhook->enabled_events == $events) {
					$result = $webhook_data;
				} else {					
					$stripe->webhookEndpoints->delete($webhook_data['id']);
				}
			}
		} catch (Exception $e) {
			error_log($e->getMessage());
		}
	}
	

	if (empty($result)) {
		// create webhook
		try {			
			$webhook = $stripe->webhookEndpoints->create([
				'url'				=> $url,
				'enabled_events'	=> $events,
				'connect'			=> false,
			]);
			
			$result = array(
				'id'		=> $webhook->id,
				'secret'	=> $webhook->secret
			);
		} catch (Exception $e) {
			echo esc_html( $e->getMessage() );
			error_log($e->getMessage());
		}
	}

	return $result;
}

/**
 * Stripe webhook url.
 * @since 1.8
 * @return string or array
 */
function cf7pp_get_stripe_webhook_url($return = 'str') {
	$options = cf7pp_free_options();
	$mode_stripe = $options['mode_stripe'] == '1' ? 'test' : 'live';

	$namespace = 'stripewebhooks/v1';
	$route = '/cf7pp_' . $mode_stripe;

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
 * Register Stripe webhook listener.
 * @since 1.8
 */
add_action('rest_api_init', 'cf7pp_stripe_webhook_listener');
function cf7pp_stripe_webhook_listener() {
	$webhook_url = cf7pp_get_stripe_webhook_url('arr');
    register_rest_route($webhook_url['namespace'], $webhook_url['route'], array(
        'methods' 				=> 'POST',
        'callback' 				=> 'cf7pp_stripe_webhook_handler',
        'permission_callback'	=> 'cf7pp_stripe_webhook_auth'
    ));
}

/**
 * Stripe webhook permission callback.
 * @since 1.8
 * @return bool
 */
function cf7pp_stripe_webhook_auth() {
	return true; // security done in the handler
}

/**
 * Stripe webhook handler.
 * @since 1.8
 */
function cf7pp_stripe_webhook_handler() {
	$options = cf7pp_free_options();
	$mode_stripe = $options['mode_stripe'] == '1' ? 'test' : 'live';
	$endpoint_secret = $options['webhook_data_' . $mode_stripe]['secret'];

    $payload = @file_get_contents('php://input');
    $sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
	$event = null;

    try {
        $event = \Stripe\Webhook::constructEvent(
        	$payload, $sig_header, $endpoint_secret
    	);
    } catch(\UnexpectedValueException $e) {
		// Invalid payload
		http_response_code(400);
		exit();
	} catch(\Stripe\Exception\SignatureVerificationException $e) {
		// Invalid signature
		http_response_code(400);
		exit();
	}

	// Handle the event
	switch ($event->type) {
		case 'checkout.session.completed':
			$payment_id = isset($event->data->object->client_reference_id) ? (int) $event->data->object->client_reference_id : 0;
			$status = $event->data->object->payment_status == 'paid' ? 'completed' : 'failed';
			$transaction_id = $event->data->object->payment_intent;
			
			// Security: Verify the amount paid matches the stored payment record's amount.
			// The client_reference_id is set from a user-controlled URL parameter at checkout
			// creation, so an attacker could complete a small checkout while pointing it at a
			// different high-value pending payment record. The webhook itself is authentic and
			// signed, but the linkage value inside it can be poisoned by the attacker. Comparing
			// the actual amount paid to the stored payment amount blocks this mismatch.
			if ($status == 'completed' && $payment_id > 0) {
				$stored_amount = (float) get_post_meta($payment_id, 'amount', true);
				$paid_amount = isset($event->data->object->amount_total) ? (int) $event->data->object->amount_total : 0;
				$paid_currency = isset($event->data->object->currency) ? strtoupper($event->data->object->currency) : '';
				
				// Convert stored amount to the smallest currency unit (cents) for comparison.
				// JPY does not use decimals so the amount is already in the smallest unit.
				$expected_amount = $paid_currency == 'JPY' ? (int) $stored_amount : (int) round($stored_amount * 100);
				
				if ($paid_amount < $expected_amount) {
					error_log('cf7pp_stripe_webhook_handler: Amount mismatch for payment ID ' . $payment_id . '. Expected: ' . $expected_amount . ', Got: ' . $paid_amount . ' ' . $paid_currency);
					$status = 'failed';
				}
			}
			
         	cf7pp_complete_payment($payment_id, $status, $transaction_id);
            break;

		default:
			http_response_code(400);
			exit();
	}

	http_response_code(200);
}

/**
 * Stripe Connect webhook url.
 * @since 1.8
 * @return string or array
 */
function cf7pp_get_stripe_connect_webhook_url($return = 'str') {
	$arg = 'cf7pp_notice';
	$val = 'stripewebhook';

	if ($return == 'str') {
		$result = add_query_arg($arg, $val, get_site_url());
	} else {
		$result = array(
			'arg'	=> $arg,
			'val'	=> $val
		);
	}

	return $result;
}

/**
 * Register Stripe Connect webhook listener.
 * @since 1.8
 */
add_action('plugins_loaded', 'cf7pp_stripe_connect_webhook_listener');
function cf7pp_stripe_connect_webhook_listener() {
	// check if webhook endpoint
	$webhook_url = cf7pp_get_stripe_connect_webhook_url('arr');
	if (!isset($_REQUEST[$webhook_url['arg']]) || $_REQUEST[$webhook_url['arg']] != $webhook_url['val']) return;

	// check required arguments
	if (!isset($_REQUEST['payment_id']) || !isset($_REQUEST['status']) || !isset($_REQUEST['transaction_id']) || !isset($_REQUEST['mode']) || !isset($_REQUEST['token'])) return;

	$options = cf7pp_free_options();
	if ($_REQUEST['mode'] == 'live') {
		$token = isset($options['stripe_connect_token_live']) ? $options['stripe_connect_token_live'] : '';
	} else {
		$token = isset($options['stripe_connect_token_test']) ? $options['stripe_connect_token_test'] : '';
	}

	// check token
	if (empty($_REQUEST['token']) || $_REQUEST['token'] != $token) return;

	$result = cf7pp_complete_payment($_REQUEST['payment_id'], $_REQUEST['status'], $_REQUEST['transaction_id']);

	wp_send_json(array(
		'result'	=> $result ? 'success' : 'fail'
	));
}