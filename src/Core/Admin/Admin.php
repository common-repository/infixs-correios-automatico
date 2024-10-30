<?php

namespace Infixs\CorreiosAutomatico\Core\Admin;
use Infixs\CorreiosAutomatico\Container;
use Infixs\CorreiosAutomatico\Core\Admin\WooCommerce\OrderTrackingColumn;
use Infixs\CorreiosAutomatico\Core\Admin\WooCommerce\WCIntegration;
use Infixs\CorreiosAutomatico\Core\Support\Plugin;
use Infixs\CorreiosAutomatico\Services\InfixsApi;

defined( 'ABSPATH' ) || exit;

/**
 * Admin Core Class
 * 
 * Global Admin Methodos for configuration and settings.
 *
 * @package Infixs\CorreiosAutomatico
 * @since   1.0.0
 */
class Admin {


	/**
	 * Dashboard instance.
	 *
	 * @var Dashboard
	 */
	public $dashboard;

	protected $infixsApi;

	/**
	 * Admin constructor.
	 * 
	 * @since 1.0.0
	 */
	public function __construct( InfixsApi $infixsApi ) {
		$this->infixsApi = $infixsApi;
		$this->dashboard = new Dashboard();
		new OrderTrackingColumn( Container::trackingService() );
	}

	/**
	 * This method is used to check if the current page is the plugin dashboard.
	 * 
	 * @since 1.0.0
	 */
	public function is_dashboard_page() {
		// checks if the current page is the dashboard, no nonce is needed for this
		// phpcs:ignore
		return isset( $_GET['page'] ) && 'infixs-correios-automatico' === $_GET['page'];
	}

	/**
	 * This method is used to check if the current page is the plugin dashboard.
	 * 
	 * @since 1.0.0
	 */
	public function is_print_page() {
		// checks if the current page is the dashboard, no nonce is needed for this
		// phpcs:ignore
		return isset( $_GET['page'], $_GET['path'] ) && 'infixs-correios-automatico' === $_GET['page'] && '/print' === $_GET['path'];
	}

	/**
	 * This method is used to check if the current page is the starter plugin setup.
	 * 
	 * @since 1.0.0
	 */
	public function is_starter_page() {
		// checks if the current page is the dashboard, no nonce is needed for this
		// phpcs:ignore
		return isset( $_GET['page'], $_GET['path'] ) && 'infixs-correios-automatico' === $_GET['page'] && strpos( $_GET['path'], '/starter' ) === 0;
	}



	/**
	 * Enqueue scripts.
	 *
	 * @since 1.0.0
	 */
	public function enqueue_scripts( $hook ) {
		if ( WCIntegration::is_edit_order_page() ) {
			wp_enqueue_style(
				'infixs-correios-automatico-orders',
				\INFIXS_CORREIOS_AUTOMATICO_PLUGIN_URL . 'assets/admin/css/orders.css',
				[],
				filemtime( \INFIXS_CORREIOS_AUTOMATICO_PLUGIN_PATH . 'assets/admin/css/orders.css' )
			);

			wp_enqueue_script(
				'infixs-correios-automatico-orders',
				\INFIXS_CORREIOS_AUTOMATICO_PLUGIN_URL . 'assets/admin/js/orders.js',
				[ 'jquery', 'jquery-blockui', 'wp-util' ],
				filemtime( \INFIXS_CORREIOS_AUTOMATICO_PLUGIN_PATH . 'assets/admin/js/orders.js' ),
				true
			);

			wp_localize_script(
				'infixs-correios-automatico-orders',
				'infixsCorreiosAutomaticoOrdersParams',
				[ 
					'restUrl' => Container::routes()->get_rest_url(),
					'nonce' => wp_create_nonce( 'wp_rest' ),
					'adminUrl' => admin_url( 'admin.php?page=infixs-correios-automatico' )
				]
			);
		}

		if ( $this->is_dashboard_page() || 'plugins.php' === $hook ) {
			wp_enqueue_media();
			$scriptData = [];
			if ( 'plugins.php' === $hook ) {
				$scriptData['unistallNonce'] = wp_create_nonce( 'infixs_correios_automatico_uninstall' );
			}
			if ( $this->is_dashboard_page() ) {
				$scriptData['notices'] = $this->dashboard->display_notices();
				$scriptData['nonceNotice'] = wp_create_nonce( 'infixs_correios_automatico_dismiss_notice' );
			}
			self::load_dashboard_scripts( $scriptData );
		}
	}

	public static function load_dashboard_scripts( $params = [] ) {
		wp_enqueue_script( 'infixs-correios-automatico-admin',
			\INFIXS_CORREIOS_AUTOMATICO_PLUGIN_URL . 'assets/dashboard/js/main.js',
			[ 'jquery', 'wp-util' ],
			filemtime( \INFIXS_CORREIOS_AUTOMATICO_PLUGIN_PATH . 'assets/dashboard/js/main.js' ),
			true
		);

		$url_part = wp_parse_url( admin_url() );
		$relative_path = $url_part['path'];

		$scriptData = array_merge( $params, [ 
			'adminEmail' => get_option( 'admin_email' ),
			'upgradeProUrl' => Plugin::PRO_URL,
			'siteUrl' => site_url(),
			'adminUrl' => admin_url(),
			'adminPath' => $relative_path,
			'restUrl' => Container::routes()->get_rest_url(),
			'resourcesUrl' => \INFIXS_CORREIOS_AUTOMATICO_PLUGIN_URL . 'assets/dashboard',
			'nonce' => wp_create_nonce( 'wp_rest' ),
		] );

		wp_localize_script(
			'infixs-correios-automatico-admin',
			'infixsCorreiosAutomaticoGlobals',
			apply_filters( 'infixs_correios_automatico_dashboard_global_params', $scriptData )
		);
	}

	/**
	 * Hide notices.
	 *
	 * @since 1.0.0
	 */
	public function hide_notices() {
		if ( ! $this->is_dashboard_page() ) {
			return;
		}
		remove_all_actions( 'user_admin_notices' );
		remove_all_actions( 'admin_notices' );
	}

	/**
	 * Add admin unistall template.
	 */
	public function unistall_html_modal() {
		global $pagenow;

		if ( 'plugins.php' === $pagenow ) {
			include_once \INFIXS_CORREIOS_AUTOMATICO_PLUGIN_PATH . 'src/Presentation/admin/views/html-uninstall.php';
		}
	}

	public function submit_deactivate_feedback() {
		if ( ! isset( $_POST['nonce'] ) || ! wp_verify_nonce(
			sanitize_text_field(
				wp_unslash( $_POST['nonce'] )
			),
			'infixs_correios_automatico_uninstall'
		) ) {
			wp_send_json_error( [ 'message' => __( 'Operação não autorizada.', 'infixs-correios-automatico' ) ] );
		}

		if ( ! isset( $_POST['reason'] ) ) {
			wp_send_json_error( [ 'message' => __( 'Informe o motivo da desativação.', 'infixs-correios-automatico' ) ] );
		}

		$reason = sanitize_text_field( wp_unslash( $_POST['reason'] ) );
		$email = isset( $_POST['email'] ) ? sanitize_email( wp_unslash( $_POST['email'] ) ) : '';
		$description = isset( $_POST['description'] ) ? sanitize_textarea_field( wp_unslash( $_POST['description'] ) ) : '';

		$response = $this->infixsApi->postDeactivationPlugin( [ 
			'plugin_id' => 'infixs-correios-automatico',
			'plugin_version' => \INFIXS_CORREIOS_AUTOMATICO_PLUGIN_VERSION,
			'site' => site_url(),
			'reason' => $reason,
			'email' => $email,
			'description' => $description,
		] );

		if ( is_wp_error( $response ) ) {
			wp_send_json_error( [ 'message' => $response->get_error_message() ] );
		}

		wp_send_json_success( [ 'message' => __( 'Feedback enviado com sucesso.', 'infixs-correios-automatico' ) ] );
	}


	/**
	 * Add links to plugin settings page.
	 * 
	 * @since 1.1.0
	 * 
	 * @return array
	 */
	public function plugin_action_links( $links ) {
		$pluginLinks = [];

		$baseUrl = esc_url( admin_url( 'admin.php?page=infixs-correios-automatico&path=/config/general' ) );

		$pluginLinks[] = sprintf( '<a href="%s">%s</a>', $baseUrl, __( 'Configurações', 'infixs-correios-automatico' ) );
		$pluginLinks[] = sprintf( '<a href="%s" target="_blank" style="color: #1da867 !important;font-weight:600;">%s</a>', esc_attr( 'https://store.infixs.io/product/correios-automatico-rastreio-etiqueta-e-frete-versao-pro/' ), __( 'Atualizar para Pro', 'infixs-correios-automatico' ) );
		$pluginLinks[] = sprintf( '<a href="%s" target="_blank">%s</a>', esc_attr( 'https://wordpress.org/support/plugin/infixs-correios-automatico/' ), __( 'Suporte', 'infixs-correios-automatico' ) );
		$pluginLinks[] = sprintf( '<a href="%s" target="_blank">%s</a>', esc_attr( 'https://wordpress.org/plugins/infixs-correios-automatico/#reviews' ), __( 'Avaliar', 'infixs-correios-automatico' ) );

		return array_merge( $pluginLinks, $links );
	}

	public function admin_body_class( $classes ) {
		if ( $this->is_print_page() || $this->is_starter_page() ) {
			$classes .= ' infixs-correios-automatico-fullscreen';
		}

		if ( $this->is_dashboard_page() ) {
			$classes .= ' infixs-correios-automatico-dashboard-page';
		}


		return $classes;
	}
}