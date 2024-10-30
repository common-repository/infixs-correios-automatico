<?php

namespace Infixs\CorreiosAutomatico\Repositories;

use Infixs\CorreiosAutomatico\Models\Prepost;

defined( 'ABSPATH' ) || exit;

/**
 * Prepost repository.
 * 
 * @package Infixs\CorreiosAutomatico
 * @since   1.0.0
 */
class PrepostRepository {

	/**
	 * Create a new prepost.
	 * 
	 * @since 1.0.0
	 * 
	 * @param array{
	 *      external_id: string,
	 *      object_code: string,
	 *      service_code: string,
	 *      payment_type: int,
	 *      height: string,
	 *      width: string,
	 *      length: string,
	 *      weight: string,
	 *      request_pickup: int,
	 *      reverse_logistic: int,
	 *      status: int,
	 *      status_label: string,
	 *      expire_at: string,
	 *      updated_at: string,
	 *      created_at: string
	 * } $data
	 * 
	 * @return bool|int
	 */
	public function create( $data ) {
		return Prepost::create( $data );
	}

	/**
	 * Retrieve a prepost by its ID.
	 * 
	 * @param mixed $id
	 * 
	 * @return Prepost|null
	 */
	public function find( $id ) {
		return Prepost::find( $id );
	}
}