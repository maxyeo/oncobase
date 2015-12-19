<?php

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

require 'config.php';

try {
    $conn = new PDO("mysql:host=$server;dbname=$database", $username, $password);
}
catch(PDOException $e) {
    header('location: sorry.html');
}

echo $race;
echo $gender;
echo $cancer;
echo $year;

if (empty($data_missing)) {
	$sql = "INSERT INTO hcd.christmassale15 (firstname, lastname, email, size) VALUES (?, ?, ?, ?)";
	$stmt = $conn->prepare($sql);
	
	try {
		$stmt->execute(array($fname, $lname, $email, $size));
	} catch(PDOException $e) {
		header('location: sorry.html');
	}

	$affected_rows = $stmt->rowCount();

	if ($affected_rows == 1) {
		header('location: thanks.html');
	} else {
		header('location: sorry.html');
	}
	
} else {
	foreach ($data_missing as $missing) {
		echo "$missing<br />";
	}
	header('location: sorry.html');
}

?>