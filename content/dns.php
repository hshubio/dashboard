<?php
	include "/var/www/html/hshub/etc/includes.php";
?>
<div class="section">
	<div class="title">New Record</div>
	<div class="box">
		<table id="addRecordTable">
			<thead>
				<tr>
					<th class="type">Type</th>
					<th class="name">Name</th>
					<th class="content">Content</th>
					<th class="prio">Priority</th>
					<th class="ttl">TTL</th>
					<th class="action"></th>
				</tr>
			</thead>
			<tbody>
				<tr>
				    <td class="type editing">
				    	<select>
				    		<?php
				    			foreach ($config["recordTypes"] as $type) { ?>
				    				<option><?php echo $type; ?></option>
				    			<?php
				    			}
				    		?>
						</select>
				    </td>
				    <td class="name editing"><div class="edit" contenteditable="true"></div></td>
				    <td class="content editing"><div class="edit" contenteditable="true"></div></td>
				    <td class="prio"><div class="edit" contenteditable="false"></div></td>
				    <td class="ttl editing"><div class="edit" contenteditable="true"></div></td>
				    <td class="add"><div class="actions"><div class="icon add" data-action="addRecord"></div></div></td>
				</tr>
			</tbody>
		</table>
	</div>
</div>
<div class="section">
	<div class="title">Records</div>
	<div class="box">
		<table id="dnsTable" class="editable" data-neg="cancelEditRecord" data-pos="updateRecord">
			<thead>
				<tr>
					<th class="type">Type</th>
					<th class="name">Name</th>
					<th class="content">Content</th>
					<th class="prio">Priority</th>
					<th class="ttl">TTL</th>
					<th class="action"></th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
</div>
<div class="section">
	<div class="title">Nameservers and DNSSEC</div>
	<div class="box">
		<table id="nsTable">
			<thead>
				<tr>
					<th class="type">Type</th>
					<th class="value">Value</th>
				</tr>
			</thead>
			<tbody>
			</tbody>
		</table>
	</div>
</div>