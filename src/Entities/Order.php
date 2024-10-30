<?php

namespace Infixs\CorreiosAutomatico\Entities;

use Infixs\CorreiosAutomatico\Core\Shipping\CorreiosShippingMethod;
use Infixs\CorreiosAutomatico\Models\TrackingCode;
use Infixs\CorreiosAutomatico\Services\Correios\Includes\Package;
use Infixs\CorreiosAutomatico\Utils\Sanitizer;

defined( 'ABSPATH' ) || exit;

class Order {
	/**
	 * Order instance.
	 * 
	 * @var \WC_Order
	 */
	private $order;

	/**
	 * Order constructor.
	 * 
	 * @param \WC_Order $order
	 */
	public function __construct( $order ) {
		$this->order = $order;
	}


	/**
	 * Get order from id.
	 * 
	 * @since 1.0.0
	 * 
	 * @param int $order Order id.
	 * 
	 * @return Order|false
	 */
	public static function fromId( $order ) {
		$order = wc_get_order( $order );
		return $order ? new self( $order ) : false;
	}

	/**
	 * Extract address from order.
	 * 
	 * @since 1.0.0
	 * 
	 * @param \WC_Order $order
	 * 
	 * @return Address
	 */
	public function getAddress() {
		if ( $this->order->has_shipping_address() ) {
			return new Address(
				Sanitizer::numeric_text( $this->order->get_shipping_postcode() ),
				$this->order->get_shipping_address_1(),
				$this->order->get_meta( '_shipping_number' ),
				$this->order->get_meta( '_shipping_neighborhood' ),
				$this->order->get_shipping_city(),
				$this->order->get_shipping_state(),
				$this->order->get_shipping_address_2(),
			);
		} else {
			return new Address(
				Sanitizer::numeric_text( $this->order->get_billing_postcode() ),
				$this->order->get_billing_address_1(),
				$this->order->get_meta( '_billing_number' ),
				$this->order->get_meta( '_billing_neighborhood' ),
				$this->order->get_billing_city(),
				$this->order->get_billing_state(),
				$this->order->get_billing_address_2()
			);
		}
	}

	public function getLastTrackingCode() {
		$model = TrackingCode::where( 'order_id', $this->order->get_id() )->orderBy( 'id', 'desc' )->first();
		if ( ! $model ) {
			return null;
		}
		return $model->code;
	}

	/**
	 * Get customer from order.
	 * 
	 * @since 1.0.0
	 * 
	 * @return Customer
	 */
	public function getCustomer() {
		$customer_info = $this->isBusinessCustomer() ?
			$this->getBillingCustomerInfo() :
			$this->getShippingCustomerInfo();


		$recipient_phone = $this->getPhone();
		$recipient_cellphone = $this->getCellphone();

		return new Customer(
			$customer_info['name'],
			$this->order->get_billing_email(),
			empty( $recipient_cellphone ) ? $recipient_phone : $recipient_cellphone,
			$customer_info['document'],
		);
	}

	public function getCustomerFullName() {
		$customer_info = $this->isBusinessCustomer() ?
			$this->getBillingCustomerInfo() :
			$this->getShippingCustomerInfo();

		return $customer_info['name'];
	}

	public function getCustomerEmail() {
		return $this->order->get_billing_email();
	}

	public function getCustomerDocument() {
		$customer_info = $this->isBusinessCustomer() ?
			$this->getBillingCustomerInfo() :
			$this->getShippingCustomerInfo();

		return $customer_info['document'];
	}

	public function getCellphone() {
		return Sanitizer::celphone( empty( $this->order->get_meta( '_billing_cellphone' ) ) ? $this->order->get_billing_phone() : $this->order->get_meta( '_billing_cellphone' ) );
	}

	public function getPhone() {
		return Sanitizer::phone( empty( $this->order->get_shipping_phone() ) ? $this->order->get_billing_phone() : $this->order->get_shipping_phone() );
	}

	public function getAlwaysPhone() {
		return empty( $this->getCellphone() ) ? $this->getPhone() : $this->getCellphone();
	}

	/**
	 * Get billing customer info.
	 * 
	 * This method is responsible for getting billing customer info.
	 * 
	 * @since 1.0.0
	 * 
	 * @param \WC_Order $order Order.
	 * 
	 * @return array{
	 *      string cpfCnpj,
	 *      string name
	 * }
	 */
	public function getBillingCustomerInfo() {
		$document = Sanitizer::numeric_text( empty( $this->order->get_meta( '_billing_cnpj' ) ) ? $this->order->get_meta( '_billing_cpf' ) : $this->order->get_meta( '_billing_cnpj' ) );
		$name = empty( $this->order->get_shipping_company() ) ? $this->order->get_billing_company() : $this->order->get_shipping_company();
		return [ 
			'document' => $document,
			'name' => $name
		];
	}

	/**
	 * Get shipping customer info.
	 * 
	 * This method is responsible for getting shipping customer info.
	 * 
	 * @since 1.0.0
	 * 
	 * @param \WC_Order $order Order.
	 * 
	 * @return array{
	 *      string cpfCnpj,
	 *      string name
	 * }
	 */
	public function getShippingCustomerInfo() {
		$cpf = $this->order->get_meta( '_billing_cpf' );
		$document = empty( $cpf ) ? '' : Sanitizer::numeric_text( $cpf );
		$first_name = empty( $this->order->get_shipping_first_name() ) ? $this->order->get_billing_first_name() : $this->order->get_shipping_first_name();
		$last_name = empty( $this->order->get_shipping_last_name() ) ? $this->order->get_billing_last_name() : $this->order->get_shipping_last_name();
		$name = trim( "$first_name $last_name" );
		return [ 
			'document' => $document,
			'name' => $name
		];
	}


	public function isBusinessCustomer() {
		return $this->order->meta_exists( '_billing_persontype' ) && $this->order->get_meta( '_billing_persontype' ) == '2';
	}

	public function getItems() {
		return $this->order->get_items();
	}

	public function getPackage() {
		$package_data = [];
		foreach ( $this->getItems() as $item ) {
			$package_data['contents'][ $item->get_id()] = [ 
				'quantity' => $item->get_quantity(),
				'data' => $item->get_product(),
				'line_total' => $item->get_total(),
			];
		}

		$shipping_method = $this->getShippingMethod();

		if ( ! $shipping_method ) {
			return new Package( $package_data );
		}

		return $shipping_method->get_package( $package_data );
	}

	/**
	 * Get the Correios shipping method from the order
	 * 
	 * @return CorreiosShippingMethod|false
	 */
	function getShippingMethod() {
		foreach ( $this->order->get_shipping_methods() as $shipping_method ) {
			if ( strpos( $shipping_method->get_method_id(), 'infixs-correios-automatico' ) === 0 ) {
				$instance_id = $shipping_method->get_instance_id();
				return new CorreiosShippingMethod( $instance_id );
			}
		}
		return false;
	}

	public function getSubtotal() {
		return $this->order->get_subtotal();
	}

	public function toArray() {
		$address = $this->getAddress()->toArray();
		$customer = $this->getCustomer()->toArray();
		$customer['id'] = $this->order->get_customer_id();
		$customer['address'] = $address;

		$items = array_map( function ($item, $index) {
			return [ 
				'id' => $item->get_id(),
				'name' => $item->get_name(),
				'quantity' => intval( $item->get_quantity() ),
				'price' => intval( $item->get_total() * 100 ),
			];
		}, $this->order->get_items(), array_keys( $this->order->get_items() ) );


		$shipping_meta = $this->order->get_meta( '_infixs_correios_automatico_data' );

		$data = [ 
			'id' => $this->order->get_id(),
			'order_url' => $this->order->get_edit_order_url(),
			'status' => $this->order->get_status(),
			'status_label' => wc_get_order_status_name( $this->order->get_status() ),
			'total_amount' => intval( $this->order->get_total() * 100 ),
			'items' => $items,
			'shipping' => [ 
				'shipping_amount' => intval( $this->order->get_shipping_total() * 100 ),
				'shipping_method' => $this->order->get_shipping_method(),
				'delivery_time' => isset( $shipping_meta['delivery_time'] ) ? $shipping_meta['delivery_time'] : 0,
				'width' => $shipping_meta['width'] ?? 0,
				'height' => $shipping_meta['height'] ?? 0,
				'length' => $shipping_meta['lenght'] ?? 0,
				'weight' => $shipping_meta['weight'] ?? 0,
			],
			'customer' => $customer,
			'created_at' => $this->order->get_date_created()->date( 'Y-m-d H:i:s' ),
		];

		$tracking_code = $this->getLastTrackingCode();
		if ( $tracking_code ) {
			$data['tracking_code'] = $tracking_code;
		}
		return $data;
	}
}