<?php
	session_start();

	$user = @$_SESSION["id"];
	$userInfo = userInfo($user);

	$revision = "20220413v1";

	$page = "domains";
	if (@$_GET["page"]) {
		$page = $_GET["page"];
	}
	if (@$_GET["zone"]) {
		$zone = $_GET["zone"];
	}
	if (@$_SERVER["PHP_SELF"] === "/api.php") {
		$page = "api";
	}

	if (@$page) {
		$page = preg_replace("/[^a-zA-Z]/", '', $page);
	}
	if (@$zone) {
		$zone = preg_replace("/[^a-zA-Z0-9]/", '', $zone);
	}

	$allowedPages = ["api", "login", "signup"];
	if (!$user && !in_array($page, $allowedPages)) {
		header("Location: /login");
		die();
	}
?>