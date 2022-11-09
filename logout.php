<?php
// start sessionen 
session_start();
 
// deaktiver sessionens variabler 
$_SESSION = array();
 
// ødelæg sessionen
session_destroy();
 
// omdiager til login side
header("location: login.php");
exit;
?>