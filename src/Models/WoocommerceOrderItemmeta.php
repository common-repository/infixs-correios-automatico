<?php

namespace Infixs\CorreiosAutomatico\Models;

use Infixs\WordpressEloquent\Model;

defined( 'ABSPATH' ) || exit;

/**
 * WoocommerceOrderItemmeta model.
 *
 * @package Infixs\CorreiosAutomatico
 * @since   1.0.0
 */
class WoocommerceOrderItemmeta extends Model {
	protected $table = 'woocommerce_order_itemmeta';

	protected $primaryKey = 'meta_id';
}
