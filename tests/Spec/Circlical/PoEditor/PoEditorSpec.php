<?php

namespace Spec\Circlical\PoEditor;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;
use Circlical\PoEditor\PoEditor;
use Circlical\PoEditor\Block;

class PoEditorSpec extends ObjectBehavior
{

    function it_is_initializable()
    {
        $this->shouldHaveType('Circlical\PoEditor\PoEditor');
    }

    function it_reads_files_into_structures()
    {
        $this->setSourceFile( getcwd() . '/tests/assets/context.po' );
        $this->parse();
        $this->getBlocks()->shouldHaveCount( 10 );
    }

    function it_reads_phpformat_flags()
    {
        /** @var PoEditor $this */
        $this->setSourceFile( getcwd() . '/tests/assets/flags-phpformat.po' );
        $this->parse();
        $this->getBlocks()->shouldHaveCount( 2 );
        $block = $this->getBlock("Attachment", "Background Attachment");
        $block->shouldBeAnInstanceOf(Block::class);
        $comments = $block->getComments();
        $comments->shouldBeArray();
        $comments->shouldHaveCount(2);
    }

    private function compile_test( $source )
    {
        $file = getcwd() . '/tests/assets/' . $source;
        $this->setSourceFile( $file );
        $this->parse();
        $str = $this->compile();
        $str->shouldBeString();

        $orig = file_get_contents( $file );
        $str->shouldBeEqualTo( $orig );
    }

    private function compile_test_with_output( $source )
    {
        $file = getcwd() . '/tests/assets/' . $source;
        $this->setSourceFile( $file );
        $this->parse();
        $str = $this->compile();
        $str->shouldBeString();

        $orig = file_get_contents( $file );

        file_put_contents( getcwd() . '/compiled.txt', $str->getWrappedObject() );
        file_put_contents( getcwd() . '/orig.txt', $orig );

        $str->shouldBeEqualTo( $orig );
    }

    function it_compiles()
    {
        $this->compile_test( 'healthy.po' );
    }

    function it_compiles_context()
    {
        $this->compile_test( 'context.po' );
    }

    function it_compiles_flags_in_phpformat()
    {
        $this->compile_test( 'flags-phpformat.po' );
    }

    function it_compiles_flags_in_fuzzy_phpformat()
    {
        $this->compile_test( 'flags-phpformat-fuzzy.po' );
    }

    function it_compiles_multiflags()
    {
        $this->compile_test( 'multiflags.po' );
    }

    function it_compiles_multilines()
    {
        $this->compile_test( 'multiflags.po' );
    }

    function it_compiles_no_header()
    {
        $this->compile_test( 'noheader.po' );
    }

    function it_compiles_plurals()
    {
        $this->compile_test( 'plurals.po' );
    }

    function it_compiles_multiline_plurals()
    {
        $this->compile_test( 'pluralsMultiline.po' );
    }

    function it_compiles_old_annotations()
    {
        $this->compile_test( 'previous_untranslated.po' );
    }

    function it_changes_msgstr()
    {
        /** @var PoEditor $this */
        $after = file_get_contents( getcwd() . '/tests/assets/before_and_after/a.b.po' );
        $this->setSourceFile( getcwd() . '/tests/assets/before_and_after/a.a.po' );
        $this->parse();

        $block = $this->getBlock( 'Welcome', 'Howdy' );
        $block->setMsgstr( "Bonjour" );
        $block_compile = $block->compile();
        $block_compile->shouldBeString();
        $block_compile->shouldBeLike( trim( $after ) );

        $po_compile = $this->compile();
        $po_compile->shouldBeLike( $after );
    }

    function it_changes_comments()
    {
        /** @var PoEditor $this */
        $after = file_get_contents( getcwd() . '/tests/assets/before_and_after/a.c.po' );
        $this->setSourceFile( getcwd() . '/tests/assets/before_and_after/a.a.po' );
        $this->parse();

        $block = $this->getBlock( 'Welcome', 'Howdy' );
        $block->setComments([ "#: This comment comes first", "#: This comment comes second"]);
        $po_compile = $this->compile();
        $po_compile->shouldBeLike( $after );
    }

    function it_morphs_msgid()
    {
        /** @var PoEditor $this */
        $after = file_get_contents( getcwd() . '/tests/assets/before_and_after/a.d.po' );
        $this->setSourceFile( getcwd() . '/tests/assets/before_and_after/a.a.po' );
        $this->parse();

        $block = $this->getBlock( 'Welcome', 'Howdy' );
        $this->removeBlock( $block );

        $block->setMsgid("Choop");
        $this->addBlock( $block );

        $po_compile = $this->compile();
        $po_compile->shouldBeLike( $after );
    }

    function it_works_with_autogen()
    {
        $this->compile_test( 'autogen.po' );
    }

    function it_can_deal_with_evil_html()
    {
        $this->compile_test( 'evil_html.po' );
    }

    function it_preserves_double_quotes()
    {
        $this->compile_test( 'quotes.po' );
    }

    function it_ignores_duplicates()
    {
        $file = getcwd() . '/tests/assets/duplicates.po';
        $this->setSourceFile( $file );
        $this->parse();

        /** @var PoEditor $this */
        $this->getBlocks()->shouldHaveCount(3);
    }
}
