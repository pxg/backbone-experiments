<?php

require 'Slim/Slim.php';

$app = new Slim();

$app->get('/questions', 'getQuestions');
//$app->get('/wines/:id',	'getWine');
//$app->get('/wines/search/:query', 'findByName');
$app->post('/questions', 'addQuestion');
$app->put('/questions/:id', 'updateQuestion');
$app->delete('/questions/:id',	'deleteQuestion');

$app->run();

function getQuestions() {
	$sql = "SELECT * FROM questions ORDER BY asked DESC, weight DESC";
	try {
		$db = getConnection();
		$stmt = $db->query($sql);  
		$wines = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		// echo '{"wine": ' . json_encode($wines) . '}';
		echo json_encode($wines);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

/*
function getWine($id) {
	$sql = "SELECT * FROM wine WHERE id=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$wine = $stmt->fetchObject();  
		$db = null;
		echo json_encode($wine); 
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
*/

function addQuestion() {
	error_log('addQuestion\n', 3, '/var/tmp/php.log');
	$request = Slim::getInstance()->request();
	$data = json_decode($request->getBody());
	$sql = "INSERT INTO questions (question, created_at, updated_at) VALUES (:question, NOW(), NOW())";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("question", $data->question); // map to post request
		$stmt->execute();
		$data->id = $db->lastInsertId();
		$db = null;
		echo json_encode($data); 
	} catch(PDOException $e) {
		error_log($e->getMessage(), 3, '/var/tmp/php.log');
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}


function updateQuestion($id) {
	$request = Slim::getInstance()->request();
	$body = $request->getBody();
	$data = json_decode($body);
	$sql = "UPDATE questions SET question=:question, weight=:weight WHERE id=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("question", $data->question);
		$stmt->bindParam("weight", $data->weight);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;
		echo json_encode($data); 
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}


function deleteQuestion($id) {
	$sql = "DELETE FROM questions WHERE id=:id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);  
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}

/*
function findByName($query) {
	$sql = "SELECT * FROM wine WHERE UPPER(name) LIKE :query ORDER BY name";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$query = "%".$query."%";  
		$stmt->bindParam("query", $query);
		$stmt->execute();
		$wines = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo json_encode($wines);
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}'; 
	}
}
*/

function getConnection() {
	$dbhost="127.0.0.1";
	$dbuser="root";
	$dbpass="root";
	$dbname="favourite_q";
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);	
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

?>