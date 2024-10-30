<?php
if (!defined('ABSPATH')) {
    exit;
}

add_action('wp_ajax_lfdl_sync_listings', 'lfdl_sync_listings');

function lfdl_sync_listings() {
	$res_array = array();

	if ( !isset($_POST) || empty($_POST) || !is_user_logged_in() || ! wp_verify_nonce( sanitize_text_field( wp_unslash ( $_POST['lfdl_nonce'] ) ) , 'lfdl_ajax_admin_obj_nonce' ) )  {
		header('HTTP/1.1 400 Empty POST Values');
		$res_array['error'] = __('Error - Could not verify POST values', 'better-listings-for-rent');
		echo wp_json_encode($res_array);
		exit;
	}

	$url = get_option('lfdl_url');
	$xml = simplexml_load_file($url);

	$all_cities = get_option('lfdl_all_cities') ? get_option('lfdl_all_cities') : array();
	$all_zip_codes = get_option('lfdl_all_zip_codes') ? get_option('lfdl_all_zip_codes') : array();

	if (isset($xml->Property)) {
		$properties = $xml->Property;

		foreach ($properties as $property) {
			$propertyID = $property->PropertyID;
			$propertyIDValue = isset($property->attributes()->IDValue) ? (string) $property->attributes()->IDValue : '';

			$existing_post = get_posts(array(
				'post_type' => 'listing',
				'meta_key' => 'property_id',
				'meta_value' => $propertyIDValue,
			));

			 // Initialize variables
			 $city = '';
			 $zip = '';
			 $address = '';
			 $description = '';
			 $rent = '';
			 $baths = '';
			 $beds = '';
			 $sq_ft = '';
			 $availability = '';
			 $applyLink = '';
			 $phn_no = '';
			 $email = '';
			 $latitude = '';
			 $longitude = '';
			 $amenities = array();
			 $image_urls = array();

			// Check and assign values
			if (isset($propertyID->Address->City)) {
				$city = (string) $propertyID->Address->City;
			}
			if (isset($propertyID->Address->PostalCode)) {
				$zip = (string) $propertyID->Address->PostalCode;
			}
			if (isset($propertyID->Address->Address) && isset($propertyID->Address->City) && isset($propertyID->Address->State) && isset($propertyID->Address->Country) && isset($propertyID->Address->PostalCode)) {
				$address = (string) $propertyID->Address->Address . ', ' . $propertyID->Address->City . ', ' . $propertyID->Address->State . ', ' . $propertyID->Address->Country . ' ' . $propertyID->Address->PostalCode;
			}
			if (isset($property->Information->LongDescription)) {
				$description = (string) $property->Information->LongDescription;
			}
			if (isset($property->Information->LeaseLength)) {
				$leaseLength = (string) $property->Information->LeaseLength;
			}
			if (isset($property->ILS_Unit->Units->Unit->UnitRent)) {
				$rent = (string) $property->ILS_Unit->Units->Unit->UnitRent;
			}
			if (isset($property->ILS_Unit->Units->Unit->MarketRent)) {
				$marketRent = (string) $property->ILS_Unit->Units->Unit->MarketRent;
			}
			if (isset($property->ILS_Unit->Units->Unit->UnitBathrooms)) {
				$baths = (string) $property->ILS_Unit->Units->Unit->UnitBathrooms;
			}
			if (isset($property->ILS_Unit->Units->Unit->UnitBedrooms)) {
				$beds = (string) $property->ILS_Unit->Units->Unit->UnitBedrooms;
			}
			if (isset($property->ILS_Unit->Units->Unit->MinSquareFeet)) {
				$sq_ft = (string) $property->ILS_Unit->Units->Unit->MinSquareFeet;
			}
			if (isset($property->ILS_Unit->Availability->VacateDate->attributes()->Month) && isset($property->ILS_Unit->Availability->VacateDate->attributes()->Day) && isset($property->ILS_Unit->Availability->VacateDate->attributes()->Year)) {
				$availability = date('Y-m-d', strtotime($property->ILS_Unit->Availability->VacateDate->attributes()->Month . '/' . $property->ILS_Unit->Availability->VacateDate->attributes()->Day . '/' . $property->ILS_Unit->Availability->VacateDate->attributes()->Year));
			}
			if (isset($property->ILS_Unit->Availability->UnitAvailabilityURL)) {
				$applyLink = (string) $property->ILS_Unit->Availability->UnitAvailabilityURL;
			}

			$contact = array();
			if(isset($propertyID->Phone)) {
				foreach($propertyID->Phone as $Phone) {
					if (isset($Phone->attributes()->PhoneType)) {
					   $phoneType = (string)$Phone->attributes()->PhoneType;
					   if (isset($Phone->PhoneDescription)) {
						$contact[$phoneType]['name'] = (string)$Phone->PhoneDescription;
					   }

					   if (isset($Phone->PhoneNumber)) {
						$contact[$phoneType]['no'] = (string)$Phone->PhoneNumber;
					   }

					}
				}
			}


			if (isset($propertyID->Email)) {
				$email = (string) $propertyID->Email;
			}
			if (isset($property->ILS_Identification->Latitude)) {
				$latitude = (string) $property->ILS_Identification->Latitude;
			}
			if (isset($property->ILS_Identification->Longitude)) {
				$longitude = (string) $property->ILS_Identification->Longitude;
			}

			if (isset($property->ILS_Unit->Amenity)) {
				foreach ($property->ILS_Unit->Amenity as $amenity) {
					$amenities[] = (string) $amenity->Description;
				}
			}

			if (isset($property->ILS_Unit->File)) {
				foreach ($property->ILS_Unit->File as $image) {
					$image_url = (string) $image->Src;
					$image_name = (string) $image->Name;
					$image_urls[] = array(
						'url' => $image_url,
						'name' => $image_name
					);
				}
			}

			$post_id = '';

			if (!empty($existing_post)) {
				$post_id = $existing_post[0]->ID;

				$post_args = array(
					'post_title' => $address,
					'post_content' => $description,
				);
				wp_update_post($post_args);
			} else {
				$post_args = array(
					'post_title' => $address,
					'post_content' => $description,
					'post_status' => 'publish',
					'post_type' => 'listing',
				);
				$post_id = wp_insert_post($post_args);
				
			}

			$existing_attachments = get_post_meta($post_id, 'property_attachment_ids', true) ? get_post_meta($post_id, 'property_attachment_ids', true) : array();

			$new_attachment_ids = array();
			$attachment_to_delete = array();

			// Step 1: Add new images to media library and attach to the post
			foreach ($image_urls as $image_data) {
				$image_url = $image_data['url'];
				$image_name = $image_data['name'];

				// Check if the image is already attached to the post
				$attached = false;
				foreach ($existing_attachments as $existing_attachment) {
					$existing_attachment_data = get_post($existing_attachment);
					if ($existing_attachment_data && $existing_attachment_data->post_title === $image_name) {
						$attached = true;
						break;
					}
				}

				if (!$attached) {
					$response = wp_safe_remote_get($image_url);

					if (!is_wp_error($response)) {
						$image_data = wp_remote_retrieve_body($response);
						$file_name = sanitize_file_name(wp_basename($image_url));
						$upload = wp_upload_bits($file_name, null, $image_data);

						if (!$upload['error']) {
							$file_path = $upload['file'];
							$attachment = array(
								'post_title' => $image_name,
								'post_content' => '',
							);
							$attach_id = wp_insert_attachment($attachment, $file_path);

							if (!is_wp_error($attach_id)) {
								$new_attachment_ids[] = $attach_id;
							} else {
								$res_array['error'] = 'Error during attachment';
								echo wp_json_encode($res_array);
								exit;
							}
						} else {
							$res_array['error'] = 'Error during upload';
							echo wp_json_encode($res_array);
							exit;
						}
					}
				}
			}

			// Step 2: Detach any images that are no longer associated with the post
			foreach ($existing_attachments as $existing_attachment) {
				$existing_attachment_data = get_post($existing_attachment);
				$existing_attachment_name = $existing_attachment_data->post_title;
				
				// Check if the existing attachment name exists in the fetched images
				$detached = true;
				foreach ($image_urls as $image_data) {
					$image_name = $image_data['name'];
					if ($existing_attachment_name === $image_name) {
						$detached = false;
						break;
					}
				}

				if ($detached) {
					// Detach the image from the post
					wp_update_post(array(
						'ID' => $existing_attachment,
						'post_parent' => 0,
					));

					// Schedule the image for deletion
					$attachment_to_delete[] = $existing_attachment;
				}
			}

			// Step 3: Delete the scheduled images
			if (!empty($attachment_to_delete)) {
				foreach ($attachment_to_delete as $attachment_id) {
					wp_delete_attachment($attachment_id, true); // Set the second parameter to true for permanent deletion
				}
			}

			// Update the post meta with the new attachment IDs
			$new_attachment_ids = array_merge($existing_attachments, $new_attachment_ids);
			update_post_meta($post_id, 'property_attachment_ids', $new_attachment_ids);

			if(!in_array($city, $all_cities)){
				$all_cities[] = $city;
			}

			if(!in_array($zip, $all_zip_codes)){
				$all_zip_codes[] = $zip;
			}
			
			update_post_meta($post_id, 'property_lease', $leaseLength);
			update_post_meta($post_id, 'property_lat', $latitude);
			update_post_meta($post_id, 'property_long', $longitude);
			update_post_meta($post_id, 'property_city', $city);
			update_post_meta($post_id, 'property_zip', $zip);
			update_post_meta($post_id, 'property_id', $propertyIDValue);
			update_post_meta($post_id, 'property_rent', $rent);
			update_post_meta($post_id, 'property_market_rent', $marketRent);
			update_post_meta($post_id, 'property_baths', $baths);
			update_post_meta($post_id, 'property_beds', $beds);
			update_post_meta($post_id, 'property_sq_ft', $sq_ft);
			update_post_meta($post_id, 'property_availability', $availability);
			update_post_meta($post_id, 'property_apply_link', $applyLink);
			update_post_meta($post_id, 'property_phn_no', $contact);
			update_post_meta($post_id, 'property_email', $email);
			update_post_meta($post_id, 'property_amenities', serialize($amenities));
		}
	}

	update_option('lfdl_all_cities', $all_cities);
	update_option('lfdl_all_zip_codes', $all_zip_codes);

	$res_array['success'] = 'Yes';
	echo wp_json_encode($res_array);
	exit;
}
?>