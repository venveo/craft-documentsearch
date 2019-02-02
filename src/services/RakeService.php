<?php
/**
 * Document Search plugin for Craft CMS 3.x
 *
 * Extract the contents of text documents and add to Craft's search index
 *
 * @link      https://venveo.com
 * @copyright Copyright (c) 2019 Venveo
 */

namespace venveo\documentsearch\services;

use craft\base\Component;
use TextAnalysis\Analysis\Keywords\Rake;
use TextAnalysis\Documents\TokensDocument;
use TextAnalysis\Filters;
use TextAnalysis\Tokenizers\WhitespaceTokenizer;


/**
 * @author    Venveo
 * @package   DocumentSearch
 * @since     1.0.0
 */
class RakeService extends Component
{
    const NGRAM_SIZE = 3;

    /**
     * @var \TextAnalysis\Interfaces\ITokenTransformation[]
     */
    protected $tokenFilters = [];

    /**
     * @var \TextAnalysis\Interfaces\ITokenTransformation[]
     */
    protected $contentFilters = [];


    public function get($content)
    {
        foreach($this->getContentFilters() as $contentFilter)
        {
            $content = $contentFilter->transform($content);
        }
        return $this->getKeywordScores($content);
    }
    /**
     *
     * @return \TextAnalysis\Interfaces\ITokenTransformation[]
     */
    public function getContentFilters()
    {
        if(empty($this->contentFilters)) {

            $lambdaFunc = function($word){
                return  preg_replace('/[\x00-\x1F\x80-\xFF]/u', ' ', $word);
            };

            $this->contentFilters = [
                new Filters\StripTagsFilter(),
                new Filters\LowerCaseFilter(),
                new Filters\NumbersFilter(),
                new Filters\EmailFilter(),
                new Filters\UrlFilter(),
                new Filters\PossessiveNounFilter(),
                new Filters\QuotesFilter(),
                new Filters\PunctuationFilter(),
                new Filters\CharFilter(),
                new Filters\LambdaFilter($lambdaFunc),
                new Filters\WhitespaceFilter()
            ];
        }
        return $this->contentFilters;
    }

    /**
     *
     * @return \TextAnalysis\Interfaces\ITokenTransformation[]
     */
    public function getTokenFilters()
    {
        if(empty($this->tokenFilters)) {
            $stopwords = [
                'a', 'about', 'above', 'after', 'again', 'against', 'ain', 'all', 'am', 'an', 'and', 'any', 'are', 'aren', "aren't", 'as', 'at', 'be', 'because', 'been', 'before', 'being', 'below', 'between', 'both', 'but', 'by', 'can', 'couldn', "couldn't", 'd', 'did', 'didn', "didn't", 'do', 'does', 'doesn', "doesn't", 'doing', 'don', "don't", 'down', 'during', 'each', 'few', 'for', 'from', 'further', 'had', 'hadn', "hadn't", 'has', 'hasn', "hasn't", 'have', 'haven', "haven't", 'having', 'he', 'her', 'here', 'hers', 'herself', 'him', 'himself', 'his', 'how', 'i', 'if', 'in', 'into', 'is', 'isn', "isn't", 'it', "it's", 'its', 'itself', 'just', 'll', 'm', 'ma', 'me', 'mightn', "mightn't", 'more', 'most', 'mustn', "mustn't", 'my', 'myself', 'needn', "needn't", 'no', 'nor', 'not', 'now', 'o', 'of', 'off', 'on', 'once', 'only', 'or', 'other', 'our', 'ours', 'ourselves', 'out', 'over', 'own', 're', 's', 'same', 'shan', "shan't", 'she', "she's", 'should', "should've", 'shouldn', "shouldn't", 'so', 'some', 'such', 't', 'than', 'that', "that'll", 'the', 'their', 'theirs', 'them', 'themselves', 'then', 'there', 'these', 'they', 'this', 'those', 'through', 'to', 'too', 'under', 'until', 'up', 've', 'very', 'was', 'wasn', "wasn't", 'we', 'were', 'weren', "weren't", 'what', 'when', 'where', 'which', 'while', 'who', 'whom', 'why', 'will', 'with', 'won', "won't", 'wouldn', "wouldn't", 'y', 'you', "you'd", "you'll", "you're", "you've", 'your', 'yours', 'yourself', 'yourselves'
            ];
            $this->tokenFilters = [
                new Filters\StopWordsFilter($stopwords),
            ];
        }
        return $this->tokenFilters;
    }

    /**
     *
     * @param string $content
     * @return array
     */
    public function getKeywordScores($content)
    {
        $tokens = (new WhitespaceTokenizer())->tokenize($content);
        $tokenDoc = new TokensDocument(array_map('strval', $tokens));
        unset($tokens);

        foreach($this->getTokenFilters() as $filter)
        {
            $tokenDoc->applyTransformation($filter, false);
        }

        $size = count($tokenDoc->toArray());
        if($size < self::NGRAM_SIZE) {
            return [];
        }

        $rake = new Rake($tokenDoc, self::NGRAM_SIZE);
        return $rake->getKeywordScores();
    }
}
