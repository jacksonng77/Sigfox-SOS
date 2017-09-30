<?PHP

    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, PATCH, PUT, DELETE, OPTIONS');
    header('Access-Control-Allow-Headers: Origin, Content-Type, X-Auth-Token');
    header("Content-Type: application/json; charset=UTF-8");
    error_reporting(E_ERROR);

	include("global.php");

    //search the database for the email address based on sigfox equipment ID
    $conn = new mysqli(server, dbuser, dbpw, db);
    $deviceid = $_GET['deviceid'];
    $result = $conn->query("select email from device where deviceid = '" . $deviceid . "'");

    while($rs = $result->fetch_array(MYSQLI_ASSOC)) {
	    $outp = $rs["email"];
    }

    $conn->close();

    $result = decodeMessage($_GET['message'], ['d1', 'd2', 'd3']);

	$email = $outp;
	$message = implode(" ",$result); 
        
	$arrayfull = [];

	$arrayhead = array("field" => "tag", "key" => "email", "relation" => "=");
	$emailArray = explode(',', $email);

	if (count($emailArray) == 1) {
		$arrNew = array('value' => $emailArray[0]);
		$arrayhead = $arrayhead + $arrNew;
		array_push($arrayfull, $arrayhead);
	}
	else {
		foreach ($emailArray as &$value) {
			$arrOr = array('operator' => 'OR');
			$arrNew = $arrayhead + array("value" => $value);
			array_push($arrayfull, $arrayNew);
			array_push($arrayfull, $arrOr);
		}
	}

	$response = sendMessage($email, $message, $arrayfull);
	$return["allresponses"] = $response;
	$return = json_encode( $return);

	//find the end of the recipient string in the response
	$pos = strpos($return, '\"recipients\":');
	$posend = $pos + 15;

	$lastchunk = substr($return, $posend, strlen($return)-$posend+1);
	$commapos = strpos($lastchunk, ",");
	$bracketpos = strpos($lastchunk, "}");

	if ($commapos != null){
		$num = substr($lastchunk, 0, $commapos);
	}
	else {
		$num = substr($lastchunk, 0, $bracketpos);
	}

	$json_out = "[" . json_encode(array("result"=>(int)$num)) . "]";
	echo $json_out;
?>