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
use venveo\documentsearch\models\Settings;
use voku\helper\StopWordsLanguageNotExists;

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
        /** @var Settings $settings */
        $settings = Plugin::$plugin->getSettings();

        // check to make sure the volume is allowed to be indexed
        /** @var Volume $volume */
        $volume = $asset->getVolume();
        if (!in_array($volume->id, $settings->indexVolumes, true)) {
            return null;
        }

        if ($asset->size > $settings->maximumDocumentSize) {
            Craft::info('Skipping asset ('.$asset->id.') because it exceeds maximumDocumentSize ('.$settings->maximumDocumentSize.')', __METHOD__);
            return null;
        }

        // We're only dealing with PDFs right now
        if ($asset->kind == Asset::KIND_PDF) {
            $text = $this->extractContentFromPDF($asset->getCopyOfFile());
        }

        // If we have text, let's extract the keywords from it
        if (isset($text)) {
            // Try to figure out what language the document is in
            $language = $asset->getSite()->language ?: 'en';
            $languageParts = explode('-', $language);
            $languageShort = strtolower(array_shift($languageParts));
            try {
                $scoredKeywords = Plugin::$plugin->rake->getKeywordScores($text, $languageShort);
            } catch (StopWordsLanguageNotExists $e) {
                // Just do it in english
                try {
                    $scoredKeywords = Plugin::$plugin->rake->getKeywordScores($text);
                    Craft::warning('Rake could not locate asset locale: '.$e->getMessage(), __METHOD__);
                } catch (StopWordsLanguageNotExists $e) {
                    Craft::error('Rake could not locate asset locale: '.$e->getMessage(), __METHOD__);
                    return null;
                }
            }

            // Assemble the keywords into a string
            $results = implode(' ', array_slice(array_keys($scoredKeywords), 0, $settings->maximumKeywords - 1));
            Craft::info('Extracted '.count($scoredKeywords).' keywords from: '.$asset->id.' in '.$languageShort, __METHOD__);
        } else {
            Craft::info('No text found in '.$asset->id, __METHOD__);
            return null;
        }
        return $results;
    }


    /**
     * Gets the textual content from a PDF
     *
     * @param string $filepath
     * @return string
     */
    public function extractContentFromPDF($filepath): string
    {
        Craft::info('Extracting PDF content from: '.$filepath, __METHOD__);
        // change directory to guarantee writable directory
        chdir(Craft::$app->path->getAssetsPath().DIRECTORY_SEPARATOR);
        return Pdf::getText($filepath, Plugin::$plugin->getSettings()->pdfToTextExecutable);
    }
}
