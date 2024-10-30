<?php

namespace Infixs\CorreiosAutomatico\Core\Admin;

use Infixs\CorreiosAutomatico\Container;
use Infixs\CorreiosAutomatico\Routes\RestRoutes;
use Infixs\CorreiosAutomatico\Core\Admin\WooCommerce\WCIntegration;


defined( 'ABSPATH' ) || exit;

/**
 * Correios Automático Admin Hooks
 * 
 * Settup all hooks for admin area, actions and filters.
 *
 * @package Infixs\CorreiosAutomatico
 * @since   1.0.0
 */
class Hooks {

	/**
	 * Admin instance.
	 *
	 * @since 1.0.0
	 * @var Admin
	 */
	private $admin;


	/**
	 * Routes instance.
	 *
	 * @since 1.0.0
	 * @var RestRoutes
	 */
	private $rest_routes;

	/**
	 * WCIntegration instance.
	 *
	 * @since 1.0.0
	 * @var WCIntegration
	 */
	private $woocommerce;

	/**
	 * Hooks constructor.
	 *
	 * @param RestRoutes $rest_routes Rest routes instance.
	 * @since 1.0.0
	 */
	public function __construct() {
		$this->admin = new Admin( Container::infixsApi() );
		$this->rest_routes = Container::routes();
		$this->woocommerce = new WCIntegration();
		$this->actions();
		$this->filters();
	}

	/**
	 * Settup all actions.
	 *
	 * @since 1.0.0
	 */
	public function actions() {

		//Wordpress Actions
		add_action( 'admin_menu', [ $this->admin->dashboard, 'admin_menu' ], 60 );
		add_action( 'in_admin_header', [ $this->admin, 'hide_notices' ], 99 );
		add_action( 'admin_enqueue_scripts', [ $this->admin, 'enqueue_scripts' ] );
		add_action( 'rest_api_init', [ $this->rest_routes, 'register_routes' ] );
		add_action( 'admin_footer', [ $this->admin, 'unistall_html_modal' ] );
		add_action( 'wp_ajax_infixs_correios_automatico_deactivate', [ $this->admin, 'submit_deactivate_feedback' ] );
		//add_action( 'bulk_actions-awoocommerce_page_wc-orders', [ $this, 'woocommerce_order_actions_end' ], 1 );

		//Woocommerce Actions
		add_action( 'add_meta_boxes', [ $this->woocommerce->tracking, 'register_order_meta_box' ] );
		add_action( 'add_meta_boxes', [ $this->woocommerce->prepost, 'register_order_meta_box' ] );
		add_action( 'add_meta_boxes', [ $this->woocommerce->label, 'register_order_meta_box' ] );
		add_action( 'woocommerce_process_shop_order_meta', [ $this->woocommerce->tracking, 'save_tracking_code' ], 20, 2 );
		add_action( 'before_woocommerce_init', [ $this->woocommerce, 'woocommerce_declare_compatibility' ] );
		add_action( 'woocommerce_order_list_table_extra_tablenav', [ $this->woocommerce, 'add_print_button_order_table' ] );
		add_action( 'woocommerce_settings_shipping', [ $this->woocommerce->shipping, 'shipping_settings_page' ] );
		add_action( 'woocommerce_payment_complete', [ $this->woocommerce->order, 'payment_complete' ], 100 );
		add_action( 'woocommerce_order_status_completed', [ $this->woocommerce->order, 'payment_complete' ], 100 );
		add_action( 'woocommerce_checkout_update_order_meta', [ $this->woocommerce->order, 'save_order_meta_data' ] );
	}

	/**
	 * Settup all filters.
	 *
	 * @since 1.0.0
	 */
	public function filters() {

		//Wordpress Filters
		add_filter( 'plugin_action_links_' . \INFIXS_CORREIOS_AUTOMATICO_BASE_NAME, [ $this->admin, 'plugin_action_links' ] );
		add_filter( 'admin_body_class', [ $this->admin, 'admin_body_class' ] );


		//Woocommerce Filters
		add_filter( 'woocommerce_email_classes', [ $this->woocommerce->tracking, 'include_emails' ] );
		add_filter( 'bulk_actions-woocommerce_page_wc-orders', [ $this->woocommerce, 'register_bulk_actions' ] );
		add_filter( 'handle_bulk_actions-woocommerce_page_wc-orders', [ $this->woocommerce, 'handle_bulk_actions' ], 10, 3 );
		add_filter( 'woocommerce_shipping_methods', [ $this->woocommerce->shipping, 'include_methods' ] );
		add_filter( 'bulk_actions-edit-shop_order', [ $this->woocommerce, 'register_bulk_actions' ] ); // Compatibility with old woocommerce
		add_filter( 'handle_bulk_actions-edit-shop_order', [ $this->woocommerce, 'handle_bulk_actions' ], 10, 3 );

	}


}