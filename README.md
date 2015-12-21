# PHP Gettext Translation Editor
[![Build Status](https://travis-ci.org/Saeven/circlical-po-editor.svg?branch=master)](https://travis-ci.org/Saeven/circlical-po-editor)

PHP Parser/Editor for Gettext PO files

PoEditor is a class that allows you to load / parse / manipulate / compile .po files using PHP.  I built this to support a web-based PO file editor.

This repository is thoroughly tested, mosts tests were taken from [raulferras/PHP-po-parser](https://github.com/raulferras/PHP-po-parser), thank you!

It supports all of the same features, with a simpler and stable interface:
* headers (HeaderBlock)
* msgid (message id)
* msgstr (translation strings)
* msgctxt (context)
* msgid_plural (plural translations)
* # (flags, comments, source, references, annotations, etc.)

When you parse a po file, it transforms it into **blocks** that you can cleanly manipulate to thereafter compile.

## Usage
### Parsing Files

```php
$po = new Circlical\PoEditor( 'file.po' );
$po->parse();
```

### Editing Blocks

```php
$po = new Circlical\PoEditor( 'file.po' );
$po->parse();
$po->getBlock( 'welcome' )->setMsgstr( "hola" );
$po->compile();
```

That transforms **file.po** from:

    #: wp-admin/install.php:177
    msgid "welcome"
    msgstr "welcome"

to:

    #: wp-admin/install.php:177
    msgid "welcome"
    msgstr "hola"