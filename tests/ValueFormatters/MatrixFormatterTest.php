<?php

namespace ValueFormatters\Test;

use DataValues\DecimalValue;
use DataValues\MatrixValue;
use ValueFormatters\FormatterOptions;
use ValueFormatters\MatrixFormatter;

/**
 * @covers ValueFormatters\MatrixFormatter
 *
 * @group ValueFormatters
 * @group DataValueExtensions
 *
 * @licence GNU GPL v2+
 * @author Andrius Merkys < andrius.merkys@gmail.com >
 */
class MatrixFormatterTest extends ValueFormatterTestBase {

	/**
	 * @deprecated since 0.2, just use getInstance.
	 */
	protected function getFormatterClass() {
		throw new \LogicException( 'Should not be called, use getInstance' );
	}

	/**
	 * @see ValueFormatterTestBase::getInstance
	 *
	 * @param FormatterOptions|null $options
	 *
	 * @return MatrixFormatter
	 */
	protected function getInstance( FormatterOptions $options = null ) {
		return new MatrixFormatter( $options );
	}

	/**
	 * @see ValueFormatterTestBase::validProvider
	 */
	public function validProvider() {
		$row1 = array( new DecimalValue( 1 ) );

		$valid = array(
			'[[1]]' => new MatrixValue( array( $row1 ) ),
			'[[1],[1]]' => new MatrixValue( array( $row1, $row1 ) ),
		);

		$argLists = array();

		foreach ( $valid as $output => $input ) {
			$argLists[] = array( $input, $output );
		}

		return $argLists;
	}
}
