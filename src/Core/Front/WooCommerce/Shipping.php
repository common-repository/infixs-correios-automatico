<?php

namespace Infixs\CorreiosAutomatico\Core\Front\WooCommerce;
use Infixs\CorreiosAutomatico\Core\Support\Config;
use Infixs\CorreiosAutomatico\Services\ShippingService;
use Infixs\CorreiosAutomatico\Utils\Sanitizer;

defined( 'ABSPATH' ) || exit;

/**
 * Correios Automático Shipping Class
 *
 * @package Infixs\CorreiosAutomatico
 * @since   1.0.0
 */
class Shipping {

	/**
	 * Shipping service instance.
	 * 
	 * @var ShippingService
	 */
	private $shippingService;

	/**
	 * Constructor
	 * 
	 * @since 1.1.1
	 * 
	 * @param ShippingService $shippingService Shipping service instance.
	 */
	public function __construct( ShippingService $shippingService ) {
		$this->shippingService = $shippingService;
	}


	/**
	 * Display estimated delivery time.
	 *
	 * @param string $label Shipping method label.
	 * @param \WC_Shipping_Rate $method Shipping method object.
	 * 
	 * @since 1.0.0
	 */
	public function shipping_method_label( $label, $method ) {
		if ( 'infixs-correios-automatico' === $method->method_id ) {
			$meta_data = $method->get_meta_data();

			if ( isset( $meta_data['delivery_time'] ) ) {
				$days = (int) $meta_data['delivery_time'];
				if ( $days <= 0 )
					return $label;
				$sufix = $days > 1 ? 'dias úteis' : 'dia útil';
				$label = $label . ' - ' . sprintf( "<span class='caref-text-sm'><small>Estimativa de entrega %d %s</small></span>", esc_html( $days ), $sufix );
			}
		}
		return $label;
	}

	/**
	 * Display shipping calculator.
	 * 
	 * @since 1.0.1
	 */
	public function shipping_calculator_shortcode() {
		ob_start();

		if ( is_product() ) {
			$template = 'shipping-calculator.php';

			wc_get_template(
				$template,
				[],
				'infixs-correios-automatico/',
				\INFIXS_CORREIOS_AUTOMATICO_PLUGIN_PATH . 'templates/'
			);
		}

		return ob_get_clean();
	}

	public function display_shipping_calculator() {
		if ( is_product() && Config::boolean( "general.calculate_shipping_product_page" ) ) {
			global $product;
			if ( $product->needs_shipping() ) {
				// phpcs:ignore
				echo $this->shipping_calculator_shortcode();
			}
		}

	}

	public function calculate_shipping() {

		if ( ! isset( $_POST['postcode'] ) || ! isset( $_POST['product_id'] ) ) {
			return wp_send_json_error( [ 'message' => 'CEP e o produto são obrigatórios' ] );
		}

		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'infixs_correios_automatico_nonce' ) ) {
			return wp_send_json_error( [ 'message' => 'Nonce inválido' ] );
		}

		$postscode = sanitize_text_field( wp_unslash( $_POST['postcode'] ) );
		$product_id = sanitize_text_field( wp_unslash( $_POST['product_id'] ) );

		$variation_id = isset( $_POST['variation_id'] ) ? sanitize_text_field( wp_unslash( $_POST['variation_id'] ) ) : null;

		$product = wc_get_product( $variation_id ?: $product_id );

		$package_cost = $product->get_price();

		WC()->shipping()->calculate_shipping(

			[ 
				[ 
					'contents' => [ 
						0 => [ 
							'data' => $product,
							'quantity' => 1,
						],
					],
					'contents_cost' => $package_cost,
					'applied_coupons' => false,
					'user' => [ 
						'ID' => get_current_user_id(),
					],
					'destination' => [ 
						'country' => 'BR',
						'state' => $this->shippingService->getStateByPostcode( $postscode ),
						'postcode' => Sanitizer::numeric_text( $postscode ),
					],
					'cart_subtotal' => $package_cost,
					'is_product_page' => true,
				],
			]
		);


		$packages = WC()->shipping()->get_packages();

		wc_get_template(
			'shipping-calculator-results.php',
			[ 
				'rates' => $packages[0]['rates'],
			],
			'infixs-correios-automatico/',
			\INFIXS_CORREIOS_AUTOMATICO_PLUGIN_PATH . 'templates/'
		);

		wp_die();
	}
}