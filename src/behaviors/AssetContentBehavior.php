<?php

namespace venveo\documentsearch\behaviors;

use craft\elements\Asset;
use craft\web\Session;
use venveo\documentsearch\DocumentSearch as Plugin;
use yii\base\Behavior;

/**
 * @property mixed $contentKeywords
 * @property Session $owner
 * @author Venveo <info@venveo.com>
 */
class AssetContentBehavior extends Behavior
{
    // Properties
    // =========================================================================

    public function getContentKeywords()
    {
        /** @var Asset $asset */
        $asset = $this->owner;
        return Plugin::$plugin->documentContent->getAssetContentKeywords($asset);
    }
}
