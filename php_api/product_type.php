<?php
include 'condb.php'; // เชื่อมต่อฐานข้อมูล

header('Content-Type: application/json'); // กำหนดให้เป็นการตอบกลับในรูปแบบ JSON

// ดึงข้อมูลประเภทสินค้าจากฐานข้อมูล
$sql = "SELECT * FROM product_type"; // คำสั่ง SQL ดึงข้อมูลจากตาราง product_type
$stmt = $conn->prepare($sql); // เตรียมคำสั่ง SQL

if ($stmt->execute()) {
    $categories = $stmt->fetchAll(PDO::FETCH_ASSOC); // ดึงข้อมูลทั้งหมด
    echo json_encode(["success" => true, "data" => $categories]); // ส่งข้อมูลกลับในรูปแบบ JSON
} else {
    echo json_encode(["success" => false, "message" => "ไม่สามารถดึงข้อมูลประเภทสินค้าได้"]); // หากเกิดข้อผิดพลาด
}
?>
