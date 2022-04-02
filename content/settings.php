<?php
	include "/var/www/html/hshub/etc/includes.php";
?>
<div class="section">
	<div class="title">Settings</div>
	<div class="box">
		<form>
			<table id="settings">
				<tr>
					<td>Support Token</td>
					<td>
						<input type="text" name="token" value="<?php echo $userInfo["token"]; ?>" readonly />
					</td>
				</tr>
				<tr>
					<td>Email</td>
					<td>
						<input type="text" name="email" value="<?php echo $userInfo["email"]; ?>" />
					</td>
				</tr>
				<tr>
					<td>Current Password</td>
					<td>
						<input type="password" name="password" />
					</td>
				</tr>
				<tr>
					<td>New Password</td>
					<td>
						<input type="password" name="new-password" />
					</td>
				</tr>
			</table>
			<input type="hidden" name="action" value="settings">
			<div class="submit" data-action="settings">Save</div>
		</div>
	</div>
</div>