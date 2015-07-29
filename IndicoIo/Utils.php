<?php

namespace Utils;
use \Eventviva\ImageResize;

Class Image
{
    public static function processImages($array_string, $size, $min_axis) {
        $array = array();
        foreach ($array_string as $string) {
            array_push($array, self::processImage($string, $size, $min_axis));
        }

        return $array;
    }
    public static function processImage($string, $size, $min_axis) {
        if (gettype($string) == "array") {
            echo "Image input as an array will be deprecated. Please use filepath or base64 string";
            // Causes Execution Halt
            // trigger_error(
            //     "Image input as an array will be deprecated. Please use filepath or base64 string");
            return $string;
        }

        if (file_exists($string)) {
            return self::resizeImage(new ImageResize($string), $size, $min_axis);
        } else {
            return self::resizeImage(
                ImageResize::createFromString(base64_decode($string)),
                $size,
                $min_axis
            );
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

        $image -> resize($size, $size);
        $image -> getImageAsString(IMAGETYPE_PNG, 4);
        return base64_encode($image);
    }
}

Class Multi
{
    public static $MAP_APIS = array(
        "sentiment" => "sentiment",
        "text_tags" => "texttags",
        "language" => "language",
        "political" => "political",
        "fer" => "fer",
        "image_features" => "imagefeatures",
        "facial_features" => "facialfeatures",
        "twitter_engagement" => "twitterengagement",
        "named_entities" => "namedentities",
        "content_filter" => "contentfiltering"
    );

    public static function filterApis($apis, $accepted) {
        $converted_apis = array();

        foreach ($apis as $api) {
            if (!in_array($api, $accepted)) {
                throw new Exception(
                    $api
                    + " is not an acceptable api name. Please use "
                    + implode(",", $accepted)
                );
            }

            $converted_apis[] = self::$MAP_APIS[$api];
        }

        return implode(",", $converted_apis);
    }

    public static function convertResults($results, $apis) {
        $converted_results = array();
        foreach ($apis as $api) {
            $response = $results[self::$MAP_APIS[$api]];
            if (array_key_exists("results", $response)) {
                $converted_results[$api] = $response["results"];
            } else {
                throw new Exception($api . " encountered an error: " . $response['error']);
            }
        }

        return $converted_results;
    }
}
