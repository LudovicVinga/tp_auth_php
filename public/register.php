<?php
session_start();

    require __DIR__ . "/../partials/head.php";
    require __DIR__ . "/../functions/security.php";
    require __DIR__ . "/../functions/helper.php";
    require __DIR__ . "/../functions/authenticator.php";
    require __DIR__ . "/../functions/dbConnector.php";

    $db = connectToDb();

    // Si les données arrivent au serveur via la methode POST
    if ( $_SERVER['REQUEST_METHOD'] === "POST" )
    {

        /**
         * ***********************************************
         * Traitement des données du formulaire
         * ***********************************************
         */

        //  1- Protéger le serveur contre les failles de type csrf
        if ( ! array_key_exists('csrf_token', $_POST) )
        {
            return header("Location: register.php");
        }

        if ( ! isCsrfTokenValid($_SESSION['csrf_token'], $_POST['csrf_token']) )
        {
            return header("Location: register.php");
        }

        // 2- Protéger le serveur contre les robots spamers
        if( ! array_key_exists('honey_pot', $_POST) )
        {
            return header("Location: register.php");
        }

        if ( isHoneyPotLicked($_POST['honey_pot']) )
        {
            return header("Location: register.php");       
        }


        // 3- Définir les contraintes de validation
        $formErrors = [];

        if ( isset($_POST['firstName']) )
        {
            if ( trim($_POST['firstName']) == "" )
            {
                $formErrors['firstName'] = "Le prénom est obligatoire.";
            }
            elseif ( mb_strlen($_POST['firstName']) > 255 )
            {
                $formErrors['firstName'] = "Le prénom ne doit pas dépasser 255 caractères";
            }
            elseif ( ! preg_match("/^[0-9A-Za-zÀ-ÖØ-öø-ÿ' _-]+$/u", $_POST['firstName']) )
            {
                $formErrors['firstName'] = "Le prénom ne peut contenir que des lettres, chiffres, et tiret.";
            }
        }

        if ( isset($_POST['lastName']) )
        {
            if ( trim($_POST['lastName']) == "" )
            {
                $formErrors['lastName'] = "Le nom est obligatoire.";
            }
            elseif ( mb_strlen($_POST['lastName']) > 255 )
            {
                $formErrors['lastName'] = "Le nom ne doit pas dépasser 255 caractères";
            }
            elseif ( ! preg_match("/^[0-9A-Za-zÀ-ÖØ-öø-ÿ' _-]+$/u", $_POST['lastName']) )
            {
                $formErrors['lastName'] = "Le nom ne peut contenir que des lettres, chiffres, et tiret.";
            }
        }

        if ( isset($_POST['email']) )
        {
            if ( trim($_POST['email']) == "" )
            {
                $formErrors['email'] = "L'email est obligatoire.";
            }
            elseif ( mb_strlen($_POST['email']) > 255 )
            {
                $formErrors['email'] = "L'email ne doit pas dépasser 255 caractères";
            }
            elseif ( ! filter_var($_POST['email'], FILTER_VALIDATE_EMAIL) )
            {
                $formErrors['email'] = "L'email est invalide.";
            }
            elseif ( already_exists($_POST['email'], $db))
            {
                $formErrors['email'] = "Impossible d'utiliser cet email pour créer un compte.";
            }
        }

        if ( isset($_POST['password']) )
        {
            if ( trim($_POST['password']) == "" )
            {
                $formErrors['password'] = "Le mot de passe est obligatoire.";
            }
            elseif ( mb_strlen($_POST['password']) < 12 )
            {
                $formErrors['password'] = "Le mot de passe doit contenir au minimum 12 caractères.";
            }
            elseif ( mb_strlen($_POST['password']) > 255 )
            {
                $formErrors['password'] = "Le mot de passe doit contenir au maximum 255 caractères.";
            }
            elseif( ! preg_match("/^(?=.*[a-zà-ÿ])(?=.*[A-ZÀ-Ỳ])(?=.*[0-9])(?=.*[^a-zà-ÿA-ZÀ-Ỳ0-9]).{12,255}$/", $_POST['password']) )
            {
                $formErrors['password'] = "Le mot de passe doit contenir au moins un chiffre, une lettre majuscule et une lettre minuscule, un caractère spécial";
            }
        }

        if ( isset($_POST['confirmPassword']) )
        {
            if ( trim($_POST['confirmPassword']) == "" )
            {
                $formErrors['confirmPassword'] = "La confirmation du mot de passe est obligatoire.";
            }
            elseif ( mb_strlen($_POST['confirmPassword']) < 12 )
            {
                $formErrors['confirmPassword'] = "La confirmation du mot de passe doit contenir au minimum 12 caractères.";
            }
            elseif ( mb_strlen($_POST['confirmPassword']) > 255 )
            {
                $formErrors['confirmPassword'] = "La confirmation du mot de passe doit contenir au maximum 255 caractères.";
            }
            elseif ( ! preg_match("/^(?=.*[a-zà-ÿ])(?=.*[A-ZÀ-Ỳ])(?=.*[0-9])(?=.*[^a-zà-ÿA-ZÀ-Ỳ0-9]).{12,255}$/", $_POST['password']) )
            {
                $formErrors['confirmPassword'] = "La confirmation du mot de passe doit contenir au moins un chiffre, une lettre majuscule et une lettre minuscule, un caractère spécial";
            }
            elseif ( $_POST['confirmPassword'] !== $_POST['password'] )
            {
                $formErrors['confirmPassword'] = "Le mot de passe doit être identique a sa confirmation.";
            }
        }



        // 4- Si le formulaire est soumis et invalide
        if ( count($formErrors) > 0 )
        {
            $_SESSION['formErrors'] = $formErrors;
            $_SESSION['old'] = $_POST;

            return header("Location: register.php");
        }
        
        // Dans le cas contraire,

        // Encodons d'abord le mot de passe
        $passwordHashed = password_hash($_POST['password'], PASSWORD_BCRYPT);
        // 5- Etablir la requête d'insertion du nouvel utilisateur en BDD

        try
        {
            $request = $db->prepare("INSERT INTO user (first_name, last_name, email, password, created_at, updated_at) VALUES (:first_name, :last_name, :email, :password, now(), now() ) ");
    
            $request->bindValue(":first_name", $_POST['firstName']);
            $request->bindValue(":last_name", $_POST['lastName']);
            $request->bindValue(":email", $_POST['email']);
            $request->bindValue(":password", $passwordHashed);
    
            $request->execute();
            $request->closeCursor(); 
        }
        catch (\PDOException $exception)
        {
            throw new Exception($exception->getMessage());
        }

        // 6- Générer le message flash de succès
        $_SESSION['success'] = "Votre compte a bien été crée, vous pouvez vous connecter!";

        // 7- Rediriger l'utilisateur vers la page de connexion
        // Arrêter l'éxécution du script.
        return header("Location: login.php");
    }

    // Générer un jeton de sécurité
    $_SESSION['csrf_token'] = bin2hex(random_bytes(10));
?>

<main>
    <div class="container my-5">
        <div class="row">
            <div class="col-lg-6">
                <!-- Form -->
                <h1 class="text-center my-3 display-5 fw-bold mb-3">Inscription</h1>

                <!-- Afficher les messages d'erreurs -->
                <?php if( isset($_SESSION['formErrors']) && !empty($_SESSION['formErrors'])) : ?>
                    <div class="alert alert-danger" role="alert">
                        <ul>
                            <?php foreach($_SESSION['formErrors'] as $error) : ?>
                                <li>
                                    <?= $error ?>
                                </li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                    <?php unset($_SESSION['formErrors']) ?>
                <?php endif ?>
                
                <form method="post">
                    <div class="mb-3">
                        <input type="text" name="firstName" class="form-control" placeholder="Prénom" autofocus value="<?= isset($_SESSION['old']['firstName']) && !empty($_SESSION['old']['firstName']) ? htmlspecialchars($_SESSION['old']['firstName']) : ''; unset($_SESSION['old']['firstName']) ?>">
                    </div>
                    <div class="mb-3">
                        <input type="text" name="lastName" class="form-control" placeholder="Nom" value="<?= isset($_SESSION['old']['lastName']) && !empty($_SESSION['old']['lastName']) ? htmlspecialchars($_SESSION['old']['lastName']) : ''; unset($_SESSION['old']['lastName']) ?>">
                    </div>
                    <div class="mb-3">
                        <input type="email" name="email" class="form-control" placeholder="Email" value="<?= isset($_SESSION['old']['email']) && !empty($_SESSION['old']['email']) ? htmlspecialchars($_SESSION['old']['email']) : ''; unset($_SESSION['old']['email']) ?>">
                    </div>
                    <div class="mb-3">
                        <input type="password" name="password" class="form-control" placeholder="Mot de passe" value="<?= isset($_SESSION['old']['password']) && !empty($_SESSION['old']['password']) ? htmlspecialchars($_SESSION['old']['password']) : ''; unset($_SESSION['old']['password']) ?>">
                        <small><em>Le mot de passe doit contenir au moins un chiffre, une lettre majuscule, une lettre minuscule et un caractère spécial.</em></small>
                    </div>
                    <div class="mb-3">
                        <input type="password" name="confirmPassword" class="form-control" placeholder="Confirmez le mot de passe">
                    </div>

                    <!-- csrf_token -->
                    <div>
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                    </div>

                    <!-- honey_pot -->
                     <div>
                        <input type="hidden" name="honey_pot" value="">
                     </div>

                    <div>
                        <input formnovalidate type="submit" class="btn btn-dark w-100" value="S'inscrire">
                    </div>
                </form>

                <div class="text-center mt-3">
                    <p>Avez-vous déjà un compte ? <a href="">Connectez-vous</a></p>
                    <p><a href="/index.php">Retour a l'accueil</a></p>
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