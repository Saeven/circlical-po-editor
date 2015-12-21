<?php

namespace Spec\Circlical\PoEditor;

use Circlical\PoEditor\Block;
use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BlockSpec extends ObjectBehavior
{
    function it_is_initializable()
    {
        $this->shouldHaveType('Circlical\PoEditor\Block');
    }

    /**
     * Test that it can read header blocks
     */
    function it_reads_headers()
    {
        foreach( file( getcwd() . '/tests/assets/block.header.po' ) as $line )
        {
            $this->process( $line );
        }
    }

    function it_understands_single_line_message_id()
    {
        foreach( file( getcwd() . '/tests/assets/block.singular.po' ) as $line )
            $this->process( $line );

        $this->msgid->shouldBeArray();
        $this->msgid->shouldHaveCount(1);
    }

    function it_compiles_single_line_message_id()
    {
        $fname = 'block.singular.po';
	    $dir = getcwd() . '/tests/assets/';
        $file = file( $dir . $fname );
        foreach( $file as $line )
            $this->process( $line );

	    $file_contents = trim( file_get_contents( $dir . $fname ));
	    $str = $this->compile();
		$str->shouldBeString();
		$str->shouldBeLike( $file_contents );
    }

    function it_understands_multi_line_message_id()
    {
        foreach( file( getcwd() . '/tests/assets/block.multiline.msgid.po' ) as $line )
            $this->process( $line );

        $this->msgid->shouldBeArray();
        $this->msgid->shouldHaveCount(3);
    }

	function it_compiles_multi_line_message_id()
    {
        $fname = 'block.multiline.msgid.po';
	    $dir = getcwd() . '/tests/assets/';
        $file = file( $dir . $fname );
        foreach( $file as $line )
            $this->process( $line );

	    $file_contents = trim( file_get_contents( $dir . $fname ));
	    $str = $this->compile();
		$str->shouldBeString();
		$str->shouldBeLike( $file_contents );
    }

    function it_understands_multi_line_msgstr()
    {
        /** @var Block $this */
        foreach( file( getcwd() . '/tests/assets/block.multiline.msgstr.po' ) as $line )
            $this->process( $line );

        $this->getMsgid()->shouldBeArray();
        $this->getMsgid()->shouldHaveCount(3);
        $this->getMsgstr()->shouldBeArray();
        $this->getMsgstr()->shouldHaveCount(4);
    }

	function it_compiles_multi_line_msgstr()
    {
        $fname = 'block.multiline.msgstr.po';
	    $dir = getcwd() . '/tests/assets/';
        $file = file( $dir . $fname );
        foreach( $file as $line )
            $this->process( $line );

	    $file_contents = trim( file_get_contents( $dir . $fname ));
	    $str = $this->compile();
		$str->shouldBeString();
		$str->shouldBeLike( $file_contents );
    }

    function it_reads_context_blocks()
    {
        /** @var Block $this */
        foreach( file( getcwd() . '/tests/assets/block.context.po' ) as $line )
            $this->process( $line );

        $c = $this->getMsgctxt();
	    $c->shouldBeString();
	    $c->shouldBeLike("Howdy");
    }

	function it_compiles_context_blocks()
    {
        $fname = 'block.context.po';
	    $dir = getcwd() . '/tests/assets/';
        $file = file( $dir . $fname );
        foreach( $file as $line )
            $this->process( $line );

	    $file_contents = trim( file_get_contents( $dir . $fname ));
	    $str = $this->compile();
		$str->shouldBeString();
		$str->shouldBeLike( $file_contents );
    }

	function it_reads_multiline_plural_blocks()
	{
		/** @var Block $this */
		$fname = 'block.multiline.plural.po';
	    $dir = getcwd() . '/tests/assets/';
        $file = file( $dir . $fname );
        foreach( $file as $line )
            $this->process( $line );
		$this->getMsgidPlural()->shouldBeArray();
		$this->getMsgidPlural()->shouldHaveCount(2);
	}

	function it_compiles_multiline_plural_blocks()
    {
        $fname = 'block.multiline.plural.po';
	    $dir = getcwd() . '/tests/assets/';
        $file = file( $dir . $fname );
        foreach( $file as $line )
            $this->process( $line );

	    $file_contents = trim( file_get_contents( $dir . $fname ));
	    $str = $this->compile();
		$str->shouldBeString();
		$str->shouldBeLike( $file_contents );
    }
}
