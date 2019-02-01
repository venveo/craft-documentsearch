<?php
/**
 * Document Search plugin for Craft CMS 3.x
 *
 * Extract the contents of text documents and add to Craft's search index
 *
 * @link      https://venveo.com
 * @copyright Copyright (c) 2019 Venveo
 */

namespace venveo\documentsearch\assetbundles\DocumentSearch;

use Craft;
use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

/**
 * @author    Venveo
 * @package   DocumentSearch
 * @since     1.0.0
 */
class DocumentSearchAsset extends AssetBundle
{
    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        $this->sourcePath = "@venveo/documentsearch/assetbundles/documentsearch/dist";

        $this->depends = [
            CpAsset::class,
        ];

        $this->js = [
            'js/DocumentSearch.js',
        ];

        $this->css = [
            'css/DocumentSearch.css',
        ];

        parent::init();
    }
}
