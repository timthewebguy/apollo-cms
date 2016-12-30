<?php

function show_404($msg = null) {
	if($msg == null)  {
		die("<strong>Error: <em>404</em></strong><br>The requested page could not be found.");
	} else {
		die($msg);
	}
}
