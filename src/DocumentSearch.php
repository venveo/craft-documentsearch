<?php
/**
 * Document Search plugin for Craft CMS 3.x
 *
 * Extract the contents of text documents and add to Craft's search index
 *
 * @link      https://venveo.com
 * @copyright Copyright (c) 2019 Venveo
 */

namespace venveo\documentsearch;

use craft\elements\Asset;
use craft\events\DefineBehaviorsEvent;
use craft\events\RegisterElementSearchableAttributesEvent;
use venveo\documentsearch\behaviors\AssetContentBehavior;
use venveo\documentsearch\services\RakeService;
use venveo\documentsearch\models\Settings;
use venveo\documentsearch\utilities\DocumentSearch as DocumentSearchUtility;

use Craft;
use craft\base\Plugin;
use craft\services\Plugins;
use craft\events\PluginEvent;
use craft\console\Application as ConsoleApplication;
use craft\web\UrlManager;
use craft\services\Utilities;
use craft\web\twig\variables\CraftVariable;
use craft\events\RegisterComponentTypesEvent;
use craft\events\RegisterUrlRulesEvent;

use yii\base\Event;

/**
 * Class DocumentSearch
 *
 * @author    Venveo
 * @package   DocumentSearch
 * @since     1.0.0
 *
 * @property  RakeService $rake
 */
class DocumentSearch extends Plugin
{
    // Static Properties
    // =========================================================================

    /**
     * @var DocumentSearch
     */
    public static $plugin;

    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $schemaVersion = '1.0.0';

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
        self::$plugin = $this;

        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'venveo\documentsearch\console\controllers';
        }

        $this->setComponents([
            'rake' => RakeService::class
        ]);
//
//        Event::on(
//            UrlManager::class,
//            UrlManager::EVENT_REGISTER_SITE_URL_RULES,
//            function (RegisterUrlRulesEvent $event) {
//                $event->rules['siteActionTrigger1'] = 'document-search/document-search';
//            }
//        );
//
//        Event::on(
//            UrlManager::class,
//            UrlManager::EVENT_REGISTER_CP_URL_RULES,
//            function (RegisterUrlRulesEvent $event) {
//                $event->rules['cpActionTrigger1'] = 'document-search/document-search/do-something';
//            }
//        );


        Event::on(Asset::class, Asset::EVENT_DEFINE_BEHAVIORS,
            function(DefineBehaviorsEvent $event) {
                $event->behaviors[] = AssetContentBehavior::class;
            });

        Event::on(Asset::class, Asset::EVENT_REGISTER_SEARCHABLE_ATTRIBUTES, function(RegisterElementSearchableAttributesEvent $e) {
            $e->attributes[] = 'contentKeywords';
        });

        Event::on(
            Utilities::class,
            Utilities::EVENT_REGISTER_UTILITY_TYPES,
            function (RegisterComponentTypesEvent $event) {
                $event->types[] = DocumentSearchUtility::class;
            }
        );

//        Event::on(
//            CraftVariable::class,
//            CraftVariable::EVENT_INIT,
//            function (Event $event) {
//                /** @var CraftVariable $variable */
//                $variable = $event->sender;
//                $variable->set('documentSearch', DocumentSearchVariable::class);
//            }
//        );
//
//        Craft::info(
//            Craft::t(
//                'document-search',
//                '{name} plugin loaded',
//                ['name' => $this->name]
//            ),
//            __METHOD__
//        );
    }

    // Protected Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    protected function createSettingsModel()
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): string
    {
        return Craft::$app->view->renderTemplate(
            'document-search/settings',
            [
                'settings' => $this->getSettings()
            ]
        );
    }
}
