<?php

namespace Infixs\CorreiosAutomatico\Models;

use Infixs\WordpressEloquent\Model;

defined( 'ABSPATH' ) || exit;

/**
 * WoocommerceOrderItem model.
 *
 * @package Infixs\CorreiosAutomatico
 * @since   1.0.0
 */
class WoocommerceOrderItem extends Model {

	protected $primaryKey = 'order_item_id';
}
