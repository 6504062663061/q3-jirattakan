<?php include "connect.php" ?>

<!doctype html>
<html lang="en">


  <head>
    <meta charset="utf-8">
    <title>CS Shop</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="mobile-web-app-capable" content="yes">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <link href="mcss.css" rel="stylesheet" type="text/css" />
    <script src="mpage.js"></script>
    <style>
      table {
        margin: auto;
      }

      .logout {
        border: 1px black solid;
        border-radius: 2px;
        color: red;
        position: relative;
        top: 20px;
        left: 700px;

      }
      .order {
        color: blue;
      }

      .login {
        color: aliceblue;
      }
    </style>
  </head>

  <body>

    <header>
      <div class="logo">
        <img src="cslogo.jpg" width="200" alt="Site Logo">
      </div>
      <div class="search">
        <form>
          <input type="search" placeholder="Search the site...">
          <button>Search</button>

        </form>
        
      </div>
      <?php 
        if (!isset($_SESSION['username'])) {
          
          echo "<a class='login' href='./cart/login-form.php'>เข้าสู่ระบบ</a>";
          
        }else{
          echo " ";
        }
      ?>
      
    </header>

    <div class="mobile_bar">
      <a href="#"><img src="responsive-demo-home.gif" alt="Home"></a>
      <a href="#" onClick='toggle_visibility("menu"); return false;'><img src="responsive-demo-menu.gif" alt="Menu"></a>
    </div>

    <main>
      <article>
        <h1>Welcome</h1>

        

        <?php

            session_start();

            
        
        

        // ดึงชื่อผู้ใช้ที่ล็อกอิน
        $username = $_SESSION['username'];

        // ตรวจสอบสิทธิ์ของผู้ใช้
        $stmt = $pdo->prepare("SELECT type FROM member WHERE username = :username");
        $stmt->execute(['username' => $username]);
        $user = $stmt->fetch();

        // ตรวจสอบว่ามีพารามิเตอร์ 'view_orders' ส่งมาไหม (ในกรณีที่ Admin คลิกลิงก์)
        $view_orders_of = isset($_GET['view_orders']) ? $_GET['view_orders'] : null;

        if ($user['type'] == 'admin' && $view_orders_of) {
            // หากผู้ใช้เป็น Admin และมีการส่ง username ของผู้ใช้ที่ต้องการดูรายการคำสั่งซื้อ
            $stmt = $pdo->prepare("SELECT o.ord_id, o.username, o.ord_date, p.pname, i.quantity, p.price 
                                    FROM orders o
                                    JOIN item i ON o.ord_id = i.ord_id
                                    JOIN product p ON i.pid = p.pid
                                    WHERE o.username = :username 
                                    ");
            $stmt->execute(['username' => $view_orders_of]);
            $orders = $stmt->fetchAll();
        } elseif ($user['type'] == 'admin') {
            // หากผู้ใช้เป็น Admin ให้ดึงข้อมูลจำนวนคำสั่งซื้อของผู้ใช้แต่ละคน
            $stmt = $pdo->prepare("SELECT username, COUNT(ord_id) as order_count 
                                    FROM orders 
                                    GROUP BY username");
            $stmt->execute();
            $orders = $stmt->fetchAll();
        } else {
            // หากเป็นผู้ใช้ปกติ ให้ดึงข้อมูลรายการสั่งซื้อของผู้ใช้
            $stmt = $pdo->prepare("SELECT o.ord_id,o.username, o.ord_date, p.pname, i.quantity, p.price 
                                    FROM orders o
                                    JOIN item i ON o.ord_id = i.ord_id
                                    JOIN product p ON i.pid = p.pid
                                    WHERE o.username = :username 
                                    ");
            $stmt->execute(['username' => $username]);
            $orders = $stmt->fetchAll();
        }
        ?>

        
        <div>
            <div>
                <h1 style="margin :auto">ยินดีต้อนรับ, <?= htmlspecialchars($username) ?>!</h1>
            </div>

            <?php if ($user['type'] == 'admin' && !$view_orders_of): ?>
                <h2>จำนวนคำสั่งซื้อของผู้ใช้แต่ละคน</h2>
                <table border="1">
                    <tr>
                        <th>ชื่อผู้ใช้</th>
                        <th>จำนวนคำสั่งซื้อ</th>
                    </tr>
                    <?php if (count($orders) > 0): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= htmlspecialchars($order['username']) ?></td>
                                <td><a class="order" href="?view_orders=<?= htmlspecialchars($order['username']) ?>">
                                    <?= $order['order_count'] ?>
                                </a></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2">ไม่มีข้อมูลคำสั่งซื้อ</td>
                        </tr>
                    <?php endif; ?>
                </table>
            <?php elseif ($user['type'] == 'admin' && $view_orders_of): ?>
                <h2>รายการสั่งซื้อของผู้ใช้: <?= htmlspecialchars($view_orders_of) ?></h2>
                <table border="1">
                    <tr>
                        <th>หมายเลขคำสั่งซื้อ</th>
                        <th>username</th>
                        <th>วันที่</th>
                        <th>ชื่อสินค้า</th>
                        <th>จำนวน</th>
                        <th>ราคา</th>
                    </tr>
                    <?php if (count($orders) > 0): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= $order['ord_id'] ?></td>
                                <td><?= $order['username'] ?></td>
                                <td><?= $order['ord_date'] ?></td>
                                <td><?= $order['pname'] ?></td>
                                <td><?= $order['quantity'] ?></td>
                                <td><?= $order['price'] * $order['quantity']?> บาท</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">ไม่มีรายการสั่งซื้อ</td>
                        </tr>
                    <?php endif; ?>
                </table>
                
            <?php else: ?>
                <h2>รายการสั่งซื้อของคุณ</h2>
                <table border="1">
                    <tr>
                        <th>หมายเลขคำสั่งซื้อ</th>
                        <th>username</th>
                        <th>วันที่</th>
                        <th>ชื่อสินค้า</th>
                        <th>จำนวน</th>
                        <th>ราคา</th>
                    </tr>
                    <?php if (count($orders) > 0): ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><?= $order['ord_id'] ?></td>
                                <td><?= $order['username'] ?></td>
                                <td><?= $order['ord_date'] ?></td>
                                <td><?= $order['pname'] ?></td>
                                <td><?= $order['quantity'] ?></td>
                                <td><?= $order['price'] * $order['quantity']?> บาท</td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6">ไม่มีรายการสั่งซื้อ</td>
                        </tr>
                    <?php endif; ?>
                </table>
            <?php endif; ?>
            <?php
              // ตรวจสอบว่า ผู้ใช้ล็อกอินหรือไม่
                if (isset($_SESSION['username'])) {
                  // หากยังไม่ได้ล็อกอิน, เปลี่ยนเส้นทางไปยังหน้า login
                  echo "<a class='logout' href='./cart/logout.php'>ล็อกเอาท์</a>";
                
              }
            
            ?>
          </div>   
      </article>
      <nav id="menu">
        <h2>Navigation</h2>
        <ul class="menu">
        <li class="dead"><a href="./index.php">Home</a></li>
          <li><a href="./product_php/display_products.php">All Products</a></li>
          <li><a href="./product_php/table_product.php">Table of All Products</a></li>
          <li><a href="./cart/store.php">Buy Products</a></li>
          <li><a href="./cart/cart.php">Cart</a></li>
          <li><a href="./member_php/member.php">All Member</a></li>
          <li><a href="./insert_product.php">Insert Products</a></li>
          <li><a href="./insert_member.php">Insert Member</a></li>
          <li><a href="./member_php/edit_member.php">Delete/edit Member</a></li>
          <li><a href="./product_php/edit_product.php">Delete/edit product</a></li>
          <li><a href="./workshop/ws1.php">Workshop1</a></li>
          <li><a href="./workshop/ws2.php">Workshop2</a></li>
          <li><a href="./workshop/ws3.php">Workshop3</a></li>
          <li><a href="./workshop/ws4.php">Workshop4</a></li>
          <li><a href="./workshop/ws5.php">Workshop5</a></li>
          <li><a href="./workshop/ws6.php">Workshop6</a></li>
          <li><a href="./workshop/ws7.php">Workshop7</a></li>
          <li><a href="./workshop/ws8.php">Workshop8</a></li>
          <li><a href="./workshop/ws9.php">Workshop9</a></li>
          <li><a href="./lab7.php">Lab7</a></li>
          
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