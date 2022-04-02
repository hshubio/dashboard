var page,zone;
var zones = {};
var records = {};

var zoneInURL = ["dns"];

var progress;

var css = getComputedStyle($("html")[0]);

function log(string) {
	return console.log(string);
}

async function api(data) {
	let output = new Promise(function(resolve) {
		$.post("/api", JSON.stringify(data), function(response){
			if (response) {
				let json = JSON.parse(response);

				if (json.message) {
					alert(json.message);
				}

				resolve(json);
			}
		});
	});

	return await output;
}

function serializeObject(obj) {
    var jsn = {};
    $.each(obj, function() {
        if (jsn[this.name]) {
            if (!jsn[this.name].push) {
                jsn[this.name] = [jsn[this.name]];
            }
            jsn[this.name].push(this.value || '');
        } else {
            jsn[this.name] = this.value || '';
        }
    });
    return jsn;
};

function pageHasZoneInURL(page) {
	switch (page) {
		case "dns":
			return true;

		default:
			return false;
	}
}

function updateMenu() {
	if (!Object.keys(zones).length) {
		$(".menu .item[data-page=dns]").addClass("disabled");
	}
	else {
		$(".menu .item[data-page=dns]").removeClass("disabled");
	}
}

function loadPage(noState) {
	if (!page) {
		return;
	}

	var title;
	if (page == "dns" && zone) {
		title = "hshub | "+zones[zone]+" | "+page;
	}
	else {
		title = "hshub | "+page;
	}

	if (!Object.keys(zones).length && page == "dns") {
		page = "domains";
		updateMenu();
	}

	document.title = title;

	$.ajax({
		type: "GET",
		url: "/content/"+page,
		error: function(xhr, response) { 
			$(".holder").html(xhr.status);
			afterLoad(page);
		},
		success: function(content){ 
			$(".holder").html(content);
			afterLoad(page);
		}
	});

	if (!noState) {
		if (pageHasZoneInURL(page) && zone) {
			window.history.pushState(null, null, "/"+page+"/"+zone);
		}
		else {
			window.history.pushState(null, null, "/"+page);
		}
	}
}

function afterLoad(page) {
	$(".main").attr("data-page", page).data("page", page);
	$(".main").attr("data-zone", zone).data("zone", zone);
	$(".menu .item.current").removeClass("current");
	$(".menu .item .icon."+page).parent().addClass("current");

	switch (page) {
		case "dns":
			showDomainSelector(true);
			break;

		default:
			showDomainSelector(false);
			break;
	}

	switch (page) {
		case "domains":
			$.each(zones, function(id, name){
				$("#domainTable").append(domainRow(id, name));
			});

			if (Object.keys(zones).length > 0) {
				$(".section[data-section=domains]").addClass("shown");
			}
			break;

		case "dns":
			loadRecords();
			showZone(zone).then(function(response){
				$.each(response.data, function(key, value){
					switch (key) {
						case "DS":
							$("#nsTable").append(nsRow(key, value));
							break;

						case "NS":
							$.each(value, function(i, v){
								$("#nsTable").append(nsRow(key, v));
							});
							break;
					}		
				});
			});
			break;
	}
}

function showDomainSelector(bool) {
	if (bool) {
		$(".header .domains").css("visibility", "visible");
	}
	else {
		$(".header .domains").css("visibility", "hidden");
	}
}

function setZones() {
	$(".header .domains").empty();

	let keys = Object.keys(zones);

	if (!(zone && keys.indexOf(zone) > 0)) {
		zone = keys[0];
	}

	$.each(zones, function(key, name){
		if (zone == key) {
			$(".header .domains").append('<option value="'+key+'" selected>'+name+'</option>');
		}
		else {
			$(".header .domains").append('<option value="'+key+'">'+name+'</option>');
		}
	});
}

function getZones() {
	let data = {
		action: "getZones"
	};

	return api(data);
}

function getRecords(zone) {
	let data = {
		action: "getRecords",
		zone: zone
	};

	return api(data);
}

function loadRecords() {
	getRecords(zone).then(function(response){
		$.each(response.data, function(key, record){
			if (!$("#dnsTable tr[data-id="+record.uuid+"]").length) {
				records[record.uuid] = record;
				$("#dnsTable").append(dnsRecordRow(record));
			}
		});
	});
}

function updateRecord(zone, record, column, value) {
	let data = {
		action: "updateRecord",
		zone: zone,
		record: record,
		column: column,
		value: value
	};

	return api(data);
}

function deleteRecord(zone, record) {
	let data = {
		action: "deleteRecord",
		zone: zone,
		record: record
	};

	return api(data);
}

function deleteZone(id) {
	let data = {
		action: "deleteZone",
		zone: id
	};

	return api(data);
}

function showZone(id) {
	let data = {
		action: "showZone",
		zone: id
	};

	return api(data);
}

function logout(id) {
	let data = {
		action: "logout"
	};

	return api(data);
}

function domainRow(id, name) {
	return '<tr data-id="'+id+'"><td class="name select">'+name+'</td><td class="dns"><div class="link" data-action="manageDNS">Manage DNS</div></td><td class="delete"><div class="actions"><div class="circle"></div><div class="icon delete" data-action="deleteDomain" title="Hold to delete"></div></div></td></tr>';
}

function dnsRecordRow(record) {
	let prio = record.prio || "";
	return '<tr data-id="'+record.uuid+'"><td class="type">'+record.type+'</td><td class="name"><div class="edit">'+record.name+'</div></td><td class="content"><div class="edit">'+record.content+'</div></td><td class="prio"><div class="edit">'+prio+'</div></td><td class="ttl"><div class="edit">'+record.ttl+'</div></td><td><div class="actions"><div class="circle"></div><div class="icon delete" data-action="deleteRecord"  title="Hold to delete"></div></div></td></tr>';
}

function nsRow(type, value) {
	return '<tr><td class="type">'+type+'</td><td class="value select">'+value+'</td></tr>';
}

function scrollEditables() {
	$("td.editing").each(function(e){
		let element = $(this).find(".edit");

		element.scrollLeft(0);
	});
}

function editField(element) {
	if (element.length) {
		let column = element[0].classList[0];
		let id = element.closest("tr").data("id");

		if (element.closest("table").attr("id") === "dnsTable") {
			let type = records[id]["type"];

			if (column == "prio" && type !== "MX") {
				return;
			}
		}

		let field = element.find(".edit");
		field.attr("contenteditable", true);

		let value = field.text();
		let length = value.length;

		if (length) {
			setCursor(field, length);
		}
		field.focus();
	}
}

function createZone() {
	let table = $("#createZoneTable");
	let domain = table.find("td.domain .edit").text();

	let data = {
		action: "createZone",
		domain: domain
	};

	return api(data);
}

function doCreateZone() {
	let row = $("#createZoneTable tr");

	createZone().then(function(r){
		if (!r.success) {
			let fields = r.fields;

			$.each(fields, function(key, field){
				row.find("td."+field).addClass("error");
			});
		}
		else {
			loadZones();
		}
	});
}

function addRecord() {
	let table = $("#addRecordTable");

	let type = table.find("td.type select").val();
	let name = table.find("td.name .edit").text();
	let content = table.find("td.content .edit").text();
	let prio = table.find("td.prio .edit").text();
	let ttl = table.find("td.ttl .edit").text();

	let data = {
		action: "addRecord",
		zone: zone,
		type: type,
		name: name,
		content: content,
		prio: prio,
		ttl: ttl
	};

	return api(data);
}

function doAddRecord() {
	let row = $("#addRecordTable tr");

	$.each(row.find("td.error"), function(key, r) {
		$(r).removeClass("error");
	});

	addRecord().then(function(r){
		if (!r.success) {
			let fields = r.fields;

			$.each(fields, function(key, field){
				row.find("td."+field).addClass("error");
			});
		}
		else {
			loadRecords();
			let fields = row.find("td .edit");
			$.each(fields, function(key, field){
				$(field).html('');
			});
			row.find("td.name .edit").focus();
		}
	});
}

function setCursor(element, position) {
	var range = document.createRange();
	var selection = window.getSelection();
	range.setStart(element[0].childNodes[0], position);
	range.collapse(true);
	selection.removeAllRanges();
	selection.addRange(range);
}

function makeUneditable(element) {
	element.attr("contenteditable", false);
	element.parent().removeClass("editing");
	element.parent().find(".actions").remove();
}

function handleAction(element, column, action) {
	let row = element.closest("tr");
	let id = row.data("id");
	let value = element.text();

	switch (action) {
		case "cancelEditRecord":
			if (id) {
				let originalValue = records[id][column];
				element.html(originalValue);
				row.find("td."+column).removeClass("error");
			}
			break;

		case "updateRecord":
			row.find("td."+column).removeClass("error");

			updateRecord(zone, id, column, value).then(function(r){
				if (!r.success) {
					let fields = r.fields;

					$.each(fields, function(key, field){
						row.find("td."+field).addClass("error");
					});
				}
				else {
					records[id][column] = r.data.value;
					element.text(records[id][column]);
				}
			});
			break;

		case "deleteRecord":
			row.remove();
			deleteRecord(zone, id);
			break;

		case "deleteDomain":
			row.remove();

			delete zones[id];
			setZones();
			deleteZone(id).then(function(){
				updateMenu();
			});

			if (!Object.keys(zones).length) {
				$(".section[data-section=domains]").removeClass("shown");
			}
			break;

		case "account":
			showPopover("account");
			break;

		case "settings":
			page = "settings";
			loadPage();
			$("#blackout").click();
			break;

		case "logout":
			logout().then(function(){
				goto("/");
			});
			break;

		default:
			adminAction(element, column, action);
			break;
	}

	makeUneditable(element);
}

function changeZone(id) {
	zone = id;
	$(".domains").val(zone);
}

function showPopover(name) {
	$("#blackout").show();
	$(".popover[data-name="+name+"]").addClass("shown");
}

function close() {
	$("#blackout").hide();
	$(".popover.shown").removeClass("shown");
}

function editActions(neg, pos) {
	return '<div class="actions"><div class="icon cancel" data-action="'+neg+'"></div><div class="icon save" data-action="'+pos+'"></div></div>';
}

$("html").on("click", "#blackout", function(){
	close();
});

$(window).on("popstate", function(e) {
	let split = e.target.location.pathname.substring(1).split("/");

	if (split.length > 1) {
		page = split[0];

		if (pageHasZoneInURL(page)) {
			zone = split[1];
		}

		setZones();
	}
	else {
		page = split[0];
	}

	loadPage(1);
});

$("html").on("mousedown", ".icon.delete", function(e){
	let container = $(e.target).parent().find(".circle");
	let action = $(e.target).data("action");

	progress = new ProgressBar.Circle(container[0], {
	    color: css.getPropertyValue("--deleteColor"),
	    strokeWidth: 5,
	    text: {
	        value: ''
	    },
	    duration: 2000,
	});
	progress.animate(1.0, function(){
		if (container.children().length) {
			handleAction($(e.target), null, action);
		}
	});
});

$("html").on("mouseup", ".icon.delete", function(e){
	let container = $(e.target).parent().find(".circle");

	if (container.children().length) {
		progress.destroy();
	}
});

$("html").on("mouseout", ".icon.delete", function(e){
    $(this).mouseup();
});

$("html").on("click", function(e){
	scrollEditables();

	let target = $(e.target);
	let action = target.data("action");
	let parent = target.parent();
	let row = target.closest("tr");

	if (parent.is(".editable tr")) {
		if (!target.hasClass("editing")) {
			editField(target);
		}
	}
	else if (target.is(".link")) {
		switch (action) {
			case "manageDNS":
				changeZone(row.data("id"));
				page = "dns";
				loadPage();
				break;
		}
	}
	else if (parent.is(".add .actions")) {
		$.each(row.find("td.error"), function(key, r) {
			$(r).removeClass("error");
		});

		switch (action) {
			case "createZone":
				doCreateZone();
				break;

			case "addRecord":
				doAddRecord();
				break;
		}
	}
	else if (parent.is(".actions")) {
		let edit = target.parent().parent().find(".edit");
		let column = target.closest("td")[0].classList[0];

		if (edit.length) {
			handleAction(target.parent().parent().find(".edit"), column, action);
		}
		else {
			if (action && !action.startsWith("delete")) {
				handleAction(target, column, action);
			}
		}
	}
	else if (target.hasClass("action")) {
		handleAction(target, null, action);
	}
	else if (target.hasClass("editing")) {
		target.find(".edit").focus();
	}
});

$("html").on("focus", ".editable .edit", function(e){
	let table = $(e.target).closest("table");
	if (table.attr("id") == "addRecordTable") {
		return;
	}

	let neg = table.data("neg");
	let pos = table.data("pos");

	if (!$(e.target).parent().hasClass("editing")) {
		$(e.target).parent().addClass("editing");
		$(e.target).parent().append(editActions(neg, pos));
	}
});

$("html").on("keydown", ".edit[contenteditable=true]", function(e){
	if (e.which == 13) {
		e.preventDefault();
	}
});

$("html").on("keyup", "#createZoneTable .edit[contenteditable=true]", function(e){
	if (e.which == 13) {
		doCreateZone();
	}
});

$("html").on("keyup", "#addRecordTable .edit[contenteditable=true]", function(e){
	if (e.which == 13) {
		doAddRecord();
	}
});

$("html").on("keyup", ".editable .edit[contenteditable=true]", function(e){
	var action;

	let table = $(e.target).closest("table");

	switch (e.which) {
		case 13:
			action = table.data("pos");
			break;

		case 27:
			action = table.data("neg");
			break;
	}

	if (action) {
		let column = $(e.target).parent()[0].classList[0];
		handleAction($(e.target), column, action);
	}
});

$("html").on("click", ".menu .item", function(e){
	if ($(this).hasClass("disabled")) {
		return;
	}

	page = $(this).data("page");
	loadPage();
});

$("html").on("change", ".header .domains", function(e){
	zone = $(this).val();
	loadPage();
});

$("html").on("change", "#addRecordTable td.type select", function(e){
	let cell = $("#addRecordTable td.prio");
	let field = cell.find(".edit");

	if ($(this).val() == "MX") {
		cell.addClass("editing");
		field.attr("contenteditable", true);
	}
	else {
		cell.removeClass("editing");
		field.attr("contenteditable", false);
		field.text('');
	}

	$("#addRecordTable td.name .edit").focus();
});

$("html").on("submit", "form", function(e){
	e.preventDefault();

	let form = $(e.target);

	$.each(form.find("input.error"), function(key, r) {
		$(r).removeClass("error");
	});

	let data = serializeObject($(this).serializeArray());
	api(data).then(function(r){
		if (!r.success) {
			let fields = r.fields;

			$.each(fields, function(key, field){
				form.find("input[name="+field+"]").addClass("error");
				form.find("input[name="+field+"]").attr("data-com-onepassword-filled", "");
			});

			if (r.message) {
				alert(r.message);
			}
		}
		else {
			afterSubmit(form);
		}
	});
});

function afterSubmit(form) {
	let action = form.find(".submit").data("action");

	switch (action) {
		case "login":
		case "signup":
			goto("/domains");
			break;

		case "settings":
			break;
	}
}

$("html").on("click", ".submit", function(e){
	$(this).closest("form").submit();
});

$("html").on("keyup", "input", function(e){
	if (e.which == 13) {
		$(this).closest("form").submit();
	}
});

function goto(page) {
	window.location = page;
}

function loadZones() {
	getZones().then(function(response){
		zones = {};

		let z = response.data;

		$.each(z, function(k, zone){
			zones[zone.uuid] = zone.name;
		});
		
		setZones();
		loadPage(1);
		updateMenu();
	});
}

$(function(){
	page = $(".main").data("page");
	zone = $(".main").data("zone");

	loadZones();
});