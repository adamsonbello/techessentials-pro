<?php
echo "<h2>Limites PHP actuelles</h2>";
echo "<p><strong>post_max_size:</strong> " . ini_get('post_max_size') . "</p>";
echo "<p><strong>max_input_vars:</strong> " . ini_get('max_input_vars') . "</p>";
echo "<p><strong>upload_max_filesize:</strong> " . ini_get('upload_max_filesize') . "</p>";
echo "<p><strong>memory_limit:</strong> " . ini_get('memory_limit') . "</p>";
?>