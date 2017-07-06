<?php
return array(
    'API\\V1\\Rest\\Frequency\\Controller' => array(
        'collection' => array(
            'GET' => array(
                'response' => '{
                   "_links": {
                       "self": {
                           "href": "/frequency"
                       },
                       "first": {
                           "href": "/frequency?page={page}"
                       },
                       "prev": {
                           "href": "/frequency?page={page}"
                       },
                       "next": {
                           "href": "/frequency?page={page}"
                       },
                       "last": {
                           "href": "/frequency?page={page}"
                       }
                   }
                   "_embedded": {
                       "frequency": [
                           {
                               "_links": {
                                   "self": {
                                       "href": "/frequency[/:frequency_id]"
                                   }
                               }
                
                           }
                       ]
                   }
                }',
            ),
        ),
        'entity' => array(
            'GET' => array(
                'response' => '{
                   "_links": {
                       "self": {
                           "href": "/frequency[/:frequency_id]"
                       }
                   }
                
                }',
                'description' => 'Get a frequency',
            ),
            'description' => '',
        ),
        'description' => 'Get frequencies',
    ),
    'API\\V1\\Rest\\Event\\Controller' => array(
        'description' => 'Get events',
        'collection' => array(
            'GET' => array(
                'response' => '{
                   "_links": {
                       "self": {
                           "href": "/api/event"
                       },
                       "first": {
                           "href": "/api/event?page={page}"
                       },
                       "prev": {
                           "href": "/api/event?page={page}"
                       },
                       "next": {
                           "href": "/api/event?page={page}"
                       },
                       "last": {
                           "href": "/api/event?page={page}"
                       }
                   }
                   "_embedded": {
                       "event": [
                           {
                               "_links": {
                                   "self": {
                                       "href": "/api/event[/:event_id]"
                                   }
                               }
                
                           }
                       ]
                   }
                }',
            ),
        ),
        'entity' => array(
            'GET' => array(
                'response' => '{
                   "_links": {
                       "self": {
                           "href": "/api/event[/:event_id]"
                       }
                   }
                
                }',
            ),
        ),
    ),
);
