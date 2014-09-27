<!DOCTYPE html>
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<meta charset="UTF-8">
		<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1.0, user-scalable=no">

		<title>Php Memcache demo</title>

		<style type="text/css">

			like {
                display: block;
				padding: 20px 25px 20px 25px;
				background: #DDEEFF;
				border-left: 5px solid #5599DD;
				margin-right: 30px;
				width: 350px;
			}

			legende {
                display: block;
                color: grey;
                padding-top: 18px;
                padding-bottom: 18px;
            }

		</style>
	</head>
	<body>

<?php

function mcache_delete($query) {

    global $mem;

    $hash = md5($query);
    $value = $mem->delete($hash);

    return $value;

}


try {

	$methode = "memcache";

	$mem = new Memcached();
	$mem->addServer("127.0.0.1", 11211) or die ("Could not connect to memcached");

	$db = new PDO('mysql:host=localhost;dbname=likes;charset=utf8', 'kraeger', 'password');
	$db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	$db->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);

	$query = "SELECT count(*) as count FROM compteur WHERE image_id = '1'";

	//mcache_delete($query);

	$hash = md5($query);

    $nblikes = $mem->get($hash);

    if ($nblikes === FALSE) {

    	$methode = "mysql";

        foreach($db->query($query) as $row) {
        	$nblikes = $row["count"];
		}
        $mem->set($hash,$nblikes,NULL,3600);
    }

	echo "		<legende>information récupérée depuis: <b>" . $methode. "</b></legende>\n";

	echo "		<img src=\"./img.jpg\" width=\"405px\" alt=\"img.jpg\" />\n";
	if($nblikes>1)
	    echo "		<like>" . $nblikes . " personnes aiment cette photo.</like>\n";
	else
		echo "		<like>" . $nblikes . " personne aime cette photo.</like>\n";

} catch(PDOException $pdoe) {
    echo "<br/>Failed: " . $pdoe->getMessage() . PHP_EOL;
} catch ( Exception $e ) {
    echo "<br/>Failed: " . $e->getMessage() . PHP_EOL;
}

?>
	</body>
</html>
