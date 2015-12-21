var race, gender, cancer, str;

$(document).ready(function() {  
	showData();
});
window.onload = function() {
	showData();
};

function showData() {
	race = $('#race').val();
	gender = $('#gender').val();
	cancer = $('#cancer').val();
	str = 'years.php?race=' + race + '&gender=' + gender + '&cancer=' + cancer;
	visual(str);
}

function sorry() {
	$("#graph").html("<p>Unfortunately, I can't access my database right now.</p>");
}

function nodata() {
	$("#graph").html("<p>Unfortunately, this query resulted in no values.</p>");
}

function clean() {
	$("#graph").html("");
	svg = d3.select("#graph").append("svg")
		.attr("width", width + margin.left + margin.right)
		.attr("height", height + margin.top + margin.bottom)
		.append("g")
		.attr("transform", "translate(" + margin.left + "," + margin.top + ")");
}

var margin = {top: 20, right: 20, bottom: 30, left: 40},
	width = 960 - margin.left - margin.right,
	height = 500 - margin.top - margin.bottom;

// var formatPercent = d3.format(".0%");

var x = d3.scale.ordinal()
	.rangeRoundBands([0, width], .1, 1);

var y = d3.scale.linear()
	.range([height, 0]);

var xAxis = d3.svg.axis()
	.scale(x)
	.orient("bottom");

var yAxis = d3.svg.axis()
	.scale(y)
	.orient("left");

var svg = d3.select("#graph").append("svg")
	.attr("width", width + margin.left + margin.right)
	.attr("height", height + margin.top + margin.bottom)
	.append("g")
	.attr("transform", "translate(" + margin.left + "," + margin.top + ")");

function visual(input) {
	d3.json(input, function(data) {

		if (data.length == 0) {
			nodata();
		} else {
			console.log(data);
			clean();

			data.forEach(function(d) {
				d.letter = d[0];
				d.frequency = +d[1];
			});

			x.domain(data.map(function(d) { return String(d.letter).substring(2); }));
			y.domain([0, d3.max(data, function(d) { return d.frequency; })]);

			svg.append("g")
				.attr("class", "x axis")
				.attr("transform", "translate(0," + height + ")")
				.call(xAxis);

			svg.append("g")
				.attr("class", "y axis")
				.call(yAxis)
				.append("text")
				.attr("transform", "rotate(-90)")
				.attr("y", 6)
				.attr("dy", ".71em")
				.style("text-anchor", "end")
				.text("Rate (per 100,000 people)");

			svg.selectAll(".bar")
				.data(data)
				.enter().append("rect")
				.attr("class", "bar")
				.attr("x", function(d) { return x(String(d.letter).substring(2) ); })
				.attr("width", x.rangeBand())
				.attr("y", function(d) { return y(d.frequency); })
				.attr("height", function(d) { return height - y(d.frequency); });

			d3.select("#sort-switch").on("change", change);

			var sortTimeout = setTimeout(function() {
				//d3.select("#sort-switch").property("checked", true).each(change);
			}, 2000);

			function change() {
				clearTimeout(sortTimeout);

				// Copy-on-write since tweens are evaluated after a delay.
				var x0 = x.domain(data.sort(this.checked
					? function(a, b) { return b.frequency - a.frequency; }
					: function(a, b) { return d3.ascending(a.letter, b.letter); })
					.map(function(d) { return String(d.letter).substring(2); }))
					.copy();

				svg.selectAll(".bar")
					.sort(function(a, b) { return x0(String(a.letter).substring(2) ) - x0(String(b.letter).substring(2) ); });

				var transition = svg.transition().duration(750),
					delay = function(d, i) { return i * 50; };

				transition.selectAll(".bar")
					.delay(delay)
					.attr("x", function(d) { return x0(String(d.letter).substring(2) ); });

				transition.select(".x.axis")
					.call(xAxis)
					.selectAll("g")
					.delay(delay);
			}
		}

	});
}