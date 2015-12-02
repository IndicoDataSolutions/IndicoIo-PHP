<?php

namespace Utils;
use \Eventviva\ImageResize;
class ImageException extends \Exception {}

Class Image
{
    public static function processImages($array_string, $size, $min_axis) {
        $array = array();
        foreach ($array_string as $string) {
            array_push($array, self::processImage($string, $size, $min_axis));
        }

        return $array;
    }

    public static function isValidURL($url) { 
        return filter_var($url, FILTER_VALIDATE_URL) !== false;
    }

    public static function processImage($string, $size, $min_axis) {
        if (gettype($string) == "array") {
            return self::processImages($string ,$size, $min_axis);
        }

        if (file_exists($string)) {
            return self::resizeImage(new ImageResize($string), $size, $min_axis);
        } else if (self::isValidURL($string)) {
            return $string; 
        } else {
            try {
                $image = ImageResize::createFromString(base64_decode($string));
                return self::resizeImage(
                    $image,
                    $size,
                    $min_axis
                );
            } catch (\Exception $e) {
                $msg = $e->getMessage();
                if (strpos($msg, "Could not read file") !== FALSE) {
                    throw new ImageException();
                } else {
                    throw $e;
                }
            }
        }
    }

    public static function resizeImage($image, $size, $min_axis) {
        // Check Aspect Ratio
        $ratio = ($image->getSourceWidth())/($image->getSourceHeight());
        if ($ratio >= 10 || $ratio <= .1) {
            echo "For best performance, we recommend images of apsect ratio less than 1:10.";
        }

        if ($min_axis) {
            $image -> resizeToBestFit($size, $size);
            return base64_encode($image);
        }
        if ($size) {
            $image -> resize($size, $size);
        }
        $image -> getImageAsString(IMAGETYPE_PNG, 4);
        return base64_encode($image);
    }
}

Class Multi
{
    public static function filterApis($apis, $accepted) {
        foreach ($apis as $api) {
            if (!in_array($api, $accepted)) {
                throw new Exception(
                    $api
                    + " is not an acceptable api name. Please use "
                    + implode(",", $accepted)
                );
            }
        }

        return implode(",", $apis);
    }

    public static function convertResults($results, $apis) {
        $converted_results = array();
        foreach ($apis as $api) {
            $response = $results[$api];
            if (array_key_exists("results", $response)) {
                $converted_results[$api] = $response["results"];
            } else {
                throw new Exception($api . " encountered an error: " . $response['error']);
            }
        }

        return $converted_results;
    }
}
