EpeiresApplication
=======================

Installation
------------
Install dependencies :
    php composer.phar update



Database Configuration
------------
Configure Doctrine driver in config/autoload/doctrine.local.php

Create the schema :
    
    vendor/bin/doctrine-module orm:schema-tool:create

