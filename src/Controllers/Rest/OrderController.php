<?php

namespace Infixs\CorreiosAutomatico\Controllers\Rest;

use Infixs\CorreiosAutomatico\Services\OrderService;

defined( 'ABSPATH' ) || exit;
class OrderController {

	/**
	 * Order controller instance.
	 * 
	 * @since 1.0.0
	 * 
	 * @var OrderService
	 */
	private $orderService;

	public function __construct( OrderService $orderService ) {
		$this->orderService = $orderService;
	}

	/**
	 * List orders.
	 * 
	 * @since 1.0.0
	 * 
	 * @param \WP_REST_Request $request
	 * 
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function list( $request ) {
		$page = $request->get_param( 'page' );
		$per_page = $request->get_param( 'per_page' );
		$search = $request->get_param( 'search' );

		$orders = $this->orderService->getOrders( [ 
			'page' => $page,
			'per_page' => $per_page,
			'search' => $search
		] );

		return rest_ensure_response(
			array_merge( [ 
				"status" => "success",
			],
				$orders
			)
		);
	}
}