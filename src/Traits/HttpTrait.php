<?php
namespace Infixs\CorreiosAutomatico\Traits;

trait HttpTrait {
	/**
	 * Post request
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $endpoint
	 * @param array $data
	 * @param array $headers
	 * 
	 * @return array|\WP_Error
	 */
	protected function post( $url, $data, $headers = [] ) {
		$response = wp_safe_remote_post( $url, [ 
			'body' => wp_json_encode( $data ),
			'headers' => array_merge( [ 
				'Content-Type' => 'application/json',
			], $headers ),
		] );

		if ( is_wp_error( $response ) ) {
			return $response;
		}

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );
		$code = wp_remote_retrieve_response_code( $response );

		if ( ! in_array( wp_remote_retrieve_response_code( $response ), [ 200, 201 ], true ) ) {
			$message = $data['msgs'][0] ?? $data['msg'] ?? 'Erro ao autenticar com os correios';
			return new \WP_Error( "http_error", $message, [ 'status' => $code ] );
		}

		return $data;
	}

	/**
	 * Join URL
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $url
	 * @param string $path
	 * 
	 * @return string
	 */
	public function join_url( $url, $path ) {
		return join( '/', [ rtrim( $url, '/' ), ltrim( $path, '/' ) ] );
	}

	/**
	 * Get request
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $url
	 * @param array $params
	 * @param array $headers
	 * 
	 * @return array|\WP_Error
	 */
	protected function get( $url, $params = [], $headers = [] ) {

		$url = add_query_arg( $params, $url );

		$response = wp_safe_remote_get( $url,
			[ 
				'headers' => $headers,
			]
		);

		return $response;
	}
}