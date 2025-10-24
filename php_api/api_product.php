<?php
// 📌 1. ตั้งค่า Header ให้ไฟล์นี้ตอบกลับเป็น JSON เสมอ
header('Content-Type: application/json');

// 📌 2. เปิดการแสดงผล Error (ใช้เฉพาะตอนพัฒนาเท่านั้น)
ini_set('display_errors', 1);
error_reporting(E_ALL);

try {
    // 📌 3. เชื่อมต่อฐานข้อมูล
    include 'condb.php';

    /*
    // 📌 4. [สำคัญมาก] ไฟล์ condb.php ของคุณควรมีลักษณะนี้
    // เพื่อให้ PDO โยน Exception เมื่อเกิดข้อผิดพลาด
    $dsn = 'mysql:host=localhost;dbname=your_db_name;charset=utf8mb4';
    $user = 'your_username';
    $pass = 'your_password';
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION, // <<< นี่คือตัวสำคัญ
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];

    try {
        $conn = new PDO($dsn, $user, $pass, $options);
    } catch (PDOException $e) {
        throw new PDOException($e->getMessage(), (int)$e->getCode());
    }
    */
    
    // ตรวจสอบว่า $conn ถูกสร้างขึ้นหรือไม่
    if (!isset($conn)) {
        throw new Exception("Database connection variable \$conn is not set in condb.php");
    }


    $action = $_POST['action'] ?? null;

    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
        // เพิ่ม / แก้ไข / ลบ
        switch ($action) {

            case 'add':
                $product_name = $_POST['product_name'];
                $description = $_POST['description'];
                $price = $_POST['price'];
                $stock = $_POST['stock'];
                $type_id = $_POST['type_id'];

                // อัพโหลดไฟล์รูป
                $filename = null;
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    $targetDir = "uploads/";
                    // 📌 5. ตรวจสอบการสร้างโฟลเดอร์
                    if (!is_dir($targetDir)) {
                        if (!mkdir($targetDir, 0777, true)) {
                            throw new Exception("ไม่สามารถสร้างโฟลเดอร์ 'uploads' ได้ โปรดตรวจสอบสิทธิ์ (Permissions)");
                        }
                    }
                    $filename = time() . '_' . basename($_FILES['image']['name']);
                    $targetFile = $targetDir . $filename;
                    
                    // 📌 5. ตรวจสอบการย้ายไฟล์
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                        throw new Exception("อัพโหลดไฟล์ล้มเหลว");
                    }
                }

                // SQL สำหรับเพิ่มสินค้าใหม่
                $sql = "INSERT INTO products (product_name, description, price, stock, image, type_id)
                        VALUES (:product_name, :description, :price, :stock, :image, :type_id)";
                $stmt = $conn->prepare($sql);
                $stmt->bindParam(':product_name', $product_name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':price', $price);
                $stmt->bindParam(':stock', $stock);
                $stmt->bindParam(':image', $filename);
                $stmt->bindParam(':type_id', $type_id);
                $stmt->execute();
                
                // 📌 7. ส่งสถานะ 201 (Created) เมื่อสำเร็จ
                http_response_code(201); 
                echo json_encode(["message" => "เพิ่มสินค้าสำเร็จ"]);
                
                break;

            case 'update':
                $product_id = $_POST['product_id'];
                $product_name = $_POST['product_name'];
                $description = $_POST['description'];
                $price = $_POST['price'];
                $stock = $_POST['stock'];
                $type_id = $_POST['type_id'];
                
                $filename = null; // เก็บชื่อไฟล์ใหม่
                $imageSQL = "";  // ส่วนของ SQL สำหรับอัพเดทรูป

                // 📌 6. ตรวจสอบว่ามีการอัพโหลดไฟล์ใหม่หรือไม่
                if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                    
                    // 1. ดึงชื่อรูปเก่าเพื่อลบทิ้ง
                    $stmt_old = $conn->prepare("SELECT image FROM products WHERE product_id = :product_id");
                    $stmt_old->bindParam(':product_id', $product_id);
                    $stmt_old->execute();
                    $old_product = $stmt_old->fetch();

                    if ($old_product && !empty($old_product['image'])) {
                        $oldFilePath = "uploads/" . $old_product['image'];
                        if (file_exists($oldFilePath)) {
                            unlink($oldFilePath); // ลบไฟล์รูปเก่า
                        }
                    }

                    // 2. อัพโหลดไฟล์ใหม่
                    $targetDir = "uploads/";
                    $filename = time() . '_' . basename($_FILES['image']['name']);
                    $targetFile = $targetDir . $filename;
                    
                    if (!move_uploaded_file($_FILES['image']['tmp_name'], $targetFile)) {
                         throw new Exception("อัพโหลดไฟล์ใหม่ล้มเหลว");
                    }
                    $imageSQL = ", image = :image"; // เตรียม SQL
                }

                // SQL สำหรับแก้ไขข้อมูลสินค้า
                $sql = "UPDATE products SET 
                            product_name = :product_name,
                            description = :description,
                            price = :price,
                            stock = :stock,
                            type_id = :type_id
                            $imageSQL
                        WHERE product_id = :product_id";
                $stmt = $conn->prepare($sql);

                $stmt->bindParam(':product_name', $product_name);
                $stmt->bindParam(':description', $description);
                $stmt->bindParam(':price', $price);
                $stmt->bindParam(':stock', $stock);
                $stmt->bindParam(':type_id', $type_id);
                $stmt->bindParam(':product_id', $product_id);

                // ถ้ามีการอัพโหลดรูปใหม่ (filename ไม่ใช่ null)
                if ($filename !== null) {
                    $stmt->bindParam(':image', $filename);
                }

                $stmt->execute();
                
                http_response_code(200); // 200 OK
                echo json_encode(["message" => "แก้ไขสินค้าสำเร็จ"]);
                
                break;

            case 'delete':
                $product_id = $_POST['product_id'];

                // 🔍 ดึงชื่อไฟล์รูปจากฐานข้อมูลก่อนลบ
                $stmt = $conn->prepare("SELECT image FROM products WHERE product_id = :product_id");
                $stmt->bindParam(':product_id', $product_id);
                $stmt->execute();
                $product = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($product && !empty($product['image'])) {
                    $filePath = "uploads/" . $product['image'];
                    // 🧹 ลบไฟล์รูปถ้ามีอยู่จริง
                    if (file_exists($filePath)) {
                        unlink($filePath);
                    }
                }

                // 🔥 ลบข้อมูลสินค้าออกจากฐานข้อมูล
                $stmt = $conn->prepare("DELETE FROM products WHERE product_id = :product_id");
                $stmt->bindParam(':product_id', $product_id);
                $stmt->execute();

                http_response_code(200); // 200 OK
                echo json_encode(["message" => "ลบสินค้าสำเร็จ และลบรูปภาพออกจากโฟลเดอร์แล้ว"]);
                
                break;

            default:
                // 📌 7. ส่งสถานะ 400 (Bad Request)
                http_response_code(400); 
                echo json_encode(["error" => "Action ไม่ถูกต้อง"]);
                break;
        }
    } else {
        // GET: ดึงข้อมูลสินค้า (ส่วนนี้ถูกต้องอยู่แล้ว)
        $stmt = $conn->prepare("SELECT products.*, product_type.type_name FROM products
                                LEFT JOIN product_type ON products.type_id = product_type.type_id
                                ORDER BY product_id DESC");
        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        http_response_code(200); // 200 OK
        echo json_encode(["success" => true, "data" => $products]);
    }

} catch (PDOException $e) {
    // 📌 3. ดักจับ Error จากฐานข้อมูล
    http_response_code(500); // 500 Internal Server Error
    echo json_encode(["error" => "Database Error: " . $e->getMessage()]);

} catch (Exception $e) {
    // 📌 3. ดักจับ Error ทั่วไป (เช่น อัพโหลดไฟล์ล้มเหลว)
    http_response_code(400); // 400 Bad Request
    echo json_encode(["error" => "Error: " . $e->getMessage()]);
}
?>