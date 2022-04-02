<?php
	include "/var/www/html/hshub/etc/includes.php";
?>
<div class="section">
	<div class="title">Add Domain</div>
	<div class="box">
		<table id="createZoneTable">
			<tbody>
				<tr>
					<td class="domain editing"><div class="edit" contenteditable="true"></div></td>
					<td class="add"><div class="actions"><div class="icon add" data-action="createZone"></div></div></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<div class="section" data-section="domains">
	<div class="title">Domains</div>
	<div class="box">
		<table id="domainTable">
			<tbody></tbody>
		</table>
	</div>
</div>