<?php

require_once 'core/password.php';

// Now you can use password_hash()
//$hash = password_hash('uKwT41yy634n!', PASSWORD_BCRYPT);
//$hash = password_hash('iM5JlBeo62mE*', PASSWORD_BCRYPT);
$hash = password_hash('$rFzTaT80m4cp', PASSWORD_BCRYPT);
echo $hash;
