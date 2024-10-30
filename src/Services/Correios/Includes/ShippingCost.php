<?php

namespace Infixs\CorreiosAutomatico\Services\Correios\Includes;

use Infixs\CorreiosAutomatico\Container;
use Infixs\CorreiosAutomatico\Services\Correios\Enums\AddicionalServiceCode;

defined( 'ABSPATH' ) || exit;

class ShippingCost {

	/**
	 * Own hands
	 * 
	 * @var bool
	 */
	private $own_hands = false;

	/**
	 * Receipt notice
	 * 
	 * @var bool
	 */
	private $receipt_notice = false;

	/**
	 * Product code
	 * 
	 * Use DeliveryServiceCode constants
	 * 
	 * @var string
	 */
	private $product_code;

	/**
	 * Origin postcode
	 * 
	 * @var string
	 */
	private $origin_postcode;

	/**
	 * Destination postcode
	 * 
	 * @var string
	 */
	private $destination_postcode;

	/**
	 * Package
	 * 
	 * @var Package
	 */
	private $package;

	/**
	 * Width in cm
	 * 
	 * @var int
	 */
	private $width;

	/**
	 * Height in cm
	 * 
	 * @var int
	 */
	private $height;

	/**
	 * Length in cm
	 * 
	 * @var int
	 */
	private $length;

	/**
	 * Weight in kg
	 * 
	 * @var float|int
	 */
	private $weight;

	/**
	 * Object type
	 * 
	 * @var string $object_type "package"|"label"
	 */
	private $object_type;

	public function __construct( $product_code, $origin_postcode, $destination_postcode ) {
		$this->product_code = $product_code;
		$this->origin_postcode = $origin_postcode;
		$this->destination_postcode = $destination_postcode;
	}

	public function getOwnHands() {
		return $this->own_hands;
	}

	/**
	 * Set Package
	 * 
	 * @param Package $package
	 * @return void
	 */
	public function setPackage( $package ) {
		$this->package = $package;
		$data = $package->get_data();
		$this->setHeight( $data['height'] );
		$this->setWidth( $data['width'] );
		$this->setLength( $data['length'] );
		$this->setWeight( $data['weight'] );
	}

	/**
	 * Set Own Hands
	 * 
	 * @since 1.0.0
	 * 
	 * @param bool $own_hands
	 * 
	 * @return void
	 */
	public function setOwnHands( $own_hands ) {
		$this->own_hands = $own_hands;
	}

	public function getReceiptNotice() {
		return $this->receipt_notice;
	}

	public function setReceiptNotice( $receipt_notice ) {
		$this->receipt_notice = $receipt_notice;
	}

	public function getProductCode() {
		return $this->product_code;
	}

	public function getOriginPostcode() {
		return $this->origin_postcode;
	}

	public function getDestinationPostcode() {
		return $this->destination_postcode;
	}

	/**
	 * Get the weight
	 * 
	 * @param string $unit  'g', 'kg', 'lbs', 'oz'.
	 * 
	 * @return float
	 */
	public function getWeight( $unit = 'kg' ) {
		return wc_get_weight( $this->weight, $unit );
	}

	public function setWeight( $weight ) {
		$this->weight = $weight;
	}

	public function getHeight() {
		return $this->height;
	}

	public function setHeight( $height ) {
		$this->height = $height;
	}

	public function getWidth() {
		return $this->width;
	}

	public function setWidth( $width ) {
		$this->width = $width;
	}

	public function getLength() {
		return $this->length;
	}

	public function setLength( $length ) {
		$this->length = $length;
	}
}