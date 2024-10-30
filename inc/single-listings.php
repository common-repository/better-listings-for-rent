<?php

// Exit if accessed directly
if (!defined('ABSPATH')) {
	exit;
}

function lfdl_pp_display_single_listing($propertyIDValue) {

	global $lfdl_plugin_url;
	// Check if a post with the same property ID already exists
	$listing = get_posts(
		array(
			'post_type' => 'listing',
			'meta_key' => 'property_id',
			'meta_value' => $propertyIDValue,
		)
	)[0];


	if (!empty($listing)) {
		$post_id = $listing->ID;
		$post_title = $listing->post_title;
		$post_content = $listing->post_content;
		$property_id = get_post_meta($post_id, 'property_id', true);
		$property_rent = get_post_meta($post_id, 'property_rent', true);
		$property_baths = get_post_meta($post_id, 'property_baths', true);
		$property_beds = get_post_meta($post_id, 'property_beds', true);
		$property_sqfeet = get_post_meta($post_id, 'property_sq_ft', true);
		$contact = get_post_meta($post_id, 'property_phn_no', true);
		$property_availability = get_post_meta($post_id, 'property_availability', true);
		$property_apply_link = get_post_meta($post_id, 'property_apply_link', true);
		$image_ids = get_post_meta($post_id, 'property_attachment_ids', true);
		$amenities = get_post_meta($post_id, 'property_amenities', true);
		$email = get_post_meta($post_id, 'property_email', true);
		$amenities = unserialize($amenities);
		$leaseLength = get_post_meta($post_id, 'property_lease', true);
		$marketRent = get_post_meta($post_id, 'property_market_rent', true);

		$sl_html = '<div class="lfdl-sl-wrapper" style="width: 100%; max-width: 100%;">';

		// All listings 
		$all_lstng_url = get_option('lfdl_all_lstngs_page');
		$sl_html .= '<div style="margin-bottom: 2rem;"><a class="lfdl-prmry-btn" href="' . $all_lstng_url . '" style="margin-left: 2%;"> << All Listings</a></div>';


		$sl_html .= '<div class="listing-sec"><div class="lfdl-column lfdl-two-fifth">';
		if ($image_ids && is_array($image_ids)) {
			$sl_html .= '<div class="lfdl-gallery">';
			$j = 1;

			$video_url = '';

			foreach ($image_ids as $image_id) {
				$img_url = wp_get_attachment_url($image_id);
				if (str_contains($img_url, 'youtube')) {
					$video_url = $img_url;
				}

				$sl_html .= '<div class="mySlides">
								<div class="numbertext">' . $j . ' / ' . count($image_ids) . '</div>
								<img src="' . $img_url . '" data-href="' . $img_url . '" data-id="lfdl_gal_img_' . $j . '">
							</div>';
				$j++;
			}
			$sl_html .= '<a class="prev" onclick="plusSlides(-1)">&#10094;</a>
						<a class="next" onclick="plusSlides(1)">&#10095;</a>
						<div class="row" style="margin-top: 7px;">';
			$k = 1;
			foreach ($image_ids as $image_id) {
				$img_url = wp_get_attachment_url($image_id);
				$sl_html .= '<div id="image-prvw" class="imgcolumn">
									<img class="demo cursor" src="' . $img_url . '" onclick="currentSlide(' . $k . ')">
								</div>';
				$k++;
			}
			$sl_html .= '</div></div>';

			if ($video_url) {

				$iframe_code = preg_replace("/\s*[a-zA-Z\/\/:\.]*youtube.com\/watch\?v=([a-zA-Z0-9\-_]+)([a-zA-Z0-9\/\*\-\_\?\&\;\%\=\.]*)/i", "<iframe width=\"560\" height=\"330\" src=\"//www.youtube.com/embed/$1\" frameborder=\"0\" allowfullscreen></iframe>", $video_url);

				$sl_html .= '<div id="lfdl-vdo">
						
							' . $iframe_code . '
							
						</div>';
			}

		}
		$sl_html .= '</div>';
		$sl_html .= '<div class="lfdl-column lfdl-three-fifth">';

		$sl_html .= '<div class="lst-dtls">
						<div class="details-left">';
		$address = $post_title;
		$google_map_link = 'https://maps.google.com/maps?daddr=' . $address . '';
		$sl_html .= '<h3 class="address-hdng">' . $address;
		if (!empty($address)) {
			$sl_html .= '<a target="blank" class="header__title__map-link" href="' . $google_map_link . '">MAP</a>';
		}
		$sl_html .= '</h3>';
		$sl_html .= '<p class="bed-bath-std">';

		$sl_html .= '<img class="bedimg" src="' . $lfdl_plugin_url . 'images/sleep.png">';
		if (!empty($property_beds)) {
			$sl_html .= '<span>' . $property_beds . ' Beds</span>';
		}
		$sl_html .= ' <img class="bathimg" src="' . $lfdl_plugin_url . 'images/bathtub.png">';
		if (!empty($property_baths)) {
			$sl_html .= '<span>' . $property_baths . ' Baths</span>';
		}
		if (!empty($property_sqfeet)) {
			$sl_html .= '<span> | ' . $property_sqfeet . ' sq ft</span>';
		}
		$sl_html .= '</p>';
		$sl_html .= '</div>';
		$sl_html .= '<div class="details-right">';
		$sl_html .= '<p class="rent-hdng"><img class="price-tag" src="' . $lfdl_plugin_url . 'images/dollar-tag.png">';
		if (!empty($property_rent)) {
			$sl_html .= '$' . $property_rent;
		}
		$sl_html .= '</p>';


		$sl_html .= '<p style="margin-bottom: 1rem;">';
		$sl_html .= '<img class="avail-now" src="' . $lfdl_plugin_url . 'images/check.png">';
		if (!empty($property_availability)) {
			$sl_html .= '<span id="avail-txt">' . $property_availability . '</span>';
		}
		$sl_html .= '</p>';




		$sl_html .= '</div>
						</div>';

		$sl_html .= '<h3 class="desctitle">' . $post_content . '</h3>';

		$sl_html .= '<p class="desc">' . $post_title . '</p>
						<div class="lfdl-half">';
		if($leaseLength) {
			$sl_html .= '<p><strong>Lease Length</strong></p>';
			$sl_html .= '<ul>';
			$sl_html .= '<li>'.$leaseLength.'</li>';
			$sl_html .= '</ul>';
		}

		if($marketRent) {
			$sl_html .= '<p><strong>Market Rent</strong></p>';
			$sl_html .= '<ul>';
			$sl_html .= '<li>$'.$marketRent.'</li>';
			$sl_html .= '</ul>';
		}

		if (!empty($amenities)) {
			$sl_html .= '<p><strong>Amenities:</strong></p>';
			$sl_html .= '<ul>';
			foreach ($amenities as $amenity) {
				$sl_html .= '<li>' . esc_html($amenity) . '</li>';
			}
			$sl_html .= '</ul>';
		}
		if (!empty($contact)) {
			$sl_html .= '<p><strong>Contact Information</strong></p>';
			$sl_html .= '<ul>';
			foreach ($contact as $phoneType => $info) {
				$sl_html .= '<li><p><strong>' . $phoneType . ':</strong> ' . $info['name'] . '</p>';
				$sl_html .= '<a class="call-top" href="tel:' . $info['no'] . '"><img class="call-now" src="' . $lfdl_plugin_url . 'images/phone-call.png"><strong>' . $info['no'] . '</strong></a></li>';
			}
		}
		if (!empty($email)) {
			$sl_html .= '<li><p><strong>Email:</strong>' . $email . ' </p></li>';
		}
		$sl_html .= '</ul>';
		

		$sl_html .= '</div>';

		$sl_html .= '<div class="lfdl-half apply-sec">';
		if (!empty($property_apply_link)) {
			$sl_html .= '<a id="applyBtn" class="sl-btns" target="_blank" href="' . $property_apply_link . '">Apply Now</a>';
		}


		$sl_html .= '</div></div>';

		$sl_html .= '</div>';
		
	} 
	else {
		$sl_html = 'Not found';
	}
	
	return $sl_html;

}