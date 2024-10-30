<?php

namespace Infixs\CorreiosAutomatico\Controllers\Rest;

use Infixs\CorreiosAutomatico\Container;
use Infixs\CorreiosAutomatico\Core\Admin\WooCommerce\Tracking;

defined( 'ABSPATH' ) || exit;
class TrackingController {

	/**
	 * Create a tracking code.
	 * 
	 * @since 1.0.0
	 * 
	 * @param \WP_REST_Request $request
	 * 
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function create( $request ) {
		$params = $request->get_params();

		if ( ! isset( $params['order_id'] ) ) {
			return new \WP_Error( 'order_id_not_found', __( 'Order id not found.', 'infixs-correios-automatico' ), [ 'status' => 404 ] );
		}

		if ( ! isset( $params['code'] ) ) {
			return new \WP_Error( 'tracking_code_not_found', __( 'Tracking code not found.', 'infixs-correios-automatico' ), [ 'status' => 404 ] );
		}

		$order = wc_get_order( $params['order_id'] );
		$send_email = isset( $params['sendmail'] ) && $params['sendmail'] === true ? true : false;

		$tracking_code = apply_filters( 'infixs_correios_automatico_tracking_create', $params['code'], $params );
		$created_tracking = Container::trackingService()->add( $order->get_id(), $tracking_code );

		if ( ! $created_tracking ) {
			return new \WP_Error( 'tracking_code_not_created', __( 'Tracking code not created.', 'infixs-correios-automatico' ), [ 'status' => 500 ] );
		}

		if ( $send_email ) {
			Tracking::trigger_tracking_code_email( $order, $tracking_code );
		}

		return rest_ensure_response( [ 
			"status" => "success",
			"data" => [ 
				"id" => $created_tracking->id,
			]
		] );
	}

	/**
	 * Delete a tracking code.
	 * 
	 * @since 1.0.0
	 * @param \WP_REST_Request $request
	 * 
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function delete( $request ) {
		$params = $request->get_params();

		$tracking_code_id = apply_filters( 'infixs_correios_automatico_tracking_delete', $request['id'], $request );
		$removed = Container::trackingService()->delete( $tracking_code_id );

		if ( ! $removed ) {
			return new \WP_Error( 'tracking_code_not_found', __( 'Tracking code not found.', 'infixs-correios-automatico' ), [ 'status' => 404 ] );
		}

		return rest_ensure_response( [ 
			"status" => "success",
		] );
	}

	/**
	 * List tracking codes.
	 * 
	 * @since 1.0.0
	 * 
	 * @param \WP_REST_Request $request
	 * 
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function list( $request ) {
		$params = $request->get_params();

		//$tracking_codes = Container::trackingService()->get( $params['order_id'] );

		return rest_ensure_response( [ 
			"status" => "success",
			"data" => [],
		] );
	}
}