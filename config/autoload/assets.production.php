<?php
return array(
	    'asset_manager' => array(
    		//only cache on production environments
	    	'caching' => array(
    			'default' => array(
    				'cache'     => 'FilePath',
                	'options' => array(
               			'dir' => 'public',
               		),
    			),
    		),
    	),
);
