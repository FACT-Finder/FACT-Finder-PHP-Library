<?php
namespace FACTFinder\Adapter;

use FACTFinder\Loader as FF;

class Search extends AbstractAdapter
{
    /**
     * @var FACTFinder\Util\LoggerInterface
     */
    private $log;

    /**
     * @var FACTFinder\Data\Result
     */
    private $result;

    /**
     * @var FACTFinder\Data\AfterSearchNavigation
     */
    private $afterSearchNavigation;

    /**
     * @var FACTFinder\Data\ResultsPerPageOptions
     */
    private $resultsPerPageOptions;

    /**
     * @var FACTFinder\Data\Paging
     */
    private $paging;

    public function __construct(
        $loggerClass,
        \FACTFinder\Core\ConfigurationInterface $configuration,
        \FACTFinder\Core\Server\Request $request,
        \FACTFinder\Core\Client\UrlBuilder $urlBuilder
    ) {
        parent::__construct($loggerClass, $configuration, $request,
                            $urlBuilder);

        $this->log = $loggerClass::getLogger(__CLASS__);

        $this->request->setAction('Search.ff');
        $this->parameters['format'] = 'json';

        $this->useJsonResponseContentProcessor();
    }

    /**
     * @return \FACTFinder\Data\Result
     */
    public function getResult()
    {
        if (is_null($this->result))
            $this->result = $this->createResult();

        return $this->result;
    }

    /**
     * @return \FACTFinder\Data\Result
     */
    private function createResult()
    {
        //init default values
        $records      = array();
        $resultCount = 0;

        $jsonData = $this->getResponseContent();
        $searchResultData = $jsonData['searchResult'];

        if (!empty($searchResultData['records']))
        {
            $resultCount = $searchResultData['resultCount'];

            foreach ($searchResultData['records'] as $recordData)
            {
                $position = $recordData['position'];

                $record = FF::getInstance('Data\Record',
                    strval($recordData['id']),
                    $recordData['record'],
                    $recordData['searchSimilarity'],
                    $position,
                    $recordData['seoPath'],
                    $recordData['keywords']
                );

                $records[] = $record;
            }
        }

        return FF::getInstance(
            'Data\Result',
            $records,
            $searchResultData['refKey'],
            $resultCount
        );
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        $jsonData = $this->getResponseContent();

        $searchStatusEnum = FF::getClassName('Data\SearchStatus');
        switch($jsonData['searchResult']['resultStatus'])
        {
        case 'nothingFound':
            $status = $searchStatusEnum::EmptyResult();
            break;
        case 'resultsFound':
            $status = $searchStatusEnum::RecordsFound();
            break;
        default:
            $status = $searchStatusEnum::NoResult();
            break;
        }

        return $status;
    }

    /**
     * @return bool
     */
    public function isSearchTimedOut()
    {
        $jsonData = $this->getResponseContent();

        return $jsonData['searchResult']['timedOut'];
    }

    /**
     * @return \FACTFinder\Data\AfterSearchNavigation
     */
    public function getAfterSearchNavigation()
    {
        if (is_null($this->afterSearchNavigation))
            $this->afterSearchNavigation = $this->createAfterSearchNavigation();

        return $this->afterSearchNavigation;
    }

    /**
     * @return \FACTFinder\Data\AfterSearchNavigation
     */
    private function createAfterSearchNavigation()
    {
        $jsonData = $this->getResponseContent();

        $filterGroups = array();
        foreach ($jsonData['searchResult']['groups'] as $groupData)
                $filterGroups[] = $this->createFilterGroup($groupData);

        return FF::getInstance(
            'Data\AfterSearchNavigation',
            $filterGroups
        );
    }

    /**
     * @param mixed[] $groupData An associative array corresponding to the JSON
     *        for a single filter group.
     * @return \FACTFinder\Data\FilterGroup
     */
    private function createFilterGroup($groupData)
    {
        $elements = array_merge(
            $groupData['selectedElements'],
            $groupData['elements']
        );

        $filterStyleEnum = FF::getClassName('Data\FilterStyle');
        switch ($groupData['filterStyle'])
        {
        case 'SLIDER':
            $filterStyle = $filterStyleEnum::Slider();
            break;
        case 'COLOR':
            $filterStyle = $filterStyleEnum::Color();
            break;
        case 'TREE':
            $filterStyle = $filterStyleEnum::Tree();
            break;
        case 'MULTISELECT':
            $filterStyle = $filterStyleEnum::MultiSelect();
            break;
        default:
            $filterStyle = $filterStyleEnum::Regular();
            break;
        }

        $filters = array();
        foreach ($elements as $filterData)
        {
            if ($filterStyle == $filterStyleEnum::Slider())
                $filters[] = $this->createSliderFilter($filterData);
            else
                $filters[] = $this->createFilter($filterData);
        }

        return FF::getInstance(
            'Data\FilterGroup',
            $filters,
            $groupData['name'],
            $filterStyle,
            $groupData['detailedLinks'],
            $groupData['unit']
        );
    }

    /**
     * @param mixed[] $filterData An associative array corresponding to the JSON
     *        for a single filter.
     * @return \FACTFinder\Data\Filter
     */
    private function createFilter($filterData)
    {
        $filterLink = $this->convertServerQueryToClientUrl(
            $filterData['searchParams']
        );

        return FF::getInstance(
            'Data\Filter',
            $filterData['name'],
            $filterLink,
            $filterData['selected'],
            $filterData['associatedFieldName'],
            $filterData['recordCount'],
            $filterData['clusterLevel'],
            $filterData['previewImageURL'] ?: ''
        );
    }

    /**
     * @param mixed[] $filterData An associative array corresponding to the JSON
     *        for a single slider filter.
     * @return \FACTFinder\Data\SliderFilter
     */
    private function createSliderFilter($filterData)
    {
        // For sliders, FACT-Finder appends a filter parameter without value to
        // the 'searchParams' field, which is to be filled with the selected
        // minimum and maximum like 'filterValue=min-max'.
        // We split that parameter off, and treat it separately to ensure that
        // it stays the last parameter when converted to a client URL.
        preg_match(
            '/
            (.*)            # match and capture as much of the query as possible
            [?&]filter      # match "?filter" or "&filter" literally
            ([^&=]*)        # group 2, the field name
            =$              # make sure there is a "=" and then the end of the
                            # string
            /x',
            $filterData['searchParams'],
            $matches
        );

        $query = $matches[1];
        $fieldName = $matches[2];

        if ($fieldName != $filterData['associatedFieldName'])
            $this->log->warn('Filter parameter of slider does not correspond '
                           . 'to transmitted "associatedFieldName". Parameter: '
                           . "$fieldName. Field name: "
                           . $filterData['associatedFieldName'] . '.');

        $filterLink = $this->convertServerQueryToClientUrl($query);

        return FF::getInstance(
            'Data\SliderFilter',
            $filterLink,
            $fieldName,
            $filterData['absoluteMinValue'],
            $filterData['absoluteMaxValue'],
            $filterData['selectedMinValue'],
            $filterData['selectedMaxValue']
        );
    }

    /**
     * @return \FACTFinder\Data\ResultsPerPageOptions
     */
    public function getResultsPerPageOptions()
    {
        if (is_null($this->resultsPerPageOptions))
            $this->resultsPerPageOptions = $this->createResultsPerPageOptions();

        return $this->resultsPerPageOptions;
    }

    /**
     * @return \FACTFinder\Data\ResultsPerPageOptions
     */
    public function createResultsPerPageOptions()
    {
        $options = array();

        $jsonData = $this->getResponseContent();

        $rppData = $jsonData['searchResult']['resultsPerPageList'];
        if (!empty($rppData))
        {
            $defaultOption = null;
            $selectedOption = null;

            foreach ($rppData as $optionData)
            {
                $optionLink = $this->convertServerQueryToClientUrl(
                    $optionData['searchParams']
                );

                $option = FF::getInstance(
                    'Data\Item',
                    $optionData['value'],
                    $optionLink,
                    $optionData['selected']
                );

                if ($optionData['default'])
                    $defaultOption = $option;
                if ($optionData['selected'])
                    $selectedOption = $option;

                $options[] = $option;
            }
        }

        return FF::getInstance(
            'Data\ResultsPerPageOptions',
            $options,
            $defaultOption,
            $selectedOption
        );
    }

    /**
     * @return \FACTFinder\Data\Paging
     */
    public function getPaging()
    {
        if (is_null($this->paging))
            $this->paging = $this->createPaging();

        return $this->paging;
    }

    /**
     * @return \FACTFinder\Data\Paging
     */
    private function createPaging()
    {
        $pages = array();

        $jsonData = $this->getResponseContent();

        $pagingData = $jsonData['searchResult']['paging'];
        if (!empty($pagingData))
        {
            $currentPage = null;
            $pageCount = $pagingData['pageCount'];

            foreach ($pagingData['pageLinks'] as $pageData)
            {
                $page = $this->createPageItem($pageData);

                if ($pageData['currentPage'])
                    $currentPage = $page;

                $pages[] = $page;
            }
        }

        return FF::getInstance(
            'Data\Paging',
            $pages,
            $pageCount,
            $this->createPageItem($pagingData['firstLink']),
            $this->createPageItem($pagingData['lastLink']),
            $this->createPageItem($pagingData['previousLink']),
            $currentPage,
            $this->createPageItem($pagingData['nextLink'])
        );
    }


    /**
     * @param mixed[] $pageData An associative array corresponding to the JSON
     *        for a single page link.
     * @return \FACTFinder\Data\Item
     */
    private function createPageItem($pageData)
    {
        $pageLink = $this->convertServerQueryToClientUrl(
            $pageData['searchParams']
        );

        return FF::getInstance(
            'Data\Page',
            $pageData['number'],
            $pageData['caption'],
            $pageLink,
            $pageData['currentPage']
        );
    }

    /**
     * @return \FACTFinder\Data\Sorting
     */
    public function getSorting()
    {
        if (is_null($this->sorting))
            $this->sorting = $this->createSorting();

        return $this->sorting;
    }

    /**
     * @return \FACTFinder\Data\Sorting
     */
    private function createSorting()
    {
        $sortOptions = array();

        $jsonData = $this->getResponseContent();

        $sortingData = $jsonData['searchResult']['sortsList'];
        if (!empty($sortingData))
        {
            foreach ($sortingData['pageLinks'] as $optionData)
            {
                $optionLink = $this->convertServerQueryToClientUrl(
                    $optionData['searchParams']
                );

                $sortOptions[] = FF::getInstance(
                    'Data\Item',
                    $optionData['number'],
                    $optionData['caption'],
                    $optionLink,
                    $optionData['currentPage']
                );
            }
        }

        return FF::getInstance(
            'Data\Sorting',
            $sortOptions
        );
    }
}
