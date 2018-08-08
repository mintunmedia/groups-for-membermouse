<?php
if(!isset($_GET["file_path"]) && !isset($_GET["data"]))
{
	exit;
}
$fileType = (isset($_GET["file_type"]))?$_GET["file_type"]:"text/php";

//data
if(isset($_GET["data"])){
	$fileName = isset($_GET["filename"])?$_GET["filename"]:Date("Y-m-d").".txt";
	header("Content-type: ".$fileType);
	header("Content-Disposition: filename=".$fileName);
	header("Pragma: no-cache");
	header("Expires: 0");
	echo MM_Session::value($_GET["data"]);
	exit;
}

//file path
$filePath = $_GET["file_path"];
if(!file_exists($filePath))
{
	exit;
}

$filePathArray = explode("/",$filePath);
$fileName = array_pop($filePathArray);
header("Content-type: ".$fileType);
header("Content-Disposition: filename=".$fileName);
header("Pragma: no-cache");
header("Expires: 0");
echo file_get_contents($filePath);
exit;
