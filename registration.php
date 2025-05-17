<?php
// Check if the form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $emailErr = $passwordErr = "";
  $email = $password = "";

  // Function to sanitize and validate input data
  function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }

  // Validate email
  if (empty($_POST["email"])) {
    $emailErr = "Email is required";
  } else {
    $email = test_input($_POST["email"]);
    // Check if email address is well-formed
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $emailErr = "Invalid email format";
    }
  }

  // Validate password (consider more complex validation)
  if (empty($_POST["password"])) {
    $passwordErr = "Password is required";
  } else {
    $password = test_input($_POST["password"]);
    // Minimum length and character types check (example)
    if (strlen($password) < 8) {
      $passwordErr = "Password must be at least 8 characters long.";
    } 
  }

  // If all fields are validated, connect to database and authenticate user
  if (empty($emailErr) && empty($passwordErr)) {

    // Replace with your actual database credentials (store them securely)
    require('login.php'); // Assuming a separate file for credentials

    // Create connection
    $conn = new mysqli("localhost", "root", "", "regitration");

    // Check connection
    if ($conn->connect_error) {
      die("Connection failed: " . $conn->connect_error);
    }

    // Prepare a SQL query to select user based on email
    $sql = "SELECT * FROM users WHERE email = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      // User found, check password
      $row = $result->fetch_assoc();
      $hashed_password = $row["password"]; // Assuming password is stored hashed

      if (password_verify($password, $hashed_password)) {
        // Login successful, redirect to account details page
        session_start();
        $_SESSION["loggedin"] = true;
        $_SESSION["email"] = $email;
        header("location: account-details.html"); // Redirect to account details page
      } else {
        // Password mismatch, display specific error
        $passwordErr = "Incorrect password.";
      }
    } else {
      // User not found, display specific error
      $emailErr = "Email not found.";
    }

    $stmt->close();
    $conn->close();
  }
}
?>

<!DOCTYPE html>
<html>
<head>
  <title>Login Page</title>
</head>
<body>
  <center>
    <h2>Login</h2>
  </center>

  <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">
    <?php echo "<span class=\"error\">" . $emailErr . "</span>"; ?>
    <?php echo "<span class=\"error\">" . $passwordErr . "</span>"; ?>
    <br><br>
    Email Address: <input type="text" name="email" id="email" /> <br/><br/>
    Password: <input type="password" name="password" id="password" /> <br/><br/>
    <input type="submit" value="Login" />
    <a href="registration.php">Register Here</a>
  </form>

</body>
</html>