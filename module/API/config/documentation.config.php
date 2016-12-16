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
);
