<?php

    $database= new mysqli("localhost:3307","root","","DocOn");
    if ($database->connect_error){
        die("Connection failed:  ".$database->connect_error);
    }

?>