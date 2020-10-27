<?php
/*
 *  @link      https://www.venveo.com
 *  @copyright Copyright (c) 2020 Venveo
 */

namespace venveo\documentsearch\extractors;


class TextExtractor extends Extractor
{
    public static function extractText($filepath): string
    {
        return file_get_contents($filepath, false);
    }
}