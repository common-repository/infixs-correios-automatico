<?php

namespace Infixs\CorreiosAutomatico\Services;

use Infixs\CorreiosAutomatico\Container;
use Infixs\CorreiosAutomatico\Core\Shipping\CorreiosShippingMethod;
use Infixs\CorreiosAutomatico\Core\Support\Config;
use Infixs\CorreiosAutomatico\Core\Support\Log;
use Infixs\CorreiosAutomatico\Entities\Order;
use Infixs\CorreiosAutomatico\Repositories\PrepostRepository;
use Infixs\CorreiosAutomatico\Services\Correios\Includes\Address;
use Infixs\CorreiosAutomatico\Services\Correios\Includes\Person;
use Infixs\CorreiosAutomatico\Services\Correios\Includes\Prepost;
use Infixs\CorreiosAutomatico\Utils\Sanitizer;

defined( 'ABSPATH' ) || exit;

class PrepostService {

	/**
	 * Prepost repository.
	 * 
	 * @since 1.0.0
	 * 
	 * @var PrepostRepository
	 */
	protected $prepostRepository;

	/**
	 * Create a new instance of the service.
	 * 
	 * @since 1.0.0
	 * 
	 * @param PrepostRepository $prepostRepository Prepost repository.
	 */
	public function __construct( PrepostRepository $prepostRepository ) {
		$this->prepostRepository = $prepostRepository;
	}

	/**
	 * Create prepost.
	 * 
	 * This method is responsible for generating a prepost.
	 * 
	 * @since 1.0.0
	 * 
	 * @param int $order_id Order ID.
	 * @param array{
	 * 		invoice_number: string,
	 * 		invoice_key: string,
	 * } $data Data.
	 * 
	 * @return Prepost|\WP_Error
	 */
	public function createPrepost( $order_id, $data = [] ) {
		if ( empty( Config::string( 'sender.name' ) ) ) {
			Log::notice( "Dados do remetente inválidos, é necessário preencher os dados do remetente nas configurações para utilizar a pré-postagem." );
			return new \WP_Error( 'invalid_sender_data', 'Dados do remetente inválidos, é necessário preencher os dados do remetente nas configurações para utilizar a pré-postagem.', [ 'status' => 400 ] );
		}

		$order = wc_get_order( $order_id );

		if ( ! $order ) {
			Log::notice( "Pedido inválido ao criar a pré-postagem." );
			return new \WP_Error( 'invalid_order', 'Pedido inválido ao criar a pré-postagem.', [ 'status' => 400 ] );
		}
		$ca_order = new Order( $order );

		$shipping_method = $ca_order->getShippingMethod();

		if ( ! $shipping_method ) {
			Log::notice( "Esse pedido não tem o método de envio dos correios automático, a prepostagem não pode ser criada." );
			return new \WP_Error( 'invalid_shipping_method', 'Esse pedido não tem o método de envio dos correios automático, a prepostagem não pode ser criada.', [ 'status' => 400 ] );
		}


		$ca_address = $ca_order->getAddress();

		$recipient = new Person(
			$ca_order->getCustomerFullName(),
			new Address(
				$ca_address->getPostCode(),
				$ca_address->getStreet(),
				$ca_address->getNumber(),
				$ca_address->getComplement(),
				$ca_address->getNeighborhood(),
				$ca_address->getCity(),
				$ca_address->getState()
			),
			$ca_order->getCustomerDocument(),
			$ca_order->getPhone(),
			$ca_order->getCellphone(),
			$order->get_billing_email(),
		);

		$sender = new Person(
			Config::string( 'sender.name' ),
			new Address(
				Sanitizer::numeric_text( Config::string( 'sender.address_postalcode' ) ),
				Config::string( 'sender.address_street' ),
				Config::string( 'sender.address_number' ),
				Config::string( 'sender.address_complement' ),
				Config::string( 'sender.address_neighborhood' ),
				Config::string( 'sender.address_city' ),
				Config::string( 'sender.address_state' )
			),
			Config::string( 'sender.document' ),
			Config::string( 'sender.phone' ),
			Config::string( 'sender.celphone' ),
			Config::string( 'sender.email' )
		);


		$package = $ca_order->getPackage();

		$prepost = new Prepost(
			Config::string( 'auth.user_name' ),
			$sender,
			$recipient,
			$shipping_method->get_product_code(),
			$shipping_method->get_object_type_code()
		);

		$prepost->setPackage( $package );

		if ( isset( $data['invoice_number'] ) ) {
			$prepost->setInvoiceNumber( $data['invoice_number'] );
		}

		if ( isset( $data['invoice_key'] ) ) {
			$prepost->setInvoiceKey( $data['invoice_key'] );
		}

		$response = Container::correiosService()->create_prepost( $prepost );

		if ( is_wp_error( $response ) ) {
			Log::notice( "Erro ao criar a pré-postagem.", [ 
				'message' => $response->get_error_message(),
			] );
			return $response;
		}

		$prazoPostagem = \DateTime::createFromFormat( 'Y-m-d', $response['prazoPostagem'] )->format( 'Y-m-d H:i:s' );

		$order->update_meta_data( '_infixs_correios_automatico_prepost_created', 'yes' );

		$prepostId = $this->prepostRepository->create( [ 
			'external_id' => $response['id'],
			'object_code' => $response['codigoObjeto'],
			'service_code' => $response['codigoServico'],
			'payment_type' => $response['modalidadePagamento'],
			'height' => $response['alturaInformada'],
			'width' => $response['larguraInformada'],
			'length' => $response['comprimentoInformado'],
			'weight' => $response['pesoInformado'],
			'request_pickup' => $response['solicitarColeta'],
			'reverse_logistic' => $response['logisticaReversa'],
			'status' => $response['statusAtual'],
			'status_label' => $response['descStatusAtual'],
			'invoice_number' => $response['numeroNotaFiscal'] ?? null,
			'invoice_key' => $response['chaveNFe'] ?? null,
			'expire_at' => $prazoPostagem,
			'updated_at' => current_time( 'mysql' ),
			'created_at' => current_time( 'mysql' ),
		] );

		if ( ! $prepostId ) {
			Log::notice( "Erro ao salvar a pré-postagem no banco de dados." );
			return new \WP_Error( 'prepost_save_error', 'Erro ao salvar a pré-postagem no banco de dados.', [ 'status' => 400 ] );
		}

		$order->update_meta_data( '_infixs_correios_automatico_prepost_id', $prepostId );
		$order->save();

		Log::debug( 'Pré-postagem criada com sucesso.', [ 
			'prepost_id' => $prepostId,
			'order_id' => $order_id,
		] );

		return $prepost;
	}

	public function getPrepost( $prepostId ) {
		return $this->prepostRepository->find( $prepostId );
	}

}