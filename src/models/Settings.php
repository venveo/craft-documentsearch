<?php
/**
 * Document Search plugin for Craft CMS 3.1.x
 *
 * Extract the contents of text documents and add to Craft's search index
 *
 * @link      https://venveo.com
 * @copyright Copyright (c) 2019 Venveo
 */

namespace venveo\documentsearch\models;

use craft\base\Model;
use craft\behaviors\EnvAttributeParserBehavior;

/**
 * @author    Venveo
 * @package   DocumentSearch
 * @since     1.0.0
 */
class Settings extends Model
{
    public ?string $pdfToTextExecutable = '/usr/local/bin/pdftotext';
    public int $maximumDocumentSize = 1024 * 4;
    public array $indexVolumes = [];


    public function behaviors(): array
    {
        $behaviors = parent::behaviors();
        $behaviors['parser'] = [
            'class' => EnvAttributeParserBehavior::class,
            'attributes' => [
                'pdfToTextExecutable'
            ],
        ];
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            ['pdfToTextExecutable', 'string'],
            [['maximumDocumentSize'], 'integer', 'min' => 1, 'message' => 'Document size should be a positive number'],
        ];
    }
}
