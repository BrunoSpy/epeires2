<?php
namespace ApplicationTest\Controller;

use Zend\Test\PHPUnit\Controller\AbstractHttpControllerTestCase;

class EventsControllerTest extends AbstractHttpControllerTestCase
{

    public function setUp()
    {
        $this->setApplicationConfig(include 'config' . DIRECTORY_SEPARATOR . 'application.config.php');
        parent::setUp();
    }

    public function testIndexActionCanBeAccessed()
    {
             
        $this->dispatch('/events');
        $this->assertResponseStatusCode(200);
        
        $this->assertModuleName('Application');
        $this->assertControllerName('Application\Controller\Events');
        $this->assertControllerClass('EventsController');
        $this->assertMatchedRouteName('application');
    }
}