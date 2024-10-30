<?php

namespace Infixs\CorreiosAutomatico\Services;
use Infixs\CorreiosAutomatico\Entities\Order;
use Infixs\CorreiosAutomatico\Models\WoocommerceOrderItem;
use Infixs\CorreiosAutomatico\Models\WoocommerceOrderItemmeta;

defined( 'ABSPATH' ) || exit;

class OrderService {
	/**
	 * Get orders
	 * 
	 * @since 1.0.0
	 * 
	 * @param array{
	 * 			page: int,
	 * 			per_page: int
	 * 			search: string
	 * } $query Query parameters.
	 * 
	 * @return array
	 */
	public function getOrders( $query ) {

		$page = $query['page'] ?? 1;
		$per_page = $query['per_page'] ?? 10;
		$search = $query['search'] ?? null;

		// phpcs:ignore
		$method_where = [ 
			'meta_key' => 'method_id',
			'meta_value' => 'infixs-correios-automatico'
		];

		$total_count = WoocommerceOrderItemmeta::where( $method_where )
			->count();

		$order_item_ids = WoocommerceOrderItemmeta::select( [ 'order_item_id' ] )
			->where( $method_where )
			->groupBy( 'order_item_id' )
			->orderBy( 'order_item_id', 'desc' )
			->limit( $per_page )
			->offset( ( $page - 1 ) * $per_page )
			->get()
			->pluck( 'order_item_id' )
			->toArray();


		$order_ids = WoocommerceOrderItem::select( [ 'order_id' ] )
			->whereIn( 'order_item_id', $order_item_ids )
			->orderBy( 'order_id', 'desc' )
			->groupBy( 'order_id' )
			->get()
			->pluck( 'order_id' )
			->toArray();

		$orders = [];

		foreach ( $order_ids as $order_id ) {
			$order = wc_get_order( $order_id );
			if ( $order ) {
				$orders[] = $order;
			}
		}

		$data = $this->transformOrders( $orders );

		return [ 
			'page' => $page,
			'per_page' => $per_page,
			'total_results' => count( $data ),
			'total' => $total_count,
			'orders' => $data,
		];
	}

	/**
	 * Transform a WC_Order object into the desired array format.
	 *
	 * @param \WC_Order[] $orders Orders.
	 * @return array
	 */
	private function transformOrders( $orders ) {
		$result = [];
		foreach ( $orders as $order ) {
			$ca_order = new Order( $order );
			$result[] = $ca_order->toArray();
		}

		return $result;
	}
}