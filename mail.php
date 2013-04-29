<?php

	// $post = array ('type' => 'bugReport', 'info' => 'lol');
	// $post = http_build_query($post);
	$post = http_build_query($_POST);
	 
	$context_options = array (
	        'http' => array (
	            'method' => 'POST',
	            'header'=> "Content-type: application/x-www-form-urlencoded\r\n"
	                . "Content-Length: " . strlen($post) . "\r\n",
	            'content' => $post
	            )
	        );
	 
	$context = stream_context_create($context_options);
	$fp = fopen('http://172.21.253.41/htmlengine/old/ben/mailing/mail.php', 'r', false, $context);
?>