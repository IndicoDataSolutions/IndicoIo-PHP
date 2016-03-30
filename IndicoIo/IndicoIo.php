<?php

namespace IndicoIo;
use Exception;
use Utils\Multi as Multi;
use Utils\Image as Image;
use Utils\ImageException as ImageException;
use Configure\Configure as Configure;

require_once("Configure.php");
require_once("Utils.php");

/**
* Simple PHP wrapper for Indico
*/
class IndicoIo
{
	public static $config;
	public static $TEXT_APIS = array("sentiment", "sentimenthq", "named_entities", "text_tags", "language", "political", "keywords", "twitter_engagement", "personality");
	public static $IMAGE_APIS = array("fer", "image_features", "image_recognition", "facial_features", "content_filter");

	protected static function api_url($cloud = false, $service, $batch = false, $method = false, $api_key, $params = array()) {
		$root_url = self::$config['default_host'];
		if ($cloud) {
			$root_url = "https://$cloud.indico.domains";
		}
		if (!$api_key) {
			throw new Exception("A valid API key must be provided.");
		}

		$url = "$root_url/$service";
		if ($batch) {
			$url = $url . "/batch";
		}

		if ($method) {
			$url = $url . "/" . $method;
		}

		if (!empty($params)) {
			$url = $url . "?";
		}

		$url = $url . http_build_query($params);
		return $url;
	}


	public static function political($text, $params=array())
	{
		return self::_callService($text, 'political', 'predict', $params);
	}

    public static function emotion($text, $params=array())
    {
        return self::_callService($text, 'emotion', 'predict', $params);
    }

	public static function sentiment($text, $params=array())
	{
		return self::_callService($text, 'sentiment', 'predict', $params);
	}

	public static function sentiment_hq($text, $params=array())
	{
        return self::_callService($text, 'sentimenthq', 'predict', $params);
	}

	public static function language($text, $params=array())
	{
		return self::_callService($text, 'language', $params);
	}


	public static function text_tags($text, $params=array())
	{
		return self::_callService($text, 'texttags', 'predict', $params);
	}


	public static function fer($image, $params=array())
	{
		$size = array_key_exists("detect", $params) && $params["detect"] ? false : 48;
		$image = Image::processImage($image, $size, false);
		return self::_callService($image, 'fer', 'predict', $params);
	}

	public static function keywords($text, $params=array())
	{
		if (!array_key_exists('v', $params) || !array_key_exists('version', $params)) {
			$params['version'] = 2;
		}

		if (array_key_exists("language", $params) && $params["language"] != "english") {
			$params["version"] = 1;
		}

		return self::_callService($text, 'keywords', 'predict', $params);
	}

	public static function named_entities($text, $params=array())
	{
		return self::_callService($text, 'namedentities', 'predict', $params);
	}

	public static function twitter_engagement($text, $params=array())
	{
		return self::_callService($text, 'twitterengagement', 'predict', $params);
	}

    public static function people($text, $params=array())
    {
        return self::_callService($text, 'people', 'predict', $params);
    }

    public static function places($text, $params=array())
    {
        return self::_callService($text, 'places', 'predict', $params);
    }

    public static function organizations($text, $params=array())
    {
        return self::_callService($text, 'organizations', 'predict', $params);
    }

    public static function relevance($text, $queries, $params=array())
    {
        $params['queries'] = $queries;
        $params['synonyms'] = false;
        return self::_callService($text, 'relevance', 'predict', $params);
    }

    public static function text_features($text, $params=array())
    {
        $params['synonyms'] = false;
        return self::_callService($text, 'textfeatures', 'predict', $params);
    }

	public static function intersections($input, $params=array())
	{
		$apis = self::get($params, "apis");
		if (is_array($input) && array_keys($input) !== range(0, count($input) - 1)) {
			$diff = array_diff(array_keys($input), $apis);
			if (!empty($diff)) {
				trigger_error(
					"The `intersections` function expects the input to have the same keys as what is provided in `apis`",
					E_USER_WARNING
				);
			}
		}
		$converted_apis = Multi::filterApis($apis, self::$TEXT_APIS);
		$params["apis"] = $converted_apis;
		return self::_callService($input, 'apis/intersections', false, $params);
	}


	public static function facial_features($image, $params=array())
	{
		$image = Image::processImage($image, 64, false);
		return self::_callService($image, 'facialfeatures', 'predict', $params);
	}

	public static function image_features($image, $params=array())
	{
		$image = Image::processImage($image, 512, true);
		if (!array_key_exists('v', $params) || !array_key_exists('version', $params)){
			$params['version'] = 3;
		}
		return self::_callService($image, 'imagefeatures', 'predict', $params);
	}

	public static function image_recognition($image, $params=array())
	{
		$image = Image::processImage($image, 144, true);
		return self::_callService($image, 'imagerecognition', 'predict', $params);
	}

	public static function content_filter($image, $params=array())
	{
		$image = Image::processImage($image, 128, true);
		return self::_callService($image, 'contentfiltering', 'predict', $params);
	}

	public static function facial_localization($image, $params=array())
	{
		$image = Image::processImage($image, false, false);
		return self::_callService($image, 'faciallocalization', 'predict', $params);
	}

    public static function personality($text, $params=array())
    {
        return self::_callService($text, 'personality', 'predict', $params);
    }

    public static function personas($text, $params=array())
    {
        $params['persona'] = True;
        return self::_callService($text, 'personality', 'predict', $params);
    }

	# Multi API Calls
	public static function analyze_text($text, $params=array())
	{
		$apis = self::get($params, "apis");
		$converted_apis = Multi::filterApis($apis, self::$TEXT_APIS);
		$params["apis"] = $converted_apis;
		$results = self::_callService($text, "apis/multiapi", false, $params);
		return Multi::convertResults($results, $apis);
	}

	public static function analyze_image($image, $params=array())
	{
		$apis = self::get($params, "apis");
		$converted_apis = Multi::filterApis($apis, self::$IMAGE_APIS);
		$params["apis"] = $converted_apis;
		$results = self::_callService($image, "apis/multiapi", false, $params);
		return Multi::convertResults($results, $apis);
	}

	public static function _callService($data, $service, $method, $params = array())
	{
		# Load from configuration array if present
		$api_key = self::get($params, 'api_key');
		$cloud = self::get($params, "cloud");
		$batch = gettype($data) == "array";

		# Override $batch for custom API addData method
		if ($method == 'add_data' && !self::get($params, "batch")) {
			$batch = False;
		}

		$apis = self::get($params, "apis");
		$version = self::get($params, "version");
		unset($params["apis"]);
		unset($params["version"]);
		unset($params["batch"]);

		# Set up Url Paramters
		$url_params = array();

		# apis is already an imploded string here. might want to move that logic here.
		if ($apis) {
			$url_params["apis"] = $apis;
		}

		if ($version) {
			$url_params["version"] = $version;
		}

		# Set up Request
		$query_url = self::api_url($cloud, $service, $batch, $method, $api_key, $url_params);
		$json_data = json_encode(array_merge(array('data' => $data), $params), JSON_NUMERIC_CHECK);
		$ch = curl_init($query_url);
		curl_setopt($ch, CURLOPT_HEADER, true);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($json_data),
			'client-lib: php',
			'version-number: 0.2.0',
			'X-ApiKey: ' . $api_key
		));

		$response = curl_exec($ch);

		$header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
		$headers = mb_substr($response, 0, $header_size);
		$result = mb_substr($response, $header_size);

		$headers = explode("\n", $headers);
		foreach($headers as $header) {
		    if (stripos($header, 'x-warning:') !== false) {
		    	list ($key, $value) = explode(':', $header, 2);
		        trigger_error($value, E_USER_WARNING);
		    }
		}

		curl_close($ch);
		$parsed = json_decode($result, $assoc = true);
		if (array_key_exists('results', $parsed)) {
			return $parsed['results'];
		} else if (array_key_exists('error', $parsed)) {
			throw new Exception($parsed['error']);
		} else {
			throw new Exception($parsed);
		}
	}

	static function get(&$array, $key) {
		if (array_key_exists($key, $array)) {
			$value = $array[$key];
		} elseif (array_key_exists($key, self::$config)) {
			$value = self::$config[$key];
		} else {
			$value = False;
		}

		unset($array[$key]);
		return $value;
	}

	public static function collections($params = array()) {
		return self::_callService(False, 'custom', 'collections', $params);
	}
}

class Collection
{
    var $name;
    var $domain;

    function __construct($name, $domain=NULL)
    {
        $this->name = $name;
        $this->domain = $domain;
    }

    function addData($data, $params=array()) {
     	$params['collection'] = $this->name;
     	$params['domain'] = array_key_exists("domain", $params) ? $params["domain"] : $this->domain;
     	if (gettype($data[0]) != 'array') {
     		$params['batch'] = False;
     		try {
     			$data[0] = Image::processImage($data[0], 512, true);
     		} catch (ImageException $e) {}
     	} else {
     		$params['batch'] = True;
     		try {
     			$x = array();
     			$y = array();
     			foreach ($data as $pair) {
     				array_push($x, $pair[0]);
     				array_push($y, $pair[1]);
     			}
     			$x = Image::processImage($x, 512, true);
                // equivalent to python's zip(x, y)
     			$data = array_map(NULL, $x, $y);
     		} catch (ImageException $e) {}
     	}
     	return IndicoIo::_callService($data, 'custom', 'add_data', $params);
    }

    function predict($data, $params=array()) {
		$params['domain'] = array_key_exists("domain", $params) ? $params["domain"] : $this->domain;
    	$params['collection'] = $this->name;
    	try {
    		$data = Image::processImage($data, 512, true);
    	} catch (ImageException $e) {}
    	return IndicoIo::_callService($data, 'custom', 'predict', $params);
    }

    function removeExample($data, $params=array()) {
    	$params['collection'] = $this->name;
    	try {
    		$data = Image::processImage($data, 512, true);
    	} catch (ImageException $e) {}
    	return IndicoIo::_callService($data, 'custom', 'remove_example', $params);
    }

    function train($params=array()) {
    	$params['collection'] = $this->name;
    	return IndicoIo::_callService(NULL, 'custom', 'train', $params);
    }


    function info($params=array()) {
    	return IndicoIo::collections()[$this->name];
    }

    function wait($interval=1, $params=array()) {
    	while (TRUE) {
				$status = $this->info()['status'];
				if ($status == "ready") {
					break;
				} else if ($status != "training") {
					trigger_error(
						"The `collection` training has ended with the failure: " + $status,
						E_USER_WARNING
					);
					break;
				}
    		sleep($interval);
    	}
    }

   	function clear($params=array()) {
    	$params['collection'] = $this->name;
    	return IndicoIo::_callService(NULL, 'custom', 'clear_collection', $params);
    }
}


IndicoIo::$config = Configure::loadConfiguration();
