<?php
/**
 * Document Search plugin for Craft CMS 3.x
 *
 * Extract the contents of text documents and add to Craft's search index
 *
 * @link      https://venveo.com
 * @copyright Copyright (c) 2019 Venveo
 */

namespace venveo\documentsearch\models;

use venveo\documentsearch\DocumentSearch;

use Craft;
use craft\base\Model;

/**
 * @author    Venveo
 * @package   DocumentSearch
 * @since     1.0.0
 */
class Settings extends Model
{
    // Public Properties
    // =========================================================================

    /**
     * @var string
     */
    public $pdfToTextExecutable = '/usr/local/bin/pdftotext';
    public $maximumKeywords = 100;
    public $maximumDocumentSize = 1024 * 4;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['pdfToTextExecutable', 'string'],
            [['maximumKeywords', 'maximumDocumentSize'], 'integer'],
        ];
    }
}
