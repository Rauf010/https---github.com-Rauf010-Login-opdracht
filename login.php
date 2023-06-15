
<?php include 'nav.php'; ?>
<?php
// Initialiseer de sessie
session_start();
 
// Controleer of de gebruiker al is ingelogd, zo ja, leid hem dan door naar de welkomstpagina
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index_5.php");
    exit;
}

 
// Include config file
require_once "config.php";
 
// Defineer variabelen en initialiseer met lege waarden
$username = $password = "";
$username_err = $password_err = $login_err = "";

// Process form data wanneer het formulier is ingediend
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Check of gebruikersnaam leeg is
    if (empty(trim($_POST["username"]))) {
        $username_err = "Vul aub uw gebruikersnaam in";
    } else {
        $username = trim($_POST["username"]);
    }

    // Check of wachtwoord leeg is
    if (empty(trim($_POST["password"]))) {
        $password_err = "Vul aub uw wachtwoord in.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Valideer credentials
    if (empty($username_err) && empty($password_err)) {
        // Voorbereid SQL statement
        $sql = "SELECT id, username, password FROM users WHERE username = :username";

        if ($stmt = $pdo->prepare($sql)) {
            // Bind waarden aan prepared statement
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);

            // Set parameters
            $param_username = $username;

            // Probeer de prepared statement uit te voeren
            if ($stmt->execute()) {
                // Controleer of de gebruikersnaam correct is, zo ja, controleer het wachtwoord
                if ($stmt->rowCount() == 1) {
                    // Haal resultaatvariabelen op
                    $stmt->bindColumn("id", $id);
                    $stmt->bindColumn("username", $fetched_username);
                    $stmt->bindColumn("password", $hashed_password);
                    $stmt->fetch(PDO::FETCH_BOUND);

                    if (password_verify($password, $hashed_password)) {
                        // Wachtwoord is correct, start een nieuwe sessie
                        session_start();

                        // Sla gegevens op in sessievariabelen
                        $_SESSION["loggedin"] = true;
                        $_SESSION["id"] = $id;
                        $_SESSION["username"] = $fetched_username;

                        // Redirect gebruiker naar homepagina
                        header("location: index.php");
                        exit;
                    } else {
                        // Wachtwoord is foutief, toon een generieke foutmelding
                        $login_err = "Foute gebruikersnaam en/of wachtwoord.";
                    }
                } else {
                    // Gebruikersnaam en/of wachtwoord is foutief
                    $login_err = "Foute gebruikersnaam en/of wachtwoord.";
                }
            } else {
                echo "Oeps! Iets ging mis. Probeer het later opnieuw.";
            }

            // Sluit statement
            $stmt = null;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <link rel="icon" href="img/schoenreus logo.png" type="image/x-icon">
  <meta charset="UTF-8">
  <link rel="stylesheet" href="style.css">
  <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="style.css">
  <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">

  <title>Login</title>
</head>
<body>

<form method="post" action="">
<div class="login">
  <div class="login-box">
    <h2>Login</h2>
    <p>Vul je gebruikersnaam en wachtwoord in om in te loggen.</p>

    <?php 
    if(!empty($login_err)){
        echo '<div class="alert alert-danger">' . $login_err . '</div>';
    }        
    ?>

    <form action="" method="post">
        <div class="form-group">
            <label>Gebruikersnaam</label>
            <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
            <span class="invalid-feedback"><?php echo $username_err; ?></span>
        </div>   
        <div class="form-group">
            <label>Wachtwoord</label>
            <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
            <span class="invalid-feedback"><?php echo $password_err; ?></span>
        </div>
        <div class="form-group">
            <input type="submit" class="btn btn-primary" value="Login">
        </div>
        <p>Geen account? <a href="register.php">Registreer nu</a>.</p>
    </form>
  </div>
</div>
</form>

<?php include 'contact_actionpage.php'?>

</body>
</html>