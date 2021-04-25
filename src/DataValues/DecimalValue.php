<?php

namespace DataValues;

use InvalidArgumentException;
use LogicException;

/**
 * Class representing a decimal number with (nearly) arbitrary precision.
 *
 * For simple numeric values use @see NumberValue.
 *
 * The decimal notation for the value follows ISO 31-0, with some additional restrictions:
 * - the decimal separator is '.' (period). Comma is not used anywhere.
 * - no spacing or other separators are included for groups of digits.
 * - the first character in the string always gives the sign, either plus (+) or minus (-).
 * - scientific (exponential) notation is not used.
 * - the decimal point must not be the last character nor the fist character after the sign.
 * - no leading zeros, except one directly before the decimal point
 * - zero is always positive.
 *
 * These rules are enforced by @see QUANTITY_VALUE_PATTERN
 *
 * @since 0.1
 *
 * @license GPL-2.0-or-later
 * @author Daniel Kinzler
 */
class DecimalValue extends DataValueObject {

	/**
	 * The $value as a decimal string, in the format described in the class
	 * level documentation of @see DecimalValue, matching @see QUANTITY_VALUE_PATTERN.
	 *
	 * @var string
	 */
	private $value;

	/**
	 * Regular expression for matching decimal strings that conform to the format
	 * described in the class level documentation of @see DecimalValue.
	 */
	public const QUANTITY_VALUE_PATTERN = '/^[-+]([1-9]\d*|\d)(\.\d+)?\z/';

	/**
	 * Constructs a new DecimalValue object, representing the given value.
	 *
	 * @param string|int|float $value If given as a string, the value must match
	 *  QUANTITY_VALUE_PATTERN. The leading plus sign is optional.
	 *
	 * @throws InvalidArgumentException
	 */
	public function __construct( $value ) {
		if ( is_int( $value ) || is_float( $value ) ) {
			$value = $this->convertToDecimal( $value );
		} elseif ( !is_string( $value ) ) {
			throw new IllegalValueException( '$number must be a numeric string.' );
		}

		$value = trim( $value );
		if ( $value !== '' && $value[0] !== '-' && $value[0] !== '+' ) {
			$value = '+' . $value;
		}
		if ( strlen( $value ) > 127 ) {
			throw new IllegalValueException( 'Value must be at most 127 characters long.' );
		}
		if ( !preg_match( self::QUANTITY_VALUE_PATTERN, $value ) ) {
			throw new IllegalValueException( "\"$value\" is not a well formed decimal value" );
		}

		$this->value = $value;

		// make "negative" zero positive
		if ( $this->isZero() ) {
			$this->value = '+' . substr( $this->value, 1 );
		}
	}

	/**
	 * Converts the given number to decimal notation. The resulting string conforms to the
	 * rules described in the class level documentation of @see DecimalValue and matches
	 * @see DecimalValue::QUANTITY_VALUE_PATTERN.
	 *
	 * @param int|float $number
	 *
	 * @return string
	 * @throws InvalidArgumentException
	 */
	private function convertToDecimal( $number ) {
		if ( $number === NAN || abs( $number ) === INF ) {
			throw new InvalidArgumentException( '$number must not be NAN or INF.' );
		}

		/**
		 * The 16 digits after the decimal point are derived from PHP's "serialize_precision"
		 * default of 17 significant digits (including 1 digit before the decimal point). This
		 * ensures a full float-string-float roundtrip.
		 * @see http://php.net/manual/en/ini.core.php#ini.serialize-precision
		 */
		$decimal = sprintf( '%.16e', abs( $number ) );
		list( $base, $exponent ) = explode( 'e', $decimal );
		list( $before, $after ) = explode( '.', $base );

		// Fill with as many zeros as necessary, and move the decimal point
		if ( $exponent < 0 ) {
			$before = str_repeat( '0', -$exponent - strlen( $before ) + 1 ) . $before;
			$before = substr_replace( $before, '.', $exponent, 0 );
		} else {
			$pad = $exponent - strlen( $after );
			if ( $pad > 0 ) {
				$after .= str_repeat( '0', $pad );
			}
			// Always add the decimal point back, even if the exponent is 0
			$after = substr_replace( $after, '.', $exponent, 0 );
		}

		// Remove not needed ".0" or just "." from the end
		return ( $number < 0 ? '-' : '+' ) . $before . rtrim( rtrim( $after, '0' ), '.' );
	}

	/**
	 * Compares this DecimalValue to another DecimalValue.
	 *
	 * @param self $that
	 *
	 * @throws LogicException
	 * @return int +1 if $this > $that, 0 if $this == $that, -1 if $this < $that
	 */
	public function compare( self $that ) {
		if ( $this === $that ) {
			return 0;
		}

		$a = $this->value;
		$b = $that->value;

		if ( $a === $b ) {
			return 0;
		}

		if ( $a[0] === '+' && $b[0] === '-' ) {
			return 1;
		}

		if ( $a[0] === '-' && $b[0] === '+' ) {
			return -1;
		}

		// compare the integer parts
		$aInt = ltrim( $this->getIntegerPart(), '0' );
		$bInt = ltrim( $that->getIntegerPart(), '0' );

		$sense = $a[0] === '+' ? 1 : -1;

		// per precondition, there are no leading zeros, so the longer nummber is greater
		if ( strlen( $aInt ) > strlen( $bInt ) ) {
			return $sense;
		}

		if ( strlen( $aInt ) < strlen( $bInt ) ) {
			return -$sense;
		}

		// if both have equal length, compare alphanumerically
		$cmp = strcmp( $aInt, $bInt );
		if ( $cmp > 0 ) {
			return $sense;
		}

		if ( $cmp < 0 ) {
			return -$sense;
		}

		// compare fractional parts
		$aFract = rtrim( $this->getFractionalPart(), '0' );
		$bFract = rtrim( $that->getFractionalPart(), '0' );

		// the fractional part is left-aligned, so just check alphanumeric ordering
		$cmp = strcmp( $aFract, $bFract );
		return $cmp === 0 ? 0 : ( $cmp < 0 ? -$sense : $sense );
	}

	/**
	 * @see Serializable::serialize
	 *
	 * @return string
	 */
	public function serialize() {
		return serialize( $this->value );
	}

	/**
	 * @see Serializable::unserialize
	 *
	 * @param string $data
	 */
	public function unserialize( $data ) {
		$this->__construct( unserialize( $data ) );
	}

	/**
	 * @see DataValue::getType
	 *
	 * @return string
	 */
	public static function getType() {
		return 'decimal';
	}

	/**
	 * @deprecated Kept for compatibility with older DataValues versions.
	 * Do not use.
	 *
	 * @return float
	 */
	public function getSortKey() {
		return $this->getValueFloat();
	}

	/**
	 * Returns the value as a decimal string, using the format described in the class level
	 * documentation of @see DecimalValue and matching @see DecimalValue::QUANTITY_VALUE_PATTERN.
	 * In particular, the string always starts with a sign (either '+' or '-')
	 * and has no leading zeros (except immediately before the decimal point). The decimal point is
	 * optional, but must not be the last character. Trailing zeros are significant.
	 *
	 * @see DataValue::getValue
	 *
	 * @return string
	 */
	public function getValue() {
		return $this->value;
	}

	/**
	 * Returns the sign of the amount (+ or -).
	 *
	 * @return string "+" or "-".
	 */
	public function getSign() {
		return substr( $this->value, 0, 1 );
	}

	/**
	 * Determines whether this DecimalValue is zero.
	 *
	 * @return bool
	 */
	public function isZero() {
		return (bool)preg_match( '/^[-+]0+(\.0+)?$/', $this->value );
	}

	/**
	 * Returns a new DecimalValue that represents the complement of this DecimalValue.
	 * That is, it constructs a new DecimalValue with the same digits as this,
	 * but with the sign inverted.
	 *
	 * Note that if isZero() returns true, this method returns this
	 * DecimalValue itself (because zero is it's own complement).
	 *
	 * @return self
	 */
	public function computeComplement() {
		if ( $this->isZero() ) {
			return $this;
		}

		$sign = $this->getSign();
		$invertedSign = ( $sign === '+' ? '-' : '+' );

		$inverseDigits = $invertedSign . substr( $this->value, 1 );
		return new self( $inverseDigits );
	}

	/**
	 * Returns a new DecimalValue that represents the absolute (positive) value
	 * of this DecimalValue. That is, it constructs a new DecimalValue with the
	 * same digits as this, but with the positive sign.
	 *
	 * Note that if getSign() returns "+", this method returns this
	 * DecimalValue itself (because a positive value is its own absolute value).
	 *
	 * @return self
	 */
	public function computeAbsolute() {
		if ( $this->getSign() === '+' ) {
			return $this;
		}

		return $this->computeComplement();
	}

	/**
	 * Returns the integer part of the value, that is, the part before the decimal point,
	 * without the sign.
	 *
	 * @return string
	 */
	public function getIntegerPart() {
		$n = strpos( $this->value, '.' );

		if ( $n === false ) {
			$n = strlen( $this->value );
		}

		return substr( $this->value, 1, $n - 1 );
	}

	/**
	 * Returns the fractional part of the value, that is, the part after the decimal point,
	 * if any.
	 *
	 * @return string
	 */
	public function getFractionalPart() {
		$n = strpos( $this->value, '.' );

		if ( $n === false ) {
			return '';
		}

		return substr( $this->value, $n + 1 );
	}

	/**
	 * Returns a DecimalValue with the same digits as this one, but with any trailing zeros
	 * after the decimal point removed. If there are no trailing zeros after the decimal
	 * point, this method will return $this.
	 *
	 * @return self
	 */
	public function getTrimmed() {
		$trimmed = preg_replace( '/(\.\d+?)0+$/', '$1', $this->value );
		$trimmed = preg_replace( '/(?<=\d)\.0*$/', '', $trimmed );

		if ( $trimmed === $this->value ) {
			return $this;
		}

		return new self( $trimmed );
	}

	/**
	 * Returns the value held by this object, as a float.
	 * Equivalent to floatval( $this->getvalue() ).
	 *
	 * @return float
	 */
	public function getValueFloat() {
		return floatval( $this->value );
	}

	/**
	 * @see DataValue::getArrayValue
	 *
	 * @return string
	 */
	public function getArrayValue() {
		return $this->value;
	}

	/**
	 * Constructs a new instance from the provided data. Round-trips with @see getArrayValue.
	 *
	 * @deprecated since 0.8.3. Use the constructor instead, or in case of polymorphic calls,
	 * use @see DataValueDeserializer.
	 *
	 * param mixed $data
	 *
	 * @throws InvalidArgumentException 
	 * @return self
	 */
	public static function newFromArray( $data ) {
		return new static( $data );
	}

	/**
	 * @return string
	 */
	public function __toString() {
		return $this->value;
	}

}
