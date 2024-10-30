<?php

namespace Infixs\CorreiosAutomatico\Core\Admin\WooCommerce;
use Infixs\CorreiosAutomatico\Container;

defined( 'ABSPATH' ) || exit;

/**
 * Correios Automático WooCommerce
 * 
 * Settup all hooks for admin area, actions and filters.
 *
 * @package Infixs\CorreiosAutomatico
 * @since   1.0.0
 */
class WCIntegration {

	/**
	 * Tracking instance.
	 *
	 * @var Tracking
	 */
	public $tracking;

	/**
	 * Shipping instance.
	 *
	 * @var Shipping
	 */
	public $shipping;

	public $order;

	public $prepost;

	public $label;

	public function __construct() {
		$this->tracking = new Tracking( Container::trackingService() );
		$this->shipping = new Shipping();
		$this->order = new Order();
		$this->prepost = new Prepost( Container::prepostService() );
		$this->label = new Label( Container::labelService() );
	}

	public static function get_shop_order_screen() {
		return class_exists( 'Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' )
			&& wc_get_container()->get( \Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController::class)->custom_orders_table_usage_is_enabled()
			&& function_exists( 'wc_get_page_screen_id' )
			? wc_get_page_screen_id( 'shop-order' )
			: 'shop_order';
	}

	/**
	 * This method is used to check if the current page is the WooCommerce edit order page.
	 * 
	 * @since 1.0.0
	 */
	public static function is_edit_order_page() {
		$current_screen = get_current_screen();
		return $current_screen && $current_screen->id === self::get_shop_order_screen();
	}

	/**
	 * Register custom bulk actions.
	 *
	 * @since 1.0.0
	 */
	public function register_bulk_actions( $bulk_actions ) {
		$bulk_actions['infixs_correios_automatico_print_labels'] = __( 'Imprimir Etiquetas', 'infixs-correios-automatico' );
		return $bulk_actions;
	}

	/**
	 * Handle custom bulk actions.
	 *
	 * @since 1.0.0
	 */
	public function handle_bulk_actions( $redirect_to, $action, $post_ids ) {
		if ( $action !== 'infixs_correios_automatico_print_labels' ) {
			return $redirect_to;
		}

		$redirect_to = admin_url( sprintf( 'admin.php?page=infixs-correios-automatico&path=/print&orders=%s', implode( ',', $post_ids ) ) );
		return $redirect_to;
	}

	/**
	 * Add custom bulk action to the order list.
	 *
	 * @since 1.0.0
	 */
	public function add_print_button_order_table( $post_type ) {
		include_once INFIXS_CORREIOS_AUTOMATICO_PLUGIN_PATH . 'src/Presentation/admin/views/html-order-list-action.php';
	}

	public function woocommerce_declare_compatibility() {

		if ( class_exists( \Automattic\WooCommerce\Utilities\FeaturesUtil::class) ) {
			\Automattic\WooCommerce\Utilities\FeaturesUtil::declare_compatibility( 'custom_order_tables', \INFIXS_CORREIOS_AUTOMATICO_FILE_NAME, true );
		}
	}

}