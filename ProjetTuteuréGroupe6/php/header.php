<?php
// Ensure this is included after session_start()
if (!isset($pageTitle)) {
    $pageTitle = "Gestion des Notes";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pageTitle); ?> - Keyce</title>
    <link rel="stylesheet" href="/css/style.css">
    
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <header class="header-container">
        <div class="header-left">
            <a href="/" class="logo">
                <img src="/images/logo.png" alt="Keyce Logo">
            </a>
        </div>
        
        <h1 class="header-title"><?php echo htmlspecialchars($pageTitle); ?></h1>
        
        
    </header>

 