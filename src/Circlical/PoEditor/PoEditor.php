<?php

namespace Circlical\PoEditor;

class PoEditor
{

	/**
	 * The source .po file that this editor is manipulating
	 * @var string
	 */
	private $source_file;

	/**
	 * @var Block[]
	 */
	private $blocks;

	/**
	 * Return all parsed blocks
	 * @return array|Block[]
	 */
	public function getBlocks(){
		return $this->blocks;
	}

	/**
	 * Remove a block from the list
	 * @param $spec
	 */
	public function removeBlock( $spec ){
		if( $spec instanceof Block )
			$spec = $spec->getKey();

		if( is_string( $spec ) )
		{
			if( $this->blocks[$spec] )
			{
				unset($this->blocks[$spec]);
			}
		}
	}

	/**
	 * @param Block $block
	 */
	public function addBlock( Block $block )
	{
		$this->blocks[$block->getKey()] = $block;
	}

	/**
	 * Get a block that has the same key as the block you pass in
	 * @param Block $block
	 * @return Block
	 */
	public function getBlockLike( Block $block )
	{
		return $this->blocks[$block->getKey()] ?: null;
	}

	/**
	 * Fetch a block using a compiled key
	 * @param $key
	 * @return Block
	 */
	public function getBlockWithKey( $key ){
		return $this->blocks[$key] ?: null;
	}


	/**
	 * @param $msgid
	 * @param null $context
	 * @return Block
	 */
	public function getBlock( $msgid, $context = null )
	{
		if( is_array( $msgid ) )
			$msgid = implode( " ", $msgid );

		$key = json_encode([ 'context' => $context, 'id' => $msgid ]);
		return $this->getBlockWithKey( $key );
	}

	public function __construct( $source_file = null )
	{
		$this->source_file = $source_file;
		$this->blocks = [];
	}

	/**
	 * Parse a file into its individual subparts
	 */
	public function parse()
	{
		$handle = fopen($this->source_file, 'r');
		$currentBlock = null;

		// first, discover if we are header-less
		while( !feof($handle) )
		{
			$line = fgets( $handle );
			if( preg_match("/^msgid (.*?)$/us", $line, $match) )
			{
				// initialize the parser, rewind and break
				$currentBlock = $match[1] == '""' ? new HeaderBlock() : new Block();
				rewind( $handle );
				break;
			}
		}

		// run the actual parser
		while (!feof($handle))
		{
		    $line = fgets($handle);
		    if (trim($line) == '')
		    {
		        if ($currentBlock)
		        {
		            $this->addBlock($currentBlock);
		            $currentBlock = new Block();
		        }
		    }
		    else
		    {
		        $currentBlock->process( $line );
		    }
		}
		fclose($handle);

		if ($currentBlock && $currentBlock->isInitialized())
		{
			$this->addBlock($currentBlock);
		}
	}


	/**
	 * Take all parsed blocks, and compile their final guise.  Essentially returns the contents of a .po file.
	 * @return string
	 */
	public function compile()
	{
		$compiled_blocks = [];
		foreach( $this->blocks as $key => $block )
			$compiled_blocks[] = $block->compile();
		return implode( "\n\n", $compiled_blocks ) . "\n";
	}


	/**
	 * @return null
	 */
	public function getSourceFile()
	{
		return $this->source_file;
	}


	public function getKeys(){
		return array_values( $this->blocks );
	}

	/**
	 * @param null $source_file
	 */
	public function setSourceFile( $source_file )
	{
		$this->source_file = $source_file;
	}
}
