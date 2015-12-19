<?php
// CHECK FOR MISSING DATA
if (isset($_POST['submit'])) {
	$data_missing = array();
	if (empty($_POST['race'])) {
		$data_missing[] = 'race';
	} else {
		$race = trim($_POST['race']);
	}
	if (empty($_POST['gender'])) {
		$data_missing[] = 'gender';
	} else {
		$gender = trim($_POST['gender']);
	}
	if (empty($_POST['cancer'])) {
		$data_missing[] = 'cancer';
	} else {
		$cancer = trim($_POST['cancer']);
	}
	if (empty($_POST['year'])) {
		$data_missing[] = 'year';
	} else {
		$year = trim($_POST['year']);
	}
}
// CONNECT TO THE DATABASE
// CONFIG.PHP IS HIDDEN FROM GIT REPO FOR SECURITY
require 'config.php';
try {
    $conn = new PDO("mysql:host=$server;dbname=$database", $username, $password);
}
catch(PDOException $e) {
    header('location: sorry.html');
}
// QUERY
if (empty($data_missing)) {
	$sql = "SELECT Rate FROM akpsi.onco_cancer_site_breakdown WHERE RaceEthnicity = ? AND Sex = ? AND CancerSite = ? AND Year = ?";
	$stmt = $conn->prepare($sql);
	try {
		$stmt->execute(array($race, $gender, $cancer, $year));
		while ($row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
			$data = $row[0];

	    }
	    $stmt = null;
	} catch(PDOException $e) {
		header('location: sorry.html');
	}
} else {
	header('location: sorry.html');
}
?>

<!doctype html>
<html>
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width">

    <title>Oncobase | Databases 600.315/415</title>
    <meta name="description" content="Oncobase is the go to site for your cancer questions ">
    <meta name="author" content="Alwin Hui and Max Yeo">

    <link rel="shortcut icon" href="favicon.ico">
    <link rel="apple-touch-icon" href="img/apple-touch-icon.png">

    <meta name="theme-color" content="#2c2f43">

    <meta property="og:title" content="Oncobase Home"/>
    <meta property="og:url" content=""/>
    <meta property="og:image" content=""/>
    <meta property="og:site_name" content="Oncobase | Databases 600.315/415"/>
    <meta property="og:description" content=""/>

    <link rel="stylesheet" type="text/css" href="global.css">
    <link rel="stylesheet" type="text/css" href="style.css">
</head>

<body>

    <div id="main">
        <div class="grid">
            <h1>Oncobase</h1>
            <p>
	            <?php
	            $output = $data . ' of every 100,000 ';
	            if (strcmp($race, 'All (includes Hispanic)') == 0) {
	            	if (strcmp($gender, 'Both Sexes') == 0) {
	            		$output .= 'people ';
	            	} else if (strcmp($gender, 'Male') == 0) {
	            		$output .= 'males ';
	            	} else {
	            		$output .= 'females ';
	            	}
	            } else if (strcmp($race, 'White (includes Hispanic)') == 0) {
	            	if (strcmp($gender, 'Both Sexes') == 0) {
	            		$output .= 'whites ';
	            	} else if (strcmp($gender, 'Male') == 0) {
	            		$output .= 'white males ';
	            	} else {
	            		$output .= 'white females ';
	            	}
	            } else {
	            	if (strcmp($gender, 'Both Sexes') == 0) {
	            		$output .= 'blacks ';
	            	} else if (strcmp($gender, 'Male') == 0) {
	            		$output .= 'black males ';
	            	} else {
	            		$output .= 'black females ';
	            	}
	            }
	            $output .= 'had ';
	            if (strcmp($cancer, 'All Sites') == 0) {
	            	$output .= 'any kind of ';
	            } else {
	            	$output .= $cancer;
	            }
	            $output .= ' cancer in ' . $year . ' in the US.';
	            print $output;
	            ?>
            </p>
        </div>
    </div>
    
    <footer>
        <h6>© ONCOBASE. ALL RIGHTS RESERVED 2015</h6>
    </footer>

    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/style.js"></script>
</body>
</html>