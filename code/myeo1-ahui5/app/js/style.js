$(document).ready(function() {  
	sizeitup();
});

$(window).resize(function() {
	sizeitup();
});
window.onload = function() {
	sizeitup();
};

function sizeitup() {
	var bodyH = $(window).height();
	if ($("#main").height() > (bodyH - 200)) {
		$("#main, footer").addClass('static');
	} else {
		$("#main, footer").removeClass('static');
	}
}

$("#more").click(function() {
	$("#main").addClass("other");
	$("#other-queries").addClass("other");
})

$("#less").click(function() {
	$("#main").removeClass("other");
	$("#other-queries").removeClass("other");
})