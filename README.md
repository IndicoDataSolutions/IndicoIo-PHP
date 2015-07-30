[![Build Status](https://travis-ci.org/IndicoDataSolutions/IndicoIo-PHP.svg?branch=master)](https://travis-ci.org/IndicoDataSolutions/IndicoIo-PHP)

IndicoIo-php
=========

PHP Rest Api Wrapper for IndicoIo



Installation
--------------

1. Create (if not done yet) composer.json file on your project directory.

2. Add this to the file :

```json
{
	"require":{
		"indicoio/indicoio-php": "*"
	}
}

```
3. Run this command :


```sh
composer install
```

Documentation
------------
Found [here](http://indico.readme.io/v1.0/docs)

Usage
----

```php

require(__DIR__ . '/vendor/autoload.php');
use \IndicoIo\IndicoIo as IndicoIo;

print_r(IndicoIo::sentiment('Great food -- would recommend!'));

=> Array ( [Sentiment] => 0.86122900137512 )

print_r(IndicoIo::political('Free market economy'));

=> Array (
    [Libertarian] => 0.73124401007546
    [Liberal] => 0.027743022226388
    [Green] => 0.045547383056822
    [Conservative] => 0.19546558464133
)

print_r(IndicoIo::language('una giornata molto buona auguro') );

=> Array ( [Swedish] => 0.00011552035349677 [Vietnamese] => 0.0010439073406634 [Romanian] => 4.4859977761836E-6 [Dutch] => 4.5674707699322E-5 [Korean] => 5.3119192163625E-5 [Danish] => 9.7697777765179E-6 [Indonesian] => 4.0203025867581E-6 [Latin] => 0.0058764961008608 [Hungarian] => 5.6426058452007E-5 [Persian (Farsi)] => 6.2600437029341E-6 [Lithuanian] => 0.0039609506743307 [French] => 2.0399931496277E-6 [Norwegian] => 0.00015239304276317 [Russian] => 0.00013775439666658 [Thai] => 3.4066036425308E-5 [Finnish] => 8.1624733519993E-5 [Hebrew] => 5.8164830189384E-6 [Bulgarian] => 0.0034069103460234 [Turkish] => 3.8579592818398E-5 [Greek] => 0.00010709230008665 [Tagalog] => 0.00015189161475784 [English] => 0.00011645340410667 [Arabic] => 1.4140934271487E-5 [Italian] => 0.91248953273899 [Portuguese] => 6.6430192271289E-6 [Chinese] => 0.0001651405636031 [German] => 3.4131505928479E-5 [Japanese] => 7.2165176983677E-7 [Czech] => 2.0120301352267E-5 [Slovak] => 0.0002684897882399 [Spanish] => 0.0056873313305499 [Polish] => 0.00037255793355163 [Esperanto] => 0.065529937739673 )

print_r(IndicoIo::text_tags('This coconut green tea is amazing!'));

=> Array ( [food]: 0.3713687833244494, [cars]: 0.0037924017632370586, ...)


```

Batch API
----------------

```php
IndicoIo::sentiment(array('Text to analyze', 'More text'), 'example-api-key')
```

API key credentials can also be set as the environment variable `$INDICO_API_KEY` or as `api_key` in the indicorc file.

Private cloud API Access
------------------------

If you're looking to use indico's API for high throughput applications, please check out the [pricing page](https://indico.io/pricing) on our website to find the right plan for you.

```php
IndicoIo::sentiment("Text to analyze", "example-api-key", "example-cloud")
```

The third `cloud` parameter redirects API calls to your private cloud hosted at `[cloud].indico.domains`

Private cloud subdomains can also be set as the environment variable `$INDICO_CLOUD` or as `cloud` in the indicorc file.

Configuration
------------------------

IndicoIo-PHP will search ./.indicorc and $HOME/.indicorc for the optional configuration file. Values in the local configuration file (./.indicorc) take precedence over those found in a global configuration file ($HOME/.indicorc). The indicorc file can be used to set an authentication username and password or a private cloud subdomain, so these arguments don't need to be specified for every api call. All sections are optional.

Here is an example of a valid indicorc file:


```
[auth]
api_key = example-api-key

[private_cloud]
cloud = example
```

Environment variables take precedence over any configuration found in the indicorc file.
The following environment variables are valid:

```
$INDICO_API_KEY
$INDICO_CLOUD
```

Finally, any values explicitly passed in to an api call will override configuration options set in the indicorc file or in an environment variable.
