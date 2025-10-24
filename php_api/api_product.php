<?php

include 'condb.php';

$action = $_POST['action'] ?? null;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && $action) {
    // เพิ่ม / แก้ไข / ลบ
    switch($action) {

        case 'add':
            $product_name = $_POST['product_name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $type_id = $_POST['type_id'];  // เพิ่มประเภทสินค้า

            // อัพโหลดไฟล์รูป
            $filename = null;
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $targetDir = "uploads/";
                if (!is_dir($targetDir)) {
                    mkdir($targetDir, 0777, true);
                }
                $filename = time() . '_' . basename($_FILES['image']['name']);
                $targetFile = $targetDir . $filename;
                move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
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
            $stmt->bindParam(':type_id', $type_id);  // เพิ่มประเภทสินค้า

            if ($stmt->execute()) {
                echo json_encode(["message" => "เพิ่มสินค้าสำเร็จ"]);
            } else {
                echo json_encode(["error" => "เพิ่มสินค้าล้มเหลว"]);
            }
            break;

        case 'update':
            $product_id = $_POST['product_id'];
            $product_name = $_POST['product_name'];
            $description = $_POST['description'];
            $price = $_POST['price'];
            $stock = $_POST['stock'];
            $type_id = $_POST['type_id'];  // เพิ่มประเภทสินค้า

            // อัพโหลดไฟล์รูปใหม่ ถ้ามีการอัพโหลด
            if (isset($_FILES['image']) && $_FILES['image']['error'] === 0) {
                $targetDir = "uploads/";
                $filename = time() . '_' . basename($_FILES['image']['name']);
                $targetFile = $targetDir . $filename;
                move_uploaded_file($_FILES['image']['tmp_name'], $targetFile);
                $imageSQL = ", image = :image";
            } else {
                $imageSQL = "";
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
            $stmt->bindParam(':product_id', $product_id);
            $stmt->bindParam(':type_id', $type_id);  // เพิ่มประเภทสินค้า

            // ถ้ามีการอัพโหลดรูปใหม่
            if (isset($filename)) {
                $stmt->bindParam(':image', $filename);
            }

            if ($stmt->execute()) {
                echo json_encode(["message" => "แก้ไขสินค้าสำเร็จ"]);
            } else {
                echo json_encode(["error" => "แก้ไขสินค้าล้มเหลว"]);
            }
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

            if ($stmt->execute()) {
                echo json_encode(["message" => "ลบสินค้าสำเร็จ และลบรูปภาพออกจากโฟลเดอร์แล้ว"]);
            } else {
                echo json_encode(["error" => "ลบสินค้าล้มเหลว"]);
            }
            break;

        default:
            echo json_encode(["error" => "Action ไม่ถูกต้อง"]);
            break;
    }

} else {
    // GET: ดึงข้อมูลสินค้า
    $stmt = $conn->prepare("SELECT products.*, product_type.type_name FROM products
                            LEFT JOIN product_type ON products.type_id = product_type.type_id
                            ORDER BY product_id DESC");
    if ($stmt->execute()) {
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode(["success" => true, "data" => $products]);
    } else {
        echo json_encode(["success" => false, "data" => []]);
    }
}
?>
