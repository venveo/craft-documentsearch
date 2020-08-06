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

/**
 * @author    Venveo
 * @package   DocumentSearch
 * @since     1.0.0
 */
class Settings extends Model
{
    public $maximumDocumentSize = 1024 * 4;
    public $indexVolumes = [];

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['maximumDocumentSize'], 'integer', 'min' => 1, 'message' => 'Document size should be a positive number'],
        ];
    }
}
