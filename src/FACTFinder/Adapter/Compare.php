<?php
namespace FACTFinder\Adapter;

use FACTFinder\Loader as FF;

class Compare extends ConfigurableResponse
{
    /**
     * @var \FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var mixed[]
     * @see getSimilarAttributes()
     */
    private $comparableAttributes;

    /**
     * @var \FACTFinder\Data\Result
     */
    private $comparedRecords;

    public function __construct(
        $loggerClass,
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Core\Server\Request $request,
        \FACTFinder\Core\Client\UrlBuilder $urlBuilder,
        \FACTFinder\Core\AbstractEncodingConverter $encodingConverter = null
    ) {
        parent::__construct(
            $loggerClass,
            $configuration,
            $request,
                            $urlBuilder,
            $encodingConverter
        );

        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->request->setAction('Compare.ff');
        $this->parameters['format'] = 'json';

        $this->useJsonResponseContentProcessor();
    }

    /**
     * Set a list-like array of product IDs to compare.
     *
     * @param string[] $productIDs
     */
    public function setProductIDs($productIDs)
    {
        $parameters = $this->request->getParameters();
        $parameters['ids'] = implode(';', $productIDs);
        $this->upToDate = false;
    }

    /**
     * Returns the attributes which can be compared and whether the products in
     * question differ in each of those attributes. If no IDs have been set,
     * there will be a warning raised and an empty array will be returned.
     *
     * @return bool[] Attribute names as keys, boolean value indicates whether
     *                products have different values for this attribute.
     */
    public function getComparableAttributes()
    {
        if (is_null($this->comparableAttributes)
            || !$this->upToDate
        ) {
            $this->comparableAttributes = $this->createComparableAttributes();
            $this->upToDate = true;
        }

        return $this->comparableAttributes;
    }

    private function createComparableAttributes()
    {
        $attributes = array();

        $parameters = $this->request->getParameters();
        if (!isset($parameters['ids'])) {
            $this->log->warn('Comparable attributes cannot be loaded without product IDs. '
                           . 'Use setProductIDs() first.');
        } else {
            $jsonData = $this->getResponseContent();
            if (parent::isValidResponse($jsonData)) {
                foreach ($jsonData['attributes'] as $attributeData) {
                    $name = $attributeData['attributeName'];
                    $attributes[$name] = $attributeData['different'];
                }
            }
        }
        return $attributes;
    }

    /**
     * Returns the records corresponding to the IDs previously specified. If no
     * IDs have been set, there will be a warning raised and an empty result
     * will be returned.
     *
     * @return \FACTFinder\Data\Result
     */
    public function getComparedRecords()
    {
        if (is_null($this->comparedRecords)
            || !$this->upToDate
        ) {
            $this->comparedRecords = $this->createComparedRecords();
            $this->upToDate = true;
        }

        return $this->comparedRecords;
    }

    private function createComparedRecords()
    {
        $records = array();

        $parameters = $this->request->getParameters();
        if (!isset($parameters['ids'])) {
            $this->log->warn('Compared records cannot be loaded without product IDs. '
                           . 'Use setProductIDs() first.');
        } else {
            $position = 1;
            $jsonData = $this->getResponseContent();
            if (parent::isValidResponse($jsonData)) {
                foreach ($jsonData['records'] as $recordData) {
                    $records[] = FF::getInstance(
                        'Data\Record',
                        (string)$recordData['id'],
                        $recordData['record'],
                        100.0,
                        $position++
                    );
                }
            }
        }

        return FF::getInstance(
            'Data\Result',
            $records,
            null,
            count($records)
        );
    }
}
