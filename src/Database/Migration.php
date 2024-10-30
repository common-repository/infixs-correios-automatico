<?php

namespace Infixs\CorreiosAutomatico\Database;

use Infixs\WordpressEloquent\Database;

defined( 'ABSPATH' ) || exit;

/**
 * Migration class.
 *
 * @package Infixs\CorreiosAutomatico
 * @since   1.0.0
 */
class Migration {


	public static function run() {
		/**
		 * Create table `infixs_correios_automatico_tracking_codes`.
		 * 
		 * @since 1.0.0
		 */
		Database::createOrUpdateTable( 'infixs_correios_automatico_tracking_codes', [ 
			'order_id' => 'bigint(20) unsigned NOT NULL',
			'user_id' => 'bigint(20) unsigned DEFAULT NULL',
			'code' => 'varchar(255) DEFAULT NULL',
			'updated_at' => 'datetime NOT NULL',
			'created_at' => 'datetime NOT NULL',
		] );

		/**
		 * Create table `infixs_correios_automatico_preposts`.
		 * 
		 * @since 1.0.0
		 */
		Database::createOrUpdateTable( 'infixs_correios_automatico_preposts', [ 
			'external_id' => 'varchar(255) NOT NULL',
			'object_code' => 'varchar(255) DEFAULT NULL',
			"service_code" => "varchar(8) NOT NULL",
			"payment_type" => "tinyint(1) unsigned DEFAULT 2",
			"height" => "varchar(8) DEFAULT NULL",
			"width" => "varchar(8) DEFAULT NULL",
			"length" => "varchar(8) DEFAULT NULL",
			"weight" => "varchar(8) DEFAULT NULL",
			"request_pickup" => "tinyint(1) unsigned DEFAULT 0",
			"reverse_logistic" => "tinyint(1) unsigned DEFAULT 0",
			"status" => "tinyint(1) unsigned DEFAULT NULL",
			"status_label" => "varchar(255) NOT NULL",
			"invoice_number" => "varchar(255) DEFAULT NULL",
			"invoice_key" => "varchar(255) DEFAULT NULL",
			"expire_at" => "datetime DEFAULT NULL",
			'updated_at' => 'datetime NOT NULL',
			'created_at' => 'datetime NOT NULL',
		] );

		/**
		 * Create table `infixs_correios_automatico_postcodes`.
		 * 
		 * @since 1.0.0
		 */
		Database::createOrUpdateTable( 'infixs_correios_automatico_postcodes', [ 
			'postcode' => 'char(8) DEFAULT NULL',
			'address' => 'varchar(255) DEFAULT NULL',
			'city' => 'varchar(255) DEFAULT NULL',
			'neighborhood' => 'varchar(255) DEFAULT NULL',
			'state' => 'char(2) DEFAULT NULL',
			'created_at' => 'datetime NOT NULL',
		] );

	}
}