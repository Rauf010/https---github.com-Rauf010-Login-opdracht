<?php
session_start();
// Include config file
require_once "config.php";

 
// Definieer variabelen en initialiseer met lege waarden
$username = $password = $confirm_password = "";
$username_err = $password_err = $confirm_password_err = "";
 
// process de data
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    // Gebruikersnaam valideren
    if (empty(trim($_POST["username"]))) {
        $username_err = "Vul aub een gebruikersnaam in.";
    } elseif (!preg_match('/^[a-zA-Z0-9_]+$/', trim($_POST["username"]))) {
        $username_err = "Gebruikersnaam mag alleen letters, cijfers en underscores bevatten.";
    } else {
        // Voorbereid SQL statement
        $sql = "SELECT id FROM users WHERE username = :username";

        if ($stmt = $pdo->prepare($sql)) {
            // Bind waarden aan prepared statement
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);

            // Set parameters
            $param_username = trim($_POST["username"]);

            // Probeer de prepared statement uit te voeren
            if ($stmt->execute()) {
                if ($stmt->rowCount() == 1) {
                    $username_err = "Deze gebruikersnaam is al in gebruik.";
                } else {
                    $username = trim($_POST["username"]);
                }
            } else {
                echo "Oeps! Iets ging mis. Probeer het later opnieuw.";
            }

            // Sluit statement
            $stmt = null;
        }
    }

    // Wachtwoord valideren
    if (empty(trim($_POST["password"]))) {
        $password_err = "Vul aub uw wachtwoord in.";
    } elseif (strlen(trim($_POST["password"])) < 6) {
        $password_err = "Wachtwoord moet minstens 6 characters lang zijn.";
    } else {
        $password = trim($_POST["password"]);
    }

    // Confirm password valideren
    if (empty(trim($_POST["confirm_password"]))) {
        $confirm_password_err = "Vul aub je wachtwoord goed in.";
    } else {
        $confirm_password = trim($_POST["confirm_password"]);
        if (empty($password_err) && ($password != $confirm_password)) {
            $confirm_password_err = "Foute wachtwoord.";
        }
    }

    // Input errors checken voordat het in de database komt
    if (empty($username_err) && empty($password_err) && empty($confirm_password_err)) {

        // Insert klaarzetten
        $sql = "INSERT INTO users (username, password) VALUES (:username, :password)";

        if ($stmt = $pdo->prepare($sql)) {
            // Bind waarden aan prepared statement
            $stmt->bindParam(":username", $param_username, PDO::PARAM_STR);
            $stmt->bindParam(":password", $param_password, PDO::PARAM_STR);

            // Zet parameters
            $param_username = $username;
            $param_password = password_hash($password, PASSWORD_DEFAULT); // Wachtwoord hashen

            // Execute de statement
            if ($stmt->execute()) {
                // Redirect naar login pagina
                header("location: login.php");
                exit;
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
    <meta charset="UTF-8">
    <title>Registreer</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Registreer</h2>
        <p>Vul deze forum in om uw account aan te maken</p>
        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Gebruikersnaam</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Wachtwoord</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $password; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <label>bevestig wachtwoord</label>
                <input type="password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $confirm_password; ?>">
                <span class="invalid-feedback"><?php echo $confirm_password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Submit">
        
            </div>
            <p>Heb je al een account? <a href="login.php">Log hier in</a>.</p>
        </form>
    </div>    
</body>
</html>


