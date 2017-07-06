<?php
return array(
    'service_manager' => array(
        'factories' => array(
            'API\\V1\\Rest\\Frequency\\FrequencyResource' => 'API\\V1\\Rest\\Frequency\\FrequencyResourceFactory',
            'API\\V1\\Rest\\Event\\EventResource' => 'API\\V1\\Rest\\Event\\EventResourceFactory',
        ),
    ),
    'router' => array(
        'routes' => array(
            'api.rest.frequency' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/frequency[/:frequency_id]',
                    'defaults' => array(
                        'controller' => 'API\\V1\\Rest\\Frequency\\Controller',
                    ),
                ),
            ),
            'api.rest.event' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/event[/:event_id]',
                    'defaults' => array(
                        'controller' => 'API\\V1\\Rest\\Event\\Controller',
                    ),
                ),
            ),
        ),
    ),
    'zf-versioning' => array(
        'uri' => array(
            0 => 'api.rest.frequency',
            1 => 'api.rest.event',
        ),
    ),
    'zf-rest' => array(
        'API\\V1\\Rest\\Frequency\\Controller' => array(
            'listener' => 'API\\V1\\Rest\\Frequency\\FrequencyResource',
            'route_name' => 'api.rest.frequency',
            'route_identifier_name' => 'frequency_id',
            'collection_name' => 'frequency',
            'entity_http_methods' => array(
                0 => 'GET',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => '25',
            'page_size_param' => null,
            'entity_class' => 'Application\\Entity\\Frequency',
            'collection_class' => 'API\\V1\\Rest\\Frequency\\FrequencyCollection',
            'service_name' => 'frequency',
        ),
        'API\\V1\\Rest\\Event\\Controller' => array(
            'listener' => 'API\\V1\\Rest\\Event\\EventResource',
            'route_name' => 'api.rest.event',
            'route_identifier_name' => 'event_id',
            'collection_name' => 'event',
            'entity_http_methods' => array(
                0 => 'GET',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => '25',
            'page_size_param' => null,
            'entity_class' => 'Application\\Entity\\Event',
            'collection_class' => 'API\\V1\\Rest\\Event\\EventCollection',
            'service_name' => 'event',
        ),
    ),
    'zf-content-negotiation' => array(
        'controllers' => array(
            'API\\V1\\Rest\\Frequency\\Controller' => 'HalJson',
            'API\\V1\\Rest\\Event\\Controller' => 'HalJson',
        ),
        'accept_whitelist' => array(
            'API\\V1\\Rest\\Frequency\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
            'API\\V1\\Rest\\Event\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
        ),
        'content_type_whitelist' => array(
            'API\\V1\\Rest\\Frequency\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
            ),
            'API\\V1\\Rest\\Event\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
            ),
        ),
    ),
    'zf-hal' => array(
        'metadata_map' => array(
            'API\\V1\\Rest\\Frequency\\FrequencyEntity' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.frequency',
                'route_identifier_name' => 'frequency_id',
                'hydrator' => 'Zend\\Hydrator\\ArraySerializable',
            ),
            'API\\V1\\Rest\\Frequency\\FrequencyCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.frequency',
                'route_identifier_name' => 'frequency_id',
                'is_collection' => true,
            ),
            'Application\\Entity\\Frequency' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.frequency',
                'route_identifier_name' => 'frequency_id',
                'hydrator' => 'Zend\\Hydrator\\ArraySerializable',
            ),
            'API\\V1\\Rest\\Event\\EventEntity' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.event',
                'route_identifier_name' => 'event_id',
                'hydrator' => 'Zend\\Hydrator\\ArraySerializable',
            ),
            'API\\V1\\Rest\\Event\\EventCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.event',
                'route_identifier_name' => 'event_id',
                'is_collection' => true,
            ),
            'Application\\Entity\\Event' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.event',
                'route_identifier_name' => 'event_id',
                'hydrator' => 'Zend\\Hydrator\\ArraySerializable',
            ),
        ),
    ),
    'zf-content-validation' => array(
        'API\\V1\\Rest\\Frequency\\Controller' => array(
            'input_filter' => 'API\\V1\\Rest\\Frequency\\Validator',
        ),
    ),
    'input_filter_specs' => array(
        'API\\V1\\Rest\\Frequencies\\Validator' => array(
            0 => array(
                'required' => true,
                'validators' => array(),
                'filters' => array(
                    0 => array(
                        'name' => 'Zend\\Filter\\Boolean',
                        'options' => array(),
                    ),
                ),
                'name' => 'state',
            ),
        ),
        'API\\V1\\Rest\\Frequency\\Validator' => array(),
    ),
    'controllers' => array(
        'factories' => array(),
    ),
    'zf-rpc' => array(),
    'zf-mvc-auth' => array(
        'authorization' => array(
            'API\\V1\\Rest\\Frequency\\Controller' => array(
                'collection' => array(
                    'GET' => true,
                    'POST' => false,
                    'PUT' => false,
                    'PATCH' => false,
                    'DELETE' => false,
                ),
                'entity' => array(
                    'GET' => true,
                    'POST' => false,
                    'PUT' => false,
                    'PATCH' => false,
                    'DELETE' => false,
                ),
            ),
            'API\\V1\\Rest\\Event\\Controller' => array(
                'collection' => array(
                    'GET' => true,
                    'POST' => false,
                    'PUT' => false,
                    'PATCH' => false,
                    'DELETE' => false,
                ),
                'entity' => array(
                    'GET' => true,
                    'POST' => false,
                    'PUT' => false,
                    'PATCH' => false,
                    'DELETE' => false,
                ),
            ),
        ),
    ),
);
