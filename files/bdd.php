<?php

 $host = 'localhost';
 $db   = 'web_formation';
 $user = 'wf';
 $pass = 'l2aIopUl3GqDBw5zYddF';
 $charset = 'utf8mb4';
 $options = [
     PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
     PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
     PDO::ATTR_EMULATE_PREPARES   => false,
 ];
 $pdo = new PDO('mysql:host='.$host.'; port=3306; dbname='.$db,$user,$pass);

 ?>