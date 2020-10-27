<?php

namespace venveo\documentsearch\extractors;

use craft\base\Component;

abstract class Extractor extends Component implements ExtractorInterface
{
    public static function canHandleFile($filepath): bool
    {
        return true;
    }
}