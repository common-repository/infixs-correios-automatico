<?php

namespace Infixs\CorreiosAutomatico\Routes;

use Infixs\CorreiosAutomatico\Container;
use Infixs\CorreiosAutomatico\Controllers\Rest\LabelController;
use Infixs\CorreiosAutomatico\Controllers\Rest\OrderController;
use Infixs\CorreiosAutomatico\Controllers\Rest\PrepostController;
use Infixs\CorreiosAutomatico\Controllers\Rest\SettingsAuthController;
use Infixs\CorreiosAutomatico\Controllers\Rest\SettingsGeneralController;
use Infixs\CorreiosAutomatico\Controllers\Rest\SettingsLabelController;
use Infixs\CorreiosAutomatico\Controllers\Rest\SettingsSenderController;
use Infixs\CorreiosAutomatico\Controllers\Rest\ShippingController;
use Infixs\CorreiosAutomatico\Controllers\Rest\TrackingController;

defined( 'ABSPATH' ) || exit;
class RestRoutes {

	/**
	 * Rest namespace
	 *
	 * @since 1.0.0
	 * @var string
	 */
	protected $namespace = 'infixs-correios-automatico/v1';

	/**
	 * RestRoutes constructor.
	 * 
	 * @since 1.0.0
	 */
	public function register_routes() {
		$settings_controller = new SettingsAuthController();

		register_rest_route( $this->namespace, '/settings/auth', [ 
			'methods' => 'POST',
			'callback' => [ $settings_controller, 'save' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_woocommerce' );
			}
		] );

		register_rest_route( $this->namespace, '/settings/auth', [ 
			'methods' => \WP_REST_Server::READABLE,
			'callback' => [ $settings_controller, 'retrieve' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_woocommerce' );
			}
		] );

		$general_controller = new SettingsGeneralController();

		register_rest_route( $this->namespace, '/settings/general', [ 
			'methods' => 'POST',
			'callback' => [ $general_controller, 'save' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_woocommerce' );
			}
		] );

		register_rest_route( $this->namespace, '/settings/general', [ 
			'methods' => \WP_REST_Server::READABLE,
			'callback' => [ $general_controller, 'retrieve' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_woocommerce' );
			}
		] );

		$sender_controller = new SettingsSenderController();

		register_rest_route( $this->namespace, '/settings/sender', [ 
			'methods' => 'POST',
			'callback' => [ $sender_controller, 'save' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_woocommerce' );
			}
		] );

		register_rest_route( $this->namespace, '/settings/sender', [ 
			'methods' => \WP_REST_Server::READABLE,
			'callback' => [ $sender_controller, 'retrieve' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_woocommerce' );
			}
		] );

		$label_settings_controller = new SettingsLabelController();

		register_rest_route( $this->namespace, '/settings/label', [ 
			'methods' => 'POST',
			'callback' => [ $label_settings_controller, 'save' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_woocommerce' );
			}
		] );

		register_rest_route( $this->namespace, '/settings/label', [ 
			'methods' => \WP_REST_Server::READABLE,
			'callback' => [ $label_settings_controller, 'retrieve' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_woocommerce' );
			}
		] );

		$tracking_controller = new TrackingController();

		register_rest_route( $this->namespace, '/trackings', [ 
			'methods' => 'POST',
			'callback' => [ $tracking_controller, 'create' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_woocommerce' );
			}
		] );


		register_rest_route( $this->namespace, '/trackings/(?P<id>\d+)', [ 
			'methods' => 'DELETE',
			'callback' => [ $tracking_controller, 'delete' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_woocommerce' );
			},
			'args' => [ 
				'id' => [ 
					'required' => true,
					'validate_callback' => function ($param) {
						return is_numeric( $param );
					}
				],
			],
		] );

		register_rest_route( $this->namespace, '/trackings', [ 
			'methods' => \WP_REST_Server::READABLE,
			'callback' => [ $tracking_controller, 'list' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_woocommerce' );
			}
		] );

		$shipping_controller = new ShippingController();

		register_rest_route( $this->namespace, '/shipping/methods', [ 
			'methods' => \WP_REST_Server::READABLE,
			'callback' => [ $shipping_controller, 'list_shipping_methods' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_woocommerce' );
			}
		] );

		register_rest_route( $this->namespace, '/shipping/methods/import', [ 
			'methods' => 'POST',
			'callback' => [ $shipping_controller, 'import_shipping_methods' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_woocommerce' );
			}
		] );

		$label_controller = new LabelController( Container::labelService() );

		register_rest_route( $this->namespace, '/labels', [ 
			'methods' => \WP_REST_Server::READABLE,
			'callback' => [ $label_controller, 'list' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_woocommerce' );
			}
		] );

		$prepost_controller = new PrepostController( Container::prepostService() );

		register_rest_route( $this->namespace, '/preposts', [ 
			'methods' => 'POST',
			'callback' => [ $prepost_controller, 'createFromOrder' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_woocommerce' );
			}
		] );

		$order_controller = new OrderController( Container::orderService() );

		register_rest_route( $this->namespace, '/orders', [ 
			'methods' => \WP_REST_Server::READABLE,
			'callback' => [ $order_controller, 'list' ],
			'permission_callback' => function () {
				return current_user_can( 'manage_woocommerce' );
			},
			'args' => [ 
				'page' => [ 
					'default' => 1,
					'sanitize_callback' => 'absint',
				],
				'per_page' => [ 
					'default' => 10,
					'sanitize_callback' => 'absint',
				],
				'search' => [ 
					'sanitize_callback' => 'sanitize_text_field',
				],
			],
		] );
	}

	/**
	 * Get the namespace.
	 *
	 * @since 1.0.0
	 * 
	 * @return string
	 */
	public function get_namespace() {
		return $this->namespace;
	}

	/**
	 * Get the rest url.
	 *
	 * @since 1.0.0
	 * 
	 * @return string
	 */
	public function get_rest_url() {
		return get_rest_url( null, $this->namespace );
	}
}