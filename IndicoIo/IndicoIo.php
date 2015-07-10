<?php

namespace IndicoIo;
use Exception;
use Utils\Multi as Multi;
use Configure\Configure as Configure;

require_once("Configure.php");
require_once("Utils.php");

/**
* Simple PHP wrapper for Indico
*/
class IndicoIo
{
	public static $config;
	public static $TEXT_APIS = array("sentiment", "text_tags", "language", "political");
	public static $IMAGE_APIS = array("fer", "image_features", "facial_features");

	protected static function api_url($cloud = false, $service, $batch = false, $api_key, $params = array()) {
		if ($cloud) {
			$root_url = "http://$cloud.indico.domains";
		}
		if (!$api_key) {
			throw new Exception("A valid API key must be provided.");
		}
		$root_url = self::$config['default_host'];
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
		$params["batch"] = true;
		return self::_callService($text, 'political', $params);
	}

	public static function sentiment($text, $params=array())
	{
		return self::_callService($text, 'sentiment', $params);
	}

	public static function batch_sentiment($text, $params=array())
	{
		$params["batch"] = true;
		return self::_callService($text, 'sentiment', $params);
	}

	public static function posneg($text, $params=array())
	{
		return self::sentiment($text, $params);
	}

	public static function batch_posneg($text, $params=array())
	{
		$params["batch"] = true;
		return self::sentiment($text, $params);
	}

	public static function language($text, $params=array())
	{
		return self::_callService($text, 'language', $params);
	}

	public static function batch_language($text, $params=array())
	{
		$params["batch"] = true;
		return self::_callService($text, 'language', $params);
	}

	public static function text_tags($text, $params=array())
	{
		return self::_callService($text, 'texttags', $params);
	}

	public static function batch_text_tags($text, $params=array())
	{
		$params["batch"] = true;
		return self::_callService($text, 'texttags', $params);
	}

	public static function fer($image, $params=array())
	{
		return self::_callService($image, 'fer', $params);
	}

	public static function batch_fer($images, $params=array())
	{
		$params["batch"] = true;
		return self::_callService($images, 'fer', $params);
	}

	public static function content_filtering($image, $params=array())
	{
		return self::_callService($image, 'contentfiltering', $params);
	}

	public static function batch_content_filtering($image, $params=array())
	{
		$params["batch"] = true;
		return self::_callService($image, 'contentfiltering', $params);
	}

	public static function facial_features($image, $params=array())
	{
		return self::_callService($image, 'facialfeatures', $params);
	}

	public static function batch_facial_features($images, $params=array())
	{
		$params["batch"] = true;
		return self::_callService($images, 'facialfeatures', $params);
	}

	public static function image_features($image, $params=array())
	{
		return self::_callService($image, 'imagefeatures', $params);
	}

	public static function batch_image_features($images, $params=array())
	{
		$params["batch"] = true;
		return self::_callService($images, 'imagefeatures', $params);
	}

	# Multi API Calls
	public static function predict_text($text, $params=array())
	{
		$apis = self::get($params, "apis");
		$converted_apis = Multi::filterApis($apis, self::$TEXT_APIS);
		$params["apis"] = $converted_apis;
		$results = self::_callService($text, "apis", $params);
		return Multi::convertResults($results, $apis);
	}

	public static function batch_predict_text($text, $params=array())
	{
		$apis = self::get($params, "apis");
		$converted_apis = Multi::filterApis($apis, self::$TEXT_APIS);
		$params["apis"] = $converted_apis;
		$params["batch"] = true;
		$results = self::_callService($text, "apis", $params);
		return Multi::convertResults($results, $apis);
	}

	public static function predict_image($image, $params=array())
	{
		$apis = self::get($params, "apis");
		$converted_apis = Multi::filterApis($apis, self::$IMAGE_APIS);
		$params["apis"] = $converted_apis;
		$results = self::_callService($image, "apis", $params);
		return Multi::convertResults($results, $apis);
	}

	public static function batch_predict_image($images, $params=array())
	{
		$apis = self::get($params, "apis");
		$converted_apis = Multi::filterApis($apis, self::$IMAGE_APIS);
		$params["apis"] = $converted_apis;
		$params["batch"] = true;
		$results = self::_callService($images, "apis", $params);
		return Multi::convertResults($results, $apis);
	}

	protected static function _callService($data, $service, $params = array())
	{
		# Load from configuration array if present
		$api_key = self::get($params, 'api_key');
		$cloud = self::get($params, "cloud");
		$batch = self::get($params, "batch");
		$apis = self::get($params, "apis");

		# Set up Url Paramters
		$url_params = array();
		if ($apis) {
			$url_params["apis"] = $apis;
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
