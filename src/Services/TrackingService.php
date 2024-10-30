<?php

namespace Infixs\CorreiosAutomatico\Services;

use Infixs\CorreiosAutomatico\Repositories\TrackingRepository;

defined( 'ABSPATH' ) || exit;

/**
 * Tracking service.
 * 
 * @package Infixs\CorreiosAutomatico
 * @since   1.0.0
 */
class TrackingService {

	/**
	 * Tracking repository.
	 * 
	 * @var TrackingRepository
	 * @since 1.0.0
	 */
	protected $trackingRepository;

	/**
	 * Tracking Service constructor.
	 * 
	 * @param TrackingRepository $trackingRepository
	 * @since 1.0.0
	 */
	public function __construct( $trackingRepository ) {
		$this->trackingRepository = $trackingRepository;
	}

	/**
	 * Add a tracking code.
	 * 
	 * This method is responsible for adding a tracking code to the order.
	 * 
	 * @since 1.0.0
	 * 
	 * @param int $order_id Order ID.
	 * @param string $code Tracking code.
	 * 
	 * @return int|bool The ID of the tracking code or false on error.
	 */
	public function add( $order_id, $code ) {
		return $this->trackingRepository->create( [ 
			'order_id' => $order_id,
			'code' => $code,
			'user_id' => get_current_user_id(),
		] );
	}

	/**
	 * Delete a tracking code.
	 * 
	 * @since 1.0.0
	 * 
	 * @param int $tracking_id Tracking ID.
	 * 
	 * @return int|bool The number of rows affected or false on error.
	 */
	public function delete( $tracking_id ) {
		return $this->trackingRepository->delete( $tracking_id );
	}

	/**
	 * List tracking codes.
	 * 
	 * @param mixed $order_id
	 * @param array $config {
	 * 		@type array $order {
	 * 			@type string $column Column name.
	 * 			@type string $order Order direction "asc" or "desc".
	 * 		}
	 * }
	 * 
	 * @return \Infixs\WordpressEloquent\Collection
	 */
	public function list( $order_id, $config = [] ) {
		return $this->trackingRepository->findBy( [ 'order_id' => $order_id ], $config );
	}
}