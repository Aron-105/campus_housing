<?php
// Keep old links safe after the move to password-based accounts.
header('Location: login.php', true, 303);
exit;
