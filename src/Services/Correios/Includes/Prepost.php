<?php

namespace Infixs\CorreiosAutomatico\Services\Correios\Includes;

use Infixs\CorreiosAutomatico\Container;
use Infixs\CorreiosAutomatico\Services\Correios\Enums\DeliveryServiceCode;
use Infixs\CorreiosAutomatico\Utils\Sanitizer;

defined( 'ABSPATH' ) || exit;
/**
 * Prepost class.
 * 
 * @since 1.0.0
 */
class Prepost {

	/**
	 * ID Correios
	 * 
	 * @since 1.0.0
	 * 
	 * @var Person
	 */
	private $id;

	/**
	 * Sender (Required)
	 * 
	 * @since 1.0.0
	 * 
	 * @var Person
	 */
	private $sender;

	/**
	 * Recipient (Required)
	 * 
	 * @since 1.0.0
	 * 
	 * @var Person
	 */
	private $recipient;

	/**
	 * Service code (Required)
	 * 
	 * use enum DeliveryServiceCode  - maxLength: 8
	 * 
	 * @since 1.0.0
	 * 
	 * @var string
	 */
	private $service_code;

	/**
	 * Object format code (Required)
	 * 
	 * Formats: 1 - Letter, 2 - Package; 3 - Cilindrical/Roll
	 * 
	 * @since 1.0.0
	 * 
	 * @var string
	 */
	private $object_format_code = 2;

	/**
	 * Confirm non prohibited object (Required)
	 * 
	 * @since 1.0.0
	 * 
	 * @var int
	 */
	private $confirm_non_prohibited = 1;


	/**
	 * Additional service
	 * 
	 * @since 1.0.0
	 * 
	 * @var array{
	 * 			array{
	 * 				code: string,
	 * 				declaredValue: string
	 * 			}
	 * }
	 */
	private $addicional_service = [];

	/**
	 * Sets the sender.
	 *
	 * @param array{
	 *		code: string,
	 * 		declaredValue: string
	 * } $service
	 * 
	 * @since 1.0.0
	 */

	/**
	 * Package 
	 * 
	 * @since 1.0.0
	 * 
	 * @var Package
	 */
	private $package;

	/**
	 * Payment type
	 * 
	 * Payment method: 1 - cash, 2 - to invoice, 3 - cash and to invoice
	 * 
	 * @since 1.0.0
	 * 
	 * @var int
	 */
	private $payment_type = 2;

	/**
	 * Height in cm
	 * 
	 * Max length 8 characters
	 * 
	 * @since 1.0.0
	 * 
	 * @var string
	 */
	private $height;

	/**
	 * Width in cm
	 * 
	 * Max length 8 characters
	 * 
	 * @since 1.0.0
	 * 
	 * @var string
	 */
	private $width;

	/**
	 * Length in cm
	 * 
	 * Max length 8 characters
	 * 
	 * @since 1.0.0
	 * 
	 * @var string
	 */
	private $length;

	/**
	 * Weight in grams
	 * 
	 * Max length 10 characters
	 * 
	 * @since 1.0.0
	 * 
	 * @var string
	 */
	private $weight;

	/**
	 * Content items
	 * 
	 * @since 1.0.0
	 * 
	 * @var array{
	 * 			array{
	 * 				content: string,
	 * 				quantity: string,
	 * 				total: string,
	 * 			}
	 * }
	 */
	private $content_items = [];

	private $invoice_number = '';

	/**
	 * Invoice key
	 * 
	 * Optional or length 44 characters
	 * 
	 * @since 1.0.0
	 * 
	 * @var string
	 */
	private $invoice_key = '';


	/**
	 * Constructor.
	 *
	 * @param string $id
	 * @param Person $sender
	 * @param Person $recipient
	 * @param string $service_code
	 * @param string $object_format_code
	 * 
	 * @since 1.0.0
	 */
	public function __construct( $id, $sender, $recipient, $service_code, $object_format_code = 2 ) {
		$this->id = $id;
		$this->sender = $sender;
		$this->recipient = $recipient;
		$this->service_code = $service_code;
		$this->object_format_code = $object_format_code;
	}

	/**
	 * Sets the package.
	 *
	 * @param Package $package
	 * 
	 * @since 1.0.0
	 */
	public function setPackage( $package ) {
		$this->package = $package;
		$data = $package->get_data();
		$this->height = $data['height'];
		$this->width = $data['width'];
		$this->length = $data['length'];
		$this->weight = wc_get_weight( $data['weight'], 'g' );

		foreach ( $package->get_contents() as $content ) {
			$this->content_items[] = [ 
				'content' => $content['data']->get_title(),
				'quantity' => strval( $content['quantity'] ),
				'total' => strval( $content['line_total'] )
			];
		}
	}

	public function addAdditionalService( $service ) {
		$this->addicional_service[] = [ 
			'code' => $service['code'],
			'declaredValue' => $service['declaredValue']
		];
	}


	public function getData() {
		$addicional_service = [];
		$content_items = [];

		foreach ( $this->addicional_service as $service ) {
			$addicional_service[] = [ 
				"codigoServicoAdicional" => $service['code'],
				"valorDeclarado" => $service['declaredValue']
			];
		}

		foreach ( $this->content_items as $content ) {
			$content_items[] = [ 
				"conteudo" => $content['content'],
				"quantidade" => $content['quantity'],
				"valor" => $content['total']
			];
		}

		return [ 
			"idCorreios" => $this->id,
			"remetente" => $this->sender->getData(),
			"destinatario" => $this->recipient->getData(),
			"codigoServico" => $this->service_code,
			"listaServicoAdicional" => $addicional_service,
			"cienteObjetoNaoProibido" => $this->confirm_non_prohibited,
			"codigoFormatoObjetoInformado" => $this->object_format_code,
			"modalidadePagamento" => $this->payment_type,
			"pesoInformado" => Sanitizer::integer_text( $this->weight ),
			"alturaInformada" => Sanitizer::integer_text( $this->height ),
			"larguraInformada" => Sanitizer::integer_text( $this->width ),
			"comprimentoInformado" => Sanitizer::integer_text( $this->length ),
			"logisticaReversa" => "N",
			"itensDeclaracaoConteudo" => $content_items,
			"solicitarColeta" => "N"
		];
	}

	public function setInvoiceNumber( $invoice_number ) {
		$this->invoice_number = $invoice_number;
	}

	public function setInvoiceKey( $invoice_key ) {
		$this->invoice_key = $invoice_key;
	}
}