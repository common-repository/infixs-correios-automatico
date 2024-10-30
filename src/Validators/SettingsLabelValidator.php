<?php

namespace Infixs\CorreiosAutomatico\Validators;

use Infixs\CorreiosAutomatico\Core\Support\Validator;

defined( 'ABSPATH' ) || exit;

class SettingsLabelValidator extends Validator {
	/**
	 * Get the validation rules.
	 * 
	 * @since 1.0.0
	 * 
	 * @return array
	 */
	public function rules() {
		//TODO: see show borders
		return [ 
			'show_border' => 'required',
			'font_size' => 'required|integer',
			'width' => 'required|integer',
			'line_height' => 'required|integer',
			'show_logo' => 'required',
			'show_recipient_form' => 'required',
			'show_sender_info' => 'required',
			'show_recipient_barcode' => 'required',
			'recipient_barcode_height' => 'required',
			'logo_width' => 'required|integer',
			'page_margin' => 'required|integer',
			'items_gap' => 'required|integer',
			'columns_length' => 'required|integer',
		];
	}
}