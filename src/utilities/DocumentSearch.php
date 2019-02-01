<?php
/**
 * Document Search plugin for Craft CMS 3.x
 *
 * Extract the contents of text documents and add to Craft's search index
 *
 * @link      https://venveo.com
 * @copyright Copyright (c) 2019 Venveo
 */

namespace venveo\documentsearch\utilities;

use venveo\documentsearch\DocumentSearch as Plugin;
use venveo\documentsearch\assetbundles\documentsearchutility\DocumentSearchUtilityAsset;

use Craft;
use craft\base\Utility;

/**
 * Document Search Utility
 *
 * @author    Venveo
 * @package   DocumentSearch
 * @since     1.0.0
 */
class DocumentSearch extends Utility
{
    // Static
    // =========================================================================

    /**
     * @inheritdoc
     */
    public static function displayName(): string
    {
        return Craft::t('document-search', 'DocumentSearch');
    }

    /**
     * @inheritdoc
     */
    public static function id(): string
    {
        return 'documentsearch-document-search';
    }

    /**
     * @inheritdoc
     */
    public static function iconPath()
    {
        return Craft::getAlias("@venveo/documentsearch/assetbundles/documentsearchutility/dist/img/DocumentSearch-icon.svg");
    }

    /**
     * @inheritdoc
     */
    public static function badgeCount(): int
    {
        return 0;
    }

    /**
     * @inheritdoc
     */
    public static function contentHtml(): string
    {
        Craft::$app->getView()->registerAssetBundle(DocumentSearchUtilityAsset::class);

        $someVar = 'Have a nice day!';
        return Craft::$app->getView()->renderTemplate(
            'document-search/_components/utilities/DocumentSearch_content',
            [
                'someVar' => $someVar
            ]
        );
    }
}
