var selectedCode = "";

$(document).ready(function(){
	// start modas and tabs
	$('.tabs').tabs();
	$('.modal').modal();

	// start countdowns
	setInterval(countdown, 1000);
});

// show the modal popup
function openModalUnequip(id, name, desc, icon) {
	selectedCode = id;
	$('#modalIcon').html(icon);
	$('#modalName').html(name);
	$('#modalDesc').html(desc);
	$('#modal').modal('open');
}

// start a new purchase
function unequip() {
	apretaste.send({
		command: "AMULETOS UNEQUIP",
		data: {'id': selectedCode},
		redirect: true
	});
}

// show the modal popup
function openModalBuy(code) {
	selectedCode = code;
	$('#modal').modal('open');
}

// start a new purchase
function buy() {
	apretaste.send({
		command: "AMULETOS PAY", 
		data: {'code': selectedCode},
		redirect: true
	});
}

// make countdowns to work 
function countdown() {
	$('.countdown').each(function(i, e) {
		// get the counter from the page
		var cnt = $(e).text().trim();

		// break up the counter
		var time = cnt.split(":");
		var hours = time[0];
		var mins = time[1];
		var secs = time[2];

		// count down
		if(secs-- <= 0) {
			secs = 60;
			if(mins-- <= 0) {
				secs = 60;
				if(hours-- <= 0) return;
			}
		}

		// add two digits to the counter
		if(hours.toString().length === 1) hours = "0" + hours; 
		if(mins.toString().length === 1) mins = "0" + mins; 
		if(secs.toString().length === 1) secs = "0" + secs; 

		// put the counter back to the page
		cnt = hours + ':' + mins + ':' + secs;
		$(e).html(cnt);
	});
}