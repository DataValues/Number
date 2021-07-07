<?php
namespace DataValues\Tests;

use DataValues\DecimalValue;
use DataValues\MatrixValue;

/**
 * @covers DataValues\MatrixValue
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @licence GNU GPL v2+
 * @author Andrius Merkys < andrius.merkys@gmail.com >
 */
class MatrixValueTest extends DataValueTest {

	/**
	 * @see DataValueTest::getClass
	 *
	 * @return string
	 */
	public function getClass() {
		return 'DataValues\MatrixValue';
	}

	public function validConstructorArgumentsProvider() {
		$row1 = array( new DecimalValue( 1 ) );
		$row2 = array( new DecimalValue( 1 ), new DecimalValue( 2 ) );
		$argLists = array();
		$argLists[] = array( array( $row1 ) );
		$argLists[] = array( array( $row1, $row1 ) );
		$argLists[] = array( array( $row2, $row2 ) );
		$argLists[] = array( array( $row1, $row1, $row1 ) );
		$argLists[] = array( array( array( 1 ) ) );
		$argLists[] = array( array( array( 1, 2, 3 ) ) );
		$argLists[] = array( array( array( 1, 2, 3 ), array( 4, 5, 6 ) ) );
		return $argLists;
	}

	public function invalidConstructorArgumentsProvider() {
		$row1 = array( new DecimalValue( 1 ) );
		$row2 = array( new DecimalValue( 1 ), new DecimalValue( 2 ) );
		$argLists = array();
		$argLists[] = array( array() );
		$argLists[] = array( array( $row1, $row2 ) );
		$argLists[] = array( array( array( 1, 2 ), array( 3 ) ) );
		return $argLists;
	}
}
