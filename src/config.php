<?php
/**
 * Document Search plugin for Craft CMS 3.x
 *
 * Extract the contents of text documents and add to Craft's search index
 *
 * @link      https://venveo.com
 * @copyright Copyright (c) 2019 Venveo
 */

/**
 * Document Search config.php
 *
 * This file exists only as a template for the Document Search settings.
 * It does nothing on its own.
 *
 * Don't edit this file, instead copy it to 'craft/config' as 'document-search.php'
 * and make your changes there to override default settings.
 *
 * Once copied to 'craft/config', this file will be multi-environment aware as
 * well, so you can have different settings groups for each environment, just as
 * you do for 'general.php'
 */

return [

    'pdfToTextExecutable' => '/usr/local/bin/pdftotext',
    'maximumKeywords' => 100,
    'maximumDocumentSize' => 1024 * 5

];
