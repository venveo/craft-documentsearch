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
use craft\helpers\Db;
use venveo\documentsearch\DocumentSearch as Plugin;
use venveo\documentsearch\extractors\ExcelExtractor;
use venveo\documentsearch\extractors\Extractor;
use venveo\documentsearch\extractors\PdfExtractor;
use venveo\documentsearch\extractors\PowerpointExtractor;
use venveo\documentsearch\extractors\TextExtractor;
use venveo\documentsearch\extractors\WordExtractor;
use venveo\documentsearch\models\Settings;
use yii\db\Schema;

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
    public function getAssetContentKeywords(Asset $asset)
    {
        /** @var Settings $settings */
        $settings = Plugin::$plugin->getSettings();

        // check to make sure the volume is allowed to be indexed
        /** @var Volume $volume */
        $volume = $asset->getVolume();
        if (!$settings->indexAllVolumes && !in_array($volume->uid, $settings->indexVolumes, true)) {
            return null;
        }

        // make sure the asset size doesn't exceed our maximum
        if ($asset->size > $settings->maximumDocumentSize * 1024) {
            Craft::info('Skipping asset ('.$asset->id.') because it exceeds maximumDocumentSize ('.$settings->maximumDocumentSize.')', __METHOD__);
            return null;
        }
        $text = $this->extractAssetContent($asset);
        if (!$text) {
            Craft::info('No text found in '.$asset->id, __METHOD__);
            return null;
        }

        // Try to figure out what language the document is in
        $language = $asset->getSite()->language ?: 'en';
        $languageParts = explode('-', $language);
        $languageShort = strtolower(array_shift($languageParts));

        // If we can - let's just store the entire text
        $db = Craft::$app->getDb();
        if ($isPgsql = $db->getIsPgsql()) {
            $maxSize = Craft::$app->search->maxPostgresKeywordLength;
        } else {
            $maxSize = Db::getTextualColumnStorageCapacity(Schema::TYPE_TEXT);
        }
        if (mb_strlen($text) < $maxSize) {
            return $text;
        }

        $scoredKeywords_1 = Plugin::$plugin->rake->get($text, 1, $languageShort);
        $scoredKeywords_2 = Plugin::$plugin->rake->get($text, 2, $languageShort);
        $scoredKeywords_3 = Plugin::$plugin->rake->get($text, 3, $languageShort);
        $count = count($scoredKeywords_1) + count($scoredKeywords_2) + count($scoredKeywords_3);

        // If there are more than 100 keywords, let's just get the first third
        if ($count > 100) {
            $scoredKeywords = array_slice($scoredKeywords_1, 0, 30) +
                array_slice($scoredKeywords_2, 0, 30) +
                array_slice($scoredKeywords_3, 0, 30);
        } else {
            $scoredKeywords = $scoredKeywords_1 + $scoredKeywords_2 + $scoredKeywords_3;
        }

        // Assemble the keywords into a string
        $results = implode(' ', array_keys($scoredKeywords));
        Craft::info('Extracted '.count($scoredKeywords).' keywords from: '.$asset->id.' in '.$languageShort, __METHOD__);

        return $results;
    }

    /**
     * @param Asset $asset
     * @return string
     */
    protected function extractAssetContent(Asset $asset): string
    {
        /** @var Extractor $extractor */
        $extractor = null;
        switch ($asset->kind) {
            case Asset::KIND_PDF:
                $extractor = PdfExtractor::class;
                break;
            case Asset::KIND_EXCEL:
                $extractor = ExcelExtractor::class;
                break;
            case Asset::KIND_WORD:
                $extractor = WordExtractor::class;
                break;
            case Asset::KIND_POWERPOINT:
                $extractor = PowerpointExtractor::class;
                break;
            case Asset::KIND_TEXT:
                $extractor = TextExtractor::class;
                break;
            default:
                //No op;
                Craft::info('Document search cannot index ' . $asset->kind . '. : ' . $asset->getFilename(true),
                    __METHOD__);
        }
        $text = '';
        if (!empty($extractor)) {
            $filepath = $asset->getCopyOfFile();

            if ($extractor::canHandleFile($filepath)) {
                $text = $extractor::extractText($filepath);
            }
        }
        return $text;
    }
}
