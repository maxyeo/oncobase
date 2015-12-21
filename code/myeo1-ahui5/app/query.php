<?php
// CONNECT TO THE DATABASE
// CONFIG.PHP IS HIDDEN FROM GIT REPO FOR SECURITY
require 'config.php';
try {
    $conn = new PDO("mysql:host=$server;dbname=$database", $username, $password);
}
catch(PDOException $e) {
    header('location: sorry.html');
}
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
	if (empty($data_missing)) {
		$sql = 'SELECT Rate FROM akpsi.onco_cancer_breakdown WHERE RaceEthnicity = ? AND Sex = ? AND CancerSite = ? AND Year = ?';
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
	if (!isset($data)) {
		$data = 0;
	}
	if ($data == -1) {
		$output = "<p>Not enough data was collected to be considered statistically significant</p>";
	} else {
		$output = '<p><span class="data">' . $data . '</span> of every 100,000 ';
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
			$output .= strtolower($cancer);
		}
		$output .= ' cancer in ' . $year . ' in the US.</p>';	
	}
} else if (isset($_GET['a'])) {
	if ($_GET['a'] == 1) {
		$sql = 'SELECT SUM(A)
				FROM (SELECT Rate AS A 
					FROM onco_cancer_breakdown 
					WHERE CancerSite = "All Sites" AND Year < 2012 AND Year > 2001 AND RaceEthnicity = "All (includes Hispanic)" AND Sex = "Both Sexes") AS result;
		';
		$stmt = $conn->prepare($sql);
		try {
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
				$data = $row[0];
		    }
		    $stmt = null;
		} catch(PDOException $e) {
			header('location: sorry.html');
		}
		$output = '<p><span class="data">' . $data . '</span> of every 100,000 people had any kind of cancer in the last decade in the US.</p>';
	} else if ($_GET['a'] == 2) {
		$sql = 'SELECT MaleCancerSite, ABS(MaleRate - FemaleRate)
				FROM (SELECT CancerSite AS MaleCancerSite, Rate AS MaleRate
					FROM onco_cancer_breakdown
					WHERE Year = 2012 and RaceEthnicity = "All (includes Hispanic)" AND Sex = "Male" AND Rate <> -1) AS result,
					(SELECT CancerSite AS FemaleCancerSite, Rate AS FemaleRate
					FROM onco_cancer_breakdown
					WHERE Year = 2012 and RaceEthnicity = "All (includes Hispanic)" AND Sex = "Female" AND Rate <> -1) AS result2
				WHERE MaleCancerSite = FemaleCancerSite
				ORDER BY ABS(MaleRate - FemaleRate) DESC;
		';
		$stmt = $conn->prepare($sql);
		try {
			$output = '<p>Cancers that have the greatest difference in rates between the two sexes in 2012.</p><table><tr><th>Cancer</th><th>Difference in Rates</th></tr>';
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
				$output .= '<tr><td>' . $row[0] . '</td><td>' . $row[1] . '</td></tr>';
		    }
		    $output .= '</table>';
		    $stmt = null;
		} catch(PDOException $e) {
			header('location: sorry.html');
		}
	} else if ($_GET['a'] == 3) {
		$sql = 'SELECT ABS(MinMaleRate - MaxFemaleRate)
				FROM (SELECT MIN(MaleRate) AS MinMaleRate
					FROM (SELECT Rate AS MaleRate
						FROM onco_cancer_breakdown
						WHERE Year = 2002 AND RaceEthnicity = "All (includes Hispanic)" AND Sex = "Male" AND CancerSite <> "All Sites" AND Rate <> -1) AS result) AS result2,
					(SELECT MAX(FemaleRate) AS MaxFemaleRate
					FROM (SELECT Rate AS FemaleRate
						FROM onco_cancer_breakdown
						WHERE Year = 2002 AND RaceEthnicity = "All (includes Hispanic)" AND Sex = "Female" AND CancerSite <> "All Sites" AND Rate <> -1) AS result3) AS result4;
		';
		$stmt = $conn->prepare($sql);
		try {
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
				$data = $row[0];
		    }
		    $stmt = null;
		} catch(PDOException $e) {
			header('location: sorry.html');
		}
		$output = '<p>The difference in rates between the most common type of cancer for women and the least common type of cancer for men in 2002 is <span class="data">' . $data . '</span> incidents per 100,000 people.</p>';
	} else if ($_GET['a'] == 4) {
		$sql = 'SELECT Rate2012 - Rate1975
				FROM (SELECT Rate as Rate2012
					FROM onco_cancer_breakdown
					WHERE Year = "2012" AND RaceEthnicity = "All (includes Hispanic)" AND Sex = "Both Sexes" AND CancerSite = "All Sites" AND Rate <> -1) AS result,
					(SELECT Rate as Rate1975
					FROM onco_cancer_breakdown
					WHERE Year = "1975" AND RaceEthnicity = "All (includes Hispanic)" AND Sex = "Both Sexes" AND CancerSite = "All Sites" AND Rate <> -1) AS result2;
		';
		$stmt = $conn->prepare($sql);
		try {
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
				$data = $row[0];
		    }
		    $stmt = null;
		} catch(PDOException $e) {
			header('location: sorry.html');
		}
		$output = '<p>The difference in cancer rates in 1975 and cancer rates in 2012 (negative meaning cancer rates have gone down over the years) is <span class="data">' . $data . '</span> of every 100,000 people.</p>';
	} else if ($_GET['a'] == 5) {
		$sql = 'SELECT RaceEthnicity, CancerSite, Rate
				FROM (SELECT RaceEthnicity AS RaceEthnicityInQuestion, MAX(Rate) AS MaxRate
					FROM onco_cancer_breakdown
					WHERE Year = 1996 AND Sex = "Both Sexes" AND CancerSite <> "All Sites"
					AND Rate <> -1
					GROUP BY RaceEthnicity) AS result, onco_cancer_breakdown WHERE Rate = MaxRate AND RaceEthnicity = RaceEthnicityInQuestion;
		';
		$stmt = $conn->prepare($sql);
		try {
			$output = '<p>The most common types of cancers for each racial subgroup in 1996.</p><table><tr><th>Race</th><th>Cancer</th><th>Rate</th></tr>';
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
				$output .= '<tr><td>' . $row[0] . '</td><td>' . $row[1] . '</td><td>' . $row[2] . '</td></tr>';
		    }
		    $output .= '</table>';
		    $stmt = null;
		} catch(PDOException $e) {
			header('location: sorry.html');
		}
	} else if ($_GET['a'] == 6) {
		$sql = 'SELECT SUM(YearRate)
				FROM (SELECT Rate AS YearRate
					FROM onco_cancer_breakdown
					WHERE Year > 1997 AND Year < 2013 AND CancerSite = "Prostate" AND Sex = "Male" AND Rate <> -1 AND RaceEthnicity = "Black (includes Hispanic)") AS Result;
		';
		$stmt = $conn->prepare($sql);
		try {
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
				$data = $row[0];
		    }
		    $stmt = null;
		} catch(PDOException $e) {
			header('location: sorry.html');
		}
		$output = '<p><span class="data">' . $data . '</span> of every 100,000 black males have developed prostate cancer in the past fifteen years.</p>';
	} else if ($_GET['a'] == 7) {
		$sql = 'SELECT 2012Rate - 1987Rate
				FROM (SELECT Rate AS 2012Rate
					FROM onco_cancer_breakdown
					WHERE Year = 2012 AND RaceEthnicity = "White (includes Hispanic)" AND Sex = "Female" AND CancerSite = "Female Breast" AND Rate <> -1) AS result,
					(SELECT Rate AS 1987Rate
					FROM onco_cancer_breakdown
					WHERE Year = 1987 AND RaceEthnicity = "White (includes Hispanic)" AND Sex = "Female" AND CancerSite = "Female Breast" AND Rate <> -1) AS result2;
		';
		$stmt = $conn->prepare($sql);
		try {
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
				$data = $row[0];
		    }
		    $stmt = null;
		} catch(PDOException $e) {
			header('location: sorry.html');
		}
		$output = '<p>The difference in the rate of cancer for breast cancer (non-in situ breast cancer) in the past twenty-five years for white females, where a negative number indicates a decrease over the years is <span class="data">' . $data . '</span> of every 100,000.</p>';
	} else if ($_GET['a'] == 8) {
		$sql = 'SELECT 2012CancerSite AS IncreasedCancerSites
				FROM (SELECT CancerSite AS 2012CancerSite, Rate AS 2012Rate
					FROM onco_cancer_breakdown
					WHERE Year = 2012 AND RaceEthnicity = "All (includes Hispanic)" AND Sex = "Both Sexes" AND CancerSite <> "All Sites" AND Rate <> -1) AS result,
					(SELECT CancerSite AS 1987CancerSite, Rate AS 1987Rate
					FROM onco_cancer_breakdown
					WHERE Year = 1987 AND RaceEthnicity = "All (includes Hispanic)" AND Sex = "Both Sexes" AND CancerSite <> "All Sites" AND Rate <> -1) AS result2
				WHERE 2012CancerSite = 1987CancerSite AND 2012Rate >= 1987Rate;
		';
		$stmt = $conn->prepare($sql);
		try {
			$output = '<p>cancers have not decreased in rates of incidence from 1975 to 2012.</p><table><tr><th>Cancer</th></tr>';
			$stmt->execute();
			while ($row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
				$output .= '<tr><td>' . $row[0] . '</td></tr>';
		    }
		    $output .= '</table>';
		    $stmt = null;
		} catch(PDOException $e) {
			header('location: sorry.html');
		}
	} else {
		$output = '<p>Invalid Query</p>';
	}
} else {
	$output = '<p>Invalid Query</p>';
}
// QUERY
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
	<div id="wrapper">
	    <div id="main">
	        <div class="grid">
	            <h1>Oncobase</h1>
		        <?php print $output; ?>
	            <div id="back-home">
	                <a href="index.html">Find another cancer stat</a>
	            </div>
	        </div>
	    </div>
	    
	    <footer>
	        <h6>© ONCOBASE. ALL RIGHTS RESERVED 2015</h6>
	    </footer>
	</div>

    <script type="text/javascript" src="js/jquery.js"></script>
    <script type="text/javascript" src="js/style.js"></script>
</body>
</html>