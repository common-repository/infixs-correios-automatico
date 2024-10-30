<?php

namespace Infixs\CorreiosAutomatico\Services;

defined( 'ABSPATH' ) || exit;

class InfixsApi {
	protected $api_url = 'https://api.infixs.io';
	protected $api_version = 'v1';

	/**
	 * Send plugin deactivation data to Infixs API.
	 *
	 * @since 1.0.0
	 * 
	 * @return \WP_Error|array The response or WP_Error on failure.
	 */
	public function postDeactivationPlugin( $data ) {
		return wp_safe_remote_post( $this->getApiUrl( 'plugin/deactivate' ), [ 
			"body" => wp_json_encode( $data ),
			'headers' => [ 
				'Content-Type' => 'application/json',
			]
		] );
	}

	public function getApiUrl( $endpoint = '' ) {
		return $this->joinUrl( "{$this->api_url}/{$this->api_version}", $endpoint );
	}

	protected function joinUrl( $url, $path ) {
		return join( '/', [ rtrim( $url, '/' ), ltrim( $path, '/' ) ] );
	}

	public function fetchAddress( $postcode ) {
		$response = wp_safe_remote_get( $this->getApiUrl( "postcode/{$postcode}" ), [ 
			'headers' => [ 
				'Content-Type' => 'application/json',
			]
		] );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( ! in_array( wp_remote_retrieve_response_code( $response ), [ 200, 201 ], true ) ) {
			return new \WP_Error( "http_error", 'Erro ao buscar endereço', [ 'status' => wp_remote_retrieve_response_code( $response ) ] );
		}

		return $data;
	}
}