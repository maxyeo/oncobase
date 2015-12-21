<?php
require 'config.php';
try {
    $conn = new PDO("mysql:host=$server;dbname=$database", $username, $password);
}
catch(PDOException $e) {
    exit('Bad Request');
}
if (isset($_GET)) {
	$data_missing = array();
	if (empty($_GET['race'])) {
		$data_missing[] = 'race';
	} else {
		$race = trim($_GET['race']);
	}
	if (empty($_GET['gender'])) {
		$data_missing[] = 'gender';
	} else {
		$gender = trim($_GET['gender']);
	}
	if (empty($_GET['cancer'])) {
		$data_missing[] = 'cancer';
	} else {
		$cancer = trim($_GET['cancer']);
	}
	if (empty($data_missing)) {
		$sql = 'SELECT Year, Rate
				FROM akpsi.onco_cancer_breakdown
				WHERE RaceEthnicity = ? AND Sex = ? AND CancerSite = ?
		';
		$stmt = $conn->prepare($sql);
		try {
			$stmt->execute(array($race, $gender, $cancer));
			$rows = array();
			while ($row = $stmt->fetch(PDO::FETCH_NUM, PDO::FETCH_ORI_NEXT)) {
				$rows[] = $row;
		    }
		    echo json_encode($rows);
		    $stmt = null;
		} catch(PDOException $e) {
			exit('Bad Request');
		}
	} else {
		exit('Bad Request');
	}
}
?>