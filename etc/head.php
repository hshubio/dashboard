<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title><?php echo "hshub | ".$page; ?></title>
<link rel="shortcut icon" href="/favicon.ico" type="image/x-icon">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Rubik&display=swap" rel="stylesheet">
<link rel="stylesheet" type="text/css" href="/assets/css/style?r=<?php echo $revision; ?>">
<script src="https://code.jquery.com/jquery-3.6.0.min.js" integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
<script type="text/javascript" src="/assets/js/script?r=<?php echo $revision; ?>"></script>
<script type="text/javascript" src="/assets/js/progressbar.min.js?r=<?php echo $revision; ?>"></script>
<?php
	if (@$_COOKIE["admin"]) { ?>
		<script type="text/javascript" src="/assets/js/admin?r=<?php echo $revision; ?>"></script>
	<?php
	}
?>