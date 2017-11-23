<?php

const server = "<your database server address>";
const dbuser = "<your database server username>";
const dbpw = "<your database server password>";
const db = "<your database name>";
const global_app_id = '<your onesignal app id>';
const global_rest_id = '<your onesignal rest id>';

//send onesignal notification to mobile user
function sendMessage($email, $message, $arrayfull){
	$content = array(
		"en" => $message
	);
		
	$fields = array(
		'app_id' => global_app_id,
		'filters' => $arrayfull,
		'data' => array("foo" => "bar"),
		'contents' => $content
	);
		
	$fields = json_encode($fields);
		
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, "https://onesignal.com/api/v1/notifications");
	curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json; charset=utf-8','Authorization: Basic '. global_rest_id));
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
	curl_setopt($ch, CURLOPT_HEADER, FALSE);
	curl_setopt($ch, CURLOPT_POST, TRUE);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

	$response = curl_exec($ch);
	curl_close($ch);
		
	return $response;
}

//  Decode the structured message sent by unabiz-arduino library.
const firstLetter = 1;  //  Letters are assigned codes 1 to 26, for A to Z
const firstDigit = 27;  //  Digits are assigned codes 27 to 36, for 0 to 9

//Source: https://stackoverflow.com/questions/40841149/unicode-charcodeat-equivalent-in-php/40853121
//User: nwellnhof
function JS_charCodeAt($str, $index) {
    $utf16 = mb_convert_encoding($str, 'UTF-16LE', 'UTF-8');
    return ord($utf16[$index*2]) + (ord($utf16[$index*2+1]) << 8);
}

//Source: https://gist.github.com/msng/5039773
function strpos_array($haystack, $needles, $offset = 0) {
	if (is_array($needles)) {
		foreach ($needles as $needle) {
			$pos = strpos_array($haystack, $needle);
			if ($pos !== false) {
				return $pos;
			}
		}
		return false;
	} else {
		return false;
	}
}

//Source: https://stackoverflow.com/questions/12990195/javascript-function-in-php-fromcharcode
//User: xdazz
function str_fromcharcode() {
    return implode(array_map('chr', func_get_args()));
}


//This is a translation of UnaBiz's structuredmessage JavaScript code to PHP
//Source: https://github.com/UnaBiz/sigfox-gcloud/tree/master/decodeStructuredMessage
function decodeLetter($code) {
    //  Convert the 5-bit code to a letter.
    if ($code == 0){ 
        return 0; 
    }

    if ($code >= firstLetter && $code < firstDigit)
    {
        return ($code - firstLetter) + JS_charCodeAt('a', 0);
    }
  
    if ($code >= firstDigit){ 
        return ($code - firstDigit) + JS_charCodeAt('0',0);
    }
    return 0;
}

function decodeText($encodedText0) { /* eslint-disable no-bitwise, operator-assignment */
    //  Decode a text string with packed 5-bit letters.
    $encodedText = $encodedText0;
  
    $text = [0, 0, 0];
    for ($j = 0; $j < 3; $j = $j + 1) {
        $code = $encodedText & 31;
        $ch = decodeLetter($code);

        if ($ch > 0) $text[2 - $j] = $ch;
        $encodedText = $encodedText >> 5;
    }

    if ($text[2]){
        $result = str_fromcharcode($text[0], $text[1], $text[2]);
    }
    elseif ($text[1]){
        $result = str_fromcharcode($text[0], $text[1]);
    }
    else{
        $result = str_fromcharcode($text[0], $text[1]);
    }

    return $result;
} 

function decodeMessage($data, $textFields) {
    
  if (!$data) return null;

    $result = null;
    
    for ($i = 0; $i < strlen($data); $i = $i + 8) {
      $name = substr($data, $i, $i + 4);
      $val = substr($data, $i + 4, $i + 8);
      $encodedName =
        (intval($name[2], 16) << 12) +
        (intval($name[3], 16) << 8) +
        (intval($name[0], 16) << 4) +
        intval($name[1], 16);
      $encodedVal =
        (intval($val[2], 16) << 12) +
        (intval($val[3], 16) << 8) +
        (intval($val[0], 16) << 4) +
        intval($val[1], 16);

      //  Decode name.
      $decodedName = decodeText($encodedName);
      if ($textFields && strpos_array($textFields, $decodedName) >= 0) {
        //  Decode the text field.
        $result[$decodedName] = decodeText($encodedVal);
      } else {
        //  Decode the number.
        $result[$decodedName] = $encodedVal / 10.0;
      }
    }
    return $result;
} 

?>
