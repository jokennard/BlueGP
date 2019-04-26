<?php
	$conn=new mysqli('localhost', 'root', '', 'bluegp');
	if(!$conn){
		die("Error: ".mysqli_error());
	}
?>