<?php
namespace FACTFinder\Adapter;

use FACTFinder\Loader as FF;

class Suggest extends AbstractAdapter
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Data\SuggestQuery[]
     */
    private $suggestions;

    /**
     * @var FACTFinder\Util\Parameters
     */
    private $parameters;

    public function __construct(
        $loggerClass,
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Core\Server\Request $request,
        \FACTFinder\Core\Client\UrlBuilder $urlBuilder
    ) {
        parent::__construct($loggerClass, $configuration, $request,
                            $urlBuilder);

        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->request->setAction('Suggest.ff');
        $this->parameters = $this->request->getParameters();
        $this->parameters['format'] = 'json';

        $this->request->setConnectTimeout($configuration->getSuggestConnectTimeout());
        $this->request->setTimeout($configuration->getSuggestTimeout());

        $this->useJsonResponseContentProcessor();
    }

    /**
     * Get the suggestions from FACT-Finder as an array of SuggestQuery's.
     *
     * @param
     *
     * @return FACTFinder\Data\SuggestQuery[]
     */
    public function getSuggestions()
    {
        if (is_null($this->suggestions))
            $this->suggestions = $this->createSuggestions();

        return $this->suggestions;
    }

    private function createSuggestions()
    {
        $suggestions = array();

        $suggestData = $this->getResponseContent();
        if (!empty($suggestData))
        {
            foreach ($suggestData as $suggestQueryData)
            {
                $parameters = FF::getInstance(
                    'Util\Parameters',
                    (string)$suggestQueryData->searchParams,
                    true
                );

                $suggestions[] = FF::getInstance(
                    'Data\SuggestQuery',
                    (string)$suggestQueryData->name,
                    $this->urlBuilder->generateUrl($parameters),
                    (int)$suggestQueryData->hitCount,
                    (string)$suggestQueryData->type,
                    (string)$suggestQueryData->image,
                    (string)$suggestQueryData->refKey
                );
            }
        }

        return $suggestions;
    }
}
