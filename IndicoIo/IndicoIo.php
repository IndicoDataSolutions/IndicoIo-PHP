<?php

namespace IndicoIo;
use Exception;
use Utils\Multi as Multi;
use Utils\Image as Image;
use Configure\Configure as Configure;

require_once("Configure.php");
require_once("Utils.php");

/**
* Simple PHP wrapper for Indico
*/
class IndicoIo
{
	public static $config;
	public static $TEXT_APIS = array("sentiment", "sentimenthq", "named_entities", "text_tags", "language", "political", "keywords", "twitter_engagement");
	public static $IMAGE_APIS = array("fer", "image_features", "image_recognition", "facial_features", "content_filter");

	protected static function api_url($cloud = false, $service, $batch = false, $api_key, $params = array()) {
		$root_url = self::$config['default_host'];
		if ($cloud) {
			$root_url = "http://$cloud.indico.domains";
		}
		if (!$api_key) {
			throw new Exception("A valid API key must be provided.");
		}
		$url = "$root_url/$service";
		if ($batch) {
			$url = $url . "/batch";
		}

		$url = $url . "?key=" . $api_key;

		foreach ($params as $key => $value) {
			$url = $url . "&" . $key . "=" . $value;
		}

		return $url;
	}


	public static function political($text, $params=array())
	{
		return self::_callService($text, 'political', $params);
	}

	public static function batch_political($text, $params=array())
	{
		trigger_error(
			"The `batch_political` function will be deprecated in the next major upgrade." .
			"Please call `political` instead with the same arguments",
			E_USER_WARNING
		);
		return self::political($text, $params);
	}


	public static function sentiment($text, $params=array())
	{
		return self::_callService($text, 'sentiment', $params);
	}

	public static function batch_sentiment($text, $params=array())
	{
		trigger_error(
			"The `batch_sentiment` function will be deprecated in the next major upgrade." .
			"Please call `sentiment` instead with the same arguments",
			E_USER_WARNING
		);
		return self::sentiment($text, $params);
	}


	public static function sentiment_hq($text, $params=array())
	{
        return self::_callService($text, 'sentimenthq', $params);
	}

	public static function batch_sentiment_hq($text, $params=array())
	{
		trigger_error(
			"The `batch_sentiment_hq` function will be deprecated in the next major upgrade." .
			"Please call `sentiment_hq` instead with the same arguments",
			E_USER_WARNING
		);
		return self::sentiment_hq($text, $params);
	}


	public static function language($text, $params=array())
	{
		return self::_callService($text, 'language', $params);
	}

	public static function batch_language($text, $params=array())
	{
		trigger_error(
			"The `batch_language` function will be deprecated in the next major upgrade." .
			"Please call `language` instead with the same arguments",
			E_USER_WARNING
		);
		return self::language($text, $params);
	}


	public static function text_tags($text, $params=array())
	{
		return self::_callService($text, 'texttags', $params);
	}

	public static function batch_text_tags($text, $params=array())
	{
		trigger_error(
			"The `batch_text_tags` function will be deprecated in the next major upgrade." .
			"Please call `text_tags` instead with the same arguments",
			E_USER_WARNING
		);
		return self::text_tags($text, $params);
	}


	public static function fer($image, $params=array())
	{
		$size = array_key_exists("detect", $params) && $params["detect"] ? false : 48;
		$image = Image::processImage($image, $size, false);
		return self::_callService($image, 'fer', $params);
	}

	public static function batch_fer($text, $params=array())
	{
		trigger_error(
			"The `batch_fer` function will be deprecated in the next major upgrade." .
			"Please call `fer` instead with the same arguments",
			E_USER_WARNING
		);
		return self::fer($text, $params);
	}


	public static function keywords($text, $params=array())
	{
		return self::_callService($text, 'keywords', $params);
	}

	public static function batch_keywords($text, $params=array())
	{
		trigger_error(
			"The `batch_keywords` function will be deprecated in the next major upgrade." .
			"Please call `keywords` instead with the same arguments",
			E_USER_WARNING
		);
		return self::keywords($text, $params);
	}


	public static function named_entities($text, $params=array())
	{
		return self::_callService($text, 'namedentities', $params);
	}

	public static function batch_named_entities($text, $params=array())
	{
		trigger_error(
			"The `batch_named_entities` function will be deprecated in the next major upgrade." .
			"Please call `named_entities` instead with the same arguments",
			E_USER_WARNING
		);
		return self::named_entities($text, $params);
	}


	public static function twitter_engagement($text, $params=array())
	{
		return self::_callService($text, 'twitterengagement', $params);
	}

	public static function batch_twitter_engagement($text, $params=array())
	{
		trigger_error(
			"The `batch_twitter_engagement` function will be deprecated in the next major upgrade." .
			"Please call `twitter_engagement` instead with the same arguments",
			E_USER_WARNING
		);
		return self::twitter_engagement($text, $params);
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
		return self::_callService($input, 'apis/intersections', $params);
	}


	public static function facial_features($image, $params=array())
	{
		$image = Image::processImage($image, 64, false);
		return self::_callService($image, 'facialfeatures', $params);
	}

	public static function batch_facial_features($image, $params=array())
	{
		trigger_error(
			"The `batch_facial_features` function will be deprecated in the next major upgrade." .
			"Please call `facial_features` instead with the same arguments",
			E_USER_WARNING
		);
		return self::facial_features($image, $params);
	}


	public static function image_features($image, $params=array())
	{
		$image = Image::processImage($image, 144, true);
		return self::_callService($image, 'imagefeatures', $params);
	}

	public static function image_recognition($image, $params=array())
	{
		$image = Image::processImage($image, 144, true);
		return self::_callService($image, 'imagerecognition', $params);
	}

	public static function batch_image_features($image, $params=array())
	{
		trigger_error(
			"The `batch_image_features` function will be deprecated in the next major upgrade." .
			"Please call `image_features` instead with the same arguments",
			E_USER_WARNING
		);
		return self::image_features($image, $params);
	}


	public static function content_filter($image, $params=array())
	{
		$image = Image::processImage($image, 128, true);
		return self::_callService($image, 'contentfiltering', $params);
	}

	public static function batch_content_filter($image, $params=array())
	{
		trigger_error(
			"The `batch_content_filter` function will be deprecated in the next major upgrade." .
			"Please call `content_filter` instead with the same arguments",
			E_USER_WARNING
		);
		return self::content_filter($image, $params);
	}

	public static function facial_localization($image, $params=array())
	{
		$image = Image::processImage($image, false, false);
		return self::_callService($image, 'faciallocalization', $params);
	}


	# Multi API Calls
	public static function analyze_text($text, $params=array())
	{
		$apis = self::get($params, "apis");
		$converted_apis = Multi::filterApis($apis, self::$TEXT_APIS);
		$params["apis"] = $converted_apis;
		$results = self::_callService($text, "apis/multiapi", $params);
		return Multi::convertResults($results, $apis);
	}
	public static function batch_analyze_text($text, $params=array())
	{
		trigger_error(
			"The `batch_analyze_text` function will be deprecated in the next major upgrade." .
			"Please call `analyze_text` instead with the same arguments",
			E_USER_WARNING
		);
		return self::analyze_text($text, $params);
	}


	public static function analyze_image($image, $params=array())
	{
		$apis = self::get($params, "apis");
		$converted_apis = Multi::filterApis($apis, self::$IMAGE_APIS);
		$params["apis"] = $converted_apis;
		$results = self::_callService($image, "apis/multiapi", $params);
		return Multi::convertResults($results, $apis);
	}
	public static function batch_analyze_image($image, $params=array())
	{
		trigger_error(
			"The `batch_analyze_image` function will be deprecated in the next major upgrade." .
			"Please call `analyze_image` instead with the same arguments",
			E_USER_WARNING
		);
		return self::analyze_image($image, $params);
	}


	protected static function _callService($data, $service, $params = array())
	{
		# Load from configuration array if present
		$api_key = self::get($params, 'api_key');
		$cloud = self::get($params, "cloud");
		$batch = gettype($data) == "array";
		$apis = self::get($params, "apis");
		$version = self::get($params, "version");

		# Set up Url Paramters
		$url_params = array();
		if ($apis) {
			$url_params["apis"] = $apis;
		}
		if ($version) {
			$url_params["version"] = $version;
		}

		# Set up Request
		$query_url = self::api_url($cloud, $service, $batch, $api_key, $url_params);
		$json_data = json_encode(array_merge(array('data' => $data), $params));
		$ch = curl_init($query_url);
		curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
		curl_setopt($ch, CURLOPT_POSTFIELDS, $json_data);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, array(
			'Content-Type: application/json',
			'Content-Length: ' . strlen($json_data),
			'client-lib: php',
				'version-number: 0.1.0'
		));

		$result = curl_exec($ch);
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
}


IndicoIo::$config = Configure::loadConfiguration();
