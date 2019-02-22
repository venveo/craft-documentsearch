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

use Craft;
use craft\base\Component;
use craft\base\Volume;
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
    /**
     * @param Asset $asset
     * @return string|null
     * @throws \yii\base\InvalidConfigException
     */
    public function getAssetContentKeywords(Asset $asset): ?string
    {
        // check to make sure the volume is allowed to be indexed
        /** @var Volume $volume */
        $volume = $asset->getVolume();
        if (!in_array($volume->id, Plugin::$plugin->getSettings()['indexVolumes'])){
            return null;
        }

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
            $results = implode(' ', array_slice(array_keys($scoredKeywords), 0, Plugin::$plugin->getSettings()->maximumKeywords - 1));
            Craft::info('Extracted '.count($scoredKeywords).' keywords from: '.$asset->id.' in '.$languageShort, __METHOD__);
        } else {
            return null;
        }
        return $results;
    }


    /**
     * Gets the textual content from a PDF
     * @param string $filepath
     * @return string
     */
    public function extractContentFromPDF($filepath): string
    {
        Craft::info('Extracting PDF content from: '. $filepath, __METHOD__);
        return Pdf::getText($filepath, Plugin::$plugin->getSettings()->pdfToTextExecutable);
    }
}
