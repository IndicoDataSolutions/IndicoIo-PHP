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

    public function testSentimentHQReturnValueBetweenOneAndZero()
    {
        self::skipIfMissingCredentials();
        $data = IndicoIo::sentiment_hq('Excited to be alive!');
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
        $data = IndicoIo::text_tags('I want to move to New York City!');
        $keys_result = array_keys($data);
        $this->assertEquals(count($keys_result), 111);
    }

    public function testNamedEntities()
    {
        self::skipIfMissingCredentials();
        $data = IndicoIo::named_entities('I want to move to New York City!');

        $first_key_result = array_keys($data)[0];
        $values = $data[$first_key_result];
        $ne_keys = array_keys($values);
        $this->assertEquals($ne_keys, ['confidence', 'categories']);

        $categories_hash = $values['categories'];
        $categories = ['unknown', 'organization', 'location', 'person'];
        $this->assertEquals(array_keys($categories_hash), $categories);

        $this->assertGreaterThan(.999, array_sum(array_values($categories_hash)));
    }

    public function testKeywords()
    {
        self::skipIfMissingCredentials();
        $data = IndicoIo::keywords('This sentence contains three keywords ...');
        $keys_result = array_keys($data);
        $this->assertEquals(count($keys_result), 3);
    }

    public function testTwitterEngagement()
    {
        self::skipIfMissingCredentials();
        $examples = 'I want to move to New York City!';

        $data = IndicoIo::twitter_engagement($examples);
        $this->assertGreaterThan(0, $data);
        $this->assertGreaterThan($data, 1);
    }

    public function testFerWhenGivenTheRightParameters()
    {
        self::skipIfMissingCredentials();
        $keys_expected = array('Angry', 'Sad', 'Neutral', 'Surprise', 'Fear', 'Happy');
        $image = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'/data_test.json');

        $data = IndicoIo::fer($image);
        $keys_result = array_keys($data);

        sort($keys_result);
        sort($keys_expected);

        $this->assertEquals($keys_expected, $keys_result);
    }

    public function testContentFilterWhenGivenTheRightParameters()
    {
        self::skipIfMissingCredentials();

        $image = file_get_contents(dirname(__FILE__) .DIRECTORY_SEPARATOR.'/data_test.json');
        $data = IndicoIo::content_filter($image);

        $this->assertGreaterThan(-0.0000001, $data);
        $this->assertLessThan(1.0000001, $data);
        $this->assertEquals(gettype($data), "double");

    }

    public function testFacialFeaturesWhenGivenTheRightParameters()
    {
        self::skipIfMissingCredentials();
        $image = file_get_contents(dirname(__FILE__) .DIRECTORY_SEPARATOR.'/data_test.json');
        $data = IndicoIo::facial_features($image);

        $this->assertEquals(count($data), 48);
    }

    public function testImageFeaturesWhenGivenTheRightParameters()
    {
        self::skipIfMissingCredentials();
        $image = file_get_contents(dirname(__FILE__) .DIRECTORY_SEPARATOR.'/data_test.json');
        $data = IndicoIo::image_features($image);

        $this->assertEquals(count($data), 2048);
    }

    public function testExplicitAuthArgument()
    {
        self::skipIfMissingEnvironmentVars();
        $examples = array('worst day ever', 'best day ever');
        $api_key = getenv("INDICO_API_KEY");
        $data = IndicoIo::sentiment($examples, array("api_key"=>$api_key));

        $this->assertEquals(count($data), count($examples));
        $this->assertInternalType('array', $data);
        $this->assertInternalType('float', $data[0]);
    }

    public function testBatchPolitical()
    {
        self::skipIfMissingCredentials();
        $keys_expected = array('Libertarian', 'Liberal', 'Green', 'Conservative');
        $examples = array('save the whales', 'cut taxes');
        $data = IndicoIo::political($examples);

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
        $data = IndicoIo::sentiment_hq($examples);

        $this->assertEquals(count($data), count($examples));
        $this->assertInternalType('array', $data);
        $this->assertInternalType('float', $data[0]);
    }

    public function testBatchSentimentHQ()
    {
        self::skipIfMissingCredentials();
        $examples = array('worst day ever', 'best day ever');
        $data = IndicoIo::sentiment($examples);

        $this->assertEquals(count($data), count($examples));
        $this->assertInternalType('array', $data);
        $this->assertInternalType('float', $data[0]);
    }

    public function testBatchLanguage()
    {
        self::skipIfMissingCredentials();
        $examples = array('Clearly an english sentence.', 'Hablas espanol?');
        $data = IndicoIo::language($examples);
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
        $data = IndicoIo::text_tags($examples);
        $this->assertEquals(count($data), count($examples));

        $datapoint = $data[0];
        $keys_result = array_keys($datapoint);
        $this->assertEquals(count($keys_result), 111);
    }

    public function testBatchNamedEntities()
    {
        self::skipIfMissingCredentials();
        $examples = array(
            'I want to move to New York City!',
            'Do you prefer Gandalf the Grey or Gandalf the White?'
        );
        $data = IndicoIo::named_entities($examples);
        $this->assertEquals(count($data), count($examples));

        $datapoint = $data[0];
        $first_key_result = array_keys($datapoint)[0];
        $values = $datapoint[$first_key_result];
        $ne_keys = array_keys($values);
        $this->assertEquals($ne_keys, ['confidence', 'categories']);

        $categories_hash = $values['categories'];
        $categories = ['unknown', 'organization', 'location', 'person'];
        $this->assertEquals(array_keys($categories_hash), $categories);

        $this->assertGreaterThan(.999, array_sum(array_values($categories_hash)));
    }

    public function testBatchTwitterEngagement()
    {
        self::skipIfMissingCredentials();
        $examples = array(
            'I want to move to New York City!',
            'Do you prefer Gandalf the Grey or Gandalf the White?'
        );

        $data = IndicoIo::twitter_engagement($examples);
        $this->assertEquals(count($data), count($examples));

        $this->assertGreaterThan(0, $data[0]);
        $this->assertGreaterThan($data[0], 1);
    }

    public function testBatchKeywords()
    {
        self::skipIfMissingCredentials();
        $examples = array(
            'This sentence contains three keywords',
            'We are in for a windy Thursday and a rainy Friday'
        );
        $data = IndicoIo::keywords($examples);
        $this->assertEquals(count($data), count($examples));

        $datapoint = $data[0];
        $keys_result = array_keys($datapoint);
        $this->assertEquals(count($keys_result), 3);
    }

    public function testBatchFer()
    {
        self::skipIfMissingCredentials();
        $keys_expected = array('Angry', 'Sad', 'Neutral', 'Surprise', 'Fear', 'Happy');
        $image = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'/data_test.json');

        $examples = array($image, $image);

        $data = IndicoIo::fer($examples);
        $this->assertEquals(count($data), count($examples));

        $datapoint = $data[0];
        $keys_result = array_keys($datapoint);

        sort($keys_result);
        sort($keys_expected);

        $this->assertEquals($keys_expected, $keys_result);
    }

    public function testBatchContentFilter()
    {
        self::skipIfMissingCredentials();
        $image = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'/data_test.json');

        $examples = array($image, $image);

        $data = IndicoIo::content_filter($examples);
        $this->assertEquals(count($data), count($examples));

        $datapoint = $data[0];

        $this->assertEquals(gettype($data), "array");
        $this->assertEquals(gettype($datapoint), "double");
        $this->assertGreaterThan(-0.0000001, $datapoint);
        $this->assertLessThan(1.0000001, $datapoint);
    }

    public function testBatchFacialFeatures()
    {
        self::skipIfMissingCredentials();
        $image = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'/data_test.json');

        $examples = array($image, $image);
        $data = IndicoIo::facial_features($examples);
        $this->assertEquals(count($data), count($examples));

        $datapoint = $data[0];
        $this->assertEquals(count($datapoint), 48);
    }

    public function testBatchImageFeatures()
    {
        self::skipIfMissingCredentials();
        $image = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'/data_test.json');

        $examples = array($image, $image);
        $data = IndicoIo::image_features($examples);
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
        $data = IndicoIo::predict_text(array("Excited to be alive!", "sadness"), array("apis"=>array("sentiment", "political")));
        $this->assertGreaterThan(0, $data["sentiment"][0]);
        $this->assertGreaterThan($data["sentiment"][0], 1);
        $this->assertGreaterThan(.5, $data["sentiment"][1]);
    }


    public function testPredictImage()
    {
        self::skipIfMissingCredentials();
        $image = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'/data_test.json');

        $data = IndicoIo::predict_image($image, array("apis"=>array("image_features")));

        $this->assertEquals(count($data["image_features"]), 2048);
    }

    public function testBatchPredictImage()
    {
        self::skipIfMissingCredentials();
        $image = file_get_contents(dirname(__FILE__).DIRECTORY_SEPARATOR.'/data_test.json');

        $examples = array($image, $image);
        $data = IndicoIo::predict_image($examples, array("apis"=> array("image_features")));

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
