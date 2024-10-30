<?php

namespace Infixs\CorreiosAutomatico\Core\Front;

use Infixs\CorreiosAutomatico\Core\Front\WooCommerce\WCIntegration;
use Infixs\CorreiosAutomatico\Core\Support\Config;

defined( 'ABSPATH' ) || exit;

/**
 * Correios Automático Public Hooks
 * 
 * Settup all hooks for public front end area, actions and filters.
 *
 * @package Infixs\CorreiosAutomatico
 * @since   1.0.0
 */
class Hooks {

	protected $front;

	/**
	 * WCIntegration instance.
	 *
	 * @since 1.0.0
	 * @var WCIntegration
	 */
	protected $woocommerce;

	/**
	 * Hooks constructor.
	 *
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->front = new Front();
		$this->woocommerce = new WCIntegration();
		$this->actions();
		$this->filters();
		$this->shortcodes();
	}

	/**
	 * Settup all actions.
	 *
	 * @since 1.0.0
	 */
	public function actions() {

		//Wordpress Actions
		add_action( 'plugins_loaded', [ $this, 'wocommerce_actions' ], 11 );
		add_action( 'wp_ajax_infixs_correios_automatico_calculate_shipping', [ $this->woocommerce->shipping, 'calculate_shipping' ] );
		add_action( 'wp_ajax_nopriv_infixs_correios_automatico_calculate_shipping', [ $this->woocommerce->shipping, 'calculate_shipping' ] );
	}

	/**
	 * Settup all filters.
	 *
	 * @since 1.0.0
	 */
	public function filters() {

		//Wordpress Filters

		//Woocommerce Filters
		add_filter( 'woocommerce_cart_shipping_method_full_label', [ $this->woocommerce->shipping, 'shipping_method_label' ], 10, 2 );
	}

	/**
	 * Settup all shortcodes.
	 *
	 * @since 1.0.1
	 */
	public function shortcodes() {
		add_shortcode( 'infixs_correios_automatico_calculator', [ $this->woocommerce->shipping, 'shipping_calculator_shortcode' ] );
	}

	/**
	 * Settup all Woocommerce dependencies actions.
	 *
	 * @since 1.0.1
	 */
	public function wocommerce_actions() {
		if ( class_exists( 'WooCommerce' ) ) {
			add_action( 'wp_enqueue_scripts', [ $this->front, 'enqueue_scripts' ] );
			$position = Config::string( "general.calculate_shipping_product_page_position" );
			if ( $position === 'meta_end' ) {
				add_action( 'woocommerce_product_meta_end', [ $this->woocommerce->shipping, 'display_shipping_calculator' ], 80 );
			} else {
				add_action( 'woocommerce_product_meta_start', [ $this->woocommerce->shipping, 'display_shipping_calculator' ], 80 );
			}
		}
	}
}