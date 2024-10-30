<?php

namespace Infixs\CorreiosAutomatico\Core\Shipping;

use Infixs\CorreiosAutomatico\Container;
use Infixs\CorreiosAutomatico\Core\Admin\Admin;
use Infixs\CorreiosAutomatico\Core\Support\Config;
use Infixs\CorreiosAutomatico\Models\WoocommerceShippingZoneMethod;
use Infixs\CorreiosAutomatico\Services\Correios\Enums\DeliveryServiceCode;
use Infixs\CorreiosAutomatico\Services\Correios\Includes\Package;
use Infixs\CorreiosAutomatico\Services\Correios\Includes\ShippingCost;
use Infixs\CorreiosAutomatico\Services\Correios\Includes\ShippingTime;
use Infixs\CorreiosAutomatico\Utils\Sanitizer;

defined( 'ABSPATH' ) || exit;
/**
 * Correios Automático Core Functions
 *
 * @package Infixs\CorreiosAutomatico
 * @since   1.0.0
 */
class CorreiosShippingMethod extends \WC_Shipping_Method {

	/**
	 * Advanced mode
	 *
	 * @var bool
	 */
	protected $advanced_mode = false;

	/**
	 * Basic services
	 *
	 * @var string
	 */
	protected $basic_service = '';

	/**
	 * Advanced service
	 *
	 * @var string
	 */
	protected $advanced_service = '';

	/**
	 * Shipping class
	 *
	 * @var string
	 */
	protected $shipping_class = '';

	/**
	 * Object type
	 *
	 * @var string
	 */
	protected $object_type = 'package';

	/**
	 * Origin postcode
	 *
	 * @var string
	 */
	protected $origin_postcode = '';

	/**
	 * Estimated delivery
	 *
	 * @var bool
	 */
	protected $estimated_delivery = false;

	/**
	 * Additional days
	 *
	 * @var int
	 */
	protected $additional_days = 0;

	/**
	 * Additional tax
	 *
	 * @var int
	 */
	protected $additional_tax = 0;

	/**
	 * Own hands
	 *
	 * @var bool
	 */
	protected $own_hands = false;

	/**
	 * Receipt notice
	 *
	 * @var bool
	 */
	protected $receipt_notice = false;

	/**
	 * Insurance
	 *
	 * @var bool
	 */
	protected $insurance = false;


	/**
	 * Min Insurance value
	 *
	 * @var bool
	 */
	protected $min_insurance_value = 0;

	/**
	 * Minimum height in cm
	 *
	 * @var int
	 */
	protected $minimum_height = 2;

	/**
	 * Minimum width in cm
	 *
	 * @var int
	 */
	protected $minimum_width = 11;

	/**
	 * Minimum length in cm
	 *
	 * @var int
	 */
	protected $minimum_length = 16;

	/**
	 * Minimum weight in kg
	 *
	 * @var float
	 */
	protected $minimum_weight = 0.1;

	/**
	 * Extra weight in kg
	 *
	 * @var float
	 */
	protected $extra_weight = 0;


	/**
	 * Auto prepost
	 *
	 * @var bool
	 */
	protected $auto_prepost = false;

	/**
	 * Extra weight type
	 *
	 * @var string "product"|"order"
	 */
	protected $extra_weight_type = 'order';

	/**
	 * Initialize the Correios Automático shipping method.
	 *
	 * @param int $instance_id Shipping method instance ID.
	 */
	public function __construct( $instance_id = 0 ) {
		$this->id = 'infixs-correios-automatico';
		$this->instance_id = absint( $instance_id );
		$this->method_title = __( 'Correios Automático', 'infixs-correios-automatico' );
		$this->method_description = __( 'Método de envio dos correios de forma automático e integrada.', 'infixs-correios-automatico' );
		$this->supports = [ 
			'shipping-zones',
			'instance-settings',
		];

		$this->init_form_fields();

		$this->enabled = Sanitizer::boolean( $this->get_option( 'enabled' ) );
		$this->title = $this->get_option( 'title' );
		$this->advanced_mode = Sanitizer::boolean( $this->get_option( 'advanced_mode' ) );
		$this->basic_service = $this->get_option( 'basic_service' );
		$this->advanced_service = $this->get_option( 'advanced_service' );
		$this->shipping_class = $this->get_option( 'shipping_class' );
		$this->origin_postcode = $this->get_option( 'origin_postcode' );
		$this->estimated_delivery = Sanitizer::boolean( $this->get_option( 'estimated_delivery' ) );
		$this->additional_days = $this->get_option( 'additional_days' );
		$this->additional_tax = $this->get_option( 'additional_tax' );
		$this->own_hands = Sanitizer::boolean( $this->get_option( 'own_hands' ) );
		$this->receipt_notice = Sanitizer::boolean( $this->get_option( 'receipt_notice' ) );
		$this->minimum_height = (int) $this->get_option( 'minimum_height' );
		$this->minimum_width = (int) $this->get_option( 'minimum_width' );
		$this->minimum_length = (int) $this->get_option( 'minimum_length' );
		$this->minimum_weight = (float) $this->get_option( 'minimum_weight' );
		$this->extra_weight = (float) $this->get_option( 'extra_weight' );
		$this->insurance = Sanitizer::boolean( $this->get_option( 'insurance' ) );
		$this->min_insurance_value = Sanitizer::boolean( $this->get_option( 'min_insurance_value' ) );
		$this->auto_prepost = Sanitizer::boolean( $this->get_option( 'auto_prepost' ) );
		$this->extra_weight_type = $this->get_option( 'extra_weight_type' );

		add_filter( "woocommerce_shipping_{$this->id}_instance_settings_values", [ $this, "update_instance_settings_values" ] );
	}

	public function update_instance_settings_values( $value ) {
		WooCommerceShippingZoneMethod::update( [ 
			"is_enabled" => $value['enabled'] === 'yes' ? 1 : 0,
		], [ 
			"instance_id" => $this->instance_id,
		] );

		return $value;
	}

	public function get_enabled_option() {
		$instance = WooCommerceShippingZoneMethod::where( 'instance_id', $this->instance_id )->first();
		return ( $instance !== null && $instance->is_enabled == "1" ) ? 'yes' : 'no';
	}

	public function init_form_fields() {
		$this->instance_form_fields = [ 
			'enabled' => [ 
				'title' => __( 'Enable/Disable', 'infixs-correios-automatico' ),
				'type' => 'checkbox',
				'label' => __( 'Enable this shipping method', 'infixs-correios-automatico' ),
				'default' => 'yes',
			],
			'title' => [ 
				'title' => __( 'Title', 'infixs-correios-automatico' ),
				'type' => 'text',
				'description' => __( 'This controls the title which the user sees during checkout.', 'infixs-correios-automatico' ),
				'desc_tip' => true,
				'default' => $this->method_title,
			],
			'advanced_mode' => [ 
				'title' => __( 'Advanced Mode', 'infixs-correios-automatico' ),
				'type' => 'checkbox',
				'description' => __( 'Advanded mode controls', 'infixs-correios-automatico' ),
				'desc_tip' => true,
				'default' => 'no',
			],
			'object_type' => [ 
				'title' => __( 'Object Type', 'infixs-correios-automatico' ),
				'type' => 'select',
				'description' => __( 'Select the object type.', 'infixs-correios-automatico' ),
				'desc_tip' => true,
				'options' => [ 
					'package' => __( 'Pacote', 'infixs-correios-automatico' ),
					'letter' => __( 'Carta', 'infixs-correios-automatico' ),
				],
				'default' => 'package',
			],
			'basic_service' => [ 
				'title' => __( 'Basic Services', 'infixs-correios-automatico' ),
				'type' => 'select',
				'description' => __( 'Select the basic services that will be available for the user.', 'infixs-correios-automatico' ),
				'desc_tip' => true,
				'options' => [ 
					'pac' => __( 'PAC', 'infixs-correios-automatico' ),
					'sedex' => __( 'SEDEX', 'infixs-correios-automatico' ),
					'sedex10' => __( 'SEDEX 10', 'infixs-correios-automatico' ),
					'sedex12' => __( 'SEDEX 12', 'infixs-correios-automatico' ),
					'sedexhoje' => __( 'SEDEX HOJE', 'infixs-correios-automatico' ),
					'impressonormal' => __( 'IMPRESSO NORMAL', 'infixs-correios-automatico' ),
					'impressomodico' => __( 'IMPRESSO MÓDICO', 'infixs-correios-automatico' ),
				],
				'default' => '',
			],
			'advanced_service' => [ 
				'title' => __( 'Advanced Services', 'infixs-correios-automatico' ),
				'type' => 'select',
				'description' => __( 'Enter the advanced services that will be available for the user.', 'infixs-correios-automatico' ),
				'desc_tip' => true,
				'options' => DeliveryServiceCode::getAll(),
				'default' => '',
			],
			'shipping_class' => [ 
				'title' => __( 'Shipping Class', 'infixs-correios-automatico' ),
				'type' => 'select',
				'description' => __( 'Select for which shipping class this method will be applied.', 'infixs-correios-automatico' ),
				'desc_tip' => true,
				'default' => '',
				'options' => $this->get_shipping_classes_options(),
			],
			'origin_postcode' => [ 
				'title' => __( 'Postcode', 'infixs-correios-automatico' ),
				'type' => 'text',
				'description' => __( 'Enter the postcode of the sender.', 'infixs-correios-automatico' ),
				'sanitize_callback' => [ Sanitizer::class, 'numeric_text' ],
				'desc_tip' => true,
				'default' => Sanitizer::numeric_text( get_option( 'woocommerce_store_postcode' ) ),
			],
			'estimated_delivery' => [ 
				'title' => __( 'Estimated Delivery', 'infixs-correios-automatico' ),
				'type' => 'checkbox',
				'description' => __( 'Enable the estimated delivery.', 'infixs-correios-automatico' ),
				'desc_tip' => true,
				'default' => 'yes',
			],
			'additional_days' => [ 
				'title' => __( 'Additional Days', 'infixs-correios-automatico' ),
				'type' => 'number',
				'description' => __( 'Enter the additional days for the estimated delivery.', 'infixs-correios-automatico' ),
				'desc_tip' => true,
				'default' => '0',
			],
			'additional_tax' => [ 
				'title' => __( 'Additional Tax', 'infixs-correios-automatico' ),
				'type' => 'money',
				'description' => __( 'Enter the additional tax for the shipping.', 'infixs-correios-automatico' ),
				'desc_tip' => true,
				'default' => '0',
				'sanitize_callback' => [ Sanitizer::class, 'money100' ],
			],
			'own_hands' => [ 
				'title' => __( 'Own Hands', 'infixs-correios-automatico' ),
				'type' => 'checkbox',
				'description' => __( 'Enable the own hands.', 'infixs-correios-automatico' ),
				'desc_tip' => true,
				'default' => 'no',
			],
			'receipt_notice' => [ 
				'title' => __( 'Receipt Notice', 'infixs-correios-automatico' ),
				'type' => 'checkbox',
				'description' => __( 'Enable the receipt notice.', 'infixs-correios-automatico' ),
				'desc_tip' => true,
				'default' => 'no',
			],
			'insurance' => [ 
				'title' => __( 'Insuranse', 'infixs-correios-automatico' ),
				'type' => 'checkbox',
				'description' => __( 'Enable the insurance for package.', 'infixs-correios-automatico' ),
				'desc_tip' => true,
				'default' => 'no',
			],
			'min_insurance_value' => [ 
				'title' => __( 'Min Insurance Value', 'infixs-correios-automatico' ),
				'type' => 'money',
				'description' => __( 'Min insurance value for order.', 'infixs-correios-automatico' ),
				'desc_tip' => true,
				'default' => '50',
				'sanitize_callback' => [ Sanitizer::class, 'money100' ],
			],
			'minimum_height' => [ 
				'title' => __( 'Minimum Height', 'infixs-correios-automatico' ),
				'type' => 'number',
				'description' => __( 'Define the minimum height.', 'infixs-correios-automatico' ),
				'sanitize_callback' => [ Sanitizer::class, 'numeric_text' ],
				'desc_tip' => true,
				'default' => '2',
			],
			'minimum_width' => [ 
				'title' => __( 'Minimum Width', 'infixs-correios-automatico' ),
				'type' => 'number',
				'description' => __( 'Define the minimum width.', 'infixs-correios-automatico' ),
				'sanitize_callback' => [ Sanitizer::class, 'numeric_text' ],
				'desc_tip' => true,
				'default' => '11',
			],
			'minimum_length' => [ 
				'title' => __( 'Minimum Length', 'infixs-correios-automatico' ),
				'type' => 'number',
				'description' => __( 'Define the minimum length.', 'infixs-correios-automatico' ),
				'sanitize_callback' => [ Sanitizer::class, 'numeric_text' ],
				'desc_tip' => true,
				'default' => '16',
			],
			'minimum_weight' => [ 
				'title' => __( 'Minimum Weight', 'infixs-correios-automatico' ),
				'type' => 'float',
				'description' => __( 'Define the minimum weight.', 'infixs-correios-automatico' ),
				'desc_tip' => true,
				'sanitize_callback' => [ Sanitizer::class, 'float_text' ],
				'default' => '0.100',
			],
			'extra_weight' => [ 
				'title' => __( 'Extra Weight', 'infixs-correios-automatico' ),
				'type' => 'float',
				'description' => __( 'Define the extra weight.', 'infixs-correios-automatico' ),
				'desc_tip' => true,
				'sanitize_callback' => [ Sanitizer::class, 'float_text' ],
				'default' => '0',
			],
			'auto_prepost' => [ 
				'title' => __( 'Insuranse', 'infixs-correios-automatico' ),
				'type' => 'checkbox',
				'description' => __( 'Enable the insurance for package.', 'infixs-correios-automatico' ),
				'desc_tip' => true,
				'default' => 'no',
			],
			'extra_weight_type' => [ 
				'title' => __( 'Extra Weight Type', 'infixs-correios-automatico' ),
				'type' => 'select',
				'description' => __( 'Select the extra weight type.', 'infixs-correios-automatico' ),
				'desc_tip' => true,
				'options' => [ 
					'order' => __( 'Per Order', 'infixs-correios-automatico' ),
					'product' => __( 'Per Product', 'infixs-correios-automatico' ),
				],
				'default' => 'order',
			]
		];
	}

	public function admin_options() {
		Admin::load_dashboard_scripts();

		wp_localize_script(
			'infixs-correios-automatico-admin',
			'infixsCorreiosAutomaticoWCSettings',
			[ 
				'fields' => $this->map_options(),
				'prefix' => "{$this->plugin_id}{$this->id}_",
				'advanced_service_groups' => DeliveryServiceCode::getGroups(),
			]
		);

		$this->get_admin_options_html();
	}

	/**
	 * Get shipping classes options.
	 *
	 * @return array
	 */
	protected function get_shipping_classes_options() {
		$shipping_classes = WC()->shipping->get_shipping_classes();
		$options = [ 
			'' => 'Selecione uma classe',
		];

		if ( ! empty( $shipping_classes ) ) {
			$options += wp_list_pluck( $shipping_classes, 'name', 'slug' );
		}

		return $options;
	}

	public function map_options() {
		$options = $this->get_instance_form_fields();
		$map = [];

		foreach ( $options as $key => $option ) {
			$value = $this->get_option( $key );

			switch ( $option['type'] ) {
				case 'checkbox':
					$map[ $key ]['value'] = $value === 'yes' ? true : false;
					break;
				case 'number':
					$map[ $key ]['value'] = (int) $value;
					break;
				case 'money':
					$map[ $key ]['value'] = ( (int) $value ) / 100;
					break;
				case 'text':
					$map[ $key ]['value'] = $value;
					break;
				default:
					$map[ $key ]['value'] = $value;
					break;
			}

			if ( $option['type'] === 'select' ) {
				$map[ $key ]['options'] = $option['options'];

			}

			if ( isset( $option['title'] ) )
				$map[ $key ]['title'] = $option['title'];

			if ( isset( $option['description'] ) )
				$map[ $key ]['description'] = $option['description'];

		}

		return $map;
	}

	public function get_admin_options_html() {
		include_once \INFIXS_CORREIOS_AUTOMATICO_PLUGIN_PATH . 'src/Presentation/admin/views/html-wc-shipping-settings.php';
	}

	/**
	 * Check if it's possible to calculate the shipping.
	 *
	 * @param  array $package Cart package.
	 * @return bool
	 */
	protected function can_be_calculated( $package ) {
		if ( empty( $package['destination']['postcode'] ) ) {
			return false;
		}

		return 'BR' === $package['destination']['country'];
	}

	public function resolve_basic_service( $service ) {
		$is_contract_enabled = Config::boolean( 'auth.active' );

		switch ( $service ) {
			case 'pac':
				return $is_contract_enabled ? DeliveryServiceCode::PAC_CONTRATO_AG : DeliveryServiceCode::PAC;
			case 'sedex':
				return $is_contract_enabled ? DeliveryServiceCode::SEDEX_CONTRATO_AG : DeliveryServiceCode::SEDEX;
			case 'sedex10':
				return $is_contract_enabled ? DeliveryServiceCode::SEDEX_10_CONTRATO_AG : DeliveryServiceCode::SEDEX_10;
			case 'sedex12':
				return $is_contract_enabled ? DeliveryServiceCode::SEDEX_12_CONTRATO_AG : DeliveryServiceCode::SEDEX_12;
			case 'sedexhoje':
				return $is_contract_enabled ? DeliveryServiceCode::SEDEX_HOJE_CONTRATO_AG : DeliveryServiceCode::SEDEX_HOJE;
			case 'impressonormal':
				return DeliveryServiceCode::IMPRESSO_NORMAL;
			case 'impressomodico':
				return DeliveryServiceCode::IMPRESSO_MODICO;
			default:
				return $is_contract_enabled ? DeliveryServiceCode::PAC_CONTRATO_AG : DeliveryServiceCode::PAC;
		}
	}

	/**
	 * Get the package data.
	 *
	 * @param  array $package Cart package.
	 * 
	 * @return Package
	 */
	public function get_package( $package ) {
		$shipping_package = new Package( $package );
		$shipping_package->setExtraWeight( $this->extra_weight );
		$shipping_package->setExtraWeightType( $this->extra_weight_type );
		$shipping_package->setMinWeight( $this->minimum_weight );
		$shipping_package->setMinHeight( $this->minimum_height );
		$shipping_package->setMinWidth( $this->minimum_width );
		$shipping_package->setMinLength( $this->minimum_length );
		return $shipping_package;
	}


	/**
	 * Calculate Shipping
	 * 
	 * @param array $package
	 * 
	 * @return void
	 */
	public function calculate_shipping( $package = [] ) {

		if ( ! $this->can_be_calculated( $package ) ) {
			return;
		}

		$origin_postcode = Sanitizer::numeric_text( $this->origin_postcode );
		$destination_postcode = Sanitizer::numeric_text( $package['destination']['postcode'] );
		$product_code = $this->get_product_code();

		if ( empty( $product_code ) || empty( $origin_postcode ) || empty( $destination_postcode ) ) {
			return;
		}

		$shipping_cost = new ShippingCost(
			$product_code,
			$origin_postcode,
			$destination_postcode
		);

		$shipping_package = $this->get_package( $package );

		$shipping_cost->setPackage( $shipping_package );
		$shipping_cost->setOwnHands( $this->own_hands );
		$shipping_cost->setReceiptNotice( $this->receipt_notice );

		$cost_response = Container::correiosService()->get_shipping_cost( $shipping_cost );

		$cost = is_array( $cost_response ) && isset( $cost_response['shipping_cost'] ) ? $cost_response['shipping_cost'] : $cost_response;
		$time = is_array( $cost_response ) && isset( $cost_response['delivery_time'] ) ? $cost_response['delivery_time'] : false;

		if ( $cost === false ) {
			return;
		}

		$cost += $this->additional_tax / 100;

		$meta_data = [ 
			"_weight" => $shipping_cost->getWeight(),
			"_length" => $shipping_cost->getLength(),
			"_width" => $shipping_cost->getWidth(),
			"_height" => $shipping_cost->getHeight(),
		];

		if ( $this->estimated_delivery ) {

			if ( $time === false ) {
				$shipping_time = new ShippingTime(
					$product_code,
					$origin_postcode,
					$destination_postcode
				);
				$time = $shipping_time->calculate();
			}

			if ( $time !== false ) {
				$meta_data['delivery_time'] = $time + $this->additional_days;
			}
		}

		$rate = [ 
			'id' => "{$this->id}_{$this->instance_id}",
			'label' => $this->title,
			'cost' => $cost,
			'package' => $package,
			'meta_data' => $meta_data
		];

		$this->add_rate( apply_filters( 'infixs_correios_automatico_rate', $rate, $package ) );
	}


	public function get_auto_prepost() {
		return $this->auto_prepost;
	}

	public function get_product_code() {
		return $this->advanced_mode ? $this->advanced_service : $this->resolve_basic_service( $this->basic_service );
	}

	public function get_object_type_code() {
		if ( $this->object_type === 'letter' ) {
			return 1;
		}
		return 2;
	}
}