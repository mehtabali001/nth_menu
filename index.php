<?php
// Start session
session_start();
// Database connection
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "nth_menu";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $parent = $_POST["parent"];
    $name = $_POST["name"];

    // Insert new menu item into the "menus" table if the form is submitted
    $sql = "INSERT INTO menus (name) VALUES ('$name')";
    $conn->query($sql);
    $child_id = $conn->insert_id;

    // Insert relationship into "menu_relationships" table if parent is selected
    if ($parent != 0) {
        $sql = "INSERT INTO menu_relationships (parent_id, child_id) VALUES ($parent, $child_id)";
        $conn->query($sql);
    }

    // Set session variable to indicate form submission
    $_SESSION["form_submitted"] = true;
    $success =  "Menu added successfully.";
    // Redirect to prevent auto-insert on page reload
    header("Location: " . $_SERVER["PHP_SELF"].'?msg='.$success);
    
    exit();
}

// Check if the form is submitted to prevent auto-insert on page reload
$form_submitted = isset($_SESSION["form_submitted"]) && $_SESSION["form_submitted"];
unset($_SESSION["form_submitted"]);
?>

<!DOCTYPE html>
<html>
<head>
  <title>Menu Structure</title>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" />
  <style>
    
  </style> 
  <link href="css/bootstrap.css" rel="stylesheet">
  <link href="css/jquery.smartmenus.bootstrap.css" rel="stylesheet">
  <link id="switcher" href="css/theme-color/default-theme.css" rel="stylesheet">
  <link href="css/style.css" rel="stylesheet">
  <link href='https://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
</head>
<body>

<div class="container mt-4">
<section id="menu">
  <div class="navbar-collapse collapse">
    <ul class="nav navbar-nav">
      <?php
        // Fetch top-level menu items (those with no parent) from the database
        $sql = "SELECT id, name FROM menus WHERE id NOT IN (SELECT child_id FROM menu_relationships)";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
          echo "<li>";
          if (hasSubMenu($row['id'], $conn)) {
            echo "<a href='#'>" . $row['name'] . "<i class='icon fas fa-caret-down'></i></a>";
          } else {
            echo "<a href='#'>" . $row['name'] . "</a>";
          }
          if (hasSubMenu($row['id'], $conn)) {
          echo "<ul class='dropdown-menu'>";
          }else{
            echo "<ul class=''>";
          }
          
          // Recursive function to display child menus
          displayChildMenus($row['id'], $conn);

          echo "</ul></li>";
        }

        // Function to check if a menu item has sub-menus
        function hasSubMenu($parent_id, $conn) {
          $sql = "SELECT COUNT(*) AS count FROM menu_relationships WHERE parent_id = $parent_id";
          $result = $conn->query($sql);
          $row = $result->fetch_assoc();
          return $row['count'] > 0;
        }

        // Recursive function to display child menus
        function displayChildMenus($parent_id, $conn) {
            
          $sql = "SELECT menus.id, menus.name
                  FROM menus
                  JOIN menu_relationships ON menus.id = menu_relationships.child_id
                  WHERE menu_relationships.parent_id = $parent_id";

          $result = $conn->query($sql);
          while ($row = $result->fetch_assoc()) {
            echo "<li>";
            if (hasSubMenu($row['id'], $conn)) {
              echo "<a href='#'>" . $row['name'] . "<i class='icon fas fa-caret-right'></i></a>";
            } else {
              echo "<a href='#'>" . $row['name'] . "</a>";
            }
            if (hasSubMenu($row['id'], $conn)) {
                echo "<ul class='dropdown-menu'>";
            }else{
                echo "<ul class=''>";
            }
            displayChildMenus($row['id'], $conn);
            echo "</ul></li>";
          }
        }
      ?>
    </ul>
    </div>
    </section>

   <br><br><br><br><br>
   <?php 
    if(isset($_GET["msg"])){

    ?>
    <div class="alert alert-success" role="alert">
            <?php echo $_GET["msg"]; ?>
    </div>
   <?php } ?> 
  <hr>
  <form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>"  class="mx-2">
  <div class="form-group">
    <label for="name">Menu Name:</label>
    <input type="text" name="name" id="name" class="form-control" required>
  </div> 
  
  <div class="form-group">
    <label for="parent">Select Parent Menu:</label>
    <select name="parent" id="parent" class="form-control">
      <option value="0">None</option>
      <?php
        // Fetch existing menu items from the database to populate the dropdown
        $sql = "SELECT id, name FROM menus";
        $result = $conn->query($sql);

        while ($row = $result->fetch_assoc()) {
          echo "<option value='" . $row['id'] . "'>" . $row['name'] . "</option>";
        }
      ?>
    </select>
    </div>   
    <button type="submit" class="btn btn-primary">Add Menu</button>
  </form>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="js/bootstrap.js"></script>  
<script type="text/javascript" src="js/jquery.smartmenus.js"></script>
<script type="text/javascript" src="js/jquery.smartmenus.bootstrap.js"></script>  
<script>
$(document).ready(function() {
  $(".alert").hide();
    $(".alert").fadeTo(2000, 500).slideUp(500, function() {
      $(".alert").slideUp(500);
    });
});
</script>    
</body>
</html>
