IndicoIo-php
=========

A simple PHP wrapper for the indico APIs.



Installation
--------------

1. Create (if not done yet) composer.json file on your project directory.

2. Add this to the file :

```json
{
	"require":{
	    ...
		"indicoio/indicoio-php": "*"
		...
	}
}

```
3. Run this command : 


```sh
composer install
```


Usage
----

```php

require(__DIR__ . '/vendor/autoload.php');

print_r(\IndicoIo\IndicoIo::sentiment('I love you  !'));

=> Array ( [Sentiment] => 0.46532170063496 )

print_r( \IndicoIo\IndicoIo::political('Obama is the US president !') );

=> Array ( [Libertarian] => 0.29189946558241 [Liberal] => 0.010490688696418 [Green] => 0.0110258933524 [Conservative] => 0.68658395236877 ) 

print_r( \IndicoIo\IndicoIo::language('una giornata molto buona auguro') );

=> Array ( [Swedish] => 0.00011552035349677 [Vietnamese] => 0.0010439073406634 [Romanian] => 4.4859977761836E-6 [Dutch] => 4.5674707699322E-5 [Korean] => 5.3119192163625E-5 [Danish] => 9.7697777765179E-6 [Indonesian] => 4.0203025867581E-6 [Latin] => 0.0058764961008608 [Hungarian] => 5.6426058452007E-5 [Persian (Farsi)] => 6.2600437029341E-6 [Lithuanian] => 0.0039609506743307 [French] => 2.0399931496277E-6 [Norwegian] => 0.00015239304276317 [Russian] => 0.00013775439666658 [Thai] => 3.4066036425308E-5 [Finnish] => 8.1624733519993E-5 [Hebrew] => 5.8164830189384E-6 [Bulgarian] => 0.0034069103460234 [Turkish] => 3.8579592818398E-5 [Greek] => 0.00010709230008665 [Tagalog] => 0.00015189161475784 [English] => 0.00011645340410667 [Arabic] => 1.4140934271487E-5 [Italian] => 0.91248953273899 [Portuguese] => 6.6430192271289E-6 [Chinese] => 0.0001651405636031 [German] => 3.4131505928479E-5 [Japanese] => 7.2165176983677E-7 [Czech] => 2.0120301352267E-5 [Slovak] => 0.0002684897882399 [Spanish] => 0.0056873313305499 [Polish] => 0.00037255793355163 [Esperanto] => 0.065529937739673 )

``` 


