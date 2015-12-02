<?php

namespace IndicoIo\Test;
use \IndicoIo\IndicoIo as IndicoIo;
use \IndicoIo\Collection as Collection;

$collection_name = '__test_php__';

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    private function skipIfMissingCredentials()
    {
        if (!IndicoIo::$config['api_key']) {
            $this->markTestSkipped('No auth credentials provided, skipping batch tests...');
        }
    }

    protected function setUp() {
        self::skipIfMissingCredentials();
        $collection = new Collection($GLOBALS['collection_name']);
        if (array_key_exists($GLOBALS['collection_name'], IndicoIo::collections())) {
            $collection->clear();
        } 
    }

    public function testInitializeCollection()
    {
        self::skipIfMissingCredentials();
        $collection = new Collection($GLOBALS['collection_name']);
    }

    public function testListCollections()
    {
        self::skipIfMissingCredentials();
        $collection = new Collection($GLOBALS['collection_name']);
        $collection->addData(array(
            array('input 1', 'label 1')
        ));
        $collections = IndicoIo::collections();
        $this->assertArrayHasKey($GLOBALS['collection_name'], $collections);
    }


    public function testTrainPredict()
    {
        self::skipIfMissingCredentials();
        $collection = new Collection($GLOBALS['collection_name']);
        $collection->addData(array(
            array('input 1', 'label 1'),
            array('input 2', 'label 2'),
            array('input 3', 'label 3'),
            array('input 4', 'label 4')
        ));
        $collection->train();
        $collection->wait();
        $result = $collection->predict('input 1');
        $this->assertArrayHasKey('label 1', $result);
        $result = $collection->predict(array('input 1'))[0];
        $this->assertArrayHasKey('label 1', $result);
    }

    public function testAddSingle()
    {
        self::skipIfMissingCredentials();
        $collection = new Collection($GLOBALS['collection_name']);
        $collection->addData(array('input 1', 'label 1'));
    }

    public function testRemoveExample() 
    {
        self::skipIfMissingCredentials();        self::skipIfMissingCredentials();
        $collection = new Collection($GLOBALS['collection_name']);
        $collection->addData(array(
            array('input 1', 'label 1'),
            array('input 2', 'label 2'),
            array('input 3', 'label 3'),
            array('input 4', 'label 4')
        ));
        $collection->train();
        $collection->wait();
        $this->assertEquals($collection->info()['number_of_examples'], 4);
        $collection->removeExample('input 1');
        $collection->train();
        $collection->wait();
        $this->assertEquals($collection->info()['number_of_examples'], 3);
    }

    public function testClearCollection()
    {
        self::skipIfMissingCredentials();
        $collection = new Collection($GLOBALS['collection_name']);
        $collection->addData(array(
            array('input 1', 'label 1'),
            array('input 2', 'label 2'),
            array('input 3', 'label 3'),
            array('input 4', 'label 4')
        ));
        $this->assertArrayHasKey($GLOBALS['collection_name'], IndicoIo::collections());
        $collection->clear();
        $this->assertFalse(array_key_exists($GLOBALS['collection_name'], IndicoIo::collections()));
    }

    public function testTrainPredictImage()
    {
        self::skipIfMissingCredentials();
        $collection = new Collection($GLOBALS['collection_name']);
        $image = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'/data_test.json');
        $collection->addData(array(
            array('http://i.imgur.com/x4eMDNY.jpg', 'label 1'),
            array('http://i.imgur.com/x4eMDNY.jpg', 'label 2'),
            array('http://i.imgur.com/x4eMDNY.jpg', 'label 3'),
            array('http://i.imgur.com/x4eMDNY.jpg', 'label 4')
        ));
        $collection->addData(array(
            array($image, 'label 1'),
            array($image, 'label 2'),
        ));
        $collection->train();
        $collection->wait();
        $this->assertEquals('image', $collection->info()['input_type']);
        $result = $collection->predict('http://i.imgur.com/x4eMDNY.jpg');
        $this->assertArrayHasKey('label 1', $result);
        $result = $collection->predict(array('http://i.imgur.com/x4eMDNY.jpg'))[0];
        $this->assertArrayHasKey('label 1', $result);
    }

}