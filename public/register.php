<?php
    require __DIR__ . "/../partials/head.php";
?>

<main>
    <div class="container my-5">
        <div class="row">
            <div class="col-lg-6">
                <!-- Form -->
                <h1 class="text-center my-3 display-5 fw-bold mb-3">Inscription</h1>
                
                <form method="post">
                    <div class="mb-3">
                        <input type="text" name="firstName" class="form-control" placeholder="Prénom" autofocus>
                    </div>
                    <div class="mb-3">
                        <input type="text" name="lasttName" class="form-control" placeholder="Nom">
                    </div>
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Email">
                    </div>
                    <div class="mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Mot de passe">
                    </div>
                    <div class="mb-3">
                        <input type="password" name="confirmPassword" class="form-control" placeholder="Confirmez le mot de passe">
                    </div>
                    <div>
                        <input type="submit" class="btn btn-dark w-100" value="S'inscrire">
                    </div>
                </form>

                <div class="text-center mt-3">
                    <p>Avez-vous déjà un compte ? <a href="">Connectez-vous</a></p>
                    <p>Retour a l'accueil</p>
                </div>
            </div>

            
            <div class="col-lg-6">
                <!-- Image -->
                 <img class="img-fluid" src="/assets/images/register.png" alt="Image invitant à vous inscrire.">
            </div>
        </div>
    </div>
</main>

<?php
    require __DIR__ . "/../partials/footer.php";
    
    require __DIR__ . "/../partials/scripts.php";
?>