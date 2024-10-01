
<?php include "../connect.php" ?>
<!doctype html>
<html lang="en">


  <head>
    <meta charset="utf-8">
    <title>CS Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="mobile-web-app-capable" content="yes">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link href="../mcss.css" rel="stylesheet" type="text/css" />
    <script src="../mpage.js"></script>
    <style>
      article a {
        color: blue;
      }
    </style>
  </head>

  <body>

    <header>
      <div class="logo">
        <img src="../cslogo.jpg" width="200" alt="Site Logo">
      </div>
      <div class="search">
        <form>
          <input type="search" placeholder="Search the site...">
          <button>Search</button>
        </form>
        
      </div>
    </header>

    <div class="mobile_bar">
      <a href="#"><img src="responsive-demo-home.gif" alt="Home"></a>
      <a href="#" onClick='toggle_visibility("menu"); return false;'><img src="responsive-demo-menu.gif" alt="Menu"></a>
    </div>

    <main>
      <article>
        <h1>Cart</h1>
        <div >
        <?php
                
                session_start();

                
	
                if (!isset($_SESSION['username'])) {
                    // หากยังไม่ได้ล็อกอิน, เปลี่ยนเส้นทางไปยังหน้า login
                    header("Location: login-form.php"); // เปลี่ยนไปที่หน้าเข้าสู่ระบบของคุณ
                    exit();
                }
		

                // ตรวจสอบสิทธิ์ของผู้ใช้
                $username = $_SESSION['username'];
                $stmt = $pdo->prepare("SELECT type FROM member WHERE username = :username");
                $stmt->execute(['username' => $username]);
                $user = $stmt->fetch();

                // เพิ่มสินค้า
                if ($_GET["action"] == "add") {

                    $pid = $_GET['pid'];

                    // ตรวจสอบจำนวนสินค้าคงเหลือ
                    $stmt = $pdo->prepare("SELECT stock FROM product WHERE pid = :pid");
                    $stmt->execute(['pid' => $pid]);
                    $product = $stmt->fetch();
                    $stock = $product['stock'];

                    $qty = $_POST['qty'];

                    $cart_item = array(
                        'pid' => $pid,
                        'pname' => $_GET['pname'],
                        'price' => $_GET['price'],
                        'qty' => $_POST['qty']
                    );

                    // ถ้ายังไม่มีสินค้าในรถเข็น
                    if (empty($_SESSION['cart'])) {
                        $_SESSION['cart'] = array();
                    }

                    // ถ้ามีสินค้านั้นอยู่แล้วในรถเข็นให้บวกเพิ่ม
                    if (array_key_exists($pid, $_SESSION['cart'])) {
                        $_SESSION['cart'][$pid]['qty'] += $_POST['qty'];

                        // ตรวจสอบจำนวนสินค้าที่เลือกหลังเพิ่ม
                        if ($_SESSION['cart'][$pid]['qty'] > $stock) {
                            echo "จำนวนสินค้าที่เลือกมากกว่าสินค้าคงเหลือ!";
                            exit();
                        }
                    } else {
                        $_SESSION['cart'][$pid] = $cart_item;
                    }
                }

                // ปรับปรุงจำนวนสินค้า
                else if ($_GET["action"] == "update") {
                    $pid = $_GET["pid"];
                    $qty = $_GET["qty"];

                    // ตรวจสอบสินค้าคงเหลือก่อนอัปเดต
                    $stmt = $pdo->prepare("SELECT stock FROM product WHERE pid = :pid");
                    $stmt->execute(['pid' => $pid]);
                    $product = $stmt->fetch();
                    $stock = $product['stock'];

                    if ($qty > $stock) {
                        echo "จำนวนที่เลือกมากกว่าสินค้าคงเหลือ!";
                        exit();
                    }

                    $_SESSION['cart'][$pid]['qty'] = $qty;
                }

                // ลบสินค้า
                else if ($_GET["action"] == "delete") {
                    $pid = $_GET['pid'];
                    unset($_SESSION['cart'][$pid]);
                }
                ?>
                <form>
            <table border="1">
            <?php
                $sum = 0;
                foreach ($_SESSION["cart"] as $item) {
                    $sum += $item["price"] * $item["qty"];

                    // ดึงข้อมูลสต็อกสินค้าปัจจุบัน
                    $stmt = $pdo->prepare("SELECT stock FROM product WHERE pid = :pid");
                    $stmt->execute(['pid' => $item['pid']]);
                    $product = $stmt->fetch();
                    $stock = $product['stock'];
            ?>
                <tr>
                    <td><?= htmlspecialchars($item["pname"]) ?></td>
                    <td><?= htmlspecialchars($item["price"]) ?> บาท</td>
                    <td>
                        <!-- จำกัดจำนวนสูงสุดเท่ากับจำนวนสินค้าคงเหลือ -->
                        <input type="number" id="<?= htmlspecialchars($item["pid"]) ?>" value="<?= htmlspecialchars($item["qty"]) ?>" min="1" max="<?= htmlspecialchars($stock) ?>">
                        <a href="#" onclick="update(<?= htmlspecialchars($item["pid"]) ?>)">แก้ไข</a>
                        <a href="?action=delete&pid=<?= htmlspecialchars($item["pid"]) ?>">ลบ</a>
                    </td>
                </tr>
            <?php } ?>
            <tr><td colspan="3" align="right">รวม <?= $sum ?> บาท</td></tr>
            </table>
            </form>

            <a href="./store.php">< เลือกสินค้าต่อ</a>

            <?php if ($user['type'] == 'admin'): ?>
                <a href="stock.php">ดูสินค้าคงเหลือ</a>
            <?php endif; ?>
            </div>
        
      </article>
      <nav id="menu">
        <h2>Navigation</h2>
        <ul class="menu">
          <li class="dead"><a href="../index.php">Home</a></li>
          <li><a href="../product_php/display_products.php">All Products</a></li>
          <li><a href="../product_php/table_product.php">Table of All Products</a></li>
          <li><a href="./store.php">Buy Products</a></li>
          <li><a href="./cart.php">Cart</a></li>
          <li><a href="../member_php/member.php">All Member</a></li>
          <li><a href="../insert_product.php">Insert Products</a></li>
          <li><a href="../insert_member.php">Insert Member</a></li>
          <li><a href="../member_php/edit_member.php">Delete/edit Member</a></li>
          <li><a href="../product_php/edit_product.php">Delete/edit product</a></li>
          <li><a href="../workshop/ws1.php">Workshop1</a></li>
          <li><a href="../workshop/ws2.php">Workshop2</a></li>
          <li><a href="../workshop/ws3.php">Workshop3</a></li>
          <li><a href="../workshop/ws4.php">Workshop4</a></li>
          <li><a href="../workshop/ws5.php">Workshop5</a></li>
          <li><a href="../workshop/ws6.php">Workshop6</a></li>
          <li><a href="../workshop/ws7.php">Workshop7</a></li>
          <li><a href="../workshop/ws8.php">Workshop8</a></li>
          <li><a href="../workshop/ws9.php">Workshop9</a></li>
          <li><a href="../lab7.php">Lab7</a></li>
        </ul>
      </nav>
      <aside>
        <h2>Aside</h2>
        <p>
          Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed blandit libero sit amet nunc ultricies, eu feugiat diam placerat. Phasellus tincidunt nisi et lectus pulvinar, quis tincidunt lacus viverra. Phasellus in aliquet massa. Integer iaculis massa id dolor venenatis scelerisque.
          <br><br>
        </p>
      </aside>
    </main>
    <footer>
      <a href="#">Sitemap</a>
      <a href="#">Contact</a>
      <a href="#">Privacy</a>
    </footer>
  </body>
</html>







