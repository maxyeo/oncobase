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
  var resumeH = $("#resume-module").height();

  $("#resume-viewer").css("height",resumeH);
  
}

$("#navicon").click(function() {
	
})