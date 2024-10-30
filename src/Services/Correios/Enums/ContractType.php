<?php

namespace Infixs\CorreiosAutomatico\Services\Correios\Enums;

defined( 'ABSPATH' ) || exit;

class ContractType {
	const PJ = 'PJ';
	const PF = 'PF';

	public static function getDescription( $value ) {
		switch ( $value ) {
			case self::PJ:
				return 'Pessoa Jurídica (PJ)';
			case self::PF:
				return 'Pessoa Física (PF)';
			default:
				return 'Indefinido';
		}
	}
}