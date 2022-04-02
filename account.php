<?php
	include "/var/www/html/hshub/etc/includes.php";
?>
<!DOCTYPE html>
<html>
<head>
	<?php
		include "etc/head.php";
	?>
</head>
<body>
	<div class="main">
		<div class="body">
			<div class="account">
				<form id="accountForm">
					<a href="/">
						<div class="title">hshub</div>
					</a>
					<div class="subtitle">Free DNS hosting for Handshake</div>
					<input type="text" name="email" placeholder="Email">
					<input type="password" name="password" placeholder="Password">
					<input type="hidden" name="action" value="<?php echo $page; ?>">
					<div class="submit" data-action="<?php echo $page; ?>"><?php echo ucfirst($page); ?></div>

					<div class="message">
						<?php
							switch ($page) {
								case 'login':
									?>
									<a href="/signup">I need to signup</a>
									<?php
									break;

								case 'signup':
									?>
									<a href="/login">I already have an account</a>
									<?php
									break;
								
								default:
									break;
							}
						?>
					</div>
				</form>
			</div>
		</div>
	</div>
</body>
</html>