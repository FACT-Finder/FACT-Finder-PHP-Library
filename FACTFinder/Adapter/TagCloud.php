<?php
namespace FACTFinder\Adapter;

use FACTFinder\Loader as FF;

class TagCloud extends AbstractAdapter
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Data\TagQuery[]
     */
    private $tagCloud;

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

        $this->request->setAction('TagCloud.ff');
        $this->parameters = $this->request->getParameters();
        $this->parameters['do'] = 'getTagCloud';
        $this->parameters['format'] = 'json';

        $this->useJsonResponseContentProcessor();
    }

    /**
     * Get the tag cloud from FACT-Finder as an array of TagQuery's.
     *
     * @param
     *
     * @return FACTFinder\Data\TagQuery[]
     */
    public function getTagCloud($requestQuery = null)
    {
        if (is_null($this->tagCloud))
            $this->tagCloud = $this->createTagCloud($requestQuery);

        return $this->tagCloud;
    }

    /**
     * Set the maximum amount of tag queries to be fetched.
     *
     * @param int $wordCount The number of tag queries to be fetched. Must be a
     *        positive integer (or a string containing one).
     *
     * @throws InvalidArgumentException if $wordCount is not a positive integer.
     */
    public function setWordCount($wordCount)
    {
        if (is_numeric($wordCount)
            && intval($wordCount) == floatval($wordCount) // Is integer?
            && $wordCount > 0
        ) {
            $this->request->getParameters()->set('wordCount', $wordCount);
        }
        else
            throw new \InvalidArgumentException('Word count has to be a positive integer.');
    }

    private function createTagCloud($requestQuery = null)
    {
        $tagCloud = array();

        $tagCloudData = $this->getResponseContent();
        if (!empty($tagCloudData))
        {
            foreach ($tagCloudData as $tagQueryData)
            {
                $query = (string)$tagQueryData->query;
                $parameters = FF::getInstance('Util\Parameters');
                $parameters['query'] = $query;

                $tagCloud[] = FF::getInstance(
                    'Data\TagQuery',
                    $query,
                    $this->urlBuilder->generateUrl($parameters),
                    $requestQuery == $query,
                    $tagQueryData->weight,
                    $tagQueryData->searchCount
                );
            }
        }

        return $tagCloud;
    }
}
