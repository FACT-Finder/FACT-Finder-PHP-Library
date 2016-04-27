<?php

return array(
    'test' => array(
        'debug' => true,
        'custom' => 'php-value',
        'connection' => array(
            'protocol' => 'http', // possible values: http, https
            'address' => 'demoshop.fact-finder.de',
            'port' => 80,
            'context' => 'FACT-Finder',
            'channel' => 'de',
            'language' => 'de',
            'authentication' => array(
                'type' => 'advanced', // possible values: http, simple, advanced
                'username' => 'user',
                'password' => 'userpw',
                'prefix' => 'FACT-FINDER',
                'postfix' => 'FACT-FINDER',
            ),
            // all timeouts given in seconds
            'timeouts' => array(
                'defaultConnectTimeout' => 2,
                'defaultTimeout' => 4,
                'suggestConnectTimeout' => 1,
                'suggestTimeout' => 2,
                'trackingConnectTimeout' => 1,
                'trackingTimeout' => 2,
                'importConnectTimeout' => 10,
                'importTimeout' => 360,
            ),
        ),
        'parameters' => array(
            // parameter settings for the server
            'server' => array(
                'ignore' => array(
                    'password',
                    'username',
                    'timestamp',
                ),
                'whitelist' => array(
                    // no whitelist elements means allow everything
                    // allow search parameters
                    'query',
                    'followSearch',
                    'advisorStatus',
                    '/^filter.*/',
                    '/^sort.*/',
                    'productsPerPage',
                    'navigation',
                    'catalog',
                    'page',
                    'useKeywords',
                    'useFoundWords',
                    'searchField',
                    'omitContextName',
                    'productNumber',
                    'useSemanticEnhancer',
                    'usePersonalization',
                    // allow general settings parameters
                    'channel',
                    'format',
                    'idsOnly',
                    'useAsn',
                    'useCampaigns',
                    'verbose',
                    'log',
                    'do',
                    'callback',
                    // allow suggestions parameters
                    'ignoreForCache',
                    'userInput',
                    'queryFromSuggest',
                    // allow tracking parameters
                    'id',
                    'pos',
                    'sid',
                    'origPos',
                    'page',
                    'simi',
                    'title',
                    'event',
                    'pageSize',
                    'origPageSize',
                    'userId',
                    'cookieId',
                    'masterId',
                    'count',
                    'price',
                    // allow similar/recommandation parameters
                    'maxRecordCount',
                    'maxResults',
                    'mainId',
                    // allow special test cases
                    'a',
                    'c',
                    'ä',
                    'ü',
                    '+ ~',
                ),
                'mapping' => array(
                    array('from' => 'keywords', 'to' => 'query'),
                ),
            ),
            // parameter settings for the client
            'client' => array(
                'ignore' => array(
                    'xml',
                    'format',
                    'channel',
                    'password',
                    'username',
                    'timestamp'
                ),
                'whitelist' => array(
                    // no whitelist elements means allow everything
                    // allow search parameters
                    'keywords',
                    'followSearch',
                    'advisorStatus',
                    '/^filter.*/',
                    'seoPath',
                    'productsPerPage',
                    'navigation',
                    'catalog',
                    'page',
                    'useKeywords',
                    'useFoundWords',
                    'searchField',
                    'omitContextName',
                    'productNumber',
                    'useSemanticEnhancer',
                    'usePersonalization',
                    // allow general settings parameters
                    'idsOnly',
                    'useAsn',
                    'useCampaigns',
                    'verbose',
                    'log',
                    // allow suggestions parameters
                    'ignoreForCache',
                    'userInput',
                    'queryFromSuggest',
                    // allow special test cases
                    'foo',
                ),
                'mapping' => array(
                    array('from' => 'query', 'to' => 'keywords'),
                ),
            ),
        ),
        'encoding' => array(
            'pageContent' => 'UTF-8',
            'clientUrl' => 'ISO-8859-1',
        ),
    ),
);