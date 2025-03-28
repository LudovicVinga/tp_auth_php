<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <!-- Titre de la page affiché dans l'onglet -->
    <?php if( isset($title) && !empty($title) ) : ?>
        <title><?= $title ?> - Auth</title>
    <?php else : ?>
        <title>Auth</title>
    <?php endif ?>

    <!-- SEO -->
    <?php if(isset($description) && !empty($description)) : ?>
        <meta name="description" content="<?= $description ?>">
    <?php endif ?>

    <?php if(isset($keywords) && !empty($keywords)) : ?>
        <!-- Les mots clés spécifiques à chaque page. -->
        <meta name="keywords" content="<?= $keywords ?>">
    <?php endif ?>

    <meta name="author" content="dwwm">
    <meta name="publisher" content="dwwm">

    <!-- Bootstrap v5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- CSS StyleSheet -->
     <link rel="stylesheet" href="/assets/styles/app.css">
</head>
<body class="bg-light">