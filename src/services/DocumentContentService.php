<?php
/**
 * Document Search plugin for Craft CMS 3.x
 *
 * Extract the contents of text documents and add to Craft's search index
 *
 * @link      https://venveo.com
 * @copyright Copyright (c) 2019 Venveo
 */

namespace venveo\documentsearch\services;

use craft\base\Component;
use craft\elements\Asset;
use Spatie\PdfToText\Pdf;
use venveo\documentsearch\DocumentSearch as Plugin;

/**
 * @author    Venveo
 * @package   DocumentSearch
 * @since     1.0.0
 */
class DocumentContentService extends Component
{
    public function getAssetContentKeywords(Asset $asset)
    {
        if ($asset->kind == Asset::KIND_PDF) {
            $text = $this->extractContentFromPDF($asset->getCopyOfFile());
        }


        // If we have text, let's extract the keywords from it
        if (isset($text)) {
            // Try to figure out what language the document is in
            $language = $asset->getSite()->language ?: 'en';
            $languageParts = explode('-', $language);
            $languageShort = strtolower(array_shift($languageParts));
            $scoredKeywords = Plugin::$plugin->rake->getKeywordScores($text, $languageShort);
            $results = implode(' ', array_keys($scoredKeywords));
        } else {
            return null;
        }
        return $results;
    }


    /**
     * Gets the textual content from a PDF
     * @param $filepath
     * @return string
     */
    public function extractContentFromPDF($filepath) {
        return Pdf::getText($filepath, Plugin::$plugin->getSettings()->pdfToTextExecutable);
    }
}
