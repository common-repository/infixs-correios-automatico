<?php

namespace Infixs\CorreiosAutomatico\Services\Correios\Enums;

defined( 'ABSPATH' ) || exit;

class APIServiceCode {
	const AGENCIA = 76;
	const ARE_ELETRONICO = 392;
	const ENDERECO_CEP_V3 = 41;
	const ERP_PAIS = 586;
	const FATURAS = 587;
	const MENSAGEM_DIGITAL_EXT = 83;
	const MENSAGENS_TELEMATICAS_REST = 426;
	const MEU_CONTRATO = 566;
	const PACKET = 80;
	const PMA_PRE_POSTAGEM = 36;
	const PRAZO = 35;
	const PRECO = 34;
	const PRO_JUS_CADASTRO = 37;
	const SRO_INTERATIVIDADE = 93;
	const SRO_RASTRO = 87;
	const TOKEN = 5;
	const WEBHOOK = 78;

	private static $descriptions = [ 
		self::AGENCIA => 'Serviço de Agência',
		self::ARE_ELETRONICO => 'ARE Eletrônico',
		self::ENDERECO_CEP_V3 => 'Endereço CEP V3',
		self::ERP_PAIS => 'ERP País',
		self::FATURAS => 'Faturas',
		self::MENSAGEM_DIGITAL_EXT => 'Mensagem Digital Ext',
		self::MENSAGENS_TELEMATICAS_REST => 'Mensagens Telemáticas REST',
		self::MEU_CONTRATO => 'Meu Contrato',
		self::PACKET => 'Packet',
		self::PMA_PRE_POSTAGEM => 'PMA Pré-Postagem',
		self::PRAZO => 'Prazo',
		self::PRECO => 'Preço',
		self::PRO_JUS_CADASTRO => 'Pro Jus Cadastro',
		self::SRO_INTERATIVIDADE => 'SRO Interatividade',
		self::SRO_RASTRO => 'SRO Rastro',
		self::TOKEN => 'Token',
		self::WEBHOOK => 'Webhook',
	];

	/**
	 * Get the description of the additional service.
	 * 
	 * @param string $item Additional service code.
	 * 
	 * @return string
	 */
	public static function getValue( $item ) {
		return self::$descriptions[ $item ] ?? 0;
	}

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