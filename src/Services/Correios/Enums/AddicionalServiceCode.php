<?php

namespace Infixs\CorreiosAutomatico\Services\Correios\Enums;

defined( 'ABSPATH' ) || exit;

class AddicionalServiceCode {
	const RECEIPT_NOTICE = '001';
	const OWN_HANDS = '002';

	private static $descriptions = [ 
		self::RECEIPT_NOTICE => 'Aviso de Recebimento',
		self::OWN_HANDS => 'Mão Própria',
	];

	/**
	 * Get the description of the additional service.
	 * 
	 * @param string $item Additional service code.
	 * 
	 * @return string
	 */
	public static function getDescription( $item ) {
		return self::$descriptions[ $item ] ?? 'Serviço desconhecido';
	}
}