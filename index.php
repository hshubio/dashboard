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
	<div class="popover" data-name="account">
		<div class="menu">
			<?php
				if ($_COOKIE["admin"]) { ?>
					<div class="action item" data-action="unimpersonate">Unimpersonate</div>
				<?php
				}
			?>
			<div class="action item" data-action="settings">Settings</div>
			<div class="action item" data-action="logout">Logout</div>
		</div>
	</div>
	<div id="blackout"></div>
	<div class="header">
		<div class="section left">
			<div class="logo">
				<span>hshub</span>
			</div>
		</div>
		<div class="section right">
			<select class="domains"></select>
			<div class="action account" data-action="account">
				<div class="avatar"></div>
				<div class="arrow"></div>
			</div>
		</div>
	</div>
	<div class="main" data-page="<?php echo $page; ?>" data-zone="<?php echo @$zone; ?>">
		<div class="menu">
			<div class="item" data-page="domains">
				<div class="icon domains"></div>
				<div class="label">Domains</div>
			</div>
			<div class="item" data-page="dns">
				<div class="icon dns"></div>
				<div class="label">DNS</div>
			</div>
			<?php
				if ($userInfo["admin"]) { ?>
					<div class="separator"></div>
					<div class="item" data-page="admin">
						<div class="icon admin"></div>
						<div class="label">Admin</div>
					</div>
				<?php
				}
			?>
		</div>
		<div class="body">
			<div class="holder"></div>
			<div class="footer select">
				<a href="https://support.hshub.io">Support</a>
				hs1q5gutz3haq7ec6a8lvte485jhhjg4samvkpl6y0
			</div>
		</div>
	</div>
</body>
</html>