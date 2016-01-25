<?php

namespace Circlical\PoEditor;

/**
 * Class Block
 * @package Circlical\PoEditor
 *
 * This class defines a "part" of a PO file, inclusive of comments, plurals, ids, etc.  A formatting guide can be
 * found here https://www.gnu.org/software/gettext/manual/html_node/PO-Files.html
 */
class Block
{

	const NEWLINE = "\n";

	public $msgid = [];

	public $msgid_plural = [];

	public $msgstr = [];

	public $msgstr_plural = [];

	public $msgctxt = null;

	public $comments = [];

	private $last_processed;

	/**
	 * @var bool
	 */
	protected $initialized = false;


	public function process( $s )
	{
		$this->is_initialized = true;

		$s = trim($s);

		// headers are first in the po file, set externally
		if( $s[0] == '"' )
		{
			if( $this->last_processed )
				$this->processLine( $this->last_processed, $s );
		}
		// comments are noted by #
		else if( $s[0] == '#' )
		{
			$this->comments[] = $s;
		}
		// otherwise, process the string within the block
		else
		{
			list( $car, $cdr ) = explode( ' ', $s, 2 );
			$this->processLine( $car, $cdr );
		}
	}

	public function processLine( $car, $cdr )
	{
		// remove at most one double-quote from a string
		$clean_string = preg_replace('/^("(.*)")$/', '$2$3', $cdr );

		if( preg_match("/^msgstr\\[(\\d)\\]$/us", $car, $matches ) )
		{
			$this->msgstr_plural[$matches[1]][] = $clean_string;
		}
		else
		{
			switch( $car )
			{
				case 'msgctxt':
					$this->msgctxt = $clean_string;
					break;
				case 'msgid':
				case 'msgstr':
				case 'msgid_plural':
					array_push( $this->$car, $clean_string );
					break;
			}
		}
		$this->last_processed = $car;
	}


	/**
	 * Take all the parsed block parts and flatten them into a gettext-ready string; the part in the PO file
	 * that this block represents.
	 *
	 * @return string
	 */
	public function compile()
    {
	    // can happen if it parses only comments/artifacts
	    if( !count( $this->msgid ) )
		    return "";

		$str  = "";
	    if( $this->comments )
		    $str .= implode( self::NEWLINE, $this->comments ) . self::NEWLINE;

	    if( $this->msgctxt )
		    $str .= 'msgctxt "' . $this->msgctxt . '"' . self::NEWLINE;

	    $included_blocks = [ 'msgid' ];
	    if( $this->msgstr_plural )
		    $included_blocks[] = 'msgid_plural';
	    else
		    $included_blocks[] = 'msgstr';

	    foreach( $included_blocks as $key )
	    {
		    if( is_array( $this->$key ) )
		    {
			    $str .= "$key ";
			    $str .= implode( self::NEWLINE, array_map( [$this, 'quoteWrap'], $this->$key ) ) . self::NEWLINE;
		    }
	    }

	    if( $this->msgid_plural && $this->msgstr_plural )
	    {
		    foreach( $this->msgstr_plural as $plural_key => $plural_message )
		    {
				$str .= 'msgstr[' . $plural_key . '] ';
			    $str .= implode( self::NEWLINE, array_map( [$this, 'quoteWrap'], $plural_message ) ) . self::NEWLINE;
		    }
	    }

	    return trim( $str );
    }

	private function quoteWrap( $str ){
		return '"' . $str . '"';
	}

	/**
	 * @return array
	 */
	public function getMsgid()
	{
		return $this->msgid;
	}

	/**
	 * @param array $msgid
	 */
	public function setMsgid( $msgid )
	{
		if( !is_array( $msgid ) )
			$msgid = [ $msgid ];

		$this->msgid = $msgid;
	}

	/**
	 * @return array
	 */
	public function getMsgidPlural()
	{
		return $this->msgid_plural;
	}

	/**
	 * @param array $msgid_plural
	 */
	public function setMsgidPlural( $msgid_plural )
	{
		if( !is_array( $msgid_plural ) )
			$msgid_plural = [ $msgid_plural ];

		$this->msgid_plural = $msgid_plural;
	}

	/**
	 * @return array
	 */
	public function getMsgstr()
	{
		return $this->msgstr;
	}

	/**
	 * @param array $msgstr
	 */
	public function setMsgstr( $msgstr )
	{
		if( !is_array( $msgstr ) )
			$msgstr = [ $msgstr ];

		$this->msgstr = $msgstr;
	}

	/**
	 * @return mixed
	 */
	public function getMsgctxt()
	{
		return $this->msgctxt;
	}

	/**
	 * @param mixed $msgctxt
	 */
	public function setMsgctxt( $msgctxt )
	{
		$this->msgctxt = $msgctxt;
	}

	/**
	 * @return array
	 */
	public function getMsgstrPlural()
	{
		return $this->msgstr_plural;
	}

	/**
	 * @param array $msgstr_plural
	 */
	public function setMsgstrPlural( $msgstr_plural )
	{
		$this->msgstr_plural = $msgstr_plural;
	}


	/**
	 * @return array
	 */
	public function getPluralForm( $key )
	{
		return $this->msgstr_plural[$key] ?: "";
	}

	/**
	 * Sets a particular plural message key
	 * @param int $key The plural form being set
	 * @param string|array $plural The plural string
	 */
	public function setPluralForm( $key, $plural )
	{
		if( !is_array( $plural ) )
			$plural = [ $plural ];

		if( !$this->msgstr_plural )
			$this->msgstr_plural = [];

		$this->msgstr_plural[$key] = $plural;
	}

	/**
	 * @return array
	 */
	public function getComments()
	{
		return $this->comments;
	}

	/**
	 * @param array $comment
	 */
	public function setComments( $comment )
	{
		if( !is_array( $comment ) )
			$comment = [ $comment ];

		$this->comments = $comment;
	}

	/**
	 * @return boolean
	 */
	public function isInitialized()
	{
		return $this->initialized;
	}

	/**
	 * @param boolean $initialized
	 */
	public function setInitialized( $initialized )
	{
		$this->initialized = $initialized;
	}

	/**
	 * Return a compiled key that is a function of context (msgctxt) and id (msgid)
	 * @return string
	 */
	public function getKey()
	{
		return json_encode([ 'context' => $this->msgctxt, 'id' => implode( " ", $this->getMsgid()) ]);
	}

}