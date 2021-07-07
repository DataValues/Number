<?php

namespace ValueParsers\Test;

use DataValues\MatrixValue;
use ValueParsers\MatrixParser;

/**
 * @covers ValueParsers\MatrixParser
 *
 * @group DataValue
 * @group DataValueExtensions
 *
 * @licence GNU GPL v2+
 * @author Andrius Merkys < andrius.merkys@gmail.com >
 */
class MatrixParserTest extends StringValueParserTest {

	/**
	 * @deprecated since 0.3, just use getInstance.
	 */
	protected function getParserClass() {
		throw new \LogicException( 'Should not be called, use getInstance' );
	}

	/**
	 * @see ValueParserTestBase::getInstance
	 *
	 * @return MatrixParser
	 */
	protected function getInstance() {
		return new MatrixParser();
	}

	/**
	 * @see ValueParserTestBase::validInputProvider
	 */
	public function validInputProvider() {
		$valid = array(
			// amounts in various styles and forms
			'[[42]]' => new MatrixValue( array( array( 42 ) ) ),
			'[[1,3,5]]' => new MatrixValue( array( array( 1, 3, 5 ) ) ),
			'[["+1",-3.0,"-5.001"]]' => new MatrixValue( array( array( 1, "-3", "-5.001" ) ) ),
			'[[1,0,0], [0,-1,0], [0, 0, -1]]' =>
				new MatrixValue( array( array( 1, 0, 0 ), array( 0, -1, 0 ), array( 0, 0, -1 ) ) ),
		);

		$argLists = array();

		foreach ( $valid as $string => $expected ) {
			$argLists[] = array( $string, $expected );
		}

		return $argLists;
	}

	/**
	 * @see ValueParserTestBase::invalidInputProvider
	 */
	public function invalidInputProvider() {
		$argLists = parent::invalidInputProvider();

		$invalid = array(
			'[]',
			'[[]]',
			'[[+1]]', // invalid JSON
			'[[[1]]]',
			'[[--1]]',
			'[[+-5]]',
			'[["--1"]]',
			'[["+-5"]]',
			'[[[[42]]',
			'[[1]][[2]]',
			'[[1],[1,2]]',
			'[[1,2,3],[4,5,6],[7,8]]',
		);

		foreach ( $invalid as $value ) {
			$argLists[] = array( $value );
		}

		return $argLists;
	}
}
