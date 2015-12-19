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
}

$("#more").click(function() {
	$("#main").addClass("other");
	$("#other-queries").addClass("other");
})