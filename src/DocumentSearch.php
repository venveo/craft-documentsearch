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

use Craft;
use craft\base\Plugin;
use craft\console\Application as ConsoleApplication;
use craft\elements\Asset;
use craft\events\DefineBehaviorsEvent;
use craft\events\RegisterElementSearchableAttributesEvent;
use craft\helpers\App;
use venveo\documentsearch\behaviors\AssetContentBehavior;
use venveo\documentsearch\models\Settings;
use venveo\documentsearch\services\DocumentContentService;
use venveo\documentsearch\services\RakeService;
use yii\base\Event;

/**
 * Class DocumentSearch
 *
 * @author    Venveo
 * @package   DocumentSearch
 * @since     1.0.0
 *
 * @property  RakeService $rake
 * @property  DocumentContentService $documentContent
 */
class DocumentSearch extends Plugin
{

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if (Craft::$app instanceof ConsoleApplication) {
            $this->controllerNamespace = 'venveo\documentsearch\console\controllers';
        }

        $this->setComponents([
            'rake' => RakeService::class,
            'documentContent' => DocumentContentService::class
        ]);

        Event::on(Asset::class, Asset::EVENT_DEFINE_BEHAVIORS,
            function(DefineBehaviorsEvent $event) {
                $event->behaviors[] = AssetContentBehavior::class;
            }
        );

        Event::on(Asset::class, Asset::EVENT_REGISTER_SEARCHABLE_ATTRIBUTES, function(RegisterElementSearchableAttributesEvent $e) {
            $e->attributes[] = 'contentKeywords';
        });
    }

    /**
     * @inheritdoc
     */
    protected function createSettingsModel(): ?\craft\base\Model
    {
        return new Settings();
    }

    /**
     * @inheritdoc
     */
    protected function settingsHtml(): ?string
    {
        return Craft::$app->view->renderTemplate(
            'document-search/settings',
            [
                'settings' => $this->getSettings(),
                'binaries' => [
                    'pdftotext' => is_file(App::parseEnv($this->getSettings()->pdfToTextExecutable))
                ]
            ]
        );
    }
}
