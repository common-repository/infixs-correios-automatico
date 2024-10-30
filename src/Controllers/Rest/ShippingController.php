<?php

namespace Infixs\CorreiosAutomatico\Controllers\Rest;

use Infixs\CorreiosAutomatico\Container;
use Infixs\CorreiosAutomatico\Utils\Sanitizer;

defined( 'ABSPATH' ) || exit;

/**
 * Shipping Controller
 * 
 * @since 1.0.0
 * 
 * @package Infixs\CorreiosAutomatico\Controllers\Rest
 */
class ShippingController {

	/**
	 * Get shipping methods
	 * 
	 * @since 1.0.0
	 * 
	 * @param \WP_REST_Request $request
	 * 
	 * @return array
	 */
	public function list_shipping_methods( $request ) {
		$params = $request->get_params();

		$enabled = isset( $params['is_enabled'] ) ? Sanitizer::boolean( $params['is_enabled'] ) : null;
		$method_id = isset( $params['method_id'] ) ? Sanitizer::array_strings( $params['method_id'] ) : null;

		try {
			$shipping_methods = Container::shippingService()->list_shipping_methods( [ 
				'is_enabled' => $enabled,
				'method_id' => $method_id,
			] );

			return [ 
				'shipping_methods' => $shipping_methods,
			];
		} catch (\Exception $e) {
			return [ 
				'shipping_methods' => [],
			];
		}
	}

	/**
	 * Import shipping methods
	 * 
	 * @since 1.0.0
	 * 
	 * @param \WP_REST_Request $request
	 * 
	 * @return array
	 */
	public function import_shipping_methods( $request ) {
		$data = $request->get_json_params();

		$plugin_ids = Sanitizer::array_strings( $data['plugins'] );
		$disable_imported_methods = Sanitizer::boolean( $data['disable_imported_methods'] );

		$result = Container::shippingService()->import_shipping_methods_by_plugin_id( $plugin_ids, $disable_imported_methods );

		return array_merge( $result, [ 
			"success" => true,
		] );
	}
}