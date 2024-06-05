<?php
session_start();
@include 'connection.php';

// Check if user is logged in
if (isset($_SESSION['user_id'])) {
  $user_id = $_SESSION['user_id'];
}

// Handling the Add to Cart functionality
if (isset($_POST['add_to_cart'])) {
  $product_id = mysqli_real_escape_string($conn, $_POST["product_id"]);
  $product_quantity = 1;

  $stmt = $conn->prepare("SELECT * FROM `cart` WHERE product_id = ? AND user_id = ?");
  $stmt->bind_param("ii", $product_id, $user_id);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows > 0) {
    $stmt = $conn->prepare("UPDATE `cart` SET quantity = quantity + ? WHERE product_id = ? AND user_id = ?");
    $stmt->bind_param("iii", $product_quantity, $product_id, $user_id);
    $stmt->execute();
    $message[] = 'Product quantity updated in cart';
  } else {
    $stmt = $conn->prepare("INSERT INTO `cart` (user_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->bind_param("iii", $user_id, $product_id, $product_quantity);
    $stmt->execute();
    $message[] = 'Product added to cart successfully!';
  }
  $stmt->close();
}

$query = isset($_GET['query']) ? mysqli_real_escape_string($conn, $_GET['query']) : '';
$stmt = $conn->prepare("SELECT * FROM products WHERE product_name LIKE ? OR category_name LIKE ?");
$likeQuery = '%' . $query . '%';
$stmt->bind_param("ss", $likeQuery, $likeQuery);
$stmt->execute();
$search_results = $stmt->get_result();
?>

<!doctype html>
<html lang="en">

<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Search Results</title>
  <link href="https://stackpath.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="main.css">
</head>
<style>
  html,
  body {
    position: relative;
    height: 100%;
  }

  body {
    background: #eee;
    font-family: Helvetica Neue, Helvetica, Arial, sans-serif;
    font-size: 14px;
    color: #000;
    margin: 0;
    padding: 0;
  }

  .alert-top {
    position: fixed;
    top: 100px;
    left: 50%;
    transform: translateX(-50%);
    width: 100%;
    max-width: 600px;
    z-index: 1050;
    text-align: center;
  }
</style>

<body>

  <?php include('includes/navbar.php') ?>

  <!-- Displaying messages if any -->
  <?php
  if (isset($message)) {
    foreach ($message as $msg) {
      echo '
            <div class="alert alert-primary alert-dismissible fade show alert-top" role="alert">
                <span>' . htmlspecialchars($msg) . '</span>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>';
    }
  }
  ?>
  <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cyberware</title>
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" integrity="sha512-SnH5WK+bZxgPHs44uWIX+LLJAJ9/2PkPKZ5QiAj6Ta86w+fsb2TkcmfRyVX3pBnMFcV7oQPJkl9QevSCWr3W6A==" crossorigin="anonymous" referrerpolicy="no-referrer" />

  <head>
<style>
  .btn{
  margin-left: 10px;
}
.card-body{
    -webkit-box-shadow: 0px 19px 22px -18px rgba(46,74,117,1);
-moz-box-shadow: 0px 19px 22px -18px rgba(46,74,117,1);
box-shadow: 0px 19px 22px -18px rgba(46,74,117,1);
  }
  </style>
  <div class="container">
    <div class="text-center my-5">
      <h1>Search Results for "<?php echo htmlspecialchars($query); ?>"</h1>
      <hr />
    </div>

    <div class="row">
      <?php
      if ($search_results->num_rows > 0) {
        while ($row = mysqli_fetch_assoc($search_results)) {
      ?>
          <div class="col-lg-4 col-md-6 col-sm-12">
            <div class="card mb-5 shadow-sm">
              <a href="product_details.php?product_id=<?= htmlspecialchars($row['product_id']); ?>">
                <img src="<?= htmlspecialchars($row['product_img']); ?>" class="img-fluid" />
              </a>
              <div class="card-body">
                <div class="card-title">
                  <h4><?= htmlspecialchars($row['product_name']); ?></h4>
                  <medium class="text-muted"><?= htmlspecialchars($row['category_name']); ?></medium>
                </div>
                <div class="card-text">
                  <p class="h5">
                    Php <?= htmlspecialchars($row['price']); ?>
                  </p>
                </div>
                <?php if (isset($_SESSION['user_name'])) : ?>
                  <form action="" method="post">
                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($row['product_id']); ?>">
                    <button type="submit" name="add_to_cart" class="btn btn-outline-success rounded-0 float-end"><i class="fa-solid fa-cart-shopping"> </i></button>
                  </form>

                  <form action="user_buy_now.php" method="POST">
                    <input type="hidden" name="product_id" value="<?= htmlspecialchars($row['product_id']); ?>">
                    <button type="submit" name="buy_now_btn" class="btn btn-outline-primary rounded-0 float-end">Buy Now</button>
                  </form>
                <?php else : ?>
                  <a href="login.php" class="btn btn-outline-success rounded-0 float-end"> <i class="fa-solid fa-cart-shopping"> </i></a>
                <a href="login.php" class="btn btn-outline-primary rounded-0 float-end">Buy Now</a>
                <?php endif; ?>
              </div>
            </div>
          </div>
      <?php
        }
      } else {
        echo '<div class="col-12"><h3 class="text-center">No Result Found</h3></div>';
      }
      ?>
    </div>
  </div>

  <!-- Include Bootstrap JavaScript and dependencies (Popper.js) -->
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.5/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.0-beta1/dist/js/bootstrap.min.js"></script>
</body>

</html>
