<?php

namespace Infixs\CorreiosAutomatico\Services\Correios\Enums;

defined( 'ABSPATH' ) || exit;

class DeliveryServiceCode {
	const PAC = '04510';
	const SEDEX = '04014';
	const SEDEX_10 = '04790';
	const SEDEX_12 = '04782';
	const SEDEX_HOJE = '04804';
	const PAC_CONTRATO_AG = '03298';
	const SEDEX_CONTRATO_AG = '03220';
	const SEDEX_10_CONTRATO_AG = '03158';
	const SEDEX_12_CONTRATO_AG = '03140';
	const SEDEX_HOJE_CONTRATO_AG = '03204';
	const SEDEX_CONTRATO_GRANDE_FORMATO = '03212';
	const SEDEX_CONTRATO_PGTO_ENTREGA = '03271';
	const PAC_CONTRATO_PGTO_ENTREGA = '03310';
	const PAC_CONTRATO_GRANDE_FORMATO = '03328';
	const SEDEX_KIT = '03352';
	const SEDEX_KIT_ISENCAO = '04219';
	const SEDEX_HOJE_EMPRESARIAL = '03662';
	const CORREIOS_MINI_ENVIOS_CTR_AG = '04227';

	const IMPRESSO_NORMAL = '20010';

	const IMPRESSO_MODICO = '20192';

	private static $descriptions = [ 
		self::PAC => 'PAC (Sem Contrato)',
		self::PAC_CONTRATO_AG => 'PAC (Contrato Agência)',
		self::SEDEX => 'SEDEX (Sem Contrato)',
		self::SEDEX_CONTRATO_AG => 'SEDEX (Contrato Agência)',
		self::SEDEX_10 => 'SEDEX 10 (Sem Contrato)',
		self::SEDEX_10_CONTRATO_AG => 'SEDEX 10 (Contrato Agência)',
		self::SEDEX_12 => 'SEDEX 12 (Sem Contrato)',
		self::SEDEX_12_CONTRATO_AG => 'SEDEX 12 (Contrato Agência)',
		self::SEDEX_HOJE => 'SEDEX Hoje (Sem Contrato)',
		self::SEDEX_HOJE_CONTRATO_AG => 'SEDEX Hoje (Contrato Agência)',
		self::SEDEX_CONTRATO_GRANDE_FORMATO => 'SEDEX (Contrato Grande Formato)',
		self::SEDEX_CONTRATO_PGTO_ENTREGA => 'SEDEX (Contrato Pagamento na Entrega)',
		self::PAC_CONTRATO_PGTO_ENTREGA => 'PAC (Contrato Pagamento na Entrega)',
		self::PAC_CONTRATO_GRANDE_FORMATO => 'PAC (Contrato Grande Formato)',
		self::SEDEX_KIT => 'SEDEX KIT',
		self::SEDEX_KIT_ISENCAO => 'SEDEX KIT ISENÇÃO',
		self::SEDEX_HOJE_EMPRESARIAL => 'SEDEX HOJE EMPRESARIAL',
		self::CORREIOS_MINI_ENVIOS_CTR_AG => 'Correios Mini Envios (Contrato Agência)',
		self::IMPRESSO_NORMAL => 'Impresso Normal (Com ou sem Contrato)',
		self::IMPRESSO_MODICO => 'Impresso Módico (Com ou sem Contrato)',
	];

	/**
	 * Get description
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $item
	 * @param bool $with_code
	 * 
	 * @return string
	 */
	public static function getDescription( $item, $with_code = false ) {
		return $with_code ? constant( $item ) . ' - ' . self::$descriptions[ $item ] : self::$descriptions[ $item ];
	}

	/**
	 * Get all
	 * 
	 * @since 1.0.0
	 * 
	 * @return array
	 */
	public static function getAll() {
		$keys = array_keys( self::$descriptions );
		$values = array_map( function ($key, $value) {
			return "$key - $value";
		}, $keys, self::$descriptions );

		return array_combine( $keys, $values );
	}

	public static function getGroups() {
		return [ 
			'most_used' => [ 
				self::PAC,
				self::PAC_CONTRATO_AG,
				self::SEDEX,
				self::SEDEX_CONTRATO_AG,
				self::SEDEX_10,
				self::SEDEX_10_CONTRATO_AG,
				self::SEDEX_12,
				self::SEDEX_12_CONTRATO_AG,
				self::SEDEX_HOJE,
				self::SEDEX_HOJE_CONTRATO_AG,
			],
			'with_contract' => [

			],
			'without_contract' => [

			],
		];
	}

	/**
	 * Get dimension limits
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $service_code
	 * @param string $object_type
	 * 
	 * @return array|null
	 */
	public function getDimensionLimits( $service_code, $object_type = 'package' ) {
		switch ( $service_code ) {
			case $this::SEDEX:
			case $this::SEDEX_CONTRATO_AG:
				return [ 
					'min' => [ 'height' => 1, 'width' => 1, 'length' => 1, 'weight' => 0.3 ],
					'max' => [ 'height' => 100, 'width' => 100, 'length' => 100, 'weight' => 30 ],
				];
		}

		return null;
	}
}