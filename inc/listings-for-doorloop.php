<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

function lfdl_display_listings()
{

	if (isset($_GET['sngl_id'])) {
		$listing_no = sanitize_text_field($_GET['sngl_id']);
		return lfdl_pp_display_single_listing($listing_no);

	}

	global $lfdl_plugin_url;
	$all_lstng_url = get_option('lfdl_all_lstngs_page');
	$client_gmap_api = get_option('lfdl_gmap_api');


	$render_html = '';
	$render_html .= '<div class="lfdl-main-listings-page" style="width: 100%; max-width: 100%;">';


	// Filters
	$render_html .= '<div class="listing-filters">';

	$rent_min = '<select name="filters[market_rent_from]"><option value="">Min Price</option> <option value="250">$250</option> <option value="500">$500</option> <option value="600">$600</option> <option value="700">$700</option> <option value="800">$800</option> <option value="900">$900</option> <option value="1000">$1,000</option> <option value="1100">$1,100</option> <option value="1200">$1,200</option> <option value="1300">$1,300</option> <option value="1400">$1,400</option> <option value="1500">$1,500</option> <option value="1600">$1,600</option> <option value="1700">$1,700</option> <option value="1800">$1,800</option> <option value="1900">$1,900</option> <option value="2000">$2,000</option> <option value="2250">$2,250</option> <option value="2500">$2,500</option> <option value="2750">$2,750</option> <option value="3000">$3,000</option></select>';
	$rent_max = '<select name="filters[market_rent_to]"><option value="">Max Price</option> <option value="250">$250</option> <option value="500">$500</option> <option value="600">$600</option> <option value="700">$700</option> <option value="800">$800</option> <option value="900">$900</option> <option value="1000">$1,000</option> <option value="1100">$1,100</option> <option value="1200">$1,200</option> <option value="1300">$1,300</option> <option value="1400">$1,400</option> <option value="1500">$1,500</option> <option value="1600">$1,600</option> <option value="1700">$1,700</option> <option value="1800">$1,800</option> <option value="1900">$1,900</option> <option value="2000">$2,000</option> <option value="2250">$2,250</option> <option value="2500">$2,500</option> <option value="2750">$2,750</option> <option value="3000">$3,000</option></select>';
	$filters_bedrooms = '<select name="filters[bedrooms]"><option value="">Beds</option> <option value="1">1+</option> <option value="2">2+</option> <option value="3">3+</option> <option value="4">4+</option> <option value="5">5+</option></select>';
	$filters_bathrooms = '<select name="filters[bathrooms]"><option value="">Baths</option> <option value="1">1+</option> <option value="2">2+</option> <option value="3">3+</option></select>';


	$filters_cities = '<select name="filters[cities][]"><option value="All Cities">All Cities</option> ';

	$all_cities = get_option('lfdl_all_cities');
	if (is_array($all_cities)) {
		foreach ($all_cities as $city) {
			$filters_cities .= '<option value="' . $city . '">' . $city . '</option>';
		}
	}
	$filters_cities .= '</select>';

	$all_zip_codes = get_option('lfdl_all_zip_codes');

	$filters_zip = '<select name="filters[postal_codes][]"><option value="All Zip Codes">All Zip Codes</option> ';
	if (is_array($all_cities)) {
		foreach ($all_zip_codes as $zip) {
			$filters_zip .= '<option value="' . $zip . '">' . $zip . '</option>';
		}
	}
	$filters_cities .= '</select>';

	$filters_desired_movein = '<input type="date" onfocus="(this.type="date")" name="filters[desired_move_in]" placeholder="Desired Move In Date">';
	$filters_sort = '<select id="order_by_fltr" name="filters[order_by]"><option selected="selected" value="date_posted">Most Recent</option> <option value="rent_asc">Price (Low to High)</option> <option value="rent_desc">Price (High to Low)</option> <option value="bedrooms">Bedrooms</option><option value="availability">Availability</option></select>';

	$nonce = wp_create_nonce('lfdl_listings_nonce');
	$render_html .= '<form method="post">';
	$render_html .= '<input type="hidden" name="lfdl_listings_nonce" value="' . esc_attr($nonce) . '" />';

	$lfdl_filters_minrent = 'show';
	$lfdl_filters_maxrent = 'show';
	$lfdl_filters_bed = 'show';
	$lfdl_filters_bath = 'show';
	$lfdl_filters_cities = 'show';
	$lfdl_filters_zip = 'show';
	$lfdl_filters_movein = 'show';
	$filter_args = array(
		'post_type' => 'listing',
		'numberposts' => -1,
	);



	if (!(isset($_POST['lfdl_listings_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['lfdl_listings_nonce'])), 'lfdl_listings_nonce'))) {

		// Process Min Rent
		if ($lfdl_filters_minrent == 'show' && $rent_min) {
			$render_html .= $rent_min;
		}

		// Process Max Rent
		if ($lfdl_filters_maxrent == 'show' && $rent_max) {
			$render_html .= $rent_max;
		}

		// Process Bedrooms
		if ($lfdl_filters_bed == 'show' && $filters_bedrooms) {
			$render_html .= $filters_bedrooms;
		}

		// Process Bathrooms
		if ($lfdl_filters_bath == 'show' && $filters_bathrooms) {
			$render_html .= $filters_bathrooms;
		}

		// Process Cities
		if ($lfdl_filters_cities == 'show' && $filters_cities) {
			$render_html .= $filters_cities;
		}

		// Process ZIP Codes
		if ($lfdl_filters_zip == 'show' && $filters_zip) {
			$render_html .= $filters_zip;
		}

		if ($lfdl_filters_movein == 'show' && $filters_desired_movein) {

			$correct_movein = sanitize_text_field($filters_desired_movein);
			if (!empty($filters_desired_movein)) {
				$render_html .= '<input type="date" name="filters[desired_move_in]" value="' . $correct_movein . '">';
			} else {
				$render_html .= $filters_desired_movein;
			}

		}

		$address_search = '';
		$render_html .= '<input type="search" name="filters[textarea_input]" placeholder="Search by address..." value="' . esc_attr($address_search) . '">';



	} else {
		// Process Min Rent
		if ($lfdl_filters_minrent == 'show' && $rent_min) {
			$correct_min_rent = $rent_min;
			$selected = isset($_POST['filters']['market_rent_from']) ? sanitize_text_field($_POST['filters']['market_rent_from']) : '';

			if (!empty($selected)) {
				$correct_min_rent = str_replace('value="' . $selected . '"', 'value="' . $selected . '" selected="selected"', $correct_min_rent);
			}

			$render_html .= $correct_min_rent;
		}

		// Process Max Rent
		if ($lfdl_filters_maxrent == 'show' && $rent_max) {
			$correct_max_rent = $rent_max;
			$selected = isset($_POST['filters']['market_rent_to']) ? sanitize_text_field($_POST['filters']['market_rent_to']) : '';

			if (!empty($selected)) {
				$correct_max_rent = str_replace('value="' . $selected . '"', 'value="' . $selected . '" selected="selected"', $correct_max_rent);
			}

			$render_html .= $correct_max_rent;
		}

		// Process Bedrooms
		if ($lfdl_filters_bed == 'show' && $filters_bedrooms) {
			$correct_beds = $filters_bedrooms;
			$selected = isset($_POST['filters']['bedrooms']) ? sanitize_text_field($_POST['filters']['bedrooms']) : '';

			if (!empty($selected)) {
				$correct_beds = str_replace('value="' . $selected . '"', 'value="' . $selected . '" selected="selected"', $correct_beds);
			}

			$render_html .= $correct_beds;
		}

		// Process Bathrooms
		if ($lfdl_filters_bath == 'show' && $filters_bathrooms) {
			$correct_baths = $filters_bathrooms;
			$selected = isset($_POST['filters']['bathrooms']) ? sanitize_text_field($_POST['filters']['bathrooms']) : '';

			if (!empty($selected)) {
				$correct_baths = str_replace('value="' . $selected . '"', 'value="' . $selected . '" selected="selected"', $correct_baths);
			}

			$render_html .= $correct_baths;
		}

		// Process Cities
		if ($lfdl_filters_cities == 'show' && $filters_cities) {
			$correct_cities = $filters_cities;
			$selected = isset($_POST['filters']['cities'][0]) ? sanitize_text_field($_POST['filters']['cities'][0]) : '';

			if (!empty($selected)) {
				$correct_cities = str_replace('value="' . $selected . '"', 'value="' . $selected . '" selected="selected"', $correct_cities);
			}

			$render_html .= $correct_cities;
		}

		// Process ZIP Codes
		if ($lfdl_filters_zip == 'show' && $filters_zip) {
			$correct_zip = $filters_zip;
			$selected = isset($_POST['filters']['postal_codes'][0]) ? sanitize_text_field($_POST['filters']['postal_codes'][0]) : '';

			if (!empty($selected)) {
				$correct_zip = str_replace('value="' . $selected . '"', 'value="' . $selected . '" selected="selected"', $correct_zip);
			}

			$render_html .= $correct_zip;
		}

		if ($lfdl_filters_movein == 'show' && $filters_desired_movein) {
			if (isset($_POST['filters']['desired_move_in'])) {
				$selected = sanitize_text_field($_POST['filters']['desired_move_in']);

				if ($selected) {
					$render_html .= '<input type="date" name="filters[desired_move_in]" value="' . $selected . '">';
				} else {
					$render_html .= $filters_desired_movein;
				}
			} else {
				$correct_movein = sanitize_text_field($filters_desired_movein);
				if (!empty($filters_desired_movein)) {
					$render_html .= '<input type="date" name="filters[desired_move_in]" value="' . $correct_movein . '">';
				} else {
					$render_html .= $filters_desired_movein;
				}
			}
		}

		$address_search = isset($_POST['filters']['textarea_input']) ? sanitize_text_field($_POST['filters']['textarea_input']) : '';
		$render_html .= '<input type="search" name="filters[textarea_input]" placeholder="Search by address..." value="' . esc_attr($address_search) . '">';


		// Check if the availability filter is selected
		if (isset($_POST['filters']['order_by'])) {
			$selected = sanitize_text_field($_POST['filters']['order_by']);
			$filters_sort = str_replace('value="' . $selected . '"', 'value="' . $selected . '" selected="selected"', $filters_sort);
		}

		// Apply filters based on user input
		if (isset($_POST['filters'])) {
			$filters = array_map('sanitize_text_field', $_POST['filters']);
			$filters = array_map('esc_attr', $filters);

			// Min Rent filter
			if (!empty($filters['market_rent_from'])) {
				$filter_args['meta_query'][] = array(
					'key' => 'property_rent',
					'value' => intval($filters['market_rent_from']),
					'compare' => '>=',
					'type' => 'NUMERIC',
				);
			}

			// Max Rent filter
			if (!empty($filters['market_rent_to'])) {
				$filter_args['meta_query'][] = array(
					'key' => 'property_rent',
					'value' => intval($filters['market_rent_to']),
					'compare' => '<=',
					'type' => 'NUMERIC',
				);
			}

			// Bedrooms filter
			if (!empty($filters['bedrooms'])) {
				$filter_args['meta_query'][] = array(
					'key' => 'property_beds',
					'value' => intval($filters['bedrooms']),
					'compare' => '>=',
					'type' => 'NUMERIC',
				);
			}

			// Bathrooms filter
			if (!empty($filters['bathrooms'])) {
				$filter_args['meta_query'][] = array(
					'key' => 'property_baths',
					'value' => intval($filters['bathrooms']),
					'compare' => '>=',
					'type' => 'NUMERIC',
				);
			}

			// Cities filter
			if (!empty($filters['cities']) && $filters['cities'][0] !== 'All Cities') {
				$filter_args['meta_query'][] = array(
					'key' => 'property_city',
					'value' => $filters['cities'][0],
					'compare' => '=',
				);
			}

			// ZIP Codes filter
			if (!empty($filters['postal_codes']) && $filters['postal_codes'][0] !== 'All Zip Codes') {
				$filter_args['meta_query'][] = array(
					'key' => 'property_zip',
					'value' => $filters['postal_codes'][0],
					'compare' => '=',
				);
			}
		}

		// Availability filter
		if (!empty($filters['desired_move_in'])) {
			$desired_move_in = strtotime($filters['desired_move_in']);

			$filter_args['meta_query'][] = array(
				'key' => 'property_availability',
				'value' => date('Y-m-d', $desired_move_in),
				'compare' => '<=',
				'type' => 'DATE',
			);
		}


	}




	$render_html .= $filters_sort;


	$render_html .= '<input type="submit" value="SEARCH" name="fltr-submt">';
	$render_html .= '</form></div>';

	// Google map for listings
	if ($client_gmap_api) {
		$render_html .= '<div id="googlemap"></div>';
	}
	//$render_html .= '<div id="googlemap"></div>';




	if (!empty($address_search)) {
		$filter_args['s'] = $address_search;
	}

	// Get filtered listings
	$listings_posts = get_posts($filter_args);

	if (isset($_POST['lfdl_listings_nonce']) && wp_verify_nonce(sanitize_text_field(wp_unslash($_POST['lfdl_listings_nonce'])), 'lfdl_listings_nonce')) {
		if (isset($_POST['filters']['order_by'])) {
			$order_by = sanitize_text_field($_POST['filters']['order_by']);

			usort($listings_posts, function ($a, $b) use ($order_by) {
				// Define your comparison logic based on the selected order_by value
				switch ($order_by) {
					case 'rent_asc':
						return get_post_meta($a->ID, 'property_rent', true) - get_post_meta($b->ID, 'property_rent', true);
					case 'rent_desc':
						return get_post_meta($b->ID, 'property_rent', true) - get_post_meta($a->ID, 'property_rent', true);
					case 'bedrooms':
						return get_post_meta($a->ID, 'property_bedsbeds', true) - get_post_meta($b->ID, 'property_beds', true);
					case 'availability':
						return strtotime(get_post_meta($a->ID, 'property_availability', true)) - strtotime(get_post_meta($b->ID, 'property_availability', true));
					default:
						return 0;
				}
			});
		}
	}
	$lfdl_template = 1;
	// Listings
	$render_html .= '<div class="all-listings">';
	// Check if the XML contains Property data
	if ($listings_posts) {

		$listings_posts = array_reverse($listings_posts);
		$listings_html = array();
		$i = 0;
		$lfdl_template = 2;
		foreach ($listings_posts as $listing) {
			$post_id = $listing->ID;
			$post_title = $listing->post_title;
			$post_content = $listing->post_content;
			$property_id = get_post_meta($post_id, 'property_id', true);
			$property_rent = get_post_meta($post_id, 'property_rent', true);
			$property_baths = get_post_meta($post_id, 'property_baths', true);
			$property_beds = get_post_meta($post_id, 'property_beds', true);
			$property_availability = get_post_meta($post_id, 'property_availability', true);
			$property_apply_link = get_post_meta($post_id, 'property_apply_link', true);
			$latitude = get_post_meta($post_id, 'property_lat', true);
			$longitude = get_post_meta($post_id, 'property_long', true);
			$image_ids = get_post_meta($post_id, 'property_attachment_ids', true);
			$first_image_url = '';

			if (!empty($image_ids) && is_array($image_ids) && !empty($image_ids[0])) {
				$first_image_id = $image_ids[0];
				$first_image_url = wp_get_attachment_url($first_image_id);
			}

			$listing_html = '';

			$listing_html .= '<div class="listing-item column mcb-column one-third">';
			$listing_html .= '<a href="' . $all_lstng_url . '?sngl_id=' . urlencode($property_id) . '">';
			$listing_html .= '<div class="list-img">';
			if (!empty($first_image_url)) {
				$listing_html .= '<img src="' . $first_image_url . '">';
			}
			$listing_html .= '<span class="rent-price">$' . $property_rent . '</span>';
			if (!empty($property_availability)) {
				$listing_html .= '<span class="lstng-avail">Available ' . $property_availability . '</span>';
			}
			$listing_html .= '</div></a>';
			$listing_html .= '<div class="details">';
			$listing_html .= '<h4 class="lstng_ttl">' . $post_title . '</h4>';
			$listing_html .= '<h5 class="address">' . $post_content . '</h5>';
			$listing_html .= '<p>';
			$listing_html .= '<img class="bedimg" src="' . $lfdl_plugin_url . '/images/sleep.png">';
			if (!empty($property_beds)) {
				$listing_html .= '<span class="beds">' . $property_beds . ' beds</span>';
			}
			$listing_html .= '<img class="bathimg" src="' . $lfdl_plugin_url . '/images/bathtub.png">';
			if (!empty($property_baths)) {
				$listing_html .= '<span class="baths">' . $property_baths . ' Baths</span>';
			}
			$listing_html .= '</p>';
			$listing_html .= '<div class="btns">';
			$listing_html .= '<a class="more_detail_btn" href="' . $all_lstng_url . '?sngl_id=' . urlencode($property_id) . '">Details</a>';
			if (!empty($property_apply_link)) {
				$listing_html .= '<a class="apply_btn" href="' . $property_apply_link . '" target="_blank">Apply</a>';
			}
			$listing_html .= '</div>';
			$listing_html .= '</div>';
			$listing_html .= '</div>';

			$listings_html[$i] = $listing_html;
			$i++;
		}

		$lfdl_columns_cnt = 3;
		$itm_cntr = 0;


		foreach ($listings_html as $listing_html) {

			if ($lfdl_template != 1) {
				if ($itm_cntr % $lfdl_columns_cnt == 0) {
					$render_html .= '<div class="listing-items-grp">';
				}
			}

			$render_html .= $listing_html;
			$itm_cntr++;
			if ($lfdl_template != 1) {
				if ($itm_cntr % $lfdl_columns_cnt == 0 || $itm_cntr == $lfdl_columns_cnt) {
					$render_html .= '</div>';
				}
			}

		}

	} else {
		$render_html .= '<p>No property data available.</p>';
	}

	$render_html .= '</div>';
	$render_html .= '</div>';


	if ($client_gmap_api) {

		$customMarkers = [];
		$locationMap = []; // Store listings at the same location

		foreach ($listings_posts as $listing) {
			$post_id = $listing->ID;
			$latitude = get_post_meta($post_id, 'property_lat', true);
			$longitude = get_post_meta($post_id, 'property_long', true);

			// Create a unique identifier for the location based on lat and long
			$locationKey = $latitude . '-' . $longitude;

			if (isset($locationMap[$locationKey])) {
				// Location already exists, add this listing to it
				$locationMap[$locationKey]['listings'][] = $listing;
			} else {
				// Create a new location entry
				$locationMap[$locationKey] = [
					'latitude' => $latitude,
					'longitude' => $longitude,
					'listings' => [$listing],
				];
			}
		}

		// Loop through the locations and create a single marker for each
		foreach ($locationMap as $location) {
			$latitude = $location['latitude'];
			$longitude = $location['longitude'];
			$listings = $location['listings'];

			// Create marker content for all listings at this location
			$content = '<div class="mm-prop-popup">';
			foreach ($listings as $listing) {
				$post_id = $listing->ID;
				$post_title = $listing->post_title;

				$image_ids = get_post_meta($post_id, 'property_attachment_ids', true);
				$first_image_url = '';
				if (!empty($image_ids) && is_array($image_ids)) {
					$first_image_id = $image_ids[0];
					$first_image_url = wp_get_attachment_url($first_image_id);
				}
				// Add property details for each listing as needed
				$content .= '<div class="map-popup-thumbnail"><a href="' . $all_lstng_url . '?sngl_id=' . urlencode(get_post_meta($post_id, 'property_id', true)) . '" target="_blank"><img src="' . $first_image_url . '" width="144"></a></div>' .
					'<div class="map-popup-info">' .
					'<h3 class="map-popup-rent">$' . get_post_meta($post_id, 'property_rent', true) . '</h3>' .
					'<p class="map-popup-specs">' . get_post_meta($post_id, 'property_baths', true) . ' baths ' .
					get_post_meta($post_id, 'property_beds', true) . ' beds ' . get_post_meta($post_id, 'property_sq_ft', true) . ' sq ft</p>' .
					'<p class="map-popup-address">' . $post_title . '</p>' .
					'<p><a href="' . $all_lstng_url . '?sngl_id=' . urlencode($property_id) . '" target="_blank" class="btn btn-secondary btn-sm pt-1 pb-1">Details</a>' .
					'<a href="https://maps.google.com/maps?daddr=' . $post_title . '" target="_blank" class="btn btn-secondary btn-sm pt-1 pb-1 directions-link">Directions</a>' .
					'</p>';
			}
			$content .= '</div></div>';

			// Create a marker for this location
			$marker = [
				'position' => ['lat' => floatval($latitude), 'lng' => floatval($longitude)],
				'title' => $post_title,
				'content' => $content,
			];

			// Add the marker to the array of custom markers
			$customMarkers[] = $marker;
		}

		update_option('lfdl_custom_markers', $customMarkers);
		wp_enqueue_script('google-map-script', plugins_url('js/googleMap.js', __FILE__), array('google-maps'), '1.0', true);

		// Pass PHP data to the script
		$php_data = array(
			'markers' => wp_json_encode($customMarkers),
		);
		wp_localize_script('google-map-script', 'php_data', $php_data);

	}

	return $render_html;

}
