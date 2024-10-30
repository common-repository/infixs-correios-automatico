<?php

namespace Infixs\CorreiosAutomatico\Controllers\Rest;

use Infixs\CorreiosAutomatico\Core\Support\Config;
use Infixs\CorreiosAutomatico\Core\Support\Log;

defined( 'ABSPATH' ) || exit;
class SettingsGeneralController {

	/**
	 * Auth settings save
	 * 
	 * @since 1.0.0
	 * 
	 * @param \WP_REST_Request $request
	 * 
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function save( $request ) {
		$data = $request->get_json_params();

		do_action( 'infixs_correios_automatico_before_save_general_settings', $data );

		$updated_settings = [];

		$updated_log_settings = [];

		if ( isset( $data['autofill_address'] ) )
			$updated_settings['autofill_address'] = rest_sanitize_boolean( $data['autofill_address'] );

		if ( isset( $data['calculate_shipping_product_page'] ) )
			$updated_settings['calculate_shipping_product_page'] = rest_sanitize_boolean( $data['calculate_shipping_product_page'] );

		if ( isset( $data['calculate_shipping_product_page_position'] ) )
			$updated_settings['calculate_shipping_product_page_position'] = sanitize_text_field( $data['calculate_shipping_product_page_position'] );

		if ( isset( $data['show_order_tracking_form'] ) )
			$updated_settings['show_order_tracking_form'] = rest_sanitize_boolean( $data['show_order_tracking_form'] );

		if ( isset( $data['show_order_label_form'] ) )
			$updated_settings['show_order_label_form'] = rest_sanitize_boolean( $data['show_order_label_form'] );

		if ( isset( $data['show_order_prepost_form'] ) )
			$updated_settings['show_order_prepost_form'] = rest_sanitize_boolean( $data['show_order_prepost_form'] );

		if ( isset( $data['debug_active'] ) )
			$updated_log_settings['active'] = rest_sanitize_boolean( $data['debug_active'] );

		if ( isset( $data['debug_log'] ) )
			$updated_log_settings['debug_log'] = rest_sanitize_boolean( $data['debug_log'] );

		if ( isset( $data['info_log'] ) )
			$updated_log_settings['info_log'] = rest_sanitize_boolean( $data['info_log'] );

		if ( isset( $data['notice_log'] ) )
			$updated_log_settings['notice_log'] = rest_sanitize_boolean( $data['notice_log'] );

		if ( isset( $data['warning_log'] ) )
			$updated_log_settings['warning_log'] = rest_sanitize_boolean( $data['warning_log'] );

		if ( isset( $data['error_log'] ) )
			$updated_log_settings['error_log'] = rest_sanitize_boolean( $data['error_log'] );

		if ( isset( $data['critical_log'] ) )
			$updated_log_settings['critical_log'] = rest_sanitize_boolean( $data['critical_log'] );

		if ( isset( $data['alert_log'] ) )
			$updated_log_settings['alert_log'] = rest_sanitize_boolean( $data['alert_log'] );

		if ( isset( $data['emergency_log'] ) )
			$updated_log_settings['emergency_log'] = rest_sanitize_boolean( $data['emergency_log'] );

		$updated_settings = apply_filters( 'infixs_correios_automatico_save_general_settings', $updated_settings, $data );

		if ( ! empty( $updated_settings ) ) {
			Config::update( 'general', $updated_settings );
			Log::debug( 'Configurações gerais salvas' );
		}

		if ( ! empty( $updated_log_settings ) ) {
			Config::update( 'debug', apply_filters( 'infixs_correios_automatico_save_debug_settings', $updated_log_settings, $data ) );
			Log::debug( 'Configurações de depuração salvas' );
		}



		$response_data = $this->prepare_data();

		$response = [ 
			'status' => 'success',
			'data' => $response_data,
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
	 * 
	 * @param array $settings
	 * 
	 * @return array
	 */
	protected function prepare_data() {
		$sanitized_settings = [ 
			'autofill_address' => Config::boolean( 'general.autofill_address' ),
			'calculate_shipping_product_page' => Config::boolean( 'general.calculate_shipping_product_page' ),
			'calculate_shipping_product_page_position' => Config::string( 'general.calculate_shipping_product_page_position' ),
			'show_order_tracking_form' => Config::boolean( 'general.show_order_tracking_form' ),
			'show_order_label_form' => Config::boolean( 'general.show_order_label_form' ),
			'show_order_prepost_form' => Config::boolean( 'general.show_order_prepost_form' ),
			'debug_active' => Config::boolean( 'debug.active' ),
			'debug_log' => Config::boolean( 'debug.debug_log' ),
			'info_log' => Config::boolean( 'debug.info_log' ),
			'notice_log' => Config::boolean( 'debug.notice_log' ),
			'warning_log' => Config::boolean( 'debug.warning_log' ),
			'error_log' => Config::boolean( 'debug.error_log' ),
			'critical_log' => Config::boolean( 'debug.critical_log' ),
			'alert_log' => Config::boolean( 'debug.alert_log' ),
			'emergency_log' => Config::boolean( 'debug.emergency_log' ),
		];

		return apply_filters( 'infixs_correios_automatico_prepare_general_settings', $sanitized_settings );
	}
}