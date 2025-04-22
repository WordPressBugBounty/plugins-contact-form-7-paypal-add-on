<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly


// admin table
function cf7pp_admin_table() {

	// get options
	$options = cf7pp_free_options();

	if ( !current_user_can( "manage_options" ) )  {
	wp_die( __( "You do not have sufficient permissions to access this page.", 'contact-form-7-paypal-add-on' ) );
	}



	// save and update options
	if (isset($_POST['update'])) {
		
		$error = false;
		
		if ( empty( $_POST['cf7pp_nonce_field'] ) || !wp_verify_nonce( $_POST['cf7pp_nonce_field'], 'cf7pp_save_settings') ) {
			wp_die( __( "You do not have sufficient permissions to access this page.", 'contact-form-7-paypal-add-on' ) );
		}
		
		$options['currency'] = 					sanitize_text_field($_POST['currency']);
		if (empty($options['currency'])) { 		$options['currency'] = ''; }

		$options['language'] = 					sanitize_text_field($_POST['language']);
		if (empty($options['language'])) { 		$options['language'] = ''; }

		$options['mode'] = 						sanitize_text_field($_POST['mode']);
		if (empty($options['mode'])) { 			$options['mode'] = '2'; }

		$options['mode_stripe'] = 				sanitize_text_field($_POST['mode_stripe']);
		if (empty($options['mode_stripe'])) { 	$options['mode_stripe'] = '2'; }

		$options['cancel'] = 					sanitize_text_field($_POST['cancel']);
		if (empty($options['cancel'])) { 		$options['cancel'] = ''; }

		$options['return'] = 					sanitize_text_field($_POST['return']);
		if (empty($options['return'])) { 		$options['return'] = ''; }

		$options['redirect'] = 					sanitize_text_field($_POST['redirect']);
		if (empty($options['redirect'])) { 		$options['redirect'] = '1'; }
		
		$options['request_method'] = 					sanitize_text_field($_POST['request_method']);
		if (empty($options['request_method'])) { 		$options['request_method'] = '1'; }
		
		$options['session'] = 					sanitize_text_field($_POST['session']);
		if (empty($options['session'])) { 		$options['session'] = '1'; }

		$options['stripe_return'] = 			sanitize_text_field($_POST['stripe_return']);
		if (empty($options['stripe_return'])) { $options['stripe_return'] = ''; }
		
		$options['success'] = 					sanitize_text_field($_POST['success']);
		if (empty($options['success'])) { 		$options['success'] = __('Payment Successful', 'contact-form-7-paypal-add-on'); }
		
		$options['failed'] = 					sanitize_text_field($_POST['failed']);
		if (empty($options['failed'])) { 		$options['failed'] = __('Payment Failed', 'contact-form-7-paypal-add-on'); }
		
		
		if (
			(!empty($options['cancel']) && !filter_var($options['cancel'], FILTER_VALIDATE_URL)) ||
			(!empty($options['stripe_return']) && !filter_var($options['stripe_return'], FILTER_VALIDATE_URL)) ||
			(!empty($options['return']) && !filter_var($options['return'], FILTER_VALIDATE_URL))
		) {
			?>
			<script>
				window.location.replace("?page=cf7pp_admin_table&tab=6&err=1");
			</script>
			<?php
			$error = true;
		}
		
		
		if ($error == false) { 
			cf7pp_free_options_update( $options );
			?>
			<script>
				window.location.replace("?page=cf7pp_admin_table&tab=<?php echo isset($_POST['hidden_tab_value']) ? (int)$_POST['hidden_tab_value'] : $active_tab; ?>&saved=1");
			</script>
			<?php
		}

	}



	if (isset($_POST['hidden_tab_value'])) {
		$active_tab =  (int) $_POST['hidden_tab_value'];
	} else {
		$active_tab = isset( $_GET[ 'tab' ] ) ? $_GET[ 'tab' ] : '1';
		$active_tab = (int) $active_tab;
	}
	
	if ($active_tab == 0) {
		$active_tab = 1;
	}

	?>


<form method='post'>


		<?php
		if (isset($_GET['err']) && sanitize_text_field($_GET['err'])) {
			echo "<br /><div class='error'><p><strong>"; _e(" Error: PayPal and Stripe \"Cancel\" and \"Return\" options must be full URLs that start with http:// or https://", 'contact-form-7-paypal-add-on'); echo "</strong></p></div>";
			$error = true;
		}

		if (isset($_GET['saved']) && sanitize_text_field($_GET['saved'])) {
			echo "<br /><div class='updated'><p><strong>"; _e("Settings Updated", 'contact-form-7-paypal-add-on'); echo "</strong></p></div>";
		}
		?>

	<table width='70%'><tr><td>
	<div class='wrap'><h2><?php _e('Contact Form 7 - PayPal & Stripe Settings', 'contact-form-7-paypal-add-on'); ?></h2></div><br /></td><td><br />
	<input type='submit' name='btn2' class='button-primary' style='font-size: 17px;line-height: 28px;height: 32px;float: right;' value='<?php _e('Save Settings', 'contact-form-7-paypal-add-on'); ?>'>
	</td></tr></table>

	<table width='100%'><tr><td width='70%' valign='top'>




		<h2 class="nav-tab-wrapper">
			<a onclick='closetabs("1,3,4,5,6,7");newtab("1");' href="#" id="id1" class="nav-tab <?php echo $active_tab == '1' ? 'nav-tab-active' : ''; ?>"><?php _e('Getting Started', 'contact-form-7-paypal-add-on'); ?></a>
			<a onclick='closetabs("1,3,4,5,6,7");newtab("3");' href="#" id="id3" class="nav-tab <?php echo $active_tab == '3' ? 'nav-tab-active' : ''; ?>"><?php _e('Language & Currency', 'contact-form-7-paypal-add-on'); ?></a>
			<a onclick='closetabs("1,3,4,5,6,7");newtab("4");' href="#" id="id4" class="nav-tab <?php echo $active_tab == '4' ? 'nav-tab-active' : ''; ?>"><?php _e('PayPal', 'contact-form-7-paypal-add-on'); ?></a>
			<a onclick='closetabs("1,3,4,5,6,7");newtab("5");' href="#" id="id5" class="nav-tab <?php echo $active_tab == '5' ? 'nav-tab-active' : ''; ?>"><?php _e('Stripe', 'contact-form-7-paypal-add-on'); ?></a>
			<a onclick='closetabs("1,3,4,5,6,7");newtab("6");' href="#" id="id6" class="nav-tab <?php echo $active_tab == '6' ? 'nav-tab-active' : ''; ?>"><?php _e('Other', 'contact-form-7-paypal-add-on'); ?></a>
			<a onclick='closetabs("1,3,4,5,6,7");newtab("7");' href="#" id="id7" class="nav-tab <?php echo $active_tab == '7' ? 'nav-tab-active' : ''; ?>"><?php _e('Extensions', 'contact-form-7-paypal-add-on'); ?></a>
		</h2>
		<br />




	</td><td colspan='3'></td></tr><tr><td valign='top'>









	<div id="1" style="display:none;border: 1px solid #CCCCCC;<?php echo $active_tab == '1' ? 'display:block;' : ''; ?>">
		<div style="background-color:#E4E4E4;padding:8px;color:#000;font-size:15px;color:#464646;font-weight: 700;border-bottom: 1px solid #CCCCCC;">
			&nbsp; <?php _e('Getting Started', 'contact-form-7-paypal-add-on'); ?>
		</div>
		<div style="background-color:#fff;padding:8px;">
		
			<h3><?php _e('Important - If your form has trouble redirecting - try', 'contact-form-7-paypal-add-on'); ?> <a href='admin.php?page=cf7pp_admin_table&tab=6'><?php _e('changing these settings', 'contact-form-7-paypal-add-on'); ?></a>. <?php _e('This will fix most issues easily.', 'contact-form-7-paypal-add-on'); ?></h3>
			
			<hr>
			
			<?php _e('This plugin allows you to accept payments through your Contact Form 7 forms.', 'contact-form-7-paypal-add-on'); ?>
			
			<br /><br />
			
			<?php _e('On this page, you can setup your general PayPal & Stripe settings which will be used for all of your', 'contact-form-7-paypal-add-on'); ?> <a href='admin.php?page=wpcf7'><?php _e('Contact Form 7 forms', 'contact-form-7-paypal-add-on'); ?></a>.
			
			<br /><br />
			
			<?php _e('When you go to your list of contact forms, make a new form or edit an existing form, you will see a new tab called \'PayPal & Stripe\'. Here you can set individual settings for that specific contact form.', 'contact-form-7-paypal-add-on'); ?>
			
			<br /><br />
			
			<?php _e('Once you have PayPal or Stripe enabled on a form, you will receive an email as soon as the customer submits the form. You can view the payment status on the', 'contact-form-7-paypal-add-on'); ?> <a href='edit.php?post_type=cf7pp_payments'><?php _e('PayPal & Stripe Payments page', 'contact-form-7-paypal-add-on'); ?></a>.
			
			<br /><br />
			
			<?php _e('You can view documentation for this plugin', 'contact-form-7-paypal-add-on'); ?> <a target='_blank' href='https://wpplugin.org/knowledgebase_category/contact-form-7-paypal-stripe-add-on-free/'><?php _e('here', 'contact-form-7-paypal-add-on'); ?></a>.
			
			<br /><br />
			
			<?php _e('If you need support, please post your question', 'contact-form-7-paypal-add-on'); ?> <a target='_blank' href='https://wordpress.org/support/plugin/contact-form-7-paypal-add-on/'><?php _e('here', 'contact-form-7-paypal-add-on'); ?></a>.
			
			<br /><br />
			
			<?php _e('A lot of work went into building this plugin. If you enjoy it, please leave a 5 star review', 'contact-form-7-paypal-add-on'); ?> <a target='_blank' href='https://wordpress.org/support/plugin/contact-form-7-paypal-add-on/reviews/?filter=5#new-post'><?php _e('here', 'contact-form-7-paypal-add-on'); ?></a>.
			
			<br />
			
		</div>
	</div>



	<div id="3" style="display:none;border: 1px solid #CCCCCC;<?php echo $active_tab == '3' ? 'display:block;' : ''; ?>">
		<div style="background-color:#E4E4E4;padding:8px;color:#000;font-size:15px;color:#464646;font-weight: 700;border-bottom: 1px solid #CCCCCC;">
			&nbsp; <?php _e('Language & Currency', 'contact-form-7-paypal-add-on'); ?>
		</div>
		<div style="background-color:#fff;padding:8px;">

			<table>

				<tr><td class='cf7pp_width'>
					<b><?php _e('Language:', 'contact-form-7-paypal-add-on'); ?></b>
				</td><td>
					<select name="language">
					<option <?php if ($options['language'] == "1") { echo "SELECTED"; } ?> value="1"><?php _e('Danish', 'contact-form-7-paypal-add-on'); ?></option>
					<option <?php if ($options['language'] == "2") { echo "SELECTED"; } ?> value="2"><?php _e('Dutch', 'contact-form-7-paypal-add-on'); ?></option>
					<option <?php if ($options['language'] == "3") { echo "SELECTED"; } ?> value="3"><?php _e('English', 'contact-form-7-paypal-add-on'); ?></option>
					<option <?php if ($options['language'] == "20") { echo "SELECTED"; } ?> value="20"><?php _e('English - UK', 'contact-form-7-paypal-add-on'); ?></option>
					<option <?php if ($options['language'] == "4") { echo "SELECTED"; } ?> value="4"><?php _e('French', 'contact-form-7-paypal-add-on'); ?></option>
					<option <?php if ($options['language'] == "5") { echo "SELECTED"; } ?> value="5"><?php _e('German', 'contact-form-7-paypal-add-on'); ?></option>
					<option <?php if ($options['language'] == "6") { echo "SELECTED"; } ?> value="6"><?php _e('Hebrew', 'contact-form-7-paypal-add-on'); ?></option>
					<option <?php if ($options['language'] == "7") { echo "SELECTED"; } ?> value="7"><?php _e('Italian', 'contact-form-7-paypal-add-on'); ?></option>
					<option <?php if ($options['language'] == "8") { echo "SELECTED"; } ?> value="8"><?php _e('Japanese', 'contact-form-7-paypal-add-on'); ?></option>
					<option <?php if ($options['language'] == "9") { echo "SELECTED"; } ?> value="9"><?php _e('Norwgian', 'contact-form-7-paypal-add-on'); ?></option>
					<option <?php if ($options['language'] == "10") { echo "SELECTED"; } ?> value="10"><?php _e('Polish', 'contact-form-7-paypal-add-on'); ?></option>
					<option <?php if ($options['language'] == "11") { echo "SELECTED"; } ?> value="11"><?php _e('Portuguese', 'contact-form-7-paypal-add-on'); ?></option>
					<option <?php if ($options['language'] == "12") { echo "SELECTED"; } ?> value="12"><?php _e('Russian', 'contact-form-7-paypal-add-on'); ?></option>
					<option <?php if ($options['language'] == "13") { echo "SELECTED"; } ?> value="13"><?php _e('Spanish', 'contact-form-7-paypal-add-on'); ?></option>
					<option <?php if ($options['language'] == "14") { echo "SELECTED"; } ?> value="14"><?php _e('Swedish', 'contact-form-7-paypal-add-on'); ?></option>
					<option <?php if ($options['language'] == "15") { echo "SELECTED"; } ?> value="15"><?php _e('Simplified Chinese -China only', 'contact-form-7-paypal-add-on'); ?></option>
					<option <?php if ($options['language'] == "16") { echo "SELECTED"; } ?> value="16"><?php _e('Traditional Chinese - Hong Kong only', 'contact-form-7-paypal-add-on'); ?></option>
					<option <?php if ($options['language'] == "17") { echo "SELECTED"; } ?> value="17"><?php _e('Traditional Chinese - Taiwan only', 'contact-form-7-paypal-add-on'); ?></option>
					<option <?php if ($options['language'] == "18") { echo "SELECTED"; } ?> value="18"><?php _e('Turkish', 'contact-form-7-paypal-add-on'); ?></option>
					<option <?php if ($options['language'] == "19") { echo "SELECTED"; } ?> value="19"><?php _e('Thai', 'contact-form-7-paypal-add-on'); ?></option>
					</select>
			</td></tr>

				<tr><td>
				</td></tr>

				<tr><td class='cf7pp_width'>
				<b><?php _e('Currency:', 'contact-form-7-paypal-add-on'); ?></b></td><td>
				<select name="currency">
				<option <?php if ($options['currency'] == "1") { echo "SELECTED"; } ?> value="1"><?php _e('Australian Dollar - AUD', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "2") { echo "SELECTED"; } ?> value="2"><?php _e('Brazilian Real - BRL', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "3") { echo "SELECTED"; } ?> value="3"><?php _e('Canadian Dollar - CAD', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "4") { echo "SELECTED"; } ?> value="4"><?php _e('Czech Koruna - CZK', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "5") { echo "SELECTED"; } ?> value="5"><?php _e('Danish Krone - DKK', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "6") { echo "SELECTED"; } ?> value="6"><?php _e('Euro - EUR', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "7") { echo "SELECTED"; } ?> value="7"><?php _e('Hong Kong Dollar - HKD', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "8") { echo "SELECTED"; } ?> value="8"><?php _e('Hungarian Forint - HUF', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "9") { echo "SELECTED"; } ?> value="9"><?php _e('Israeli New Sheqel - ILS', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "10") { echo "SELECTED"; } ?> value="10"><?php _e('Japanese Yen - JPY', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "11") { echo "SELECTED"; } ?> value="11"><?php _e('Malaysian Ringgit - MYR', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "12") { echo "SELECTED"; } ?> value="12"><?php _e('Mexican Peso - MXN', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "13") { echo "SELECTED"; } ?> value="13"><?php _e('Norwegian Krone - NOK', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "14") { echo "SELECTED"; } ?> value="14"><?php _e('New Zealand Dollar - NZD', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "15") { echo "SELECTED"; } ?> value="15"><?php _e('Philippine Peso - PHP', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "16") { echo "SELECTED"; } ?> value="16"><?php _e('Polish Zloty - PLN', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "17") { echo "SELECTED"; } ?> value="17"><?php _e('Pound Sterling - GBP', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "26") { echo "SELECTED"; } ?> value="26"><?php _e('Romanian Leu - RON (Stripe Only)', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "18") { echo "SELECTED"; } ?> value="18"><?php _e('Russian Ruble - RUB', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "19") { echo "SELECTED"; } ?> value="19"><?php _e('Singapore Dollar - SGD', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "20") { echo "SELECTED"; } ?> value="20"><?php _e('Swedish Krona - SEK', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "21") { echo "SELECTED"; } ?> value="21"><?php _e('Swiss Franc - CHF', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "22") { echo "SELECTED"; } ?> value="22"><?php _e('Taiwan New Dollar - TWD', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "23") { echo "SELECTED"; } ?> value="23"><?php _e('Thai Baht - THB', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "24") { echo "SELECTED"; } ?> value="24"><?php _e('Turkish Lira - TRY', 'contact-form-7-paypal-add-on'); ?></option>
				<option <?php if ($options['currency'] == "25") { echo "SELECTED"; } ?> value="25"><?php _e('U.S. Dollar - USD', 'contact-form-7-paypal-add-on'); ?></option>
				</select></td></tr>

			</table>

		</div>
	</div>




	<div id="4" style="display:none;border: 1px solid #CCCCCC;<?php echo $active_tab == '4' ? 'display:block;' : ''; ?>">
		<div style="background-color:#E4E4E4;padding:8px;color:#000;font-size:15px;color:#464646;font-weight: 700;border-bottom: 1px solid #CCCCCC;">
		&nbsp; <?php _e('PayPal Account', 'contact-form-7-paypal-add-on'); ?>
		</div>
		<div style="background-color:#fff;padding:8px;">

            <?php echo cf7pp_free_ppcp_status_markup(); ?>

			<table width='100%'>
                <tr><td colspan='2'><br /></td></tr>

                <?php if ( !empty( $options['liveaccount'] ) ) { ?>
				<tr><td class='cf7pp_width'>
				<b><?php _e('Live Account: ', 'contact-form-7-paypal-add-on'); ?></b></td><td><input type='text' size=40 name='liveaccount' value='<?php echo $options['liveaccount']; ?>' readonly />
				</td></tr>

				<tr><td class='cf7pp_width'></td><td>
				<br /><?php _e('Enter a valid Merchant account ID (strongly recommend) or PayPal account email address. All payments will go to this account.', 'contact-form-7-paypal-add-on'); ?>
				<br /><br /><?php _e('You can find your Merchant account ID in your PayPal account under Profile -> My business info -> Merchant account ID', 'contact-form-7-paypal-add-on'); ?>

				<br /><br /><?php _e('If you don\'t have a PayPal account, you can sign up for free at', 'contact-form-7-paypal-add-on'); ?> <a target='_blank' href='https://paypal.com'><?php _e('PayPal', 'contact-form-7-paypal-add-on'); ?></a>. <br /><br />
				</td></tr>
                <?php } ?>

	            <?php if ( !empty( $options['sandboxaccount'] ) ) { ?>
				<tr><td class='cf7pp_width'>
				<b><?php _e('Sandbox Account: ', 'contact-form-7-paypal-add-on'); ?></b></td><td><input type='text' size=40 name='sandboxaccount' value='<?php echo $options['sandboxaccount']; ?>' readonly />
				</td></tr>

				<tr><td class='cf7pp_width'></td><td>
				<?php _e('Enter a valid sandbox PayPal account email address. A Sandbox account is a PayPal accont with fake money used for testing. This is useful to make sure your PayPal account and settings are working properly being going live.', 'contact-form-7-paypal-add-on'); ?>
				<br /><br /><?php _e('To create a Sandbox account, you first need a Developer Account. You can sign up for free at the', 'contact-form-7-paypal-add-on'); ?> <a target='_blank' href='https://www.paypal.com/webapps/merchantboarding/webflow/unifiedflow?execution=e1s2'><?php _e('PayPal Developer', 'contact-form-7-paypal-add-on'); ?></a> <?php _e('site.', 'contact-form-7-paypal-add-on'); ?> <br /><br />

				<?php _e('Once you have made an account, create a Sandbox Business and Personal Account', 'contact-form-7-paypal-add-on'); ?> <a target='_blank' href='https://developer.paypal.com/webapps/developer/applications/accounts'><?php _e('here', 'contact-form-7-paypal-add-on'); ?></a>. <?php _e('Enter the Business acount email on this page and use the Personal account username and password to buy something on your site as a customer.', 'contact-form-7-paypal-add-on'); ?>
				<br /><br />
				</td></tr>
	            <?php } ?>

				<tr><td class='cf7pp_width'>
				<b><?php _e('Sandbox Mode:', 'contact-form-7-paypal-add-on'); ?></b></td><td>
				<input <?php if ($options['mode'] == "1") { echo "checked='checked'"; } ?> type='radio' name='mode' value='1'><?php _e('On (Sandbox mode)', 'contact-form-7-paypal-add-on'); ?>
				<input <?php if ($options['mode'] == "2") { echo "checked='checked'"; } ?> type='radio' name='mode' value='2'><?php _e('Off (Live mode)', 'contact-form-7-paypal-add-on'); ?>
				</td></tr>

			</table>

		</div>
	</div>




	<div id="5" style="display:none;border: 1px solid #CCCCCC;<?php echo $active_tab == '5' ? 'display:block;' : ''; ?>">
		<div style="background-color:#E4E4E4;padding:8px;color:#000;font-size:15px;color:#464646;font-weight: 700;border-bottom: 1px solid #CCCCCC;">
		&nbsp; <?php _e('Stripe Account', 'contact-form-7-paypal-add-on'); ?>
		</div>
		<div style="background-color:#fff;padding:8px;">

			<table width='100%'>
				<tr><td class='cf7pp_width'><b><?php _e('Connection status:', 'contact-form-7-paypal-add-on'); ?> </b></td><td><?php cf7pp_stripe_connection_status_html(); ?></td></tr>

				<tr><td colspan="2"><br /></td></tr>

				<?php if ( !empty($options['pub_key_live']) && !empty($options['sec_key_live']) ) { ?>
				<tr><td class='cf7pp_width'><b><?php _e('Live Publishable Key:', 'contact-form-7-paypal-add-on'); ?> </b></td><td><input type='text' size=40 name='pub_key_live' value='<?php echo $options['pub_key_live']; ?>' disabled="disabled"></td></tr>
				<tr><td class='cf7pp_width'><b><?php _e('Live Secret Key:', 'contact-form-7-paypal-add-on'); ?> </b></td><td><input type='text' size=40 name='sec_key_live' value='<?php echo $options['sec_key_live']; ?>' disabled="disabled"></td></tr>
				<tr><td colspan="2"><br /></td></tr>
				<?php } ?>

				<?php if ( !empty($options['pub_key_test']) && !empty($options['sec_key_test']) ) { ?>
				<tr><td class='cf7pp_width'><b><?php _e('Test Publishable Key:', 'contact-form-7-paypal-add-on'); ?> </b></td><td><input type='text' size=40 name='pub_key_test' value='<?php echo $options['pub_key_test']; ?>' disabled="disabled"></td></tr>
				<tr><td class='cf7pp_width'><b><?php _e('Test Secret Key:', 'contact-form-7-paypal-add-on'); ?> </b></td><td><input type='text' size=40 name='sec_key_test' value='<?php echo $options['sec_key_test']; ?>' disabled="disabled"></td></tr>
				<tr><td colspan="2"><br /></td></tr>
				<?php } ?>

				<tr><td class='cf7pp_width'><b><?php _e('Sandbox Mode:', 'contact-form-7-paypal-add-on'); ?></b></td><td>

				<input <?php if ($options['mode_stripe'] == "1") { echo "checked='checked'"; } ?> type='radio' name='mode_stripe' value='1'><?php _e('On (Sandbox mode)', 'contact-form-7-paypal-add-on'); ?>
				<input <?php if ($options['mode_stripe'] == "2") { echo "checked='checked'"; } ?> type='radio' name='mode_stripe' value='2'><?php _e('Off (Live mode)', 'contact-form-7-paypal-add-on'); ?></td></tr>


				<tr><td>
				<br />
				</td></tr>

				<tr><td class='cf7pp_width'><b><?php _e('Default Text:', 'contact-form-7-paypal-add-on'); ?> </b></td><td></td></tr>
				<tr><td class='cf7pp_width'><b><?php _e('Payment Successful:', 'contact-form-7-paypal-add-on'); ?> </b></td><td><input type='text' size='40' name='success' value='<?php echo esc_attr($options['success']); ?>'></td></tr>
				<tr><td class='cf7pp_width'><b><?php _e('Payment Failed:', 'contact-form-7-paypal-add-on'); ?> </b></td><td><input type='text' size='40' name='failed' value='<?php echo esc_attr($options['failed']); ?>'></td></tr>
				
			</table>

		</div>
	</div>


	<div id="6" style="display:none;border: 1px solid #CCCCCC;<?php echo $active_tab == '6' ? 'display:block;' : ''; ?>">
		<div style="background-color:#E4E4E4;padding:8px;font-size:15px;color:#464646;font-weight: 700;border-bottom: 1px solid #CCCCCC;">
			&nbsp; <?php _e('Other Settings', 'contact-form-7-paypal-add-on'); ?>
		</div>
		<div style="background-color:#fff;padding:8px;">

			<table style="width: 100%;">

				<tr><td class='cf7pp_width'><b><?php _e('PayPal Cancel URL:', 'contact-form-7-paypal-add-on'); ?> </b></td><td><input type='text' name='cancel' value='<?php echo $options['cancel']; ?>'> <?php _e('Optional', 'contact-form-7-paypal-add-on'); ?> <br /></td></tr>
				<tr><td class='cf7pp_width'></td><td><?php _e('If the customer goes to PayPal and clicks the cancel button, where do they go. Example: http://example.com/cancel. Max length: 1,024. ', 'contact-form-7-paypal-add-on'); ?></td></tr>

				<tr><td>
				<br />
				</td></tr>

				<tr><td class='cf7pp_width'><b><?php _e('PayPal Return URL:', 'contact-form-7-paypal-add-on'); ?> </b></td><td><input type='text' name='return' value='<?php echo $options['return']; ?>'> <?php _e('Optional', 'contact-form-7-paypal-add-on'); ?> <br /></td></tr>
				<tr><td class='cf7pp_width'></td><td><?php _e('If the customer goes to PayPal and successfully pays, where are they redirected to after. Example: http://example.com/thankyou. Max length: 1,024. ', 'contact-form-7-paypal-add-on'); ?></td></tr>
				
				<tr><td>
				<br />
				</td></tr>
				
				<tr><td class='cf7pp_width'><b><?php _e('Stripe Return URL:', 'contact-form-7-paypal-add-on'); ?> </b></td><td><input type='text' name='stripe_return' value='<?php echo $options['stripe_return']; ?>'> <?php _e('Optional', 'contact-form-7-paypal-add-on'); ?> <br /></td></tr>
				<tr><td class='cf7pp_width'></td><td><?php _e('If the customer successfully pays with Stripe, where are they redirected to after. Example: http://example.com/thankyou. ', 'contact-form-7-paypal-add-on'); ?></td></tr>
				
				<tr><td>
				<br />
				</td></tr>
				
				<tr><td class='cf7pp_width'>
				<b><?php _e('Redirect Method:', 'contact-form-7-paypal-add-on'); ?></b></td><td>
				<input <?php if ($options['redirect'] == "1") { echo "checked='checked'"; } ?> type='radio' name='redirect' value='1'><?php _e('1 (DOM wpcf7mailsent event listener)', 'contact-form-7-paypal-add-on'); ?>
				<input <?php if ($options['redirect'] == "2") { echo "checked='checked'"; } ?> type='radio' name='redirect' value='2'><?php _e('2 (Form sent class listener)', 'contact-form-7-paypal-add-on'); ?>
				</td></tr>
				<tr><td class='cf7pp_width'></td><td><?php _e('Method 1 recommend unless the form has problems redirecting.', 'contact-form-7-paypal-add-on'); ?></td></tr>
				
				
				<tr><td>
				<br />
				</td></tr>
				
				<tr><td class='cf7pp_width'>
				<b><?php _e('Request Method:', 'contact-form-7-paypal-add-on'); ?></b></td><td>
				<input <?php if ($options['request_method'] == "1") { echo "checked='checked'"; } ?> type='radio' name='request_method' value='1'><?php _e('1 (Admin Ajax)', 'contact-form-7-paypal-add-on'); ?>
				<input <?php if ($options['request_method'] == "2") { echo "checked='checked'"; } ?> type='radio' name='request_method' value='2'><?php _e('2 (Rest API)', 'contact-form-7-paypal-add-on'); ?>
				</td></tr>
				<tr><td class='cf7pp_width'></td><td><?php _e('Method 1 recommend unless the form has problems redirecting.', 'contact-form-7-paypal-add-on'); ?></td></tr>
				
				
				<tr><td>
				<br />
				</td></tr>
				
				<tr><td class='cf7pp_width'>
				<b><?php _e('Temporary Storage Method:', 'contact-form-7-paypal-add-on'); ?></b></td><td>
				<input <?php if ($options['session'] == "1") { echo "checked='checked'"; } ?> type='radio' name='session' value='1'><?php _e('Cookies', 'contact-form-7-paypal-add-on'); ?>
				<input <?php if ($options['session'] == "2") { echo "checked='checked'"; } ?> type='radio' name='session' value='2'><?php _e('Sessions', 'contact-form-7-paypal-add-on'); ?>
				</td></tr>
				<tr><td class='cf7pp_width'></td><td><?php _e('Cookies are recommend unless the form has problems.', 'contact-form-7-paypal-add-on'); ?></td></tr>

			</table>

		</div>
	</div>
	
	
	<div id="7" style="display:none;border: 1px solid #CCCCCC;<?php echo $active_tab == '7' ? 'display:block;' : ''; ?>">
		<div style="background-color:#E4E4E4;padding:8px;font-size:15px;color:#464646;font-weight: 700;border-bottom: 1px solid #CCCCCC;">
			&nbsp; <?php _e('Extensions', 'contact-form-7-paypal-add-on'); ?>
		</div>
		<div style="background-color:#fff;padding:8px;">
			
			<table style="width: 100%;">
				
				<?php
				cf7pp_extensions_page();
				?>
				
			</table>
			
		</div>
	</div>




	<input type='hidden' name='update' value='1'>
	<input type='hidden' name='hidden_tab_value' id="hidden_tab_value" value="<?php echo $active_tab; ?>">
    <?php wp_nonce_field( 'cf7pp_save_settings','cf7pp_nonce_field' ); ?>

</form>













	</td><td width="3%" valign="top">

	</td><td width="24%" valign="top">

	<div style="border: 1px solid #CCCCCC;width:400px;">	
		<div style="background-color:#E4E4E4;padding:8px;font-size:15px;color:#464646;font-weight: 700;border-bottom: 1px solid #CCCCCC;">
		&nbsp; <?php _e('Pro Version Features', 'contact-form-7-paypal-add-on'); ?>
		</div>
		
		<div style="background-color:#fff;padding:8px;">
		
		<br />
		<?php _e('We offer a Pro version of our plugins for those who want more features.', 'contact-form-7-paypal-add-on'); ?>
		<br />
		
		<br />
		<div class="dashicons dashicons-yes" style="margin-bottom: 6px;"></div> <?php _e('Only send email if PayPal / Stripe payment is successful', 'contact-form-7-paypal-add-on'); ?> <br />
		<div class="dashicons dashicons-yes" style="margin-bottom: 6px;"></div> <?php _e('No 2% PayPal per transaction application fee', 'contact-form-7-paypal-add-on'); ?> <br />
		<div class="dashicons dashicons-yes" style="margin-bottom: 6px;"></div> <?php _e('No 2% Stripe per transaction application fee', 'contact-form-7-paypal-add-on'); ?> <br />
		<div class="dashicons dashicons-yes" style="margin-bottom: 6px;"></div> <?php _e('Link any form item to price, quantity, or description', 'contact-form-7-paypal-add-on'); ?> <br />
		<div class="dashicons dashicons-yes" style="margin-bottom: 6px;"></div> <?php _e('Sell up to 5 items per form', 'contact-form-7-paypal-add-on'); ?> <br />
		<div class="dashicons dashicons-yes" style="margin-bottom: 6px;"></div> <?php _e('Charge tax and shipping', 'contact-form-7-paypal-add-on'); ?> <br />
		<div class="dashicons dashicons-yes" style="margin-bottom: 6px;"></div> <?php _e('Separate PayPal & Stripe account per form', 'contact-form-7-paypal-add-on'); ?> <br />
		<div class="dashicons dashicons-yes" style="margin-bottom: 6px;"></div> <?php _e('Skip redirecting based upon form elements', 'contact-form-7-paypal-add-on'); ?><br />
		<div class="dashicons dashicons-yes" style="margin-bottom: 6px;"></div> <?php _e('Accept recurring payments with our', 'contact-form-7-paypal-add-on'); ?> <a target='_blank' href='https://wpplugin.org/downloads/contact-form-7-recurring-payments-pro/'><?php _e('Recurring Add-on', 'contact-form-7-paypal-add-on'); ?></a><br />
		<div class="dashicons dashicons-yes" style="margin-bottom: 6px;"></div> <?php _e('Amazing plugin support agents from USA', 'contact-form-7-paypal-add-on'); ?><br />
		<div class="dashicons dashicons-yes" style="margin-bottom: 6px;"></div> <?php _e('No risk, 30 day return policy', 'contact-form-7-paypal-add-on'); ?> <br />
		<div class="dashicons dashicons-yes" style="margin-bottom: 6px;"></div> <?php _e('Many more features!', 'contact-form-7-paypal-add-on'); ?> <br />
		
		<br />
		<b><center><?php _e('Over 4,200 happy Pro version customers', 'contact-form-7-paypal-add-on'); ?></center></b>
		
		<br />
		<center><a target='_blank' href="https://wpplugin.org/downloads/contact-form-7-paypal-add-on/" class='button-primary' style='font-size: 17px;line-height: 28px;height: 32px;'><?php _e('Get the Pro Version', 'contact-form-7-paypal-add-on'); ?></a></center>
		<br />
		</div>
	</div>
	
	
	</td><td width="2%" valign="top">



	</td></tr></table>

	<?php

}

function cf7pp_free_ppcp_status_markup() {
	ob_start();

	$options = cf7pp_free_options();
	$status = cf7pp_free_ppcp_status();
	if ( !empty( $status ) ) {
		if ( empty( $status['errors'] ) ) {
			$notice_type = 'success';
			$show_links = false;
		} else {
			$notice_type = 'error';
			$show_links = true;
		}
		?>
        <div id="cf7pp-ppcp-status-table">
            <table>
                <tr>
                    <td class="cf7pp-cell-left">
                        <b><?php _e('Connection status:', 'contact-form-7-paypal-add-on'); ?> </b>
                    </td>
                    <td>
                        <div class="notice inline cf7pp-ppcp-connect notice-<?php echo $notice_type; ?>">
                            <p>
								<?php if ( !empty( $status['legal_name'] ) ) { ?>
                                    <strong><?php echo $status['legal_name']; ?></strong>
                                    <br>
								<?php } ?>
								<?php echo !empty( $status['primary_email'] ) ? $status['primary_email'] . ' — ' : ''; ?><?php _e('Administrator (Owner)', 'contact-form-7-paypal-add-on'); ?></p>
								<p><?php _e('Pay as you go pricing: 2% per-transaction fee + PayPal fees.', 'contact-form-7-paypal-add-on'); ?></p>
                        </div>
                        <div>
							<?php $reconnect_mode = $status['env'] === 'live' ? 'sandbox' : 'live'; ?>
                            <?php _e('Your PayPal account is connected in', 'contact-form-7-paypal-add-on'); ?> <strong><?php echo $status['env']; ?></strong> <?php _e('mode.', 'contact-form-7-paypal-add-on'); ?>
							<?php
							$query_args = [
								'action' => 'cf7pp-ppcp-onboarding-start',
								'nonce' => wp_create_nonce( 'cf7pp-ppcp-onboarding-start' )
							];
							if ( $reconnect_mode === 'sandbox' ) {
								$query_args['sandbox'] = 1;
							}
							?>
                            <a
                                class="cf7pp-ppcp-onboarding-start"
                                data-paypal-button="true"
                                href="<?php echo add_query_arg( $query_args, admin_url( 'admin-ajax.php' ) ); ?>"
                                target="PPFrame"
                            ><?php _e('Connect in', 'contact-form-7-paypal-add-on'); ?> <strong><?php echo $reconnect_mode; ?></strong> <?php _e('mode', 'contact-form-7-paypal-add-on'); ?></a> <?php _e('or', 'contact-form-7-paypal-add-on'); ?> <a href="#" id="cf7pp-ppcp-disconnect"><?php _e('disconnect this account', 'contact-form-7-paypal-add-on'); ?></a>.
                        </div>

						<?php if ( !empty( $status['errors'] ) ) { ?>
                            <p>
                                <strong><?php _e('There were errors connecting your PayPal account. Resolve them in your account settings, by contacting support or by reconnecting your PayPal account.', 'contact-form-7-paypal-add-on'); ?></strong>
                            </p>
                            <p>
                                <strong><?php _e('See below for more details.', 'contact-form-7-paypal-add-on'); ?></strong>
                            </p>
                            <ul class="cf7pp-ppcp-list cf7pp-ppcp-list-error">
								<?php foreach ( $status['errors'] as $error ) { ?>
                                    <li><?php echo $error; ?></li>
								<?php } ?>
                            </ul>
						<?php } ?>

						<?php if ( $show_links ) { ?>
                            <ul class="cf7pp-ppcp-list">
                                <li><a href="https://www.paypal.com/myaccount/settings/"><?php _e('PayPal account settings', 'contact-form-7-paypal-add-on'); ?></a></li>
                                <li><a href="https://www.paypal.com/us/smarthelp/contact-us"><?php _e('PayPal support', 'contact-form-7-paypal-add-on'); ?></a></li>
                            </ul>
						<?php } ?>
                    </td>
                </tr>
                <tr>
                    <td colspan="2">
                        <br />
                    </td>
                </tr>
            </table>
        </div>
		<?php
	} else { ?>
        <table id="cf7pp-ppcp-status-table" class="cf7pp-ppcp-initial-view-table">
            <tr>
                <td>
                    <img class="cf7pp-ppcp-paypal-logo" src="<?php echo CF7PP_FREE_URL; ?>imgs/paypal-logo.png" alt="paypal-logo" />
                </td>
                <td class="cf7pp-ppcp-align-right cf7pp-ppcp-icons">
                    <img class="cf7pp-ppcp-paypal-methods" src="<?php echo CF7PP_FREE_URL; ?>imgs/paypal-express.png" alt="paypal-expresss" />
                    <img class="cf7pp-ppcp-paypal-methods" src="<?php echo CF7PP_FREE_URL; ?>imgs/paypal-advanced.png" alt="paypal-advanced" />
                </td>
            </tr>
            <tr>
                <td colspan="2">
                    <h3 class="cf7pp-ppcp-title"><?php _e('PayPal: The all-in-one checkout solution', 'contact-form-7-paypal-add-on'); ?></h3>
                    <ul class="cf7pp-ppcp-list">
                        <li><?php _e('Help drive conversion by offering customers a seamless checkout experience', 'contact-form-7-paypal-add-on'); ?></li>
                        <li><?php _e('Securely accepts all major credit/debit cards and local payment methods with the strength of the PayPal network', 'contact-form-7-paypal-add-on'); ?></li>
                        <li><?php _e('You only pay the standard PayPal fees + 2%.', 'contact-form-7-paypal-add-on'); ?></li>
                    </ul>
                </td>
            </tr>
            <tr>
                <td>
					<?php
					$mode = intval( $options['mode'] );
					$query_args = [
						'action' => 'cf7pp-ppcp-onboarding-start',
						'nonce' => wp_create_nonce( 'cf7pp-ppcp-onboarding-start' )
					];
					if ( $mode === 1 ) {
						$query_args['sandbox'] = 1;
					}
					?>
                    <a
                        id="cf7pp-ppcp-onboarding-start-btn"
                        class="cf7pp-ppcp-button cf7pp-ppcp-onboarding-start"
                        data-paypal-button="true"
                        href="<?php echo add_query_arg( $query_args, admin_url( 'admin-ajax.php' ) ); ?>"
                        target="PPFrame"
                    ><?php _e('Get started', 'contact-form-7-paypal-add-on'); ?></a>
                </td>
                <td class="cf7pp-ppcp-align-right">
                    <a href="https://www.paypal.com/us/webapps/mpp/merchant-fees#statement-2" class="cf7pp-ppcp-link" target="_blank"><?php _e('View our simple and transparent pricing', 'contact-form-7-paypal-add-on'); ?></a>
                </td>
            </tr>
			<?php if ( !empty( $_GET['error'] ) && in_array( $_GET['error'], ['security', 'api'] ) ) { ?>
                <tr>
                    <td colspan="2">
                        <ul class="cf7pp-ppcp-list cf7pp-ppcp-list-error">
                            <li>
								<?php
								if ( $_GET['error'] === 'security' ) {
									_e( 'The request has not been authenticated. Please reload the page and try again.', 'contact-form-7-paypal-add-on' );
								} else {
									_e( 'The request ended with an error. Please reload the page and try again.', 'contact-form-7-paypal-add-on' );
								}
								?>
                            </li>
                        </ul>
                    </td>
                </tr>
			<?php } ?>
        </table>
		<?php
	}

	if ( !wp_doing_ajax() ) { ?>
        <script>
            (function(d, s, id){
                var js, ref = d.getElementsByTagName(s)[0]; if (!d.getElementById(id)){
                    js = d.createElement(s); js.id = id; js.async = true;
                    js.src =
                        "https://www.paypal.com/webapps/merchantboarding/js/lib/lightbox/partner.js";
                    ref.parentNode.insertBefore(js, ref); }
            }(document, "script", "paypal-js"));
        </script>
	<?php }

	return ob_get_clean();
}