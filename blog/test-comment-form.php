<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Test isolation
$article_id = 2;

function displayCommentForm($article_id, $parent_id = null) {
    $title = $parent_id ? "💬 Répondre" : "💬 Laisser un commentaire";
    
    echo '<div class="comment-form-section">';
    echo '<h3>' . $title . '</h3>';
    echo '<form class="comment-form" data-article-id="' . $article_id . '" data-parent-id="' . ($parent_id ?? '') . '">';
    
    echo '<div class="form-group">';
    echo '<label>Nom *</label>';
    echo '<input type="text" name="author_name" required>';
    echo '</div>';
    
    echo '<div class="form-group">';
    echo '<label>Email *</label>';
    echo '<input type="email" name="author_email" required>';
    echo '</div>';
    
    echo '<div class="form-group">';
    echo '<label>Commentaire *</label>';
    echo '<textarea name="content" required></textarea>';
    echo '</div>';
    
    echo '<button type="submit" class="submit-btn">Publier</button>';
    echo '</form>';
    echo '</div>';
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Formulaire</title>
</head>
<body>
    <h1>Test Formulaire Commentaire</h1>
    
    <?php displayCommentForm($article_id); ?>
    
    <script>
    console.log("Formulaires:", document.querySelectorAll('.comment-form').length);
    
    document.addEventListener("DOMContentLoaded", function() {
        const forms = document.querySelectorAll(".comment-form");
        console.log("DOM Ready - Formulaires:", forms.length);
        
        forms.forEach(form => {
            form.addEventListener("submit", function(e) {
                e.preventDefault();
                alert("Formulaire soumis !");
                console.log("SUCCESS !");
            });
        });
    });
    </script>
</body>
</html>
```

**Ouvre :**
```
http://localhost/TechessentialsPro/blog/test-comment-form.php