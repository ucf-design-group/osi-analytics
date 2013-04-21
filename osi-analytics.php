<?php

/*
 * Plugin Name: OSI Analytics
 * Plugin URI: https://github.com/aj-foster/osi-analytics
 * Description: This plugin maintains Google Analytics for sites administered by the Office of Student Involvement.
 * Version: 1.0
 * Author: AJ Foster
 * Author URI: http://aj-foster.com/
 * License: None; use as you please!
 */


/* For the sake of database cleanliness, we will call functions to add and remove the option field
 * used by this function on activation and deactivation.
 */

register_activation_hook(__FILE__, 'analytics_activation');
register_deactivation_hook(__FILE__, 'analytics_deactivation');


/* On activation, an option is added to the database. */

function analytics_activation() {

	$option = array('enable' => 'false', 'code' => '');
	add_option('osi-analytics', $option);
}


/* On deactivation, we remove the option field from the database. */

function analytics_deactivation() {

	delete_option('osi-analytics');
}


/* This function is called as part of the wp_footer action.  It displays analytics if the have been
 * enabled by the user.
 */

function analytics() {

	$option = get_option('osi-analytics');

	if ($option['enable'] == "true") {
		?>
			<script type="text/javascript">
				var _gaq = _gaq || [];
				_gaq.push(['_setAccount', '<?php analytics_code(); ?>']);
				_gaq.push(['_trackPageview']);
				(function() {
					var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
					ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
					var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
				})();
			</script>
		<?php
	}
}
add_action('wp_footer', 'analytics');


/* This function retrieves the analytics code. */

function analytics_code() {

	$option = get_option('osi-analytics');
	$code = $option['code'];
	echo $code;
	return;
}


/* In order to make things easy, we setup an options page for the user to enable / disable the
 * analytics and input the account code.
 */

function setup_analytics_settings() {

	add_options_page('Analytics', 'Analytics', 'manage_options', 'osi-analytics-settings', 'analytics_settings');
}
add_action('admin_menu', 'setup_analytics_settings');


/* This function is called when the options page is opened.  It displays settings and updates them
 * if necessary.
 */

function analytics_settings() {

	?>

	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div><h2>Google Analytics Settings</h2>

	<?php

	if (!current_user_can('manage_options')) {
		wp_die('You do not have permission to access this page.');
	}

	$option = get_option('osi-analytics');
	$option = is_array($option) ? $option : array('enable' => 'true', 'code' => '');

	if (isset($_POST['osi-analytics-submit'])) {

		foreach ($option as $key => $value) {

			if (isset($_POST['osi-analytics-'.$key]) && $_POST['osi-analytics-'.$key] != $value)
				$option[$key] = $_POST['osi-analytics-'.$key];
		}

		if (update_option('osi-analytics', $option))
			echo '<div class="updated"><p><strong>Settings Updated</strong></p></div>';
		else
			echo '<div class="alert"><p><strong>Failed to Update Settings</strong></p></div>';
	}

	$checked_true = ($option['enable'] == "true") ? 'checked="checked"' : '';
	$checked_false = ($option['enable'] == "false") ? 'checked="checked"' : '';
	
	?>
		<form method="post" action="">
			<p>Use Google Analytics on this site?</p>
			<p>
				<input name="osi-analytics-enable" type="radio" value="true" id="osi-analytics-true" <?php echo $checked_true; ?>>
				<label for="osi-analytics-true">Enable </label>
			</p>
			<p>
				<input name="osi-analytics-enable" type="radio" value="false" id="osi-analytics-false" <?php echo $checked_false; ?>>
				<label for="osi-analytics-false">Disable </label>
			</p>
			<p>
				<label for="osi-analytics-code">User Account Code: </label>
				<input name="osi-analytics-code" type="text" value="<?php echo $option['code']; ?>">
			</p>
			<p>
				<input name="osi-analytics-submit" type="submit" value="Save Changes" class="button-primary">
			</p>
		</form>
	<?php

}

?>