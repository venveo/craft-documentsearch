<?php
/*
 *  @link      https://www.venveo.com
 *  @copyright Copyright (c) 2020 Venveo
 */

namespace venveo\documentsearch\extractors;


interface ExtractorInterface
{
    public static function extractText($filepath): string;

    public static function canHandleFile($filepath): bool;
}