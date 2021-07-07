<?php

namespace ValueFormatters;

use DataValues\MatrixValue;
use InvalidArgumentException;

/**
 * Formatter for matrices
 *
 * @licence GNU GPL v2+
 * @author Andrius Merkys < andrius.merkys@gmail.com >
 */
class MatrixFormatter extends ValueFormatterBase {

	/**
	 * @see ValueFormatter::format
	 *
	 * @param MatrixValue $value
	 *
	 * @throws InvalidArgumentException
	 * @return string Text
	 */
	public function format( $value ) {
		if ( !( $value instanceof MatrixValue ) ) {
			throw new InvalidArgumentException(
						'Data value type mismatch. Expected a MatrixValue.' );
		}

		$raw_rows = array();
		foreach( $value->getMatrix() as $row ) {
			$raw_row = array();
			foreach( $row as $element ) {
				$raw_row[] = preg_replace( '/^\+/', '', $element->getValue() );
			}
			$raw_rows[] = '[' . implode( ',', $raw_row ) . ']';
		}

		$formatted = '[' . implode( ',', $raw_rows ) . ']';

		return $formatted;
	}
}
