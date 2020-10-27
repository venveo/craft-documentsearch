<?php

namespace venveo\documentsearch\extractors;


use Spatie\PdfToText\Pdf;
use venveo\documentsearch\DocumentSearch as Plugin;

class PdfExtractor extends Extractor
{
    public static function extractText($filepath): string
    {
        return Pdf::getText($filepath, \Craft::parseEnv(Plugin::$plugin->getSettings()->pdfToTextExecutable));
    }
}