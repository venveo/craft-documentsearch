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

use venveo\documentsearch\DocumentSearch;

use Craft;
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
    // Public Methods
    // =========================================================================

    /**
     * Handle document-search/parse-documents console commands
     *
     * @return mixed
     */
    public function actionIndex()
    {
        $result = 'something';

        echo "Welcome to the console ParseDocumentsController actionIndex() method\n";

        return $result;
    }

    /**
     * Handle document-search/parse-documents/do-something console commands
     *
     * @return mixed
     */
    public function actionDoSomething()
    {
        $result = 'something';

        echo "Welcome to the console ParseDocumentsController actionDoSomething() method\n";

        return $result;
    }
}
