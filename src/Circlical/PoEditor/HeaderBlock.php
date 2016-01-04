<?php

namespace Circlical\PoEditor;

/**
 * Class Block
 * @package Circlical\PoEditor
 *
 * Like a block, but is first in line.  Has special accessors and compile rules.
 */
class HeaderBlock extends Block
{
	protected $header_specification = [];

	public function setValue( $key, $value ){
		$this->header_specification[$key] = $value;
	}

	public function process( $s )
	{
		if( $s[0] == '#' )
		{
			$this->comments[] = trim( $s );
			return;
		}
		else if( $s[0] != '"' )
		{
			return;
		}

		$s = trim( $s, '"' . "\n" );
		list( $car, $cdr ) = explode( ": ", $s, 2 );
		if( substr( $cdr, -2 ) == '\n' )
			$cdr = substr( $cdr, 0, -2 );

		$this->setValue($car,$cdr);
	}

	public function compile()
	{
		$str  = "";
	    if( $this->comments )
		    $str .= implode( self::NEWLINE, $this->comments ) . self::NEWLINE;

		$str .= 'msgid ""' . self::NEWLINE . 'msgstr ""' . self::NEWLINE;
		foreach( $this->header_specification as $k => $v )
			$str .= '"' . $k . ": " . $v . '\n"' . self::NEWLINE;

		return trim( $str );
	}

	public function getKey(){
		return "";
	}

}
