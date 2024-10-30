<?php

namespace Infixs\CorreiosAutomatico\Controllers\Rest;

use Infixs\CorreiosAutomatico\Core\Support\Config;
use Infixs\CorreiosAutomatico\Utils\Sanitizer;
use Infixs\CorreiosAutomatico\Validators\SettingsLabelValidator;

defined( 'ABSPATH' ) || exit;

/**
 * Settings label controller
 * 
 * @since 1.0.0
 * 
 * @package Infixs\CorreiosAutomatico\Controllers\Rest
 */
class SettingsLabelController {

	/**
	 * Label settings save
	 * 
	 * @since 1.0.0
	 * @param \WP_REST_Request $request
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function save( $request ) {
		$validator = new SettingsLabelValidator( $request );
		$validator->validate();

		if ( $validator->hasErrors() ) {
			return new \WP_Error( 'invalid_data', 'Invalid data', [ 'errors' => $validator->errors() ] );
		}

		$data = $validator->all();

		$updated_settings = [ 
			'style' => sanitize_text_field( $data['style'] ),
			'show_border' => rest_sanitize_boolean( $data['show_border'] ),
			'font_size' => (int) $data['font_size'],
			'width' => (int) $data['width'],
			'line_height' => (int) $data['line_height'],
			'show_logo' => rest_sanitize_boolean( $data['show_logo'] ),
			'logo_url' => sanitize_text_field( $data['logo_url'] ),
			'show_recipient_form' => rest_sanitize_boolean( $data['show_recipient_form'] ),
			'show_sender_info' => rest_sanitize_boolean( $data['show_sender_info'] ),
			'show_recipient_barcode' => rest_sanitize_boolean( $data['show_recipient_barcode'] ),
			'recipient_barcode_height' => (int) $data['recipient_barcode_height'],
			'logo_width' => (int) $data['logo_width'],
			'page_margin' => (int) $data['page_margin'],
			'items_gap' => (int) $data['items_gap'],
			'columns_length' => (int) $data['columns_length'],
		];

		Config::update( 'label', $updated_settings );

		$response_data = $this->prepare_data();

		$response = [ 
			'status' => 'success',
			'label' => $response_data,
		];
		return rest_ensure_response( $response );
	}

	public function retrieve() {
		$sanitized_settings = $this->prepare_data();

		return rest_ensure_response( $sanitized_settings );
	}

	/**
	 * Prepare the data
	 *
	 * @since 1.0.0
	 * @param array $settings
	 * @return array
	 */
	public function prepare_data() {
		$sanitized_settings = [ 
			'style' => Config::string( 'label.style' ),
			'show_border' => Config::boolean( 'label.show_border' ),
			'font_size' => Config::integer( 'label.font_size' ),
			'width' => Config::integer( 'label.width' ),
			'line_height' => Config::integer( 'label.line_height' ),
			'show_logo' => Config::boolean( 'label.show_logo' ),
			'logo_url' => Config::string( 'label.logo_url' ),
			'show_recipient_form' => Config::boolean( 'label.show_recipient_form' ),
			'show_sender_info' => Config::boolean( 'label.show_sender_info' ),
			'show_recipient_barcode' => Config::boolean( 'label.show_recipient_barcode' ),
			'recipient_barcode_height' => Config::integer( 'label.recipient_barcode_height' ),
			'logo_width' => Config::integer( 'label.logo_width' ),
			'page_margin' => Config::integer( 'label.page_margin' ),
			'items_gap' => Config::integer( 'label.items_gap' ),
			'columns_length' => Config::integer( 'label.columns_length' ),
		];

		return $sanitized_settings;
	}
}