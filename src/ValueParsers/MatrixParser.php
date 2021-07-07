<?php

namespace ValueParsers;

use DataValues\DecimalValue;
use DataValues\IllegalValueException;
use DataValues\MatrixValue;
use InvalidArgumentException;

/**
 * ValueParser that parses the string representation of a matrix.
 *
 * @licence GNU GPL v2+
 * @author Andrius Merkys < andrius.merkys@gmail.com >
 */
class MatrixParser extends StringValueParser {

	const FORMAT_NAME = 'matrix';

	/**
	 * @see StringValueParser::stringParse
	 *
	 * @param string $value
	 *
	 * @return MatrixValue
	 * @throws ParseException
	 */
	protected function stringParse( $value ) {
		if ( !is_string( $value ) ) {
			throw new InvalidArgumentException( '$value must be a string' );
		}

		$decoded = json_decode( $value );
		if ( $decoded == null ) {
			throw new ParseException( '$value must be correct JSON string' );
		}

		try {
			return new MatrixValue( $decoded );
		} catch ( IllegalValueException $ex ) {
			throw new ParseException( $ex->getMessage(), $value, self::FORMAT_NAME );
		}
	}
}
