<?php
namespace DataValues;

use DataValues\DecimalValue;

/**
 * @licence GNU GPL v2+
 * @author Andrius Merkys < andrius.merkys@gmail.com >
 */
class MatrixValue extends DataValueObject {

	/**
	 * @var array[]
	 */
	private $value;

	/**
	 * Constructs a new MatrixValue object, representing the given value.
	 *
	 * @var array[] $value 2-dimensional array of numbers (int|float|string|DecimalValue).
	 *
	 * @throws IllegalValueException
	 */
	public function __construct( $value ) {
		if ( !is_array( $value ) ) {
			throw new IllegalValueException( '$value must be an array' );
		}

		if ( count( $value ) == 0 ) {
			throw new IllegalValueException( '$value can not be an empty array' );
		}

		$value_now = array();

		$columns = null;
		foreach ( $value as $row ) {
			if ( !is_array( $row ) ) {
				throw new IllegalValueException( '$value must be a 2-dimensional array' );
			}
			if ( $columns == null ) {
				$columns = count( $row );
			} else if ( $columns != count( $row ) ) {
				throw new IllegalValueException( 'all rows of $value must be of the same length' );
			}

			/**
			 * Filtering the matrix, converting int|float|string into
			 * DecimalValue.
			 */
			$row_now = array();
			foreach ( $row as $element ) {
				if ( is_string( $element ) ) {
					// Adding sign if missing
					if ( !preg_match( '/^[+-]/', $element ) ) {
						$element = "+" . $element;
					}
					$element = new DecimalValue( $element );
				} else if ( !( $element instanceof DecimalValue ) ) {
					$element = new DecimalValue( $element );
				}
				$row_now[] = $element;
			}
			$value_now[] = $row_now;
		}

		if ( $columns == null || $columns == 0 ) {
			throw new IllegalValueException( 'matrix can not have empty rows' );
		}

		$this->value = $value_now;
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
	 * @param string $value
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
		return 'matrix';
	}

	/**
	 * @see DataValue::getSortKey
	 *
	 * @return int Always 0 in this implementation.
	 */
	public function getSortKey() {
		return 0;
	}

	/**
	 * Returns the object.
	 * @see DataValue::getValue
	 *
	 * @return MatrixValue
	 */
	public function getValue() {
		return $this;
	}

	/**
	 * Returns the array of arrays.
	 *
	 * @return array[] 2-dimensional array of DecimalValue.
	 */
	public function getMatrix() {
		return $this->value;
	}
}
