<?php
include "config.php";

echo 'Tag to inspect: ';

$handle = fopen ("php://stdin","r");
$line = fgets($handle);

$tag = trim($line);

while(1)
{

}
