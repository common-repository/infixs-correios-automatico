<?php

namespace Infixs\CorreiosAutomatico\Services\Correios;

use Infixs\CorreiosAutomatico\Repositories\ConfigRepository;
use Infixs\CorreiosAutomatico\Services\Correios\Enums\AddicionalServiceCode;
use Infixs\CorreiosAutomatico\Services\Correios\Includes\ShippingCost;
use Infixs\CorreiosAutomatico\Traits\HttpTrait;
use Infixs\CorreiosAutomatico\Utils\Sanitizer;

defined( 'ABSPATH' ) || exit;

class CorreiosService {

	use HttpTrait;

	protected $configRepository;

	protected $contract_enabled;

	/**
	 * CorreiosApi
	 * 
	 * @var CorreiosApi
	 */
	protected $correiosApi;

	/**
	 * Constructor
	 * 
	 * @param ConfigRepository $configRepository
	 * @param CorreiosApi $correiosApi
	 * 
	 */
	public function __construct( $correiosApi, $configRepository ) {
		$this->configRepository = $configRepository;
		$this->correiosApi = $correiosApi;
		$this->contract_enabled = $this->configRepository->boolean( 'auth.active' );
	}


	/**
	 * Summary of get_shipping_cost
	 * 
	 * @param ShippingCost $shipping_cost
	 * @param array $params
	 * 
	 * @return int|float|false|array
	 */
	public function get_shipping_cost( $shipping_cost ) {
		if ( $this->contract_enabled ) {
			$adicional_services = [];

			if ( $shipping_cost->getOwnHands() ) {
				$adicional_services[] = AddicionalServiceCode::OWN_HANDS;
			}

			if ( $shipping_cost->getReceiptNotice() ) {
				$adicional_services[] = AddicionalServiceCode::RECEIPT_NOTICE;
			}

			$response = $this->correiosApi->precoNacional(
				$shipping_cost->getProductCode(),
				[ 
					"cepOrigem" => $shipping_cost->getOriginPostcode(),
					"cepDestino" => $shipping_cost->getDestinationPostcode(),
					"psObjeto" => $shipping_cost->getWeight( 'g' ),
					"comprimento" => $shipping_cost->getLength(),
					"altura" => $shipping_cost->getHeight(),
					"largura" => $shipping_cost->getWidth(),
					"servicosAdicionais" => $adicional_services,
				]
			);

			if ( isset( $response["pcFinal"] ) )
				return Sanitizer::numeric( $response["pcFinal"] ) / 100;
		} else {
			$response = $this->post(
				'https://api.infixs.io/v1/shipping/calculate/correios',
				[ 
					"origin_postal_code" => $shipping_cost->getOriginPostcode(),
					"destination_postal_code" => $shipping_cost->getDestinationPostcode(),
					"product_code" => $shipping_cost->getProductCode(),
					"type" => $shipping_cost->getLength(),
					"package" => [ 
						"weight" => $shipping_cost->getWeight( 'g' ),
						"length" => $shipping_cost->getLength(),
						"width" => $shipping_cost->getWidth(),
						"height" => $shipping_cost->getHeight(),
					],
					"services" => [ 
						"own_hands" => $shipping_cost->getOwnHands(),
						"receipt_notice" => $shipping_cost->getReceiptNotice(),
					],

				],
				[],
			);

			if ( ! is_wp_error( $response ) && isset( $response["shipping_cost"] ) ) {
				return $response;
			}

		}
		return false;
	}


	/**
	 * Create Prepost
	 * 
	 * @param \Infixs\CorreiosAutomatico\Services\Correios\Includes\Prepost $prepost
	 * 
	 * @return array
	 */
	public function create_prepost( $prepost ) {
		return $this->correiosApi->prepostagens( $prepost->getData() );
	}

	/**
	 * Get Shipping Time
	 * 
	 * @param string $product_code
	 * @param array $params
	 * 
	 * @return int|false
	 */
	public function get_shipping_time( $product_code, $params ) {
		$response = $this->correiosApi->authenticated_get(
			$this->correiosApi->join_url( 'prazo/v1/nacional', $product_code ),
			$params
		);

		if ( ! is_wp_error( $response ) &&
			isset( $response["prazoEntrega"] ) )
			return Sanitizer::numeric( $response["prazoEntrega"] );

		return false;
	}

	/**
	 * Authenticate with postcard
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $user_name
	 * @param string $access_code
	 * @param string $postcard
	 * @param Environment::PRODUCTION|Environment::SANBOX $environment
	 * 
	 * @return array|\WP_Error
	 */
	public function auth_postcard( $user_name, $access_code, $postcard, $environment = null ) {
		return $this->correiosApi->auth_postcard( $user_name, $access_code, $postcard, $environment );
	}

	/**
	 * Fetch address from Correios API
	 * 
	 * @param string $postcode
	 * 
	 * @return array|\WP_Error
	 */
	public function fetch_postcode( $postcode ) {
		$response = $this->correiosApi->consultaCep( $postcode );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$address = [ 
			'postcode' => $response['cep'],
			'address' => $response['logradouro'],
			'neighborhood' => $response['bairro'],
			'city' => $response['localidade'],
			'state' => $response['uf']
		];

		return $address;
	}
}