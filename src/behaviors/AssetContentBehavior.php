<?php
namespace venveo\documentsearch\behaviors;

use craft\elements\Asset;
use craft\web\Session;
use craft\web\View;
use venveo\documentsearch\DocumentSearch;
use yii\base\Behavior;
use yii\base\Exception;
use yii\web\AssetBundle;
use Spatie\PdfToText\Pdf;
use craft\helpers\Search as SearchHelper;

/**
 * Extends \yii\web\Session to add support for setting the session folder and creating it if it doesn’t exist.
 *
 * @property Session $owner
 * @author Pixel & Tonic, Inc. <support@pixelandtonic.com>
 * @since 3.0
 */
class AssetContentBehavior extends Behavior
{
//    public $assetKeywords;
    // Properties
    // =========================================================================

    public function getContentKeywords() {
        /** @var Asset $asset */
        $asset = $this->owner;
        if ($asset->kind == 'pdf') {
            $text = Pdf::getText($asset->getCopyOfFile(), '/usr/local/bin/pdftotext');
            $scoredKeywords = DocumentSearch::$plugin->rake->getKeywordScores($text);
            $results = implode(' ', array_keys($scoredKeywords));
            return $results;
        }
        return null;
    }
}
