#!/usr/bin/php
<?php
ini_set("display_errors",1);
error_reporting(E_ALL);
 
include 'simple.php'; //path to 'simple php html dom parser' libruary;

$path = 'your_file_for_array_of_emails_saving'; //don't forget to change it
$mails = array();

//take array with web site's links from serialize data file:
$arr = file_get_contents('res.txt');
$arr = unserialize($arr);


//iteration with find initialisation:
foreach ($arr as $url) {	
	$mails[] = mail_from($url);
}


//results saving to serialize data file:
file_put_contents($path, serialize($mails));

/*
you also can print results:
echo '<pre>';
var_dump($mails);
echo '</pre>';
*/

//function, which find e-mails on the site:

function mail_from($site) {

	$find = 0;
	$resmail = array();
	$web = file_get_html($site);

	//check url opening:

	if ($web == false) {
		$resmail[$site] = 'unopened url';
		return $resmail;
	}

	//find links with href=mailto

	$mailto = $web->find('a[href^=mailto]');
	

	foreach($mailto as $element) {
		$url = $element->href;
		if (preg_match('@mailto:@', $url)) {
			$res = str_replace('mailto:', '', $url);
			$url = $res;	
		}

		$resmail[$site][] = $url;
		$find++;
	}

	//find e-mails by regexp:

	$text = $web->plaintext;

	if (preg_match_all('|([a-z0-9_\.\-]{1,20})@([a-z0-9\.\-]{1,20})\.([a-z]{2,4})|is', $text, $mailpreg)) {
		
		foreach ($mailpreg as $mail) {
			if (preg_match('#@#', $mail[0])) {
				$resmail[$site][] = $mail[0];
				$find++; 
			}
		}
	}


	
	//if don't find e-mails:
	
	if ( isset($find) && ($find == 0)) {
		$resmail[$site][] = 'none';
	}

	//Create unique array and return a value

	$resmail[$site] = array_unique($resmail[$site]);
	return $resmail;

}


?>
