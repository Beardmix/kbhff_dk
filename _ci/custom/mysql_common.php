<?
setlocale(LC_CTYPE, 'da_DK');

// enable reusable db vars (MAK)
if(!defined('BASEPATH')) {
	define('BASEPATH', true);
}

//include($_SERVER["LOCAL_PATH"]."/www/index.php");
include($_SERVER["CI_PATH"]."/CodeIgniter-2.2.6/application/config/database.php");

$mysqli = new mysqli("".$db['default']['hostname'], $db['default']['username'], $db['default']['password']);
if($mysqli->connect_errno) {
	echo "Kunne ikke tilsluttes databasen<br>";
	exit;
}
$mysqli->select_db($db['default']['database']);


// if (!$db_conn = @mysql_connect($db['default']['hostname'], $db['default']['username'], $db['default']['password']))
// {
// 	echo "Kunne ikke tilsluttes databasen<br>";
// 	exit;
// };
// @mysql_select_db($db['default']['database']);


// error_reporting  (E_ERROR | E_WARNING | E_PARSE);
error_reporting  (E_ALL & ~E_NOTICE);

function error_handler ($level, $message, $file, $line, $context) {

	if (!($level & error_reporting())) 
		return;

	$file = str_replace ("/srv/www/htdocs/events4u/","",$file);
	$advarsel = "Error";

	if ($level == 1) { $advarsel = "Fejl"; }
	if ($level == 2) { $advarsel = "Advarsel"; }
	if ($level == 4) { $advarsel = "Syntaksfejl"; }
	if ($level == 8) { $advarsel = "Mulig fejl"; }

	if ($level == 16) { $advarsel = "CORE_ERROR"; }
	if ($level == 32) { $advarsel = "CORE_WARNING"; }
	if ($level == 64) { $advarsel = "COMPILE_ERROR"; }
	if ($level == 128) { $advarsel = "COMPILE_WARNING"; }
	if ($level == 256) { $advarsel = "USER_ERROR"; }
	if ($level == 512) { $advarsel = "USER_WARNING"; }
	if ($level == 1024) { $advarsel = "USER_NOTICE"; }
	if ($level == 2047) { $advarsel = "ERROR"; }

echo <<<_END_

<strong>$advarsel</strong> i $file, linie $line.<br>
Fejlen rapporteres som: $message<br>

_END_;

}

set_error_handler ('error_handler');


function mac2ibm ($str )
{
	$str = str_replace("�", "�",$str);
	$str = str_replace("�", "�",$str);
	$str = str_replace("�", "�",$str);
	$str = str_replace("�", "�",$str);
	$str = str_replace("�", "�",$str);
	$str = str_replace("�", "�",$str);
	$str = str_replace("�", "&#39;",$str);
	$str = str_replace("�", "�",$str);
	$str = str_replace("�", "�",$str);
	$str = str_replace("�", "�",$str);
	$str = str_replace("�", "�",$str);
	$str = str_replace("�", "�",$str);
	$str = str_replace("�", "�",$str);
	$str = str_replace("�", "�",$str);
	$str = str_replace("�", "�",$str);

	return ( $str );
}


function dooption($opt, $leg, $d_leg = '', $class = '')
	{
		if (func_num_args() == 4)
		{
			$class= " class=\"$class\"";
		} else {
			$class= "";
		}

		echo ("\t\t\t\t<option value=\"$leg\"$class");
		if ($opt == $leg)
		{
			echo (' selected="selected"');
		}
		
		if (func_num_args() > 2)
		{
			echo (">$d_leg</option>\n");
		} else {
			echo (">$leg</option>\n");
		}
	}

function doquery($query)
{
global $mysqli;
	if(!($result = $mysqli->query($query)))
//    if(!($result = @mysql_query($query, $db_conn)))
    {
		echo "<strong>Error:</strong> ";
		echo $mysqli->connect_errno;
		exit;
    }
    return $result;
}


?>
