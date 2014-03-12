<?php
return array(
		//cache and minify only on production environments
	    'asset_manager' => array(
	    	'caching' => array(
    			'default' => array(
    				'cache'     => 'FilePath',
                	'options' => array(
               			'dir' => 'public',
               		),
    			),
    		),
	    	'filters' => array(
	    		'application/javascript' => array(
	    			array(
	    				'filter' => 'JSMin',
	    			),
	    		),
	   		),
    	),
);
