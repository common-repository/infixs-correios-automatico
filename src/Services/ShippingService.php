<?php

namespace Infixs\CorreiosAutomatico\Services;

use Infixs\CorreiosAutomatico\Container;
use Infixs\CorreiosAutomatico\Core\Shipping\CorreiosShippingMethod;
use Infixs\CorreiosAutomatico\Core\Support\Config;
use Infixs\CorreiosAutomatico\Models\WoocommerceShippingZoneMethod;
use Infixs\CorreiosAutomatico\Services\Correios\CorreiosService;
use Infixs\CorreiosAutomatico\Services\Correios\Enums\DeliveryServiceCode;
use Infixs\CorreiosAutomatico\Utils\Sanitizer;

defined( 'ABSPATH' ) || exit;

class ShippingService {

	/**
	 * Correios Service
	 * 
	 * @var CorreiosService
	 */
	protected $correiosService;

	/**
	 * InfixsApi
	 * 
	 * @var InfixsApi
	 */
	protected $infixsApi;

	/**
	 * Constructor
	 * 
	 * @param CorreiosService $correiosService
	 * @param InfixsApi $infixsApi
	 * 
	 */
	public function __construct( CorreiosService $correiosService, InfixsApi $infixsApi ) {
		$this->correiosService = $correiosService;
		$this->infixsApi = $infixsApi;
	}

	/**
	 * Get shipping methods
	 * 
	 * @since 1.0.0
	 * 
	 * @param array{is_enabled: bool|null, method_id: string[]|null} $filters
	 * 
	 * @return array
	 */
	public function list_shipping_methods( $filters ) {
		$enabled = $filters['is_enabled'] ?? null;
		$method_id = $filters['method_id'] ?? null;

		$shipping_zones = \WC_Shipping_Zones::get_zones();

		$shipping_methods = [];

		foreach ( $shipping_zones as $zone ) {
			/** @var \WC_Shipping_Method $shipping_method **/
			foreach ( $zone['shipping_methods'] as $shipping_method ) {
				if ( $enabled !== null && $shipping_method->is_enabled() !== $enabled ) {
					continue;
				}

				if ( $method_id !== null && count( $method_id ) > 0 && ! in_array( $shipping_method->id, $method_id ) ) {
					continue;
				}

				$shipping_methods[] = [ 
					"zone_id" => $zone['id'],
					"instance_id" => $shipping_method->get_instance_id(),
					"method_id" => $shipping_method->id,
					"title" => $shipping_method->get_title(),
					"enabled" => $shipping_method->is_enabled(),
				];
			}
		}

		return $shipping_methods;
	}

	/**
	 * Import active shipping methods by plugin ids
	 * 
	 * @since 1.0.0
	 * 
	 * @param array $plugin_ids
	 * @param bool $disable_imported_methods
	 * 
	 * @return array
	 */
	public function import_shipping_methods_by_plugin_id( $plugin_ids, $disable_imported_methods = true ) {

		$plugin_shipping_methods = $this->get_compatible_methods();

		$allowed_method_ids = [];

		$return_data = [ 
			"total_imported" => 0,
			"auth_imported" => false,
		];

		foreach ( $plugin_ids as $plugin_id ) {
			if ( ! isset( $plugin_shipping_methods[ $plugin_id ] ) ) {
				continue;
			}
			$allowed_method_ids = array_merge( $allowed_method_ids, $plugin_shipping_methods[ $plugin_id ] );
		}


		$shipping_zones = \WC_Shipping_Zones::get_zones();


		foreach ( $shipping_zones as $zone ) {
			/** @var \WC_Shipping_Method $shipping_method **/
			foreach ( $zone['shipping_methods'] as $shipping_method ) {
				if ( $shipping_method->is_enabled() !== true ) {
					continue;
				}

				if ( ! in_array( $shipping_method->id, $allowed_method_ids ) ) {
					continue;
				}

				$created_zone = new \WC_Shipping_Zone( $zone['id'] );
				$instance_id = $created_zone->add_shipping_method( 'infixs-correios-automatico' );

				if ( $instance_id === 0 )
					continue;

				try {
					$created_shipping_method = \WC_Shipping_Zones::get_shipping_method( $instance_id );

					$created_shipping_method->init_instance_settings();

					$data = $this->clone_options( $shipping_method, $created_shipping_method );

					foreach ( $created_shipping_method->get_instance_form_fields() as $key => $field ) {
						if ( 'title' !== $created_shipping_method->get_field_type( $field ) ) {
							try {
								$created_shipping_method->instance_settings[ $key ] = $created_shipping_method->get_field_value( $key, $field, $data );
							} catch (\Exception $e) {
								$created_shipping_method->add_error( $e->getMessage() );
							}
						}
					}

					update_option( $created_shipping_method->get_instance_option_key(), $created_shipping_method->instance_settings, 'yes' );

					$return_data['total_imported']++;

					if ( $disable_imported_methods )
						$this->disable_shipping_method( $shipping_method->instance_id );

				} catch (\Exception $e) {
					$created_zone->delete_shipping_method( $instance_id );
					continue;
				}

			}
		}

		if ( $config = $this->import_contract_config() ) {
			$postcard_response = Container::correiosService()->auth_postcard( $config['user_name'], $config['access_code'], $config['postcard'] );
			if ( ! is_wp_error( $postcard_response ) ) {
				$allowed_services = Sanitizer::array_numbers( $postcard_response['cartaoPostagem']['api'] );

				Config::update( 'auth', array_merge( $config, [ 
					'active' => true,
					'environment' => 'production',
					'token' => $postcard_response['token'],
					'allowed_services' => $allowed_services ?? [],
					'contract_type' => sanitize_text_field( $postcard_response['perfil'] ),
					'contract_document' => sanitize_text_field( $postcard_response['perfil'] === 'PJ' ? $postcard_response['cnpj'] : $postcard_response['cpf'] ),
				] ) );

				$return_data['auth_imported'] = true;
			}

		}

		return $return_data;
	}


	/**
	 * Import contract config
	 * 
	 * @since 1.0.0
	 * 
	 * @return array|bool		Return array with auth settings or false if not imported
	 */
	private function import_contract_config() {
		$option_value = get_option( 'virtuaria_correios_settings' );
		if ( ! is_array( $option_value ) ) {
			$option_value = maybe_unserialize( $option_value );
		}

		if ( is_array( $option_value ) && isset( $option_value['username'], $option_value['password'], $option_value['post_card'] ) ) {
			return [ 
				'user_name' => $option_value['username'],
				'access_code' => $option_value['password'],
				'postcard' => $option_value['post_card'],
			];
		}

		$option_value = get_option( 'woocommerce_correios-integration_settings' );

		if ( ! is_array( $option_value ) ) {
			$option_value = maybe_unserialize( $option_value );
		}

		if ( is_array( $option_value ) && isset( $option_value['username'], $option_value['password'], $option_value['post_card'] ) ) {
			return [ 
				'user_name' => $option_value['cws_username'],
				'access_code' => $option_value['cws_access_code'],
				'postcard' => $option_value['cws_posting_card'],
			];
		}


		return false;
	}

	public function disable_shipping_method( $instance_id ) {
		return WoocommerceShippingZoneMethod::update( [ 
			"is_enabled" => 0,
		], [ 
			"instance_id" => $instance_id,
		] );
	}



	/**
	 * Clone shipping options
	 * 
	 * @since 1.0.0
	 * 
	 * @param \WC_Shipping_Method $source
	 * @param CorreiosShippingMethod  $destination
	 * 
	 * @return array
	 */
	private function clone_options( $source, $destination ) {
		$field_enabled = $destination->get_field_key( 'enabled' );
		$field_title = $destination->get_field_key( 'title' );
		$field_origin_postcode = $destination->get_field_key( 'origin_postcode' );
		$field_advanced_mode = $destination->get_field_key( 'advanced_mode' );
		$field_advanced_service = $destination->get_field_key( 'advanced_service' );
		$field_basic_service = $destination->get_field_key( 'basic_service' );
		$field_estimated_delivery = $destination->get_field_key( 'estimated_delivery' );
		$field_additional_days = $destination->get_field_key( 'additional_days' );
		$field_extra_weight = $destination->get_field_key( 'extra_weight' );
		$field_additional_tax = $destination->get_field_key( 'additional_tax' );
		$field_receipt_notice = $destination->get_field_key( 'receipt_notice' );
		$field_own_hands = $destination->get_field_key( 'own_hands' );
		$field_minimum_height = $destination->get_field_key( 'minimum_height' );
		$field_minimum_width = $destination->get_field_key( 'minimum_width' );
		$field_minimum_length = $destination->get_field_key( 'minimum_length' );
		$field_minimum_weight = $destination->get_field_key( 'minimum_weight' );
		$field_object_type = $destination->get_field_key( 'object_type' );
		$field_insurance = $destination->get_field_key( 'insurance' );
		$field_min_insurance_value = $destination->get_field_key( 'min_insurance_value' );
		$field_extra_weight_type = $destination->get_field_key( 'extra_weight_type' );
		$store_postcode = get_option( 'woocommerce_store_postcode' );


		$post_data = [];
		foreach ( $destination->get_instance_form_fields() as $key => $field ) {
			$field_key = $destination->get_field_key( $key );
			if ( $field['type'] === 'checkbox' ) {
				$post_data[ $field_key ] = Sanitizer::post_checkbox( $field['default'] );
				continue;
			}
			$post_data[ $field_key ] = $field['default'] ?? '';
		}

		switch ( $source->id ) {
			case 'correios-cws':
				$postcode = $source->get_option( 'origin_postcode', '' );
				$post_data = array_merge( $post_data, [ 
					$field_enabled => $source->is_enabled(),
					$field_title => $source->get_title(),
					$field_advanced_mode => 'yes',
					$field_origin_postcode => empty( $postcode ) ? $store_postcode : $postcode,
					$field_advanced_service => $source->get_option( 'product_code' ),
					$field_estimated_delivery => Sanitizer::post_checkbox( $source->get_option( 'show_delivery_time' ) ),
					$field_additional_days => $source->get_option( 'additional_time' ),
					$field_extra_weight => $source->get_option( 'extra_weight' ),
					$field_additional_tax => $source->get_option( 'fee' ),
					$field_receipt_notice => Sanitizer::post_checkbox( $source->get_option( 'receipt_notice' ) ),
					$field_own_hands => Sanitizer::post_checkbox( $source->get_option( 'own_hands' ) ),
					$field_minimum_height => $source->get_option( 'minimum_height' ),
					$field_minimum_width => $source->get_option( 'minimum_width' ),
					$field_minimum_length => $source->get_option( 'minimum_length' ),
					$field_minimum_weight => $source->get_option( 'minimum_weight' ),
				] );
				return $post_data;
			case 'correios-pac':
			case 'correios-sedex':
			case 'correios-sedex10-pacote':
			case 'correios-sedex12':
			case 'correios-sedex-hoje':
			case 'correios-impresso-normal':
				if ( $source->get_option( 'service_type' ) != "conventional" )
					throw new \Exception( esc_html__( 'Only conventional service type is supported.', 'infixs-correios-automatico' ) );

				$converted = [ 
					"correios-pac" => 'pac',
					"correios-sedex" => 'sedex',
					"correios-sedex10-pacote" => 'sedex10',
					"correios-sedex12" => 'sedex12',
					"correios-sedex-hoje" => 'sedexhoje',
					"correios-impresso-normal" => 'impressonormal',
				];

				$postcode = $source->get_option( 'origin_postcode', '' );

				$post_data = array_merge( $post_data,
					[ 
						$field_enabled => $source->is_enabled(),
						$field_title => $source->get_title(),
						$field_advanced_mode => null,
						$field_origin_postcode => empty( $postcode ) ? $store_postcode : $postcode,
						$field_basic_service => $converted[ $source->id ],
						$field_estimated_delivery => Sanitizer::post_checkbox( $source->get_option( 'show_delivery_time' ) ),
						$field_additional_days => $source->get_option( 'additional_time' ),
						$field_extra_weight => $source->get_option( 'extra_weight' ),
						$field_additional_tax => $source->get_option( 'fee' ),
						$field_receipt_notice => Sanitizer::post_checkbox( $source->get_option( 'receipt_notice' ) ),
						$field_own_hands => Sanitizer::post_checkbox( $source->get_option( 'own_hands' ) ),
						$field_minimum_height => $source->get_option( 'minimum_height' ),
						$field_minimum_width => $source->get_option( 'minimum_width' ),
						$field_minimum_length => $source->get_option( 'minimum_length' ),
						$field_minimum_weight => $source->get_option( 'minimum_weight' ),
					] );
				return $post_data;

			case 'virtuaria-correios-sedex':
				$teste = "virutal";

				$extra_weight = json_decode( $source->get_option( 'extra_weight' ), true );
				$postcode = $source->get_option( 'origin', '' );

				$post_data = array_merge( $post_data,
					[ 
						$field_enabled => $source->is_enabled(),
						$field_title => $source->get_title(),
						$field_advanced_mode => 'yes',
						$field_advanced_service => $source->get_option( 'service_cod' ),
						$field_origin_postcode => empty( $postcode ) ? $store_postcode : $postcode,
						$field_estimated_delivery => $source->get_option( 'hide_delivery_time' ) == 'yes' ? null : 'yes',
						$field_object_type => $source->get_option( 'object_type' ) == "1" ? 'letter' : 'package',
						$field_additional_days => $source->get_option( 'additional_time' ),
						$field_extra_weight => isset( $extra_weight, $extra_weight['weight'] ) ? $extra_weight['weight'] : 0,
						$field_additional_tax => $source->get_option( 'fee' ),
						$field_receipt_notice => Sanitizer::post_checkbox( $source->get_option( 'receipt_notice' ) ),
						$field_own_hands => Sanitizer::post_checkbox( $source->get_option( 'own_hands' ) ),
						$field_minimum_height => $source->get_option( 'minimum_height' ),
						$field_minimum_width => $source->get_option( 'minimum_width' ),
						$field_minimum_length => $source->get_option( 'minimum_length' ),
						$field_minimum_weight => $source->get_option( 'minimum_weight' ),
						$field_insurance => empty( $source->get_option( 'declare_value' ) ) ? null : 'yes',
						$field_min_insurance_value => $source->get_option( 'min_value_declared' ),
						$$field_extra_weight_type => isset( $extra_weight, $extra_weight['type'] ) ? $extra_weight['type'] : 'order',
					] );
				return $post_data;

			case 'melhorenvio_correios_pac':
			case 'melhorenvio_correios_sedex':
			case 'melhorenvio_correios_mini':

				$converted = [ 
					"melhorenvio_correios_pac" => 'pac',
					"melhorenvio_correios_sedex" => 'sedex',
				];

				$post_data = array_merge( $post_data,
					[ 
						$field_enabled => $source->is_enabled(),
						$field_title => $source->get_title(),
						$field_additional_days => $source->get_option( 'additional_time' ),
						$field_additional_tax => $source->get_option( 'additional_tax' ),
					]
				);

				if ( $source->id === 'melhorenvio_correios_mini' ) {
					$post_data[ $field_advanced_mode ] = 'yes';
					$post_data[ $field_advanced_service ] = DeliveryServiceCode::CORREIOS_MINI_ENVIOS_CTR_AG;
				} else {
					$post_data[ $field_basic_service ] = $converted[ $source->id ];
				}

				return $post_data;

		}

		throw new \Exception( esc_html__( 'Shipping method not supported.', 'infixs-correios-automatico' ) );
	}

	public function get_compatible_methods( $flatten = false ) {
		$methods = [ 
			"virtuaria-correios" => [ 
				"virtuaria-correios-sedex"
			],
			"woocommerce-correios" => [ 
				"correios-cws",
				"correios-pac",
				"correios-sedex",
				"correios-sedex10-pacote",
				"correios-sedex12",
				"correios-sedex-hoje",
				"correios-impresso-normal",
				//"correios-sedex10-envelope",
				//"correios-impresso-urgente",
			],
			"melhor-envio-cotacao" => [ 
				"melhorenvio_correios_pac",
				"melhorenvio_correios_sedex",
				"melhorenvio_correios_mini"
			],
		];

		return $flatten ? array_merge( ...array_values( $methods ) ) : $methods;
	}

	public function getAddressByPostcode( string $postcode ) {
		$address = $this->fetchViacepAddress( $postcode );

		if ( $address ) {
			return $address;
		}

		$address = $this->correiosService->fetch_postcode( $postcode );

		if ( $address && ! is_wp_error( $address ) ) {
			return $address;
		}

		$address = $this->infixsApi->fetchAddress( $postcode );

		if ( $address && ! is_wp_error( $address ) ) {
			return $address;
		}

		return false;
	}

	public function fetchViacepAddress( $postcode ) {
		$address = [];

		$api_url = "https://viacep.com.br/ws/{$postcode}/json/";

		$response = wp_remote_get( $api_url );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$body = wp_remote_retrieve_body( $response );

		if ( ! $body ) {
			return false;
		}

		$data = json_decode( $body );

		if ( ! $data ) {
			return false;
		}

		if ( isset( $data->erro ) && $data->erro ) {
			return false;
		}

		$address['postcode'] = $data->cep;
		$address['address'] = $data->logradouro;
		$address['neighborhood'] = $data->bairro;
		$address['city'] = $data->localidade;
		$address['state'] = $data->uf;

		return $address;
	}

	public function getStateByPostcode( $postcode ) {
		$postcodes_map = [ 
			'01' => 'SP',
			'02' => 'SP',
			'03' => 'SP',
			'04' => 'SP',
			'05' => 'SP',
			'06' => 'SP',
			'07' => 'SP',
			'08' => 'SP',
			'09' => 'SP',
			'10' => 'SP',
			'11' => 'SP',
			'12' => 'SP',
			'13' => 'SP',
			'14' => 'SP',
			'15' => 'SP',
			'16' => 'SP',
			'17' => 'SP',
			'18' => 'SP',
			'19' => 'SP',
			'20' => 'RJ',
			'21' => 'RJ',
			'22' => 'RJ',
			'23' => 'RJ',
			'24' => 'RJ',
			'25' => 'RJ',
			'26' => 'RJ',
			'27' => 'RJ',
			'28' => 'RJ',
			'29' => 'ES',
			'30' => 'MG',
			'31' => 'MG',
			'32' => 'MG',
			'33' => 'MG',
			'34' => 'MG',
			'35' => 'MG',
			'36' => 'MG',
			'37' => 'MG',
			'38' => 'MG',
			'39' => 'MG',
			'40' => 'BA',
			'41' => 'BA',
			'42' => 'BA',
			'43' => 'BA',
			'44' => 'BA',
			'45' => 'BA',
			'46' => 'BA',
			'47' => 'BA',
			'48' => 'BA',
			'49' => 'SE',
			'50' => 'PE',
			'51' => 'PE',
			'52' => 'PE',
			'53' => 'PE',
			'54' => 'PE',
			'55' => 'AL',
			'56' => 'AL',
			'57' => 'AL',
			'58' => 'PB',
			'59' => 'RN',
			'60' => 'CE',
			'61' => 'CE',
			'62' => 'CE',
			'63' => 'CE',
			'64' => 'PI',
			'65' => 'MA',
			'66' => 'MA',
			'67' => 'MA',
			'68' => 'PA',
			'69' => 'PA',
			'70' => 'DF',
			'71' => 'DF',
			'72' => 'DF',
			'73' => 'DF',
			'74' => 'GO',
			'75' => 'GO',
			'76' => 'GO',
			'77' => 'TO',
			'78' => 'MT',
			'79' => 'MS',
			'80' => 'PR',
			'81' => 'PR',
			'82' => 'PR',
			'83' => 'PR',
			'84' => 'PR',
			'85' => 'PR',
			'86' => 'PR',
			'87' => 'PR',
			'88' => 'SC',
			'89' => 'SC',
			'90' => 'RS',
			'91' => 'RS',
			'92' => 'RS',
			'93' => 'RS',
			'94' => 'RS',
			'95' => 'RS',
			'96' => 'RS',
			'97' => 'RS',
			'98' => 'RS',
			'99' => 'RS',
		];

		$digits = substr( $postcode, 0, 2 );
		if ( isset( $postcodes_map[ $digits ] ) ) {
			return $postcodes_map[ $digits ];
		}

		$address = $this->getAddressByPostcode( $postcode );
		return $address ? $address['state'] : '';
	}
}