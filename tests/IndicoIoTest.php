<?php

namespace IndicoIo\Test;
use \IndicoIo\IndicoIo as IndicoIo;
use Configure\Configure as Configure;

class IndicoIoTest extends \PHPUnit_Framework_TestCase
{
    private function skipIfMissingCredentials()
    {
        if (!IndicoIo::$config['api_key']) {
            $this->markTestSkipped('No auth credentials provided, skipping batch tests...');
        }
    }

    private function skipIfMissingEnvironmentVars()
    {
        if (!getenv("INDICO_API_KEY")) {
            $this->markTestSkipped('No auth credentials provided, skipping batch tests...');
        }
    }

    public function testPoliticalWhenGivenTheRightParameters()
    {
        self::skipIfMissingCredentials();
        $keys_expected = array('Libertarian', 'Liberal', 'Green', 'Conservative');
        $data = IndicoIo::political('save the whales');
        $keys_result = array_keys($data);

        sort($keys_expected);
        sort($keys_result);

        $this->assertEquals($keys_expected, $keys_result);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Accepted datatypes: string
     */
    public function testPoliticalRaisesExceptionWhenGivenInteger()
    {
        self::skipIfMissingCredentials();
        $data_integer_request = IndicoIo::political(2);
    }

    /**
     * @expectedException Exception
     * @expectedExceptionMessage Accepted datatypes: string
     */
    public function testPoliticalRaisesExceptionWhenGivenBool()
    {
        self::skipIfMissingCredentials();
        $data_bool_request = IndicoIo::political(true);
    }

    public function testSentimentWhenGivenTheRightParameters()
    {
        self::skipIfMissingCredentials();
        $data = IndicoIo::sentiment('worst day ever');

        $this->assertInternalType('float', $data);
    }

    public function testSentimentReturnValueBetweenOneAndZero()
    {
        self::skipIfMissingCredentials();
        $data = IndicoIo::sentiment('Excited to be alive!');
        $this->assertGreaterThan(0, $data);
        $this->assertGreaterThan($data, 1);
    }

    public function testLanguageWhenGivenTheRightPrameters()
    {
        self::skipIfMissingCredentials();
        $data = IndicoIo::language('Clearly an english sentence.!');
        $keys_result = array_keys($data);
        $this->assertEquals(count($keys_result), 33);
    }

    public function testTextTags()
    {
        self::skipIfMissingCredentials();
        $data = IndicoIo::text_tags('On Monday, the president will be ...');
        $keys_result = array_keys($data);
        $this->assertEquals(count($keys_result), 111);
    }

    public function testFerWhenGivenTheRightParameters()
    {
        self::skipIfMissingCredentials();
        $keys_expected = array('Angry', 'Sad', 'Neutral', 'Surprise', 'Fear', 'Happy');
        $file_content =  file_get_contents(dirname(__FILE__) .DIRECTORY_SEPARATOR.'/data_test.json');
        $image = json_decode($file_content, true);
        $data = IndicoIo::fer($image);
        $keys_result = array_keys($data);

        sort($keys_result);
        sort($keys_expected);

        $this->assertEquals($keys_expected, $keys_result);
    }

    public function testContentFiltering()
    {
        self::skipIfMissingCredentials();
        $file_content =  file_get_contents(dirname(__FILE__) .DIRECTORY_SEPARATOR.'/data_test.json');
        $image = json_decode($file_content, true);
        $data = IndicoIo::content_filtering($image);

        $this->assertEquals(gettype($data), 'double');
    }

    public function testFacialFeaturesWhenGivenTheRightParameters()
    {
        self::skipIfMissingCredentials();
        $file_content =  file_get_contents(dirname(__FILE__) .DIRECTORY_SEPARATOR.'/data_test.json');
        $image = json_decode($file_content, true);
        $data = IndicoIo::facial_features($image);

        $this->assertEquals(count($data), 48);
    }

    public function testImageFeaturesWhenGivenTheRightParameters()
    {
        self::skipIfMissingCredentials();
        $file_content =  file_get_contents(dirname(__FILE__) .DIRECTORY_SEPARATOR.'/data_test.json');
        $image = json_decode($file_content, true);
        $data = IndicoIo::image_features($image);

        $this->assertEquals(count($data), 2048);
    }

    public function testExplicitAuthArgument() {
        self::skipIfMissingEnvironmentVars();
        $examples = array('worst day ever', 'best day ever');
        $api_key = getenv("INDICO_API_KEY");
        $data = IndicoIo::batch_sentiment($examples, array("api_key"=>$api_key));

        $this->assertEquals(count($data), count($examples));
        $this->assertInternalType('array', $data);
        $this->assertInternalType('float', $data[0]);
    }

    public function testBatchPolitical() {
        self::skipIfMissingCredentials();
        $keys_expected = array('Libertarian', 'Liberal', 'Green', 'Conservative');
        $examples = array('save the whales', 'cut taxes');
        $data = IndicoIo::batch_political($examples);

        $this->assertEquals(count($data), count($examples));

        $datapoint = $data[0];
        $keys_result = array_keys($datapoint);

        sort($keys_expected);
        sort($keys_result);

        $this->assertEquals($keys_expected, $keys_result);
    }

    public function testBatchSentiment()
    {
        self::skipIfMissingCredentials();
        $examples = array('worst day ever', 'best day ever');
        $data = IndicoIo::batch_sentiment($examples);

        $this->assertEquals(count($data), count($examples));
        $this->assertInternalType('array', $data);
        $this->assertInternalType('float', $data[0]);
    }

    public function testBatchLanguage()
    {
        self::skipIfMissingCredentials();
        $examples = array('Clearly an english sentence.', 'Hablas espanol?');
        $data = IndicoIo::batch_language($examples);
        $this->assertEquals(count($data), count($examples));

        $datapoint = $data[0];
        $keys_result = array_keys($datapoint);
        $this->assertEquals(count($keys_result), 33);
    }

    public function testBatchTextTags()
    {
        self::skipIfMissingCredentials();
        $examples = array(
            'On Monday, the president will be ...',
            'We are in for a windy Thursday and a rainy Friday'
        );
        $data = IndicoIo::batch_text_tags($examples);
        $this->assertEquals(count($data), count($examples));

        $datapoint = $data[0];
        $keys_result = array_keys($datapoint);
        $this->assertEquals(count($keys_result), 111);
    }

    public function testBatchFer()
    {
        self::skipIfMissingCredentials();
        $keys_expected = array('Angry', 'Sad', 'Neutral', 'Surprise', 'Fear', 'Happy');
        $file_content =  file_get_contents(dirname(__FILE__) .DIRECTORY_SEPARATOR.'/data_test.json');
        $image = json_decode($file_content, true);
        $examples = array($image, $image);

        $data = IndicoIo::batch_fer($examples);
        $this->assertEquals(count($data), count($examples));

        $datapoint = $data[0];
        $keys_result = array_keys($datapoint);

        sort($keys_result);
        sort($keys_expected);

        $this->assertEquals($keys_expected, $keys_result);
    }

    public function testBatchContentFiltering()
    {
        self::skipIfMissingCredentials();
        $file_content =  file_get_contents(dirname(__FILE__) .DIRECTORY_SEPARATOR.'/data_test.json');
        $image = json_decode($file_content, true);
        $examples = array($image, $image);
        $data = IndicoIo::batch_content_filtering($examples);

        $this->assertEquals(count($data), count($examples));

        $datapoint = $data[0];
        $this->assertEquals(gettype($datapoint), 'double');
    }
    public function testBatchFacialFeatures()
    {
        self::skipIfMissingCredentials();
        $file_content =  file_get_contents(dirname(__FILE__) .DIRECTORY_SEPARATOR.'/data_test.json');
        $image = json_decode($file_content, true);
        $examples = array($image, $image);
        $data = IndicoIo::batch_facial_features($examples);
        $this->assertEquals(count($data), count($examples));

        $datapoint = $data[0];
        $this->assertEquals(count($datapoint), 48);
    }

    public function testBatchImageFeatures()
    {
        self::skipIfMissingCredentials();
        $file_content =  file_get_contents(dirname(__FILE__) .DIRECTORY_SEPARATOR.'/data_test.json');
        $image = json_decode($file_content, true);
        $examples = array($image, $image);
        $data = IndicoIo::batch_image_features($examples);
        $this->assertEquals(count($data), count($examples));

        $datapoint = $data[0];
        $this->assertEquals(count($datapoint), 2048);
    }


    public function testPredictText()
    {
        self::skipIfMissingCredentials();
        $data = IndicoIo::predict_text('Excited to be alive!', array("apis"=>array("sentiment", "political")));
        $this->assertGreaterThan(0, $data["sentiment"]);
        $this->assertGreaterThan($data["sentiment"], 1);
    }

    public function testBatchPredictText()
    {
        self::skipIfMissingCredentials();
        $data = IndicoIo::batch_predict_text(array("Excited to be alive!", "sadness"), array("apis"=>array("sentiment", "political")));
        $this->assertGreaterThan(0, $data["sentiment"][0]);
        $this->assertGreaterThan($data["sentiment"][0], 1);
        $this->assertGreaterThan(.5, $data["sentiment"][1]);
    }


    public function testPredictImage()
    {
        self::skipIfMissingCredentials();
        $file_content =  file_get_contents(dirname(__FILE__) .DIRECTORY_SEPARATOR.'/data_test.json');
        $image = json_decode($file_content, true);
        $data = IndicoIo::predict_image($image, array("apis"=>array("image_features")));

        $this->assertEquals(count($data["image_features"]), 2048);
    }

    public function testBatchPredictImage()
    {
        self::skipIfMissingCredentials();
        $file_content =  file_get_contents(dirname(__FILE__) .DIRECTORY_SEPARATOR.'/data_test.json');
        $image = json_decode($file_content, true);
        $examples = array($image, $image);
        $data = IndicoIo::batch_predict_image($examples, array("apis"=> array("image_features")));

        $this->assertEquals(count($data["image_features"]), count($examples));
        $datapoint = $data["image_features"][0];
        $this->assertEquals(count($datapoint), 2048);
    }

    public function testConfigureFromEnvironmentVariables()
    {
        # store previous settings to reset later
        $prev_api_key = getenv("INDICO_API_KEY");
        $api_key = "env-api-key";
        putenv("INDICO_API_KEY=$api_key");

        $config = Configure::loadConfiguration();
        $this->assertEquals($config['api_key'], $api_key);

        # reset to previous configuration
        putenv("INDICO_API_KEY=$prev_api_key");
    }

    public function testConfigureFromConfigFile()
    {
        $filename = tempnam("./", 'tmp');
        $handle = fopen($filename, "w");
        $api_key = "file-api-key";
        fwrite($handle, "[auth]\napi_key=$api_key");
        fclose($handle);

        $config = IndicoIo::$config;
        $config = Configure::loadConfigFile($filename, $config);
        $this->assertEquals($config['api_key'], $api_key);

        # cleanup
        unlink($filename);
    }

    public function testEnvironmentVariablesTakePrecedence()
    {
        # store previous settings to reset later
        $prev_api_key = getenv("INDICO_API_KEY");

        $env_api_key = "env-api-key";
        putenv("INDICO_API_KEY=$env_api_key");

        $filename = tempnam("./", 'tmp');
        $handle = fopen($filename, "w");
        $api_key = "file-api-key";
        fwrite($handle, "[auth]\napi_key=$api_key");
        fclose($handle);

        $config = IndicoIo::$config;
        $config = Configure::loadConfigFile($filename, $config);
        $config = Configure::loadEnvironmentVars($config);
        $this->assertEquals($config['api_key'], $env_api_key);

        # reset to previous configuration
        putenv("INDICO_API_KEY=$prev_api_key");
        unlink($filename);
    }
}
