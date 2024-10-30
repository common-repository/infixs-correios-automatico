<?php

namespace Infixs\CorreiosAutomatico\Core\Front\WooCommerce;

use Infixs\CorreiosAutomatico\Container;

defined( 'ABSPATH' ) || exit;

/**
 * Correios Automático WooCommerce
 * 
 * Settup functions for woocommerce
 *
 * @package Infixs\CorreiosAutomatico
 * @since   1.0.0
 */
class WCIntegration {

	/**
	 * Shipping instance.
	 *
	 * @since 1.0.0
	 * @var Shipping
	 */
	public $shipping;

	public function __construct() {
		$this->shipping = new Shipping( Container::shippingService() );
	}
}