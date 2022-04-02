<?php
	function uuid($data = null) {
		$data = $data ?? random_bytes(16);
		assert(strlen($data) == 16);

		$data[6] = chr(ord($data[6]) & 0x0f | 0x40);
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80);

		return vsprintf('%s%s%s%s%s%s%s%s', str_split(bin2hex($data), 4));
	}

	function generateID($length) {
		$alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';
	    $pass = array();
	    $alphaLength = strlen($alphabet) - 1;
	    for ($i = 0; $i < $length; $i++) {
	        $n = rand(0, $alphaLength);
	        $pass[] = $alphabet[$n];
	    }
	    return implode($pass);
	}
	
	function api($data) {
		$post = json_encode($data);
		$curl = curl_init("http://172.28.155.86");
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($curl, CURLOPT_POST, true);
		curl_setopt($curl, CURLOPT_POSTFIELDS, $post);
		curl_setopt($curl, CURLOPT_HTTPHEADER, [
			"Content-Type: application/json",
			"Content-Length: ".strlen($post)
		]);
		$result = curl_exec($curl);
		curl_close($curl);
		
		return json_decode($result, true);
	}

	function userInfo($id) {
		$getUser = @sql("SELECT `email`,`token`,`admin` FROM `users` WHERE `id` = ?", [$id])[0];
		return $getUser;
	}

	function userCanAccessZone($zone, $user) {
		$getZone = sql("SELECT * FROM `pdns`.`domains` WHERE `uuid` = ? AND `account` = ?", [$zone, $user]);
		if ($getZone) {
			return true;
		}
		return false;
	}

	function domainExists($domain) {
		$getDomain = sql("SELECT * FROM `pdns`.`domains` WHERE `name` = ?", [$domain]);
		if ($getDomain) {
			return true;
		}
		return false;
	}

	function dataForRecord($record) {
		$getRecord = sql("SELECT * FROM `pdns`.`records` WHERE `uuid` = ?", [$record]);

		return $getRecord[0];
	}

	function containsInvalidCharacters($string) {
		$invalidCharacters = preg_match("/[^a-zA-Z0-9-\.\_\*]/", $string, $match);
		if (count($match)) {
			return true;
		}
		
		return false;
	}
?>