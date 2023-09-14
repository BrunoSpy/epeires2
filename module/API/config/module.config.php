<?php
return array(
    'lmc_cors' => array(
        'allowed_origins' => ['*'],
        'allowed_methods' => ['GET', 'POST', 'DELETE', 'PUT', 'OPTIONS'],
        'allowed_headers' => ['Authorization', 'Content-Type'],
    ),
    'service_manager' => array(
        'factories' => array(
            'API\\V1\\Rest\\Frequency\\FrequencyResource' => 'API\\V1\\Rest\\Frequency\\FrequencyResourceFactory',
            'API\\V1\\Rest\\Event\\EventResource' => 'API\\V1\\Rest\\Event\\EventResourceFactory',
            'API\\V1\\Rest\\Sector\\SectorResource' => 'API\\V1\\Rest\\Sector\\SectorResourceFactory',
            'API\\V1\\Rest\\Customfields\\CustomfieldsResource' => 'API\\V1\\Rest\\Customfields\\CustomfieldsResourceFactory',
            'API\\V1\\Rest\\File\\FileResource' => 'API\\V1\\Rest\\File\\FileResourceFactory',
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
            'api.rest.event.addnewevent' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/api/event/addnewevent',
                    'defaults' => array(
                        'controller' => 'API\\V1\\Rest\\Event\\Controller',
                        LmcCors\Options\CorsOptions::ROUTE_PARAM => [
                            'allowed_origins' => ["http://localhost:3000"],
                            'allowed_methods' => ['POST'],
                        ],
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'post' => array(
                        'type' => 'Method',
                        'options' => array(
                            'verb' => 'post',
                        ),
                    ),
                ),
            ),
            'api.rest.customfields.getcustomfields' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/api/customfields/getcustomfields',
                    'defaults' => array(
                        'controller' => 'API\\V1\\Rest\\Event\\Controller',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'get' => array(
                        'type' => 'Method',
                        'options' => array(
                            'verb' => 'post',
                        ),
                    ),
                ),
            ),
            'api.rest.file.addfile' => array(
                'type' => 'Literal',
                'options' => array(
                    'route' => '/api/file/addfile',
                    'defaults' => array(
                        'controller' => 'API\\V1\\Rest\\Event\\Controller',
                    ),
                ),
                'may_terminate' => true,
                'child_routes' => array(
                    'get' => array(
                        'type' => 'Method',
                        'options' => array(
                            'verb' => 'post',
                        ),
                    ),
                ),
            ),
            'api.rest.sector' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/sector[/:sector_name]',
                    'defaults' => array(
                        'controller' => 'API\\V1\\Rest\\Sector\\Controller',
                    ),
                ),
            ),
            'api-tools' => array(
                'type' => 'literal',
                'options' => array(
                    'route' => '/api-tools',
                    'defaults' => array(
                        'controller' => 'api-tools-ui',
                        'action' => 'index',
                    ),
                ),
            ),
            'api.rest.customfields' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/customfields[/:customfields_id]',
                    'defaults' => array(
                        'controller' => 'API\\V1\\Rest\\Customfields\\Controller',
                    ),
                ),
            ),
            'api.rest.file' => array(
                'type' => 'Segment',
                'options' => array(
                    'route' => '/api/file[/:file_id]',
                    'defaults' => array(
                        'controller' => 'API\\V1\\Rest\\File\\Controller',
                    ),
                ),
            ),
        ),
    ),
    'api-tools-versioning' => array(
        'uri' => array(
            0 => 'api.rest.frequency',
            1 => 'api.rest.event',
            2 => 'api.rest.sector',
            3 => 'api.rest.customfields',
            4 => 'api.rest.file',
        ),
    ),
    'api-tools-rest' => array(
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
                1 => 'POST',
                2 => 'PUT',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
                2 => 'PUT',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => '25',
            'page_size_param' => null,
            'entity_class' => 'Application\\Entity\\Event',
            'collection_class' => 'API\\V1\\Rest\\Event\\EventCollection',
            'service_name' => 'event',
        ),
        'API\\V1\\Rest\\Sector\\Controller' => array(
            'listener' => 'API\\V1\\Rest\\Sector\\SectorResource',
            'route_name' => 'api.rest.sector',
            'route_identifier_name' => 'sector_name',
            'collection_name' => 'sector',
            'entity_http_methods' => array(
                0 => 'GET',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => 'Application\\Entity\\Sector',
            'collection_class' => 'API\\V1\\Rest\\Sector\\SectorCollection',
            'service_name' => 'sector',
        ),
        'API\\V1\\Rest\\Customfields\\Controller' => array(
            'listener' => 'API\\V1\\Rest\\Customfields\\CustomfieldsResource',
            'route_name' => 'api.rest.customfields',
            'route_identifier_name' => 'customfields_id',
            'collection_name' => 'customfields',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => 'API\\V1\\Rest\\Customfields\\CustomfieldsEntity',
            'collection_class' => 'API\\V1\\Rest\\Customfields\\CustomfieldsCollection',
            'service_name' => 'customfields',
        ),
        'API\\V1\\Rest\\File\\Controller' => array(
            'listener' => 'API\\V1\\Rest\\File\\FileResource',
            'route_name' => 'api.rest.file',
            'route_identifier_name' => 'file_id',
            'collection_name' => 'file',
            'entity_http_methods' => array(
                0 => 'GET',
                1 => 'PUT',
                2 => 'POST',
            ),
            'collection_http_methods' => array(
                0 => 'GET',
                1 => 'POST',
                2 => 'PUT',
            ),
            'collection_query_whitelist' => array(),
            'page_size' => 25,
            'page_size_param' => null,
            'entity_class' => 'API\\V1\\Rest\\File\\FileEntity',
            'collection_class' => 'API\\V1\\Rest\\File\\FileCollection',
            'service_name' => 'file',
        ),
    ),
    'api-tools-content-negotiation' => array(
        'controllers' => array(
            'API\\V1\\Rest\\Frequency\\Controller' => 'HalJson',
            'API\\V1\\Rest\\Event\\Controller' => 'HalJson',
            'API\\V1\\Rest\\Sector\\Controller' => 'HalJson',
            'API\\V1\\Rest\\Customfields\\Controller' => 'HalJson',
            'API\\V1\\Rest\\File\\Controller' => 'HalJson',
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
                3 => 'multipart/form-data',
            ),
            'API\\V1\\Rest\\Sector\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
            'API\\V1\\Rest\\Customfields\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/hal+json',
                2 => 'application/json',
            ),
            'API\\V1\\Rest\\File\\Controller' => array(
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
                2 => 'multipart/form-data',
            ),
            'API\\V1\\Rest\\Sector\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
            ),
            'API\\V1\\Rest\\Customfields\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
            ),
            'API\\V1\\Rest\\File\\Controller' => array(
                0 => 'application/vnd.api.v1+json',
                1 => 'application/json',
                2 => 'multipart/form-data',
            ),
        ),
    ),
    'api-tools-hal' => array(
        'metadata_map' => array(
            'API\\V1\\Rest\\Frequency\\FrequencyEntity' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.frequency',
                'route_identifier_name' => 'frequency_id',
                'hydrator' => 'Laminas\\Hydrator\\ArraySerializable',
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
                'hydrator' => 'Laminas\\Hydrator\\ArraySerializable',
            ),
            'API\\V1\\Rest\\Event\\EventEntity' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.event',
                'route_identifier_name' => 'event_id',
                'hydrator' => 'Laminas\\Hydrator\\ArraySerializable',
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
                'hydrator' => 'Laminas\\Hydrator\\ArraySerializable',
            ),
            'API\\V1\\Rest\\Sector\\SectorEntity' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.sector',
                'route_identifier_name' => 'sector_id',
                'hydrator' => 'Laminas\\Hydrator\\ArraySerializable',
            ),
            'API\\V1\\Rest\\Sector\\SectorCollection' => array(
                'entity_identifier_name' => 'name',
                'route_name' => 'api.rest.sector',
                'route_identifier_name' => 'sector_name',
                'is_collection' => true,
            ),
            'Application\\Entity\\Sector' => array(
                'entity_identifier_name' => 'name',
                'route_name' => 'api.rest.sector',
                'route_identifier_name' => 'sector_name',
                'hydrator' => 'Laminas\\Hydrator\\ArraySerializable',
            ),
            'API\\V1\\Rest\\Auth\\AuthEntity' => array(
                'entity_identifier_name' => '',
                'route_name' => 'api.rest.auth',
                'route_identifier_name' => '',
                'hydrator' => 'Laminas\\Hydrator\\ArraySerializableHydrator',
            ),
            'Core\\Entity\\Event' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.event',
                'route_identifier_name' => 'event_id',
                'hydrator' => 'Laminas\\Hydrator\\ArraySerializable',
            ),
            'API\\V1\\Rest\\Customfields\\CustomfieldsEntity' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.customfields',
                'route_identifier_name' => 'customfields_id',
                'hydrator' => 'Laminas\\Hydrator\\ArraySerializableHydrator',
            ),
            'API\\V1\\Rest\\Customfields\\CustomfieldsCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.customfields',
                'route_identifier_name' => 'customfields_id',
                'is_collection' => true,
            ),
            'API\\V1\\Rest\\File\\FileEntity' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.file',
                'route_identifier_name' => 'file_id',
                'hydrator' => 'Laminas\\Hydrator\\ArraySerializableHydrator',
            ),
            'API\\V1\\Rest\\File\\FileCollection' => array(
                'entity_identifier_name' => 'id',
                'route_name' => 'api.rest.file',
                'route_identifier_name' => 'file_id',
                'is_collection' => true,
            ),
        ),
    ),
    'api-tools-content-validation' => array(
        'API\\V1\\Rest\\Frequency\\Controller' => array(
            'input_filter' => 'API\\V1\\Rest\\Frequency\\Validator',
        ),
        'API\\V1\\Rest\\Event\\Controller' => array(
            'input_filter' => 'API\\V1\\Rest\\Event\\Validator',
        ),
        'API\\V1\\Rest\\File\\Controller' => array(
            'input_filter' => 'API\\V1\\Rest\\File\\Validator',
        ),
    ),
    'input_filter_specs' => array(
        'API\\V1\\Rest\\Frequencies\\Validator' => array(
            0 => array(
                'required' => true,
                'validators' => array(),
                'filters' => array(
                    0 => array(
                        'name' => 'Laminas\\Filter\\Boolean',
                        'options' => array(),
                    ),
                ),
                'name' => 'state',
            ),
        ),
        'API\\V1\\Rest\\Frequency\\Validator' => array(),
        'API\\V1\\Rest\\Event\\Validator' => array(),
        'API\\V1\\Rest\\File\\Validator' => array(
            0 => array(
                'required' => true,
                'validators' => array(),
                'filters' => array(
                    0 => array(
                        'name' => 'Laminas\\Filter\\File\\RenameUpload',
                        'options' => array(
                            'randomize' => '',
                        ),
                    ),
                ),
                'name' => 'file',
                'description' => 'Contains the file',
                'type' => 'Laminas\\InputFilter\\FileInput',
            ),
            1 => array(
                'required' => true,
                'validators' => array(),
                'filters' => array(),
                'name' => 'event_id',
                'description' => 'id of the event',
            ),
        ),
    ),
    'controllers' => array(
        'factories' => array(),
    ),
    'api-tools-rpc' => array(),
    'api-tools-mvc-auth' => array(
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
                    'POST' => true,
                    'PUT' => true,
                    'PATCH' => false,
                    'DELETE' => false,
                ),
                'entity' => array(
                    'GET' => true,
                    'POST' => true,
                    'PUT' => true,
                    'PATCH' => false,
                    'DELETE' => false,
                ),
            ),
            'API\\V1\\Rest\\Sector\\Controller' => array(
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
            'API\\V1\\Rest\\Customfields\\Controller' => array(
                'collection' => array(
                    'GET' => true,
                    'POST' => true,
                    'PUT' => false,
                    'PATCH' => false,
                    'DELETE' => false,
                ),
                'entity' => array(
                    'GET' => true,
                    'POST' => true,
                    'PUT' => false,
                    'PATCH' => false,
                    'DELETE' => false,
                ),
            ),
            'API\\V1\\Rest\\File\\Controller' => array(
                'collection' => array(
                    'GET' => true,
                    'POST' => true,
                    'PUT' => true,
                    'PATCH' => false,
                    'DELETE' => false,
                ),
                'entity' => array(
                    'GET' => true,
                    'POST' => true,
                    'PUT' => true,
                    'PATCH' => false,
                    'DELETE' => false,
                ),
            ),
        ),
    ),
);
