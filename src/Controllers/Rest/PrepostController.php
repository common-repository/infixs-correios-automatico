<?php

namespace Infixs\CorreiosAutomatico\Controllers\Rest;
use Infixs\CorreiosAutomatico\Core\Support\Config;
use Infixs\CorreiosAutomatico\Services\PrepostService;

defined( 'ABSPATH' ) || exit;
class PrepostController {
	/**
	 * Prepost service instance.
	 * 
	 * @since 1.0.0
	 * 
	 * @var PrepostService
	 */
	private $prepostService;

	public function __construct( PrepostService $prepostService ) {
		$this->prepostService = $prepostService;
	}

	/**
	 * Create prepost.
	 * 
	 * @since 1.0.0
	 * 
	 * @param \WP_REST_Request $request
	 * 
	 * @return \WP_Error|\WP_REST_Response
	 */
	public function createFromOrder( \WP_REST_Request $request ) {
		$params = $request->get_params();

		if ( ! isset( $params['order_id'] ) ) {
			return new \WP_Error( 'missing_order_id', 'Order ID is required.', [ 'status' => 400 ] );
		}

		if ( isset( $params['invoice_key'] ) && strlen( trim( $params['invoice_key'] ) ) !== 44 ) {
			return new \WP_Error( 'invalid_invoice_key', 'A Chave da nota fiscal deve ter 44 caracteres.', [ 'status' => 400 ] );
		}

		if ( isset( $params['invoice_number'] ) && strlen( trim( $params['invoice_number'] ) ) == 0 ) {
			return new \WP_Error( 'invalid_invoice_number', 'O Número da nota fiscal é obrigatório.', [ 'status' => 400 ] );
		}

		if ( ! Config::boolean( 'auth.active' ) ) {
			return new \WP_Error( 'auth_not_active', 'O Contrato não está ativo, acesse as configurações para ativá-lo. Menu WooCommerce -> Correios Automático -> Configurações -> Contrato.', [ 'status' => 400 ] );
		}

		$prepost = $this->prepostService->createPrepost( $params['order_id'] );

		if ( is_wp_error( $prepost ) ) {
			return $prepost;
		}

		return rest_ensure_response( [ 
			"status" => "success",
			"prepost" => $prepost,
		] );
	}
}