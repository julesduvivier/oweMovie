<?php
/*
Created by Jules Duvivier
*/

require 'Slim/Slim.php';
$app = new Slim();


$app->get('/medias/store/:storeId', 'getMedias');
$app->post('/medias/token', 'verifToken');
$app->get('/medias/:id','getMedia');
$app->get('/medias/retard/:id','infoRetard');
$app->get('/medias/listStore/','getStore');
$app->get('/medias/search/:query', 'getMediaByName');
$app->post('/medias/store/:storeId', 'addMedia');
$app->post('/medias/authentification', 'authentification');
$app->post('/medias/authentificationClient', 'authentificationClient');
$app->post('/medias/inscriptionClient', 'inscriptionClient');
$app->put('/medias/store/:storeId/:id', 'updateMedia');
$app->put('/medias/back/:id', 'backMedia');
$app->post('/medias/rent/', 'rentMovie');
$app->delete('/medias/:id',	'deleteMedia');
$app->delete('/medias/location/:locationid', 'deleteLocation');
$app->get('/medias/location/:id','getLocation');
$app->get('/medias/locationEmp/:storeid','getLocationEmp');

$app->run();

function verifToken()
{
	$request = Slim::getInstance()->request();
	$v = json_decode($request->getBody());
	$token = $v->token;
	$id = $v->id;
	$sql = 'SELECT token FROM employee WHERE id = :id';
	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("id", $id);
	$stmt->execute();
	$token2 = $stmt->fetch();

	if ($token == $token2[0])
	{
		echo '{"success":{"text":"ok"}}';
	}

	else
	{
		echo '{"success":{"text":"incorrect"}}';
	}

}

function backMedia($id)
{
	$sql = "SELECT videoid,support_id FROM video_location WHERE locationid = :id";

	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("id", $id);
	$stmt->execute();
	$video = $stmt->fetch();
	$db = null;

	echo $video['videoid'];
	echo $video['support_id']  . '<br/>';


	$sql2 = "SELECT  supportid FROM video_support WHERE videoid = :videoid";
	$db2 = getConnection();
	$stmt2 = $db2->prepare($sql2);
	$stmt2->bindParam("videoid", $video['videoid']);
	$stmt2->execute();
	$support = $stmt2->fetch();
	$db2 = null;

	echo $support['supportid'] . '<br/>';


	$sql3 = "SELECT stockDvd,stockCassette,stockBlury FROM support WHERE id = :id";
	$db3 = getConnection();
	$stmt3 = $db3->prepare($sql3);
	$stmt3->bindParam("id", $support['supportid']);
	$stmt3->execute();
	$stock = $stmt3->fetch();
	$db3 = null;


	echo $stock['stockDvd'] . '<br/>' . $stock['stockCassette'] . '<br/>' . $stock['stockBlury'];

	$sd1 = $stock['stockDvd'] +1;
	$sc1 = $stock['stockCassette'] +1;
	$sb1  = $stock['stockBlury'] +1;


	$sql4 = "UPDATE support SET stockDvd=:dvd, stockCassette=:cst, stockBlury=:blu  WHERE id=:id";

	$db4= getConnection();
	$stmt4 = $db4->prepare($sql4);
	if ($video['support_id'] == 1)
	{
		$stmt4->bindParam("dvd",  $sd1);
		$stmt4->bindParam("cst", $stock['stockCassette']);
		$stmt4->bindParam("blu",$stock['stockBlury']);
		$stmt4->bindParam("id", $support['supportid']);
	}
	else if ($video['support_id'] == 2)
	{
		$stmt4->bindParam("dvd",  $stock['stockDvd']);
		$stmt4->bindParam("cst", $sc1);
		$stmt4->bindParam("blu",$stock['stockBlury']);
		$stmt4->bindParam("id", $support['supportid']);
	}
	else if ($video['support_id'] == 3)
	{
		$stmt4->bindParam("dvd",  $stock['stockDvd']);
		$stmt4->bindParam("cst", $stock['stockCassette']);
		$stmt4->bindParam("blu",$sb1);
		$stmt4->bindParam("id", $support['supportid']);
	}
	else
	{
		$stmt4->bindParam("dvd",  $stock['stockDvd']);
		$stmt4->bindParam("cst", $stock['stockCassette']);
		$stmt4->bindParam("blu",$stock['stockBlury']);
		$stmt4->bindParam("id", $support['supportid']);
	}

	$stmt4->execute();
	$db4 = null;


	$backdate = date('y-m-d');
	$sql5 = "UPDATE location SET backdate=:backdate WHERE id=:id";
	$db5= getConnection();
	$stmt5 = $db5->prepare($sql5);
	$stmt5->bindParam("backdate",$backdate);
	$stmt5->bindParam("id", $id);
	$stmt5->execute();
	$db5 = null;



	echo "Update Ok";



}

function infoRetard($id)
{

	$sql = "SELECT m.firstname, m.lastname, m.phone, m.email, v.title
	FROM video_location vl
	JOIN member m
	on m.id = vl.memberid
	JOIN video v
	ON v.id = vl.videoid
	WHERE locationid = :id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$location = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo '{"retard": ' . json_encode(utf8_encode_array(objectToArray($location))) . '}';
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}

}

function getLocation($id)
{
	$sql = "SELECT v.title, l.date, l.expirationdate,s.name, case vl.support_id
	when '1' then 'DVD'
	when '2' then 'Cassette'
	when '3' then 'Bluray'
	end as support_id,
	IF(l.backdate = '0000-00-00', '/', l.backdate) as backdate
	FROM video_location vl
	JOIN location l
	ON vl.locationid=l.id
	JOIN video v
	ON vl.videoid = v.id
	JOIN store s
	ON vl.storeid = s.id
	WHERE vl.memberid=:id
	ORDER BY l.date";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$location = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo '{"location": ' . json_encode(utf8_encode_array(objectToArray($location))) . '}';
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}
function getLocationEmp($storeId)
{
	$sql = "SELECT vl.locationid, v.title, l.date, l.expirationdate,s.name, case vl.support_id
	when '1' then 'DVD'
	when '2' then 'Cassette'
	when '3' then 'Bluray'
	end as support_id,
	IF(l.backdate = '0000-00-00', '/', l.backdate) as backdate
	FROM video_location vl
	JOIN location l
	ON vl.locationid=l.id
	JOIN video v
	ON vl.videoid = v.id
	JOIN store s
	ON vl.storeid = s.id
	WHERE vl.storeid=:storeId
	ORDER BY vl.locationid";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("storeId", $storeId);
		$stmt->execute();
		$location = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo '{"location": ' . json_encode(utf8_encode_array(objectToArray($location))) . '}';
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}

function rentMovie()
{
	$request = Slim::getInstance()->request();
	$rent= json_decode($request->getBody());
	$id = $rent->id;
	$duree = $rent->duree;
	$genre = $rent->genre;
	$storeId = $rent->storeId;
	$idClient = $rent->idClient;
	$sd = $rent->sd;
	$sc = $rent->sc;
	$sb = $rent->sb;

	if ($genre==1)
	$sd -=1;
	if ($genre==2)
	$sc -= 1;
	if ($genre==3)
	$sb -= 1;

	$date = date('y-m-d');
	$dateS = date('U');

	if($duree==1)
	$expDate = $dateS + 604800;

	if($duree==2)
	$expDate = $dateS + 1209600;

	if($duree==3)
	$expDate = $dateS + 1814400;


	$expDate = date('y-m-d',$expDate);



	$sql = "INSERT INTO location (date, expirationdate) VALUES (:date,:expirationdate)";
	$sql2 = "INSERT INTO video_location (videoid,locationid,memberid,storeid,support_id) VALUES (:videoid,:locationid,:memberid,:storeid,:supportid)";
	$sql3 = "SELECT supportid FROM video_support WHERE videoid=:id";
	$sql4 = "UPDATE support SET stockDvd=:dvd, stockCassette=:cst, stockBlury=:blu  WHERE id=:id";



	try {

		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("date", $date);
		$stmt->bindParam("expirationdate", $expDate);
		$stmt->execute();
		$locationid = $db->lastInsertId();
		$db = null;

		$db2 = getConnection();
		$stmt2 = $db2->prepare($sql2);
		$stmt2->bindParam("videoid", $id);
		$stmt2->bindParam("locationid", $locationid);
		$stmt2->bindParam("memberid", $idClient);
		$stmt2->bindParam("storeid", $storeId);
		$stmt2->bindParam("supportid", $genre);
		$stmt2->execute();
		$db2 = null;

		$db3 = getConnection();
		$stmt3 = $db3->prepare($sql3);
		$stmt3->bindParam("id", $id);
		$stmt3->execute();
		$value = $stmt3->fetch();
		$db3=null;

		$sid = $value['supportid'];



		$db4= getConnection();
		$stmt4 = $db4->prepare($sql4);
		$stmt4->bindParam("dvd", $sd);
		$stmt4->bindParam("cst", $sc);
		$stmt4->bindParam("blu", $sb);
		$stmt4->bindParam("id",  $sid);
		$stmt4->execute();
		$db4 = null;






		echo '{"success":{"text":"1","id":"' . $id .'","duree":"' . $duree .'","genre":"' . $genre .'","storeId":"' . $storeId. '"}}';

	} catch(PDOException $e) {
		echo '{"error":{"text":"'. $e->getMessage() .'"}}';
	}


}

function getStore()
{
	$sql = "SELECT * FROM store";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->execute();
		$store = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo '{"store": ' . json_encode(utf8_encode_array(objectToArray($store))) . '}';
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}

function authentification()
{
	$request = Slim::getInstance()->request();
	$auth = json_decode($request->getBody());
	$email = $auth->email;
	$password = $auth->password;
	$lastid=0;
	$sql = 'SELECT e.id,e.email,e.password,e.firstname,e.lastname,e.store_id, s.name FROM employee e JOIN store s ON e.store_id = s.id WHERE e.email = :email AND e.password = :password';

	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("email", $email);
	$stmt->bindParam("password",$password);
	$stmt->execute();
	$employee = $stmt->fetch();
	$count = $stmt->rowCount($sql);
	$db=null;
	if($count == 1){
		$token = uniqid() . rand();
		$sql2 = "UPDATE employee SET token=:token WHERE id=:id";
		$db2 = getConnection();
		$stmt2 = $db2->prepare($sql2);
		$stmt2->bindParam("token", $token);
		$stmt2->bindParam("id",  $employee['id']);
		$stmt2->execute();
		$db = null;


		echo '{"success":{"text":"'. $token .'","id":"' . $employee['id'] .'","storeId":"' . $employee['store_id'] .'","name":"' . $employee['firstname'] .'","store":"' . $employee['name'] .'"}}';

	}
	else
	{
		echo '{"success":{"text":"incorrect"}}';
	}
}

function authentificationClient()
{
	$request = Slim::getInstance()->request();
	$auth = json_decode($request->getBody());
	$email = $auth->email;
	$password = $auth->password;
	$lastid=0;
	$sql = 'SELECT * FROM member WHERE email = :email AND password = :password';

	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("email", $email);
	$stmt->bindParam("password",$password);
	$stmt->execute();
	$member = $stmt->fetch();
	$count = $stmt->rowCount($sql);
	$db=null;
	if($count == 1){
		echo '{"success":{"text":"1","id":"' . $member['id'] .'","name":"' . $member['firstname'] .'","zipCode":"' . $member['zipcode'] .'"}}';

	}
	else
	{
		echo '{"success":{"text":"incorrect"}}';
	}
}
function inscriptionClient()
{
	$request = Slim::getInstance()->request();
	$infos = json_decode($request->getBody());
	$email = $infos->email;
	$password = $infos->password;
	$firstname = $infos->firstname;
	$lastname = $infos->lastname;
	$birthdate = $infos->birthdate;
	$phone = $infos->phone;
	$address = $infos->address;
	$zipcode = $infos->zipcode;


	$sql = 'INSERT INTO member (firstname, lastname, email, password, dob, phone, address, zipcode)
	VALUES (:firstname, :lastname, :email, :password, :dob, :phone, :address, :zipcode)';

	$db = getConnection();
	$stmt = $db->prepare($sql);



	$stmt->execute(array(
		"firstname" => $infos->firstname,
		"lastname" => $infos->lastname,
		"email" => $infos->email,
		"password" => $infos->password,
		"dob" => $infos->birthdate,
		"phone" => $infos->phone,
		"address" => $infos->address,
		"zipcode" => $infos->zipcode
	));

	//$db->lastInsertId();

	echo '{"success":{"text":"PHP inscri ok"}}';



}


function getMedias($storeId) {

	//  a changer !

	$storeCus = $storeId;
	$sql = " SELECT c.id,c.title,c.synopsis,c.publication_date,c.rate,c.illustration
	FROM video_actor a
	LEFT JOIN video c
	ON a.videoid = c.id
	JOIN video_support d
	ON d.videoid = c.id
	JOIN store e
	ON e.id = d.store_id
	WHERE e.id=$storeCus
	GROUP BY c.title";

	try {
		$db = getConnection();
		$stmt = $db->query($sql);
		$medias = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo '{"medias": ' . json_encode(utf8_encode_array(objectToArray($medias))) . '}';
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}

function getMedia($id) {


	$sql = "SELECT c.id,c.title,c.synopsis,c.publication_date,c.rate,c.illustration,
	GROUP_CONCAT(DISTINCT b.firstname, ' ' , b.lastname SEPARATOR ', ') actor,
	GROUP_CONCAT(DISTINCT e.firstname, ' ' , e.lastname SEPARATOR ', ') realisator,
	GROUP_CONCAT(DISTINCT g.firstname, ' ' , g.lastname SEPARATOR ', ') productor,
	GROUP_CONCAT(DISTINCT l.name SEPARATOR ', ') genre,
	GROUP_CONCAT(DISTINCT i.stockDvd SEPARATOR '\n') stockDvd,
	GROUP_CONCAT(DISTINCT i.stockCassette SEPARATOR '\n') stockCassette,
	GROUP_CONCAT(DISTINCT i.stockBlury SEPARATOR '\n') stockBluray
	FROM video_actor a
	RIGHT JOIN actor b
	ON a.actorid = b.id
	RIGHT JOIN video c
	ON a.videoid = c.id
	LEFT JOIN video_realisator d
	ON d.videoid = c.id
	LEFT JOIN realisator e
	ON e.id = d.realisatorid
	LEFT JOIN video_productor f
	ON f.videoid = c.id
	LEFT JOIN productor g
	ON g.id = f.productorid
	LEFT JOIN video_support h
	ON h.videoid = c.id
	LEFT JOIN support i
	ON i.id = h.supportid
	LEFT JOIN store j
	ON j.id = h.store_id
	LEFT JOIN video_genre k
	ON k.videoid = c.id
	LEFT JOIN genre l
	ON l.id = k.genreid
	WHERE c.id = :id";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$media = $stmt->fetchObject();
		$db = null;
		echo  json_encode(utf8_encode_array(objectToArray($media)));
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}


function parseParam($param)
{
	$explode = explode(",",$param);
	$params = array();
	$i=0;
	foreach($explode as $key)
	{
		$pos = strripos($key," ");
		$firstname =  substr($key,0,$pos);
		$lastname =  substr($key,$pos,$pos+strlen($key));
		$params[$i]['firstname'] = $firstname;
		$params[$i]['lastname'] = $lastname;
		$i++;
	}
	return $params;
}


function addMedia($storeId) {
	//  a changer !
	$storeCus = $storeId;
	$request = Slim::getInstance()->request();
	$media = json_decode($request->getBody());
	$acteur = $media->actor;
	$realisator = $media->realisator;
	$productor = $media->productor;
	$genre = $media->genre;
	$stockDvd = $media->stockDvd;
	$stockCassette = $media->stockCassette;
	$stockBluray = $media->stockBluray;
	$lastidacteur = array();
	$lastidrealisator = array();
	$lastidproductor = array();
	$lastidstock = 0;
	$lastidvideo = 0;
	$i=0;
	$acteurs = array();
	$acteurs = parseParam($acteur);
	$realisators = array();
	$realisators = parseParam($realisator);
	$productors = array();
	$productors = parseParam($productor);
	$genres = array();
	$genres = explode(",",$genre);





	$sql = "INSERT INTO video (title, synopsis, publication_date, rate) VALUES (:title,:synopsis,:releaseDate,:rate)";
	$sql2 = "INSERT INTO actor (firstname,lastname) VALUES (:firstname,:lastname)";
	$sql3 = "INSERT INTO video_actor (actorid,videoid) VALUES (:actorid,:videoid)";
	$sql4 = "INSERT INTO realisator (firstname,lastname) VALUES (:firstname,:lastname)";
	$sql5 = "INSERT INTO video_realisator (realisatorid,videoid) VALUES (:realisatorid,:videoid)";
	$sql6 = "INSERT INTO productor (firstname,lastname) VALUES (:firstname,:lastname)";
	$sql7 = "INSERT INTO video_productor (productorid,videoid) VALUES (:productorid,:videoid)";
	$sql8 = "INSERT INTO genre (name) VALUES (:name)";
	$sql9 = "INSERT INTO video_genre (genreid,videoid) VALUES (:genreid,:videoid)";
	$sql10 = "INSERT INTO support (stockDvd,stockCassette,stockBlury) VALUES (:stockDvd,:stockCassette,:stockBluray)";
	$sql11 = "INSERT INTO video_support (supportid,videoid,store_id) VALUES (:supportid,:videoid,:store_id)";
	try {

		// REQUETE 1 : VIDEO
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("title", $media->titre);
		$stmt->bindParam("synopsis",$media->synopsis);
		$stmt->bindParam("releaseDate",$media->publication_date);
		$stmt->bindParam("rate", $media->rate);
		$stmt->execute();
		$lastidvideo = $db->lastInsertId();
		$db = null;

		// REQUETE 2 : ACTOR
		$db2 = getConnection();
		$stmt2 = $db2->prepare($sql2);
		foreach($acteurs as $key)
		{
			$stmt2->bindParam("firstname", $key['firstname']);
			$stmt2->bindParam("lastname",$key['lastname']);
			$stmt2->execute();
			$lastidacteur[$i] = $db2->lastInsertId();
			$i++;
		}
		$db2=null;


		// REQUETE 3: VIDEO_ACTOR
		$db3 = getConnection();
		$stmt3 = $db3->prepare($sql3);
		foreach($lastidacteur as $key)
		{
			$stmt3->bindParam("actorid", $key);
			$stmt3->bindParam("videoid",$lastidvideo);
			$stmt3->execute();
		}
		$db3=null;


		// REQUETE 4 : REALISATOR
		$i=0;
		$db4 = getConnection();
		$stmt4 = $db4->prepare($sql4);
		foreach($realisators as $key)
		{
			$stmt4->bindParam("firstname", $key['firstname']);
			$stmt4->bindParam("lastname",$key['lastname']);
			$stmt4->execute();
			$lastidrealisator[$i] = $db4->lastInsertId();
			$i++;
		}
		$db4=null;


		// REQUETE 5: video_realisator
		$db5 = getConnection();
		$stmt5 = $db5->prepare($sql5);
		foreach($lastidrealisator as $key)
		{
			$stmt5->bindParam("realisatorid", $key);
			$stmt5->bindParam("videoid",$lastidvideo);
			$stmt5->execute();
		}
		$db5=null;


		// REQUETE 6 : PRODUCTOR
		$i=0;
		$db6 = getConnection();
		$stmt6 = $db6->prepare($sql6);
		foreach($productors as $key)
		{
			$stmt6->bindParam("firstname", $key['firstname']);
			$stmt6->bindParam("lastname",$key['lastname']);
			$stmt6->execute();
			$lastidproductor[$i] = $db6->lastInsertId();
			$i++;
		}
		$db6=null;


		// REQUETE 7: video_productor
		$db7 = getConnection();
		$stmt7 = $db7->prepare($sql7);
		foreach($lastidproductor as $key)
		{
			$stmt7->bindParam("productorid", $key);
			$stmt7->bindParam("videoid",$lastidvideo);
			$stmt7->execute();
		}
		$db7=null;

		// REQUETE 8 : GENRE
		$i=0;
		$db8 = getConnection();
		$stmt8 = $db8->prepare($sql8);
		foreach($genres as $key)
		{
			$stmt8->bindParam("name", $key);
			$stmt8->execute();
			$lastidgenre[$i] = $db8->lastInsertId();
			$i++;
		}
		$db8=null;


		// REQUETE 9: video_genre
		$db9 = getConnection();
		$stmt9 = $db9->prepare($sql9);
		foreach($lastidgenre as $key)
		{
			$stmt9->bindParam("genreid", $key);
			$stmt9->bindParam("videoid",$lastidvideo);
			$stmt9->execute();
		}
		$db9=null;


		// REQUETE 10 : support
		$db10 = getConnection();
		$stmt10 = $db10->prepare($sql10);
		$stmt10->bindParam("stockDvd",$stockDvd);
		$stmt10->bindParam("stockCassette",$stockCassette);
		$stmt10->bindParam("stockBluray",$stockBluray);
		$stmt10->execute();
		$lastidstock = $db10->lastInsertId();
		$db10=null;


		// REQUETE 11: video_support
		$db11 = getConnection();
		$stmt11 = $db11->prepare($sql11);
		$stmt11->bindParam("supportid", $lastidstock);
		$stmt11->bindParam("videoid",$lastidvideo);
		$stmt11->bindParam("store_id",$storeCus);
		$stmt11->execute();
		$db11=null;



		echo '{"success":{"text":"'. $acteurs[0]['firstname'] .'"}}';

	} catch(PDOException $e) {
		echo '{"error":{"text":"'. $e->getMessage() .'"}}';
	}







}

function ifActorChange($id)
{
	$request = Slim::getInstance()->request();
	$media = json_decode($request->getBody());
	$acteur = $media->actor;
	$pacteur = str_replace(" ","",$acteur);

	$sql = "SELECT GROUP_CONCAT(DISTINCT b.firstname,b.lastname SEPARATOR ',')
	FROM video_actor a
	JOIN actor b
	ON a.actorid = b.id
	JOIN video c
	ON a.videoid = c.id
	WHERE c.id = :id";

	$db = getConnection();
	$stmt = $db->prepare($sql);
	$stmt->bindParam("id", $id);
	$stmt->execute();
	$acteurs = $stmt->fetch();
	$db = null;

	$pacteurs = str_replace(" ","",$acteurs[0]);
	return $pacteurs;
}

function ifRealisatorChange($id)
{
	$request = Slim::getInstance()->request();
	$media = json_decode($request->getBody());
	$realisateur = $media->realisator;
	$prealisateur = str_replace(" ","",$realisateur);

	$sqlR = "SELECT GROUP_CONCAT(DISTINCT b.firstname,b.lastname SEPARATOR ',')
	FROM video_realisator a
	JOIN realisator b
	ON a.realisatorid = b.id
	JOIN video c
	ON a.videoid = c.id
	WHERE c.id = :id";

	$db = getConnection();
	$stmtR = $db->prepare($sqlR);
	$stmtR->bindParam("id", $id);
	$stmtR->execute();
	$realisateurs = $stmtR->fetch();
	$db = null;

	$prealisateurs = str_replace(" ","",$realisateurs[0]);
	return $prealisateurs;
}

function ifProductorChange($id)
{
	$request = Slim::getInstance()->request();
	$media = json_decode($request->getBody());
	$producteur = $media->productor;
	$pproducteur = str_replace(" ","",$producteur);

	$sqlR = "SELECT GROUP_CONCAT(DISTINCT b.firstname,b.lastname SEPARATOR ',')
	FROM video_productor a
	JOIN productor b
	ON a.productorid = b.id
	JOIN video c
	ON a.videoid = c.id
	WHERE c.id = :id";

	$db = getConnection();
	$stmtR = $db->prepare($sqlR);
	$stmtR->bindParam("id", $id);
	$stmtR->execute();
	$producteurs = $stmtR->fetch();
	$db = null;

	$pproducteurs = str_replace(" ","",$producteurs[0]);
	return $pproducteurs;
}

function ifGenreChange($id)
{
	$request = Slim::getInstance()->request();
	$media = json_decode($request->getBody());
	$genre = $media->genre;
	$pgenre = str_replace(" ","",$genre);

	$sqlR = "SELECT GROUP_CONCAT(DISTINCT b.name SEPARATOR ',')
	FROM video_genre a
	JOIN genre b
	ON a.genreid = b.id
	JOIN video c
	ON a.videoid = c.id
	WHERE c.id = :id";

	$db = getConnection();
	$stmtR = $db->prepare($sqlR);
	$stmtR->bindParam("id", $id);
	$stmtR->execute();
	$genres = $stmtR->fetch();
	$db = null;

	$pgenres = str_replace(" ","",$genres[0]);
	return $pgenres;
}

function updateActor($acteur,$id)
{
	$acteurs = array();
	$acteurs = parseParam($acteur);
	$lastidacteur = array();
	$i=0;
	$sql2 = "INSERT INTO actor (firstname,lastname) VALUES (:firstname,:lastname)";
	$sql3 = "INSERT INTO video_actor (actorid,videoid) VALUES (:actorid,:videoid)";

	$db2 = getConnection();
	$stmt2 = $db2->prepare($sql2);
	foreach($acteurs as $key)
	{
		$stmt2->bindParam("firstname", $key['firstname']);
		$stmt2->bindParam("lastname",$key['lastname']);
		$stmt2->execute();
		$lastidacteur[$i] = $db2->lastInsertId();
		$i++;
	}
	$db2=null;

	$db3 = getConnection();
	$stmt3 = $db3->prepare($sql3);
	foreach($lastidacteur as $key)
	{
		$stmt3->bindParam("actorid", $key);
		$stmt3->bindParam("videoid",$id);
		$stmt3->execute();
	}
	$db3=null;
}

function updateRealisator($realisateur,$id)
{
	$realisateurs = array();
	$realisateurs = parseParam($realisateur);
	$lastidrealisateur = array();
	$i=0;
	$sql2 = "INSERT INTO realisator (firstname,lastname) VALUES (:firstname,:lastname)";
	$sql3 = "INSERT INTO video_realisator (realisatorid,videoid) VALUES (:realisatorid,:videoid)";

	$db2 = getConnection();
	$stmt2 = $db2->prepare($sql2);
	foreach($realisateurs as $key)
	{
		$stmt2->bindParam("firstname", $key['firstname']);
		$stmt2->bindParam("lastname",$key['lastname']);
		$stmt2->execute();
		$lastidrealisateur[$i] = $db2->lastInsertId();
		$i++;
	}
	$db2=null;

	$db3 = getConnection();
	$stmt3 = $db3->prepare($sql3);
	foreach($lastidrealisateur as $key)
	{
		$stmt3->bindParam("realisatorid", $key);
		$stmt3->bindParam("videoid",$id);
		$stmt3->execute();
	}
	$db3=null;
}

function updateProductor($producteur,$id)
{
	$producteurs = array();
	$producteurs = parseParam($producteur);
	$lastidproducteur = array();
	$i=0;
	$sql2 = "INSERT INTO productor (firstname,lastname) VALUES (:firstname,:lastname)";
	$sql3 = "INSERT INTO video_productor (productorid,videoid) VALUES (:productorid,:videoid)";

	$db2 = getConnection();
	$stmt2 = $db2->prepare($sql2);
	foreach($producteurs as $key)
	{
		$stmt2->bindParam("firstname", $key['firstname']);
		$stmt2->bindParam("lastname",$key['lastname']);
		$stmt2->execute();
		$lastidproducteur[$i] = $db2->lastInsertId();
		$i++;
	}
	$db2=null;

	$db3 = getConnection();
	$stmt3 = $db3->prepare($sql3);
	foreach($lastidproducteur as $key)
	{
		$stmt3->bindParam("productorid", $key);
		$stmt3->bindParam("videoid",$id);
		$stmt3->execute();
	}
	$db3=null;
}

function updateGenre($genre,$id)
{
	$genres = array();
	$genres = explode(",",$genre);
	$lastidgenre = array();
	$i=0;
	$sql2 = "INSERT INTO genre (name) VALUES (:name)";
	$sql3 = "INSERT INTO video_genre (genreid,videoid) VALUES (:genreid,:videoid)";

	$db2 = getConnection();
	$stmt2 = $db2->prepare($sql2);
	foreach($genres as $key)
	{
		$stmt2->bindParam("name", $key);
		$stmt2->execute();
		$lastidgenre[$i] = $db2->lastInsertId();
		$i++;
	}
	$db2=null;

	$db3 = getConnection();
	$stmt3 = $db3->prepare($sql3);
	foreach($lastidgenre as $key)
	{
		$stmt3->bindParam("genreid", $key);
		$stmt3->bindParam("videoid",$id);
		$stmt3->execute();
	}
	$db3=null;
}

function updateStock($dvd,$cassette,$bluray,$id,$storeCus)
{

	$lastidstock = array();
	$i=0;
	$sql10 = "INSERT INTO support (stockDvd,stockCassette,stockBlury) VALUES (:stockDvd,:stockCassette,:stockBluray)";
	$sql11 = "INSERT INTO video_support (supportid,videoid,store_id) VALUES (:supportid,:videoid,:store_id)";


	$db10 = getConnection();
	$stmt10 = $db10->prepare($sql10);
	$stmt10->bindParam("stockDvd",$dvd);
	$stmt10->bindParam("stockCassette",$cassette);
	$stmt10->bindParam("stockBluray",$bluray);
	$stmt10->execute();
	$lastidstock = $db10->lastInsertId();
	$db10=null;

	$db11 = getConnection();
	$stmt11 = $db11->prepare($sql11);
	$stmt11->bindParam("supportid", $lastidstock);
	$stmt11->bindParam("videoid",$id);
	$stmt11->bindParam("store_id",$storeCus);
	$stmt11->execute();
	$db11=null;
}


function deleteActor($id)
{
	$sql2 = "DELETE FROM video_actor WHERE videoid=$id";
	$db2 = getConnection();
	$stmt2 = $db2->prepare($sql2);
	$stmt2->execute();
	$db2=null;
}

function deleteRealisator($id)
{
	$sql2 = "DELETE FROM video_realisator WHERE videoid=$id";
	$db2 = getConnection();
	$stmt2 = $db2->prepare($sql2);
	$stmt2->execute();
	$db2=null;
}

function deleteProductor($id)
{
	$sql2 = "DELETE FROM video_productor WHERE videoid=$id";
	$db2 = getConnection();
	$stmt2 = $db2->prepare($sql2);
	$stmt2->execute();
	$db2=null;
}

function deleteGenre($id)
{
	$sql2 = "DELETE FROM video_genre WHERE videoid=$id";
	$db2 = getConnection();
	$stmt2 = $db2->prepare($sql2);
	$stmt2->execute();
	$db2=null;
}

function deleteStock($id)
{
	$sql2 = "DELETE FROM video_support WHERE videoid=$id";
	$db2 = getConnection();
	$stmt2 = $db2->prepare($sql2);
	$stmt2->execute();
	$db2=null;
}



function updateMedia($storeId,$id) {
	$request = Slim::getInstance()->request();
	$media = json_decode($request->getBody());
	$acteur = $media->actor;
	$realisateur = $media->realisator;
	$producteur = $media->productor;
	$genre = $media->genre;
	$dvd = $media->stockDvd;
	$cassette = $media->stockCassette;
	$bluray = $media->stockBluray;
	$pacteur = str_replace(" ","",$acteur);
	$pacteurs = ifActorChange($id);
	$prealisateur = str_replace(" ","",$realisateur);
	$prealisateurs = ifRealisatorChange($id);
	$pproducteur = str_replace(" ","",$producteur);
	$pproducteurs = ifProductorChange($id);
	$pgenre = str_replace(" ","",$genre);
	$pgenres = ifGenreChange($id);
	$storeCus = $storeId; //  a changer!!

	if ($pacteur!=$pacteurs) // Si l'acteur change
	{
		deleteActor($id);
		updateActor($acteur,$id);
	}

	if ($prealisateur!=$prealisateurs) // Si le realisateur change
	{
		deleteRealisator($id);
		updateRealisator($realisateur,$id);
	}

	if ($pproducteur!=$pproducteurs) // Si le producteur change
	{
		deleteProductor($id);
		updateProductor($producteur,$id);
	}

	if ($pgenre!=$pgenres) // Si le genre change
	{
		deleteGenre($id);
		updateGenre($genre,$id);
	}

	deleteStock($id);
	updateStock($dvd,$cassette,$bluray,$id,$storeCus);

	$sql = "UPDATE video SET title=:title, synopsis=:synopsis, publication_date=:releaseDate, rate=:rate WHERE id=:id";
	try {

		$db = getConnection();
		$stmt = $db->prepare($sql);
		$stmt->bindParam("title", $media->titre);
		$stmt->bindParam("synopsis",$media->synopsis);
		$stmt->bindParam("releaseDate",$media->publication_date);
		$stmt->bindParam("rate", $media->rate);
		$stmt->bindParam("id", $id);
		$stmt->execute();
		$db = null;


		echo '{"success":{"text":"'. $prealisateurs .'"}}';
	} catch(PDOException $e) {
		echo '{"error":{"text":"'. $e->getMessage() .'"}}';
	}
}

function deleteMedia($id) {

	$sql2 = "DELETE FROM video_actor WHERE videoid=:videoid";
	$sql3 = "DELETE FROM video_genre WHERE videoid=:videoid";
	$sql4 = "DELETE FROM video_productor WHERE videoid=:videoid";
	$sql5 = "DELETE FROM video_realisator WHERE videoid=:videoid";
	$sql6 = "DELETE FROM video_support WHERE videoid=:videoid";
	$sql = "DELETE FROM video WHERE id=:videoid";
	try {

		$db2 = getConnection();

		$stmt2 = $db2->prepare($sql2);
		$stmt2->bindParam("videoid", $id);
		$stmt2->execute();
		$stmt2 = $db2->prepare($sql3);
		$stmt2->bindParam("videoid", $id);
		$stmt2->execute();
		$stmt2 = $db2->prepare($sql4);
		$stmt2->bindParam("videoid", $id);
		$stmt2->execute();
		$stmt2 = $db2->prepare($sql5);
		$stmt2->bindParam("videoid", $id);
		$stmt2->execute();
		$stmt2 = $db2->prepare($sql6);
		$stmt2->bindParam("videoid", $id);
		$stmt2->execute();
		$stmt2 = $db2->prepare($sql);
		$stmt2->bindParam("videoid", $id);
		$stmt2->execute();
		$db2 = null;



		echo '{"success":{"text":" delete media id:'. $id .'"}}';
	} catch(PDOException $e) {
		echo '{"error":{"text":"'. $e->getMessage() .'"}}';
	}
}

function deleteLocation($locationid) {

	$sql2 = "DELETE FROM location WHERE id=:locationid";
	$sql3 = "DELETE FROM video_location WHERE locationid=:locationid";
	try {

		$db2 = getConnection();

		$stmt2 = $db2->prepare($sql3);
		$stmt2->bindParam("locationid", $locationid);
		$stmt2->execute();
		$stmt2 = $db2->prepare($sql2);
		$stmt2->bindParam("locationid", $locationid);
		$stmt2->execute();

		$db2 = null;



		echo '{"success":{"text":" delete locationid:'. $locationid .'"}}';
	} catch(PDOException $e) {
		echo '{"error":{"text":"'. $e->getMessage() .'"}}';
	}
}

function getMediaByName($query) {
	$sql = "SELECT * FROM video WHERE UPPER(title) LIKE :query ";
	try {
		$db = getConnection();
		$stmt = $db->prepare($sql);
		$query = "%".$query."%";
		$stmt->bindParam("query", $query);
		$stmt->execute();
		$medias = $stmt->fetchAll(PDO::FETCH_OBJ);
		$db = null;
		echo '{"medias": ' . json_encode(utf8_encode_array(objectToArray($medias))) . '}';
	} catch(PDOException $e) {
		echo '{"error":{"text":'. $e->getMessage() .'}}';
	}
}

function getConnection() {
	$dbhost="127.0.0.1";
	$dbuser="root";
	$dbpass="";
	$dbname="owemovie3";
	$dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);
	$dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	return $dbh;
}

function utf8_encode_array($array)
{
	array_walk_recursive($array, function(&$item, $key){
		if(!mb_detect_encoding($item, 'utf-8', true)){
			$item = utf8_encode($item);
		}
	});

	return $array;
}


function objectToArray($d) {
	if (is_object($d)) {
		// Gets the properties of the given object
		// with get_object_vars function
		$d = get_object_vars($d);
	}

	if (is_array($d)) {
		/*
		* Return array converted to object
		* Using __FUNCTION__ (Magic constant)
		* for recursive call
		*/
		return array_map(__FUNCTION__, $d);
	}
	else {
		// Return array
		return $d;
	}
}
?>
