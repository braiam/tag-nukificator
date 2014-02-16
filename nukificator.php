<?php
include "config.php";

while(1)
{
	echo 'Tag to inspect: ';

	$handle = fopen ("php://stdin","r");
	$line = fgets($handle);

	$tag = trim($line);

	
}
