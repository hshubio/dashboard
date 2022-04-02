<?php
	include "/var/www/html/hshub/etc/includes.php";

	$json = file_get_contents('php://input');
	$data = json_decode($json, true);

	if (!@$data["action"]) {
		die();
	}

	$output = [
		"success" => true,
	];

	foreach ($data as $key => $value) {
		$data[$key] = trim($value, ". ".chr(194).chr(160));
	}

	if (@$data["email"]) {
		$data["email"] = strtolower($data["email"]);
	}

	$queryMutual = true;

	// PREVENT QUERYING MUTUAL
	switch ($data["action"]) {
		case "login":
		case "signup":
		case "logout":
		case "settings":
		case "updateEmail":
			$queryMutual = false;
			break;

		case "addRecord":
		case "updateRecord":
			if (!@$data["ttl"]) {
				$data["ttl"] = 20;
			}
			if (!@$data["prio"]) {
				$data["prio"] = 0;
			}
			break;
	}

	// VERIFY PERMISSIONS
	if ($queryMutual) {
		switch ($data["action"]) {
			case "getZones":
			case "createZone":
				break;

			default:
				if (@$data["zone"]) {
					if (!userCanAccessZone($data["zone"], $user)) {
						$output["success"] = false;
						goto end;
					}
				}
				break;
		}
	}
	else {
		switch ($data["action"]) {
			case "updateEmail":
			case "resetPassword":
			case "impersonate":
				if (!$userInfo["admin"]) {
					$output["success"] = false;
					goto end;
				}
				break;

			default:
				break;
		}
	}

	// DO STUFF
	switch ($data["action"]) {
		case "login":
			if (!$data["email"]) {
				$output["fields"][] = "email";
			}
			if (!$data["password"]) {
				$output["fields"][] = "password";
			}

			if (!count(@$output["fields"])) {
				$getUser = sql("SELECT `id`,`password` FROM `users` WHERE `email` = ?", [$data["email"]])[0];
				if (!password_verify($data["password"], @$getUser["password"])) {
					$output["fields"][] = "email";
					$output["fields"][] = "password";
				}
				else {
					$_SESSION["id"] = $getUser["id"];
				}
			}
			break;

		case "signup":
			if ($signupsDisabled) {
				$output["success"] = false;
				$output["message"] = "Signups are currently disabled.";
			}
			else {
				if (!$data["email"]) {
					$output["fields"][] = "email";
				}
				if (!$data["password"]) {
					$output["fields"][] = "password";
				}

				$validEmail = filter_var($data["email"], FILTER_VALIDATE_EMAIL);
				if (!$validEmail) {
					$output["fields"][] = "email";
				}

				$passwordHash = password_hash($data["password"], PASSWORD_BCRYPT);

				if (!count(@$output["fields"])) {
					$token = generateID(16);
					$insert = sql("INSERT INTO `users` (email, password, token) VALUES (?,?,?)", [$data["email"], $passwordHash, $token]);
					if ($insert) {
						$getUser = sql("SELECT `id` FROM `users` WHERE `email` = ?", [$data["email"]])[0];
						$_SESSION["id"] = $getUser["id"];
					}
					else {
						$output["fields"][] = "email";
					}
				}
			}
			break;

		case "logout":
			$_SESSION = [];
			if (ini_get("session.use_cookies")) {
			    $params = session_get_cookie_params();
			    setcookie(session_name(), '', time() - 42000,
			        $params["path"], $params["domain"],
			        $params["secure"], $params["httponly"]
			    );
			}
			session_destroy();
			break;

		case "settings":
			if (strlen($data["email"]) < 1) {
				$output["fields"][] = "email";
			}

			$validEmail = filter_var($data["email"], FILTER_VALIDATE_EMAIL);
			if (!$validEmail) {
				$output["fields"][] = "email";
			}

			if (strlen($data["password"]) < 1) {
				$output["fields"][] = "password";
			}

			if (!count(@$output["fields"])) {
				$getUser = sql("SELECT `email`,`id`,`password` FROM `users` WHERE `id` = ?", [$user])[0];
				if (!password_verify($data["password"], @$getUser["password"])) {
					$output["fields"][] = "password";
				}
				else {
					if ($data["email"] !== $getUser["email"]) {
						sql("UPDATE `users` SET `email` = ? WHERE `id` = ?", [$data["email"], $user]);
					}
					if ($data["new-password"]) {
						$passwordHash = password_hash($data["new-password"], PASSWORD_BCRYPT);
						sql("UPDATE `users` SET `password` = ? WHERE `id` = ?", [$passwordHash, $user]);
					}
				}
			}
			break;

		case "updateEmail":
			if (strlen($data["email"]) < 1) {
				$output["fields"][] = "email";
			}

			$validEmail = filter_var($data["email"], FILTER_VALIDATE_EMAIL);
			if (!$validEmail) {
				$output["fields"][] = "email";
			}

			if (!count(@$output["fields"])) {
				$getUser = sql("SELECT `email` FROM `users` WHERE `id` = ?", [$data["user"]])[0];
				
				if ($data["email"] !== $getUser["email"]) {
					sql("UPDATE `users` SET `email` = ? WHERE `id` = ?", [$data["email"], $data["user"]]);
				}

				$output["data"]["value"] = $data["email"];
			}
			break;

		case "resetPassword":
			$password = uuid();
			$passwordHash = password_hash($password, PASSWORD_BCRYPT);
			sql("UPDATE `users` SET `password` = ? WHERE `id` = ?", [$passwordHash, $data["user"]]);
			$output["data"]["password"] = $password;
			break;

		case "impersonate":
			setcookie("admin", session_id(), time() + (86400 * 30), "/");
			session_regenerate_id();
			$_SESSION["id"] = $data["user"];
			break;

		case "createZone":
			if (strlen($data["domain"]) < 1) {
				$output["fields"][] = "domain";
			}

			if (containsInvalidCharacters($data["domain"])) {
				$output["fields"][] = "domain";
			}

			if (domainExists($data["domain"])) {
				$output["fields"][] = "domain";
				$output["message"] = "This domain can't be added right now. Contact support.";
			}
			
			$domainRequest = [
				"action" => "domainInfo",
				"domain" => $data["domain"]
			];
			$domainInfo = api($domainRequest);
			
			if ($domainInfo["reserved"] || $domainInfo["invalid"]) {
				$output["fields"][] = "domain";
			}
			break;

		case "addRecord":
			if (!$data["type"]) {
				$output["fields"][] = "type";
			}
			if (!in_array($data["type"], $config["recordTypes"])) {
				$output["fields"][] = "type";
			}

			if (!$data["name"]) {
				$output["fields"][] = "name";
			}

			if ($data["name"] !== "@" && containsInvalidCharacters($data["name"])) {
				$output["fields"][] = "name";
			}

			if (!$data["content"]) {
				$output["fields"][] = "content";
			}

			switch ($data["type"]) {
				case "A":
					if (!filter_var($data["content"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
						$output["fields"][] = "content";
					}
					break;
				case "AAAA":
					if (!filter_var($data["content"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
						$output["fields"][] = "content";
					}
					break;
				
				default:
					break;
			}

			if (!is_numeric($data["prio"])) {
				$output["fields"][] = "prio";
			}

			if (!is_numeric($data["ttl"])) {
				$output["fields"][] = "ttl";
			}
			break;

		case "updateRecord":
			$recordData = dataForRecord($data["record"]);
			$recordType = $recordData["type"];

			if (!$data["value"]) {
				$output["fields"][] = $data["column"];
			}

			switch ($data["column"]) {
				case "name":
					if ($data["value"] !== "@" && containsInvalidCharacters($data["value"])) {
						$output["fields"][] = $data["column"];
					}
					break;

				case "content":
					switch ($recordType) {
						case "A":
							if (!filter_var($data["value"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV4)) {
								$output["fields"][] = $data["column"];
							}
							break;
						case "AAAA":
							if (!filter_var($data["value"], FILTER_VALIDATE_IP, FILTER_FLAG_IPV6)) {
								$output["fields"][] = $data["column"];
							}
							break;
						
						default:
							break;
					}
					break;

				case "prio":
					if (!is_numeric($data["value"])) {
						$output["fields"][] = "prio";
					}
					break;

				case "ttl":
					if (!is_numeric($data["value"])) {
						$output["fields"][] = "ttl";
					}
					break;
				
				default:
					break;
			}
			break;
	}

	end:
	if (@count($output["fields"])) {
		$output["fields"] = array_unique($output["fields"]);
		$output["success"] = false;
	}

	if (!$output["success"]) {
		die(json_encode($output));
	}

	if ($queryMutual) {
		$data["user"] = $user;
		$response = api($data);
		if ($response) {
			$output["data"] = $response;
		}
	}

	die(json_encode($output));
?>