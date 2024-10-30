<?php
/**
 * Plugin Name: Better Listings for Rent
 * Description: This is a solution to your DoorLoop Listings integration with your WordPress website
 * Version: 0.1.0
 * Author: Media jedi
 * Author URI: https://mediajedi.com/
 * License: GPLv2 or later
 * Text Domain: better-listings-for-rent
 * Domain Path: /languages
 */

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

add_action('init', 'lfdl_plugin_init');
add_action('admin_init', 'lfdl_admin_init');

function lfdl_plugin_init()
{
	include_once(dirname(__FILE__) . '/inc/listings-for-doorloop.php');
	include_once(dirname(__FILE__) . '/inc/single-listings.php');
	include_once(dirname(__FILE__) . '/inc/functions-front.php');

	global $lfdl_plugin_url;
	$lfdl_plugin_url = plugin_dir_url(__FILE__) . '';

	// Register custom post type 'listing'
	register_post_type('listing', array(
		'labels' => array(
			'name' => 'Listings',
			'singular_name' => 'Listing',
			'add_new' => 'Add New',
			'add_new_item' => 'Add New Listing',
			'edit_item' => 'Edit Listing',
			'new_item' => 'New Listing',
			'view_item' => 'View Listing',
			'search_items' => 'Search Listings',
			'not_found' => 'No listings found',
			'not_found_in_trash' => 'No listings found in Trash',
			'menu_name' => 'Listings',
		),
		'public' => true,
		'has_archive' => true,
		'menu_icon' => 'dashicons-admin-home', // Customize the icon
		'supports' => array('title', 'editor', 'custom-fields'), // Add or remove supports as needed
	)
	);

	add_shortcode('lfdl_listings', 'lfdl_display_listings');
	add_action('wp_enqueue_scripts', 'lfdl_enqueue_scripts');
}

function lfdl_admin_init()
{
	add_action('admin_enqueue_scripts', 'lfdl_enqueue_admin_scripts');
}

function lfdl_enqueue_admin_scripts()
{

	// Enqueue lfdl-admin-script with jQuery as a dependency
	wp_enqueue_script(
		'lfdl-admin-script',
		plugin_dir_url(__FILE__) . 'js/admin.js',
		array('jquery')
	);

	wp_localize_script('lfdl-admin-script', 'lfdl_ajax_admin_obj', array(
		'ajaxurl' => admin_url('admin-ajax.php'),
		'nonce' => wp_create_nonce('lfdl_ajax_admin_obj_nonce'),
	)
	);

	wp_enqueue_style(
		'lfdl-admin-styles',
		plugin_dir_url(__FILE__) . 'css/admin-style.css'
	);

}

function lfdl_enqueue_scripts($atts)
{
	wp_enqueue_style(
		'lfdl-styles',
		plugin_dir_url(__FILE__) . 'css/style.css'
	);

	wp_enqueue_script(
		'lfdl-main-script',
		plugin_dir_url(__FILE__) . 'js/main.js',
		null, '1.0', true
	);

	$client_gmap_api = get_option('lfdl_gmap_api');

	if ($client_gmap_api) {
		wp_enqueue_script('google-map-script', plugins_url('js/googleMap.js', __FILE__), null, '1.0', true);

		// Pass PHP data to the script
		$customMarkers = get_option('lfdl_custom_markers');

		$php_data = array(
			'markers' => wp_json_encode($customMarkers),
		);
		wp_localize_script('google-map-script', 'php_data', $php_data);
		wp_enqueue_script('google-maps-api', 'https://maps.googleapis.com/maps/api/js?key=' . $client_gmap_api . '&callback=initMap', array(), null, true);
	}


}

// Plugin Configuration Page
if (is_admin()) {

	add_action('admin_menu', 'lfdl_pp_admin_config');
	function lfdl_pp_admin_config()
	{
		add_menu_page('Listings for Doorloop', 'Doorloop', 'manage_options', 'lfdl-pp', 'lfdl_pp_config_callback', 'dashicons-admin-home');
		add_submenu_page('lfdl-pp', 'Settings', 'Settings', 'manage_options', 'lfdl-pp', 'lfdl_pp_config_callback', 1);
	}

	function lfdl_pp_config_callback()
	{

		if (!current_user_can('manage_options')) {
			wp_die('You do not have sufficient permissions to access this page.');
		}

		if ($_POST) {
			if (isset($_POST['lfdl_admin_config_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['lfdl_admin_config_nonce'])), 'lfdl_admin_config_nonce')) {
				
				if (isset($_POST['lfdl_config_submit'])) {

					if (isset($_POST['lfdl_config_url'])) {
						$lfdl_url = sanitize_text_field($_POST['lfdl_config_url']);
						$lfdl_url_updated = update_option('lfdl_url', $lfdl_url);
					}
					if (isset($_POST['lfdl_config_gmap_api'])) {
						$lfdl_gmap_api = sanitize_text_field($_POST['lfdl_config_gmap_api']);
						$lfdl_gmap_api_updated = update_option('lfdl_gmap_api', $lfdl_gmap_api);
					}

					if (isset($_POST['lfdl_all_lstngs_page'])) {
						$lfdl_all_lstngs_page = sanitize_text_field($_POST['lfdl_all_lstngs_page']);
						update_option('lfdl_all_lstngs_page', $lfdl_all_lstngs_page);
					}

					// Saved message
					echo '<div class="notice notice-success is-dismissible"><p>Settings Saved!</p></div>';

				}
			}
		}


		?>
		<div class="wrap">
			<div id="lfdl_pro_settings">

				<form method='POST' action="">
					<?php 
					
					$nonce = wp_create_nonce('lfdl_admin_config_nonce');
					
					echo '<input type="hidden" name="lfdl_admin_config_nonce" value="' . esc_attr($nonce) . '" />';
					
					?>
					<br>
					<table class="form-table">
						<tr>
							<th>
								<?php $lfdl_listing_url = get_option('lfdl_url'); ?>
								<label for="lfdl_config_url">* Doorloop URL to fetch listings: </label>
							</th>
							<td>
								<input type="text" name="lfdl_config_url" id="lfdl_config_url" style="min-width: 350px;"
									value="<?php echo esc_url($lfdl_listing_url); ?>" required>
							</td>
						</tr>
						<tr>
							<th>
								<?php $lfdl_gmap_api = get_option('lfdl_gmap_api'); ?>
								<label for="lfdl_config_gmap_api">Google Map JS API Key</label>
							</th>
							<td>
								<input type="text" name="lfdl_config_gmap_api" id="lfdl_config_gmap_api"
									style="min-width: 350px;" placeholder="Leave Blank to disable Google Map"
									value="<?php echo esc_attr($lfdl_gmap_api); ?>">
							</td>
						</tr>
						<tr>
							<th>
								<?php $lfdl_all_lstngs_page = get_option('lfdl_all_lstngs_page'); ?>
								<label for="lfdl_all_lstngs_page" class="tooltip">* URL with all listings <span
										class="tooltiptext">Enter your website page URL that has [lfdl_listings]
										shortcode</span></label>
							</th>
							<td>
								<input type="text" style="min-width: 350px;" name="lfdl_all_lstngs_page"
									id="lfdl_all_lstngs_page"
									placeholder="e.g. <?php echo esc_url(site_url()); ?>/all-listings/"
									value="<?php echo esc_attr($lfdl_all_lstngs_page); ?>" required>
							</td>
						</tr>
					</table>
					<p class="submit">
						<input type="submit" name="lfdl_config_submit" id="lfdl_config_submit" class="button-primary"
							value="Save" />
					</p>
				</form>

				<button id="lfdl_sync_listings">Sync listings</button>

				<div id="lfdl_spinner_container">
					<div id="lfdl_spinner" class="lfdl_spinner"></div>
					<p id="sync-status">Sync in progress...</p>
				</div>

			</div>
		</div>

		<?php

	}

}