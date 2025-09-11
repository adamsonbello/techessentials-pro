<?php
$password = '1234';
$hash = password_hash($password, PASSWORD_DEFAULT);

echo "<h3>Générateur de hash</h3>";
echo "<p><strong>Mot de passe:</strong> " . $password . "</p>";
echo "<p><strong>Hash généré:</strong> " . $hash . "</p>";
echo "<br>";
echo "<p>Copiez ce hash dans admin-blog/index.php ligne 7:</p>";
echo "<code>\$BLOG_ADMIN_PASSWORD_HASH = '" . $hash . "';</code>";
?>