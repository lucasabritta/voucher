<?php
	$params = (array) json_decode(file_get_contents('php://input'), TRUE);
	if(!isset($params["action"])) {
		$response = new \stdClass();
		$response->message = "Missing mandatory 'action' parameter";
		$response->status = "fail";
		header("HTTP/1.1 400 Bad Request");
		$response = json_encode($response);
	} else {
		$action = $params["action"];
		if ($action == 'createSpecialOffer') {
			$fail = false;
			$fields = '';
			if(!isset($params["name"])) {
				$fail = true;
				$fields = "'name'";
			} 
			if(!isset($params["discount"])) {
				$fail = true;
				$fields = $fields == '' ? "'discount'" : $fields." and 'discount'";
			} 
			if ($fail) {
				$response = new \stdClass();
				$response->message = "Missing $fields parameter";
				$response->status = "fail";
				header("HTTP/1.1 400 Bad Request");
				$response = json_encode($response);
			} else {
				$name = $params["name"];
				$discount = $params["discount"];
				$response = createSpecialOffer($name, $discount);
			}
		} else if ($action == 'createRecipient') {
			$fail = false;
			$fields = '';
			if(!isset($params["name"])) {
				$fail = true;
				$fields = "'name'";
			} 
			if(!isset($params["email"])) {
				$fail = true;
				$fields = $fields == '' ? "'email'" : $fields." and 'email'";
			} 
			if ($fail) {
				$response = new \stdClass();
				$response->message = "Missing $fields parameter";
				$response->status = "fail";
				header("HTTP/1.1 400 Bad Request");
				$response = json_encode($response);
			} else {
				$name = $params["name"];
				$email = $params["email"];
				$response = createRecipient($name, $email);
			}
		} else if ($action == 'generateVoucherCode') {
			$fail = false;
			$fields = '';
			if(!isset($params["offerName"])) {
				$fail = true;
				$fields = "'offerName'";
			} 
			if(!isset($params["expirationDate"])) {
				$fail = true;
				$fields = $fields == '' ? "'expirationDate'" : $fields." and 'expirationDate'";
			} 
			if ($fail) {
				$response = new \stdClass();
				$response->message = "Missing $fields parameter";
				$response->status = "fail";
				header("HTTP/1.1 400 Bad Request");
				$response = json_encode($response);
			} else {
				$offerName = $params["offerName"];
				$expirationDate = $params["expirationDate"];
				$response = generateVoucherCode($offerName, $expirationDate);
			}
		} else if ($action == 'validateVoucherCode') {
			$fail = false;
			$fields = '';
			if(!isset($params["code"])) {
				$fail = true;
				$fields = "'code'";
			} 
			if(!isset($params["email"])) {
				$fail = true;
				$fields = $fields == '' ? "'email'" : $fields." and 'email'";
			} 
			if ($fail) {
				$response = new \stdClass();
				$response->message = "Missing $fields parameter";
				$response->status = "fail";
				header("HTTP/1.1 400 Bad Request");
				$response = json_encode($response);
			} else {
				$code = $params["code"];
				$email = $params["email"];
				$response = validateVoucherCode($code, $email);
			}
		} else if ($action == 'getUserValidVoucher') {
			$fail = false;
			$fields = '';
			if(!isset($params["email"])) {
				$fail = true;
				$fields = "'email'";
			}
			if ($fail) {
				$response = new \stdClass();
				$response->message = "Missing $fields parameter";
				$response->status = "fail";
				header("HTTP/1.1 400 Bad Request");
				$response = json_encode($response);
			} else {
				$email = $params["email"];
				$response = getUserValidVoucher($email);
			}
		} else {
			$response = new \stdClass();
			$response->message = "Unexpected action";
			$response->status = "fail";
			header("HTTP/1.1 400 Bad Request");
			$response = json_encode($response);
		}
	}
	header('Content-Type: application/json');
	echo $response;
function createSpecialOffer($name, $discount)
{
	include "db.php";
	$conn = new mysqli($host, $userName, $password, $dbName);
	$sql = "INSERT INTO special_offer (name, discount) VALUES ('".$name."' , ".$discount.")";
	$output = new \stdClass();
	if($conn->query($sql)) {
		$output->message = "Special offer created";
		$output->status = "sucess";
	} else {
		$output->message = "Error while creating a special offer";
		$output->status = "fail";
		header("HTTP/1.1 500 Internal Server Error");
	}
	mysqli_close($conn);
	return json_encode($output);
}
function createRecipient($name, $email)
{
	include "db.php";
	$conn = new mysqli($host, $userName, $password, $dbName);
	$sql = "INSERT INTO recipient (name, email) VALUES ('".$name."', '".$email."')";
	$output = new \stdClass();
	if($conn->query($sql)) {
		$output->message = $name." user created";
		$output->status = "sucess";
	} else {
		$output->message = "Error while creating user : ".$name;
		$output->status = "fail";
		header("HTTP/1.1 500 Internal Server Error");
	}
	mysqli_close($conn);
	return json_encode($output);
}
function generateVoucherCode($specialOfferName, $expirationDate)
{
	if (verifyDate($expirationDate) == 1) {
		$output = new \stdClass();
		$output->message = "Invalid expirationDate";
		$output->status = "fail";
		header("HTTP/1.1 400 Bad Request");
		return json_encode($output);
	}
	include "db.php";
	$conn = new mysqli($host, $userName, $password, $dbName);
	$searchOfferId = "SELECT id FROM special_offer WHERE name = '".$specialOfferName."'";
	$result = $conn->query($searchOfferId);
	if ($row = $result->fetch_assoc()) {
		$offerId = $row['id'];
	} else {
		$output = new \stdClass();
		$output->message = "Cannot find offer";
		$output->status = "fail";
		header("HTTP/1.1 500 Internal Server Error");
		return json_encode($output);
	}
	$selectUsers = "SELECT email FROM recipient";
	$result = $conn->query($selectUsers);
	if ($result->num_rows > 0) {
		$codesFail = 0;
		$codesSucess = 0;
		while($row = $result->fetch_assoc()) {
			$email = $row["email"];
			$code = createUniqueCode();
			$sql = "INSERT INTO voucher_codes (code, recipient_email, special_offer_id, expiration_date) VALUES ('".$code."', '".$email."', ".$offerId.", '".$expirationDate."')";
			if($conn->query($sql)) {
				$codesSucess++;
			} else {
				$codesFail++;
			}
		}
	}
	mysqli_close($conn);
	$output = new \stdClass();
	$output->message = "Codes created : ".$codesSucess."; Creation codes failed : ".$codesFail.";";
	if ($codesSucess == 0) {
		$output->status = "fail";
		header("HTTP/1.1 500 Internal Server Error");
	} else {
		$output->status = "sucess";
	}
	return json_encode($output);
}
function verifyDate($date)
{//return 1 if the variable $date is a invalid date and 2 if is a valid date
    $d = DateTime::createFromFormat('Y-m-d', $date);
    if ($d && $d->format('Y-m-d') == $date) return '2';
    else return '1';
}
function createUniqueCode()
{
	$codeSize = 8;
	$newCode = false;
	include "db.php";
	$conn = new mysqli($host, $userName, $password, $dbName);
	while ($newCode == false) {
		$code = strtoupper(substr(md5(uniqid(mt_rand(), true)), 0, $codeSize));
		$selectUsers = "SELECT usaged FROM voucher_codes WHERE code = '".$code."'";
		$result = $conn->query($selectUsers);
		if ($result->num_rows == 0) {
			$newCode = true;
		}
	}
	mysqli_close($conn);
	return $code;
}
function validateVoucherCode($voucherCode, $email)
{
	$output = new \stdClass();
	include "db.php";
	$conn = new mysqli($host, $userName, $password, $dbName);
	$select = "SELECT V.expiration_date, V.usaged, S.discount FROM voucher_codes V INNER JOIN special_offer S ON S.id = V.special_offer_id AND V.recipient_email = '".$email."' AND V.code = '".$voucherCode."'";
	$result = $conn->query($select);
	if ($row = $result->fetch_assoc()) {
		date_default_timezone_set("Brazil/East");
		$today = date('Y-m-d');
		if ($today > $row['expiration_date'] || $row['usaged'] == 1) {
			$output->message = "Unavailable voucher";
			$output->discount = '';
			$output->status = "fail";
			header("HTTP/1.1 500 Internal Server Error");
		} else {
			$update ="UPDATE voucher_codes SET usaged = '1', date_usage = '".$today."' WHERE code = '".$voucherCode."';";
			if($conn->query($update)) {
				$output->message = "Redeemed voucher";
				$output->discount = $row['discount'];
				$output->status = "sucess";
			} else {
				$output->message = "Error while processing voucher";
				$output->discount = '';
				$output->status = "fail";
				header("HTTP/1.1 500 Internal Server Error");
			}
		}
	} else {
		$output->message = "Code invalid for given email";
		$output->discount = '';
		$output->status = "fail";
		header("HTTP/1.1 500 Internal Server Error");
	}
	mysqli_close($conn);
	return json_encode($output);
}
function getUserValidVoucher($email)
{
	$output = new \stdClass();
	$output->vouchers = array();
	include "db.php";
	$conn = new mysqli($host, $userName, $password, $dbName);
	$select = "SELECT V.code, S.name FROM voucher_codes V INNER JOIN special_offer S ON V.special_offer_id = S.id AND (V.usaged is null OR V.usaged != 1) AND DATE(V.expiration_date) >= CURDATE() AND V.recipient_email = '".$email."'";
	$result = $conn->query($select);
	if ($result->num_rows > 0) {
		while($row = $result->fetch_assoc()) {
			$voucher = new \stdClass();
			$voucher->code = $row['code'];
			$voucher->offerName = $row['name'];
			array_push($output->vouchers,$voucher);
		}
	}
	mysqli_close($conn);
	return json_encode($output);
}
?>