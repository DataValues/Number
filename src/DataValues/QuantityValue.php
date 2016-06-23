<?php

namespace DataValues;

use InvalidArgumentException;

/**
 * Class representing a quantity with associated unit and uncertainty interval.
 * The amount is stored as a @see DecimalValue object.
 *
 * For simple numeric amounts use @see NumberValue.
 *
 * @since 0.1
 *
 * @license GPL-2.0+
 * @author Daniel Kinzler
 * @author Thiemo Mättig
 */
class QuantityValue extends DataValueObject {

	/**
	 * The quantity's amount
	 *
	 * @var DecimalValue
	 */
	private $amount;

	/**
	 * The quantity's unit identifier (use "1" for unitless quantities).
	 *
	 * @var string
	 */
	private $unit;

	/**
	 * The quantity's upper bound
	 *
	 * @var DecimalValue|null
	 */
	private $upperBound;

	/**
	 * The quantity's lower bound
	 *
	 * @var DecimalValue|null
	 */
	private $lowerBound;

	/**
	 * Constructs a new QuantityValue object, representing the given value.
	 *
	 * @since 0.1
	 *
	 * @param DecimalValue $amount
	 * @param string $unit A unit identifier. Must not be empty, use "1" for unit-less quantities.
	 * @param DecimalValue|null $upperBound The upper bound of the quantity, inclusive.
	 * @param DecimalValue|null $lowerBound The lower bound of the quantity, inclusive.
	 *
	 * @throws IllegalValueException
	 */
	public function __construct(
		DecimalValue $amount,
		$unit,
		DecimalValue $upperBound = null,
		DecimalValue $lowerBound = null
	) {
		if ( is_null( $upperBound ) !== is_null( $lowerBound ) ) {
			throw new IllegalValueException( '$upperBound and $lowerBound must both be defined or both undefined' );
		}

		if ( $lowerBound && $lowerBound->compare( $amount ) > 0 ) {
			throw new IllegalValueException( '$lowerBound ' . $lowerBound->getValue() . ' must be <= $amount ' . $amount->getValue() );
		}

		if ( $upperBound && $upperBound->compare( $amount ) < 0 ) {
			throw new IllegalValueException( '$upperBound ' . $upperBound->getValue() . ' must be >= $amount ' . $amount->getValue() );
		}

		if ( !is_string( $unit ) ) {
			throw new IllegalValueException( '$unit needs to be a string, not ' . gettype( $unit ) );
		}

		if ( $unit === '' ) {
			throw new IllegalValueException( '$unit can not be an empty string (use "1" for unit-less quantities)' );
		}

		$this->amount = $amount;
		$this->unit = $unit;
		$this->upperBound = $upperBound;
		$this->lowerBound = $lowerBound;
	}

	/**
	 * Returns a QuantityValue representing the given amount.
	 * If no upper or lower bound is given, the amount is assumed to be absolutely exact,
	 * that is, the amount itself will be used as the upper and lower bound.
	 *
	 * This is a convenience wrapper around the constructor that accepts native values
	 * instead of DecimalValue objects.
	 *
	 * @note: if the amount or a bound is given as a string, the string must conform
	 * to the rules defined by @see DecimalValue.
	 *
	 * @since 0.1
	 *
	 * @param string|int|float|DecimalValue $amount
	 * @param string $unit A unit identifier. Must not be empty, use "1" for unit-less quantities.
	 * @param string|int|float|DecimalValue|null $upperBound
	 * @param string|int|float|DecimalValue|null $lowerBound
	 *
	 * @return self
	 * @throws IllegalValueException
	 */
	public static function newFromNumber( $amount, $unit = '1', $upperBound = null, $lowerBound = null ) {
		$amount = self::asDecimalValue( 'amount', $amount );

		if ( $upperBound !== null ) {
			$upperBound = self::asDecimalValue( 'upperBound', $upperBound );
		}

		if ( $lowerBound !== null ) {
			$lowerBound = self::asDecimalValue( 'lowerBound', $lowerBound );
		}

		return new self( $amount, $unit, $upperBound, $lowerBound );
	}

	/**
	 * @see newFromNumber
	 *
	 * @deprecated since 0.1, use newFromNumber instead
	 *
	 * @param string|int|float|DecimalValue $amount
	 * @param string $unit
	 * @param string|int|float|DecimalValue|null $upperBound
	 * @param string|int|float|DecimalValue|null $lowerBound
	 *
	 * @return self
	 */
	public static function newFromDecimal( $amount, $unit = '1', $upperBound = null, $lowerBound = null ) {
		return self::newFromNumber( $amount, $unit, $upperBound, $lowerBound );
	}

	/**
	 * Converts $number to a DecimalValue if possible and necessary.
	 *
	 * @note: if the $number is given as a string, it must conform to the rules
	 *        defined by @see DecimalValue.
	 *
	 * @param string $name The variable name to use in exception messages
	 * @param string|int|float|DecimalValue $number
	 *
	 * @throws IllegalValueException
	 * @throws InvalidArgumentException
	 * @return DecimalValue
	 */
	private static function asDecimalValue( $name, $number ) {
		if ( !is_string( $name ) ) {
			throw new InvalidArgumentException( '$name must be a string' );
		}

		if ( $number instanceof DecimalValue ) {
			// nothing to do
		} elseif ( is_int( $number ) || is_float( $number ) || is_string( $number ) ) {
			$number = new DecimalValue( $number );
		} else {
			throw new IllegalValueException( '$' . $name . '  must be a string, int, or float' );
		}

		return $number;
	}

	/**
	 * @see Serializable::serialize
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function serialize() {
		return serialize( array(
			$this->amount,
			$this->unit,
			$this->upperBound,
			$this->lowerBound,
		) );
	}

	/**
	 * @see Serializable::unserialize
	 *
	 * @since 0.1
	 *
	 * @param string $data
	 */
	public function unserialize( $data ) {
		list( $amount, $unit, $upperBound, $lowerBound ) = unserialize( $data );
		$this->__construct( $amount, $unit, $upperBound, $lowerBound );
	}

	/**
	 * @see DataValue::getType
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public static function getType() {
		return 'quantity';
	}

	/**
	 * @see DataValue::getSortKey
	 *
	 * @since 0.1
	 *
	 * @return float
	 */
	public function getSortKey() {
		return $this->amount->getValueFloat();
	}

	/**
	 * Returns the quantity object.
	 * @see DataValue::getValue
	 *
	 * @since 0.1
	 *
	 * @return self
	 */
	public function getValue() {
		return $this;
	}

	/**
	 * Returns the amount represented by this quantity.
	 *
	 * @since 0.1
	 *
	 * @return DecimalValue
	 */
	public function getAmount() {
		return $this->amount;
	}

	/**
	 * @since 0.8
	 *
	 * @return bool
	 */
	public function hasBounds() {
		return $this->upperBound !== null;
	}

	/**
	 * Returns this quantity's upper bound.
	 *
	 * @since 0.1
	 *
	 * @return DecimalValue|null
	 */
	public function getUpperBound() {
		return $this->upperBound;
	}

	/**
	 * Returns this quantity's lower bound.
	 *
	 * @since 0.1
	 *
	 * @return DecimalValue|null
	 */
	public function getLowerBound() {
		return $this->lowerBound;
	}

	/**
	 * Returns the size of the uncertainty interval.
	 * This can roughly be interpreted as "amount +/- uncertainty/2".
	 *
	 * The exact interpretation of the uncertainty interval is left to the concrete application or
	 * data point. For example, the uncertainty interval may be defined to be that part of a
	 * normal distribution that is required to cover the 95th percentile.
	 *
	 * @since 0.1
	 *
	 * @return float|null
	 */
	public function getUncertainty() {
		return $this->hasBounds()
			? $this->upperBound->getValueFloat() - $this->lowerBound->getValueFloat()
			: null;
	}

	/**
	 * Returns a DecimalValue representing the symmetrical offset to be applied
	 * to the raw amount for a rough representation of the uncertainty interval,
	 * as in "amount +/- offset".
	 *
	 * The offset is calculated as max( amount - lowerBound, upperBound - amount ).
	 *
	 * @since 0.1
	 *
	 * @return DecimalValue|null
	 */
	public function getUncertaintyMargin() {
		if ( !$this->hasBounds() ) {
			return null;
		}

		$math = new DecimalMath();

		$lowerMargin = $math->sum( $this->amount, $this->lowerBound->computeComplement() );
		$upperMargin = $math->sum( $this->upperBound, $this->amount->computeComplement() );

		$margin = $math->max( $lowerMargin, $upperMargin );
		return $margin;
	}

	/**
	 * Returns the order of magnitude of the uncertainty as the exponent of
	 * last significant digit in the amount-string. The value returned by this
	 * is suitable for use with @see DecimalMath::roundToExponent().
	 *
	 * @example: if two digits after the decimal point are significant, this
	 * returns -2.
	 *
	 * @example: if the last two digits before the decimal point are insignificant,
	 * this returns 2.
	 *
	 * Note that this calculation assumes a symmetric uncertainty interval,
	 * and can be misleading.
	 *
	 * @since 0.1
	 *
	 * @return int|null
	 */
	public function getOrderOfUncertainty() {
		if ( !$this->hasBounds() ) {
			return null;
		}

		// the desired precision is given by the distance between the amount and
		// whatever is closer, the upper or lower bound.
		//TODO: use DecimalMath to avoid floating point errors!
		$amount = $this->amount->getValueFloat();
		$upperBound = $this->upperBound->getValueFloat();
		$lowerBound = $this->lowerBound->getValueFloat();
		$precision = min( $amount - $lowerBound, $upperBound - $amount );

		if ( $precision === 0.0 ) {
			// If there is no uncertainty, the order of uncertainty is a bit more than what we have digits for.
			return -strlen( $this->amount->getFractionalPart() );
		}

		// e.g. +/- 200 -> 2; +/- 0.02 -> -2
		// note: we really want floor( log10( $precision ) ), but have to account for
		// small errors made in the floating point operations above.
		// @todo: use bcmath (via DecimalMath) to avoid this if possible
		$orderOfUncertainty = floor( log10( $precision + 0.0000000005 ) );

		return (int)$orderOfUncertainty;
	}

	/**
	 * Returns the number of significant figures in the amount-string,
	 * counting the decimal point, but not counting the leading sign.
	 *
	 * Note that this calculation assumes a symmetric uncertainty interval, and can be misleading
	 *
	 * @since 0.1
	 *
	 * @return int|null
	 */
	public function getSignificantFigures() {
		$math = new DecimalMath();

		// $orderOfUncertainty is +/- 200 -> 2; +/- 0.02 -> -2
		$orderOfUncertainty = $this->getOrderOfUncertainty();

		// the number of digits (without the sign) is the same as the position (with the sign).
		$significantDigits = $math->getPositionForExponent( $orderOfUncertainty, $this->amount );

		return $significantDigits;
	}

	/**
	 * Returns the unit held by this quantity.
	 * Unit-less quantities should use "1" as their unit.
	 *
	 * @since 0.1
	 *
	 * @return string
	 */
	public function getUnit() {
		return $this->unit;
	}

	/**
	 * Returns a transformed value derived from this QuantityValue by applying
	 * the given transformation to the amount and the upper and lower bounds.
	 * The resulting amount and bounds are rounded to the significant number of
	 * digits. Note that for exact quantities (with at least one bound equal to
	 * the amount), no rounding is applied (since they are considered to have
	 * infinite precision).
	 *
	 * The transformation is provided as a callback, which must implement a
	 * monotonously increasing, fully differentiable function mapping a DecimalValue
	 * to a DecimalValue. Typically, it will be a linear transformation applying a
	 * factor and an offset.
	 *
	 * @param string $newUnit The unit of the transformed quantity.
	 *
	 * @param callable $transformation A callback that implements the desired transformation.
	 *        The transformation will be called three times, once for the amount, once
	 *        for the lower bound, and once for the upper bound. It must return a DecimalValue.
	 *        The first parameter passed to $transformation is the DecimalValue to transform
	 *        In addition, any extra parameters passed to transform() will be passed through
	 *        to the transformation callback.
	 *
	 * @param mixed ... Any extra parameters will be passed to the $transformation function.
	 *
	 * @throws InvalidArgumentException
	 * @return self
	 */
	public function transform( $newUnit, $transformation ) {
		if ( !is_callable( $transformation ) ) {
			throw new InvalidArgumentException( '$transformation must be callable.' );
		}

		if ( !is_string( $newUnit ) ) {
			throw new InvalidArgumentException( '$newUnit must be a string. Use "1" as the unit for unit-less quantities.' );
		}

		if ( $newUnit === '' ) {
			throw new InvalidArgumentException( '$newUnit must not be empty. Use "1" as the unit for unit-less quantities.' );
		}

		$oldUnit = $this->unit;

		if ( $newUnit === null ) {
			$newUnit = $oldUnit;
		}

		// Apply transformation by calling the $transform callback.
		// The first argument for the callback is the DataValue to transform. In addition,
		// any extra arguments given for transform() are passed through.
		$args = func_get_args();
		array_shift( $args );

		$args[0] = $this->amount;
		$amount = call_user_func_array( $transformation, $args );

		$upperBound = null;
		$lowerBound = null;

		if ( $this->hasBounds() ) {
			$args[0] = $this->upperBound;
			$upperBound = call_user_func_array( $transformation, $args );

			$args[0] = $this->lowerBound;
			$lowerBound = call_user_func_array( $transformation, $args );
		}

		// use a preliminary QuantityValue to determine the significant number of digits
		$transformed = new self( $amount, $newUnit, $upperBound, $lowerBound );
		$roundingExponent = $transformed->getOrderOfUncertainty();

		// apply rounding to the significant digits
		$math = new DecimalMath();

		$amount = $math->roundToExponent( $amount, $roundingExponent );

		if ( $this->hasBounds() ) {
			$upperBound = $math->roundToExponent( $upperBound, $roundingExponent );
			$lowerBound = $math->roundToExponent( $lowerBound, $roundingExponent );
		}

		return new self( $amount, $newUnit, $upperBound, $lowerBound );
	}

	public function __toString() {
		return $this->amount->getValue()
			. ( $this->hasBounds()
				? '[' . $this->lowerBound->getValue() . '..' . $this->upperBound->getValue() . ']'
				: '' )
			. ( $this->unit === '1' ? '' : $this->unit );
	}

	/**
	 * @see DataValue::getArrayValue
	 *
	 * @since 0.1
	 *
	 * @return string[]
	 */
	public function getArrayValue() {
		$array = array(
			'amount' => $this->amount->getArrayValue(),
			'unit' => $this->unit,
		);

		if ( $this->hasBounds() ) {
			$array['upperBound'] = $this->upperBound->getArrayValue();
			$array['lowerBound'] = $this->lowerBound->getArrayValue();
		}

		return $array;
	}

	/**
	 * Constructs a new instance of the DataValue from the provided data.
	 * This can round-trip with @see getArrayValue
	 *
	 * @since 0.1
	 *
	 * @param mixed $data
	 *
	 * @return self
	 * @throws IllegalValueException
	 */
	public static function newFromArray( $data ) {
		self::requireArrayFields( $data, array( 'amount', 'unit' ) );

		return new static(
			DecimalValue::newFromArray( $data['amount'] ),
			$data['unit'],
			isset( $data['upperBound'] ) ? DecimalValue::newFromArray( $data['upperBound'] ) : null,
			isset( $data['lowerBound'] ) ? DecimalValue::newFromArray( $data['lowerBound'] ) : null
		);
	}

	/**
	 * @see Comparable::equals
	 *
	 * @since 0.1
	 *
	 * @param mixed $target
	 *
	 * @return bool
	 */
	public function equals( $target ) {
		if ( $this === $target ) {
			return true;
		}

		return $target instanceof self
			&& $this->toArray() === $target->toArray();
	}

}
