<?php

namespace Infixs\CorreiosAutomatico\Services;

use Infixs\CorreiosAutomatico\Entities\Order;
use Infixs\CorreiosAutomatico\Utils\Sanitizer;

defined( 'ABSPATH' ) || exit;

class LabelService {

	/**
	 * Get labels from orders
	 * 
	 * @param array $orders
	 * @return array
	 */
	public function getLabelsFromOrders( $order_ids ) {
		$labels = [];

		foreach ( $order_ids as $order_id ) {
			$label = $this->getLabelFromOrder( $order_id );
			if ( ! $label ) {
				continue;
			}
			$labels[] = $label;
		}

		return $labels;
	}

	public function getLabelFromOrder( $order_id ) {
		$order = Order::fromId( $order_id );

		if ( ! $order ) {
			return false;
		}

		$address = $order->getAddress();

		$items = [];
		foreach ( $order->getItems() as $item ) {
			$items[] = [ 
				'name' => $item->get_name(),
				'quantity' => $item->get_quantity(),
				'amount' => Sanitizer::money100( $item->get_total() )
			];
		}

		$package = $order->getPackage();

		$package_data = $package->get_data();

		return [ 
			'name' => $order->getCustomerFullName(),
			'document' => $order->getCustomerDocument(),
			'address_street' => $address->getStreet(),
			'address_number' => $address->getNumber(),
			'address_complement' => $address->getComplement(),
			'address_neighborhood' => $address->getNeighborhood(),
			'address_city' => $address->getCity(),
			'address_state' => $address->getState(),
			'address_postalcode' => $address->getPostCode(),
			'total_weight' => $package_data['weight'],
			'total_amount' => Sanitizer::money100( $order->getSubtotal() ),
			'items_count' => $package->get_items_count(),
			'items' => $items,
		];
	}

}