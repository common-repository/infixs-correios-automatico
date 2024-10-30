<?php

namespace Infixs\CorreiosAutomatico\Utils;

defined( 'ABSPATH' ) || exit;
class Formatter {
	/**
	 * Format the document.
	 *
	 * @param string $document
	 * @return string
	 */
	public static function format_document( $document ) {
		$document = preg_replace( '/\D/', '', $document );

		if ( strlen( $document ) === 11 ) {
			return preg_replace( '/(\d{3})(\d{3})(\d{3})(\d{2})/', '$1.$2.$3-$4', $document );
		} elseif ( strlen( $document ) === 14 ) {
			return preg_replace( '/(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})/', '$1.$2.$3/$4-$5', $document );
		} else {
			return $document;
		}
	}

	public static function format_datetime( $datetime ) {
		return \DateTime::createFromFormat( 'Y-m-d H:i:s', $datetime )->format( 'd/m/Y H:i:s' );
	}
}
