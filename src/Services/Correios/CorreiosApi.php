<?php

namespace Infixs\CorreiosAutomatico\Services\Correios;

use Infixs\CorreiosAutomatico\Core\Support\Log;
use Infixs\CorreiosAutomatico\Repositories\ConfigRepository;
use Infixs\CorreiosAutomatico\Services\Correios\Enums\Environment;
use Infixs\CorreiosAutomatico\Traits\HttpTrait;

defined( 'ABSPATH' ) || exit;

class CorreiosApi {
	use HttpTrait;

	/**
	 * Config Repository
	 * 
	 * @var ConfigRepository
	 */
	protected $configRepository;

	protected $contract_enabled;

	/**
	 * Enviroment
	 * 
	 * @var Environment::PRODUCTION|Environment::SANDBOX $enviroment
	 */
	protected $enviroment;

	protected $sandboxUrl = 'https://apihom.correios.com.br';

	protected $productionUrl = 'https://api.correios.com.br';


	/**
	 * Constructor
	 * 
	 * @param ConfigRepository $configRepository
	 */
	public function __construct( $configRepository ) {

		$this->configRepository = $configRepository;

		$this->contract_enabled = $this->configRepository->boolean( 'auth.active' );

		$enviroment = $this->configRepository->get( 'auth.environment', 'production' );

		$this->enviroment = $enviroment === 'production' ? Environment::PRODUCTION : Environment::SANDBOX;
	}

	/**
	 * Get API URL
	 * 
	 * @param Environment::PRODUCTION|Environment::SANDBOX|null $enviroment
	 * 
	 * @return string
	 */
	public function getApiUrl( $enviroment = null ) {
		$enviroment = $enviroment ?: $this->enviroment;

		return $enviroment === Environment::PRODUCTION ? $this->productionUrl : $this->sandboxUrl;
	}

	/**
	 * Get token if expired
	 * 
	 * @param mixed $endpoint
	 * @param mixed $data
	 * @param mixed $headers
	 * @param mixed $base_url
	 * 
	 * @return array|\WP_Error
	 */
	protected function authenticated_post( $endpoint, $data, $headers = [], $retry = true ) {
		$token = $this->configRepository->get( 'auth.token' );
		if ( empty( $token ) ) {
			$token = $this->get_token();
		}

		if ( is_wp_error( $token ) ) {
			return $token;
		}

		$response = $this->post( $this->join_url( $this->getApiUrl(), $endpoint ), $data, array_merge( [ 
			'Authorization' => "Bearer $token",
		], $headers ) );

		if ( is_wp_error( $response ) && $retry === true ) {
			$error_data = $response->get_error_data();
			//Expired token?
			if ( is_array( $error_data ) && isset( $error_data['status'] ) && $error_data['status'] == 403 ) {
				$token = $this->get_token();
				$response = $this->authenticated_post( $endpoint, $data, $headers, false );
			}
		}

		return $response;
	}


	/**
	 * Prepostagem endpoint
	 * 
	 * @param array $data
	 * 
	 * @return array
	 */
	public function prepostagens( $data ) {
		return $this->authenticated_post(
			'prepostagem/v1/prepostagens',
			$data
		);
	}

	/**
	 * Prepostagem endpoint
	 * 
	 * @param array $data
	 * 
	 * @return array|\WP_Error
	 */
	public function precoNacional( $product_code, $data ) {
		return $this->authenticated_get(
			$this->join_url( 'preco/v1/nacional', $product_code ), $data );
	}

	/**
	 * Authenticated Get
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $endpoint
	 * @param array $params
	 * @param array $headers
	 * @param bool $retry
	 * 
	 * @return array|\WP_Error
	 */
	public function authenticated_get( $endpoint, $params = [], $headers = [], $retry = true ) {
		$token = $this->configRepository->get( 'auth.token' );
		$token = empty( $token ) ? $this->get_token() : $token;

		if ( is_wp_error( $token ) )
			return $token;

		$response = $this->get( $this->join_url( $this->getApiUrl(), $endpoint ), $params, array_merge( [ 
			'Authorization' => "Bearer $token",
		], $headers ) );

		if ( is_wp_error( $response ) )
			return $response;

		$code = wp_remote_retrieve_response_code( $response );

		if ( $code == 403 && $retry ) {
			$token = $this->get_token();
			$response = $this->authenticated_get( $endpoint, $params, $headers, false );
		}

		if ( in_array( $code, [ 200, 201 ], true ) ) {
			$body = wp_remote_retrieve_body( $response );
			$data = json_decode( $body, true );

			return $data;
		}

		return $response;
	}


	/**
	 * Get Auth Token
	 * 
	 * @since 1.0.0
	 * 
	 * @return string|\WP_Error
	 */
	protected function get_token() {
		$user_name = $this->configRepository->get( 'auth.user_name' );
		$access_code = $this->configRepository->get( 'auth.access_code' );
		$postcard = $this->configRepository->get( 'auth.postcard' );

		$response = $this->auth_postcard( $user_name, $access_code, $postcard );

		if ( is_wp_error( $response ) )
			return $response;

		return $response['token'];
	}

	/**
	 * Authenticate with postcard
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $user_name
	 * @param string $access_code
	 * @param string $postcard
	 * @param Environment::PRODUCTION|Environment::SANDBOX|null $enviroment
	 * 
	 * @return array|\WP_Error
	 */
	public function auth_postcard( $user_name, $access_code, $postcard, $enviroment = null ) {
		$credentials = base64_encode( "{$user_name}:{$access_code}" );
		$response = $this->post( $this->join_url( $this->getApiUrl( $enviroment ), 'token/v1/autentica/cartaopostagem' ),
			[ 
				'numero' => $postcard
			],
			[ 
				'Authorization' => "Basic $credentials",
			]
		);

		if ( is_wp_error( $response ) ) {
			Log::error( 'Erro ao autenticar com cartão postagem nos correios, verifque as credenciais', [ 
				'message' => $response->get_error_message(),
			] );
			return $response;
		}

		if ( ! isset( $response['token'] ) ) {
			Log::error( 'Erro ao autenticar com cartão postagem nos correios, verifque as credenciais' );
			return new \WP_Error( 'correios_auth_postcard', "Erro ao autenticar com os correios", [ 'status' => 400 ] );
		}

		$this->configRepository->update( 'auth.token', $response['token'] );
		return $response;
	}

	public function consultaCep( $postcode ) {
		return $this->authenticated_get(
			$this->join_url( 'cep/v2/enderecos', $postcode ) );
	}

	/**
	 * Set Environment
	 * 
	 * @param Environment::PRODUCTION|Environment::SANDBOX $environment
	 * 
	 * @return void
	 */
	public function setEnvironment( $environment ) {
		$this->enviroment = $environment;
	}
}