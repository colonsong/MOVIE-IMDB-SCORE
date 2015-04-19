<?php
//$_POST['q'] = 'Jaws.1975.1080p.BluRay.X264-AMIABLE [PublicHD]';

//取得正規電影名稱
preg_match('#([a-z0-9.]*)(1080p|720p)#is', $_POST['q'],$m);
//print_r($m);
if(!isset($m[1]))
	die(json_encode([]));



$movie_name = rawurldecode($m[1].'+Ratings');
$homepage = file_get_contents('https://ajax.googleapis.com/ajax/services/search/web?v=1.0&q='.$movie_name);
$a =  json_decode($homepage,TRUE);
//print_r($a);
if(isset($a['responseData']['results'][0]['content']))
{
	$res = $a['responseData']['results'][0]['content'];
	preg_match('#Ratings.*?([0-9].[0-9])#is', $res ,$m);
	//print_r($m);
	if(isset($m[1]))
	{
		echo json_encode($m[1]);
	}
	else
	{
		echo json_encode([]);
	}


}
?>
