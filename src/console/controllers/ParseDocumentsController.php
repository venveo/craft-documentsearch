<?php
/**
 * Document Search plugin for Craft CMS 3.x
 *
 * Extract the contents of text documents and add to Craft's search index
 *
 * @link      https://venveo.com
 * @copyright Copyright (c) 2019 Venveo
 */

namespace venveo\documentsearch\console\controllers;

use Craft;
use craft\elements\Asset;
use craft\elements\db\AssetQuery;
use venveo\documentsearch\DocumentSearch as Plugin;
use yii\console\Controller;
use yii\helpers\Console;

/**
 * ParseDocuments Command
 *
 * @author    Venveo
 * @package   DocumentSearch
 * @since     1.0.0
 */
class ParseDocumentsController extends Controller
{
    /**
     * Index all assets in enabled volumes
     *
     */
    public function actionIndexAll()
    {
        $volumes = Plugin::$plugin->getSettings()['indexVolumes'];

        /** @var Asset $asset */
        $volumeCount = 0;
        $errorCount = 0;
        foreach ($volumes as $volume) {
            $assetQuery = new AssetQuery(Asset::class);
            $assetQuery->volumeId = $volume;

            $assets = $assetQuery->all();
            Console::startProgress(0, count($assets));
            foreach ($assets as $i => $asset) {
                try {
                    Plugin::$plugin->documentContent->getAssetContentKeywords($asset);
                } catch (\Exception $e) {
//                    $this->stdout('Skipped a file - error: '. $asset->id . PHP_EOL);
                    Craft::warning('Skipped a file - error: '.$asset->id, 'document-search');
                    Craft::warning($e, 'document-search');
                    $errorCount++;
                }
                Console::updateProgress(++$i, count($assets));
            }
            Console::endProgress(true);
            $this->stdout('Finished volume '.++$volumeCount.'/'.count($volumes).PHP_EOL);
        }

        if ($errorCount > 0) {
            $this->stdout('Indexing completed with '.$errorCount.' errors.'.PHP_EOL);
        } else {
            $this->stdout('Indexing completed successfully.'.PHP_EOL);
        }
    }
}
