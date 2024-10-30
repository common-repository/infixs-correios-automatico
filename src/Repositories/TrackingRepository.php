<?php

namespace Infixs\CorreiosAutomatico\Repositories;

use Infixs\CorreiosAutomatico\Models\TrackingCode;

defined( 'ABSPATH' ) || exit;

/**
 * Tracking repository.
 * 
 * @package Infixs\CorreiosAutomatico
 * @since   1.0.0
 */
class TrackingRepository {

	/**
	 * Create a tracking code.
	 * 
	 * @since 1.0.0
	 * 
	 * @param array $data {
	 *    An array of elements that create a tracking code.
	 *
	 *    @type int     $order_id   Order ID.
	 *    @type string  $code    Tracking code.
	 *    @type int     $user_id    User ID.
	 * }
	 * 
	 * @return int|bool The ID of the tracking code or false on error.
	 */
	public function create( $data ) {
		$data['created_at'] = current_time( 'mysql' );
		$data['updated_at'] = current_time( 'mysql' );
		return TrackingCode::create( $data );
	}

	/**
	 * Delete a tracking code.
	 * 
	 * @since 1.0.0
	 * 
	 * @param int $id Tracking code ID.
	 * 
	 * @return int|bool The number of rows affected or false on error.
	 */
	public function delete( $id ) {
		return TrackingCode::where( 'id', $id )->delete();
	}

	/**
	 * Find tracking code by where.
	 * 
	 * @since 1.0.0
	 * 
	 * @param array $where Fields.
	 * @param array $config {
	 * 		@type array $order {
	 * 			@type string $column Column name.
	 * 			@type string $order Order direction "asc" or "desc".
	 * 		}
	 * }
	 * 
	 * @return \Infixs\WordpressEloquent\Collection
	 */
	public function findBy( $where, $config = [] ) {
		$builder = TrackingCode::where( $where );

		if ( isset( $config['order'] ) ) {
			$builder->orderBy( $config['order']['column'], $config['order']['order'] );
		}

		return $builder->get();
	}
}