<?php
// start sessionen
session_start();
 
// vi skal tjekke om brugeren er logget ind
if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: welcome.php");
    exit;
}
 
// for at connecte til databasen skal vi bruge config filen
require_once "config.php";
 
// Definer værdierne 
$username = $password = "";
$username_err = $password_err = $login_err = "";
 
// vi skal bruge data fra FORMS
if($_SERVER["REQUEST_METHOD"] == "POST"){
 
    // tjek om brugernavnet er tom
    if(empty(trim($_POST["username"]))){
        $username_err = "Please enter username.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    // tjek om password er tom
    if(empty(trim($_POST["password"]))){
        $password_err = "Please enter your password.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    // Valider oplysninger 
    if(empty($username_err) && empty($password_err)){
        // forbered et select statement
        $sql = "SELECT id, username, password FROM users WHERE username = ?";
        
        if($stmt = $mysqli->prepare($sql)){

            $stmt->bind_param("s", $param_username);
            
            // Sæt parametre 
            $param_username = $username;
            
            
            if($stmt->execute()){
                
                $stmt->store_result();
                
                // tjek om brugernavnet eksistere og bagefter skal password tjekkes.  
                if($stmt->num_rows == 1){                    
                
                    $stmt->bind_result($id, $username, $hashed_password);
                    if($stmt->fetch()){
                        if(password_verify($password, $hashed_password)){
                            // Password er rigtigt, start ny session 
                            session_start();
                            
                            // lagre data i sessionen 
                            $_SESSION["loggedin"] = true;
                            $_SESSION["id"] = $id;
                            $_SESSION["username"] = $username;                            
                            
                            // send brugeren til welcome.php
                            header("location: welcome.php");
                        } else{
                            // Password er ikke korrekt, vis meddelelse 
                            $login_err = "Invalid username or password.";
                        }
                    }
                } else{
                    // Username er ikke korrekt, vis meddelelse 
                    $login_err = "Invalid username or password.";
                }
            } else{
                echo "Oops! Something went wrong. Please try again later.";
            }

            
            $stmt->close();
        }
    }
    
    
    $mysqli->close();
}
?>
 
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        body{ font: 14px sans-serif; }
        .wrapper{ width: 360px; padding: 20px; }
    </style>
</head>
<body>
    <div class="wrapper">
        <h2>Login</h2>
        <p>Please fill in your credentials to login.</p>

        <?php 
        if(!empty($login_err)){
            echo '<div class="alert alert-danger">' . $login_err . '</div>';
        }        
        ?>

        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
            <div class="form-group">
                <label>Username</label>
                <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>">
                <span class="invalid-feedback"><?php echo $username_err; ?></span>
            </div>    
            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>">
                <span class="invalid-feedback"><?php echo $password_err; ?></span>
            </div>
            <div class="form-group">
                <input type="submit" class="btn btn-primary" value="Login">
            </div>
            <p>Don't have an account? <a href="register.php">Sign up now</a>.</p>
        </form>
    </div>
</body>
</html>