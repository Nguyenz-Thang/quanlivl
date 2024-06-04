<?php
// Kết nối cơ sở dữ liệu
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "quan_ly_vat_lieu"; // Tên cơ sở dữ liệu bạn đã tạo

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

// Hàm lấy danh sách vật liệu
function getMaterials() {
    global $conn;
    $sql = "SELECT * FROM materials";
    $result = $conn->query($sql);
    $materials = array();
    if ($result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $materials[] = $row;
        }
    }
    return $materials;
}
// Hàm thêm vật liệu mới
// Hàm thêm vật liệu mới
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add'])) {
    // Tìm ID tiếp theo
    $sql_max_id = "SELECT MAX(id) as max_id FROM materials";
    $result_max_id = $conn->query($sql_max_id);
    $row_max_id = $result_max_id->fetch_assoc();
    $next_id = $row_max_id['max_id'] + 1;

    // Lấy thông tin từ form
    $name = $_POST['name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $unit = $_POST['unit'];

    // Thêm bản ghi mới với ID tiếp theo
    $sql = "INSERT INTO materials (id, name, quantity, price, unit) VALUES ($next_id, '$name', $quantity, $price, '$unit')";
    $conn->query($sql);

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

// Hàm sửa thông tin vật liệu
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $unit = $_POST['unit'];
    $sql = "UPDATE materials SET name='$name', quantity=$quantity, price=$price, unit='$unit' WHERE id=$id";
    $conn->query($sql);
    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}


if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['delete'])) {
    $id = $_POST['id'];
    $sql = "DELETE FROM materials WHERE id=$id";
    $conn->query($sql);

    // Kiểm tra và sắp xếp lại ID
    $sql = "SELECT id FROM materials ORDER BY id";
    $result = $conn->query($sql);
    $counter = 1;
    while ($row = $result->fetch_assoc()) {
        $current_id = $row['id'];
        if ($current_id != $counter) {
            $update_sql = "UPDATE materials SET id=$counter WHERE id=$current_id";
            $conn->query($update_sql);
        }
        $counter++;
    }

    header("Location: ".$_SERVER['PHP_SELF']);
    exit();
}

$materials = getMaterials();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Quản Lý Vật Liệu</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #f4f4f4;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    header {
        background-color: #333;
        width: 100%;
        color: #fff;
        padding: 1rem 0;
        text-align: center;
        position: relative;
        /* Thêm thuộc tính position */
    }

    header img {
        position: absolute;
        /* Thêm thuộc tính position */
        left: 10px;
        /* Điều chỉnh vị trí sang bên trái */
        top: 50%;
        /* Để hình ảnh căn giữa theo chiều dọc */
        transform: translateY(-50%);
        /* Điều chỉnh vị trí dọc */
    }

    header h1 {
        margin: 0;
        margin-left: 50px;
        /* Thêm khoảng cách từ hình ảnh đến tiêu đề */
    }

    .container {
        width: 90%;
        max-width: 1200px;
        margin: 2rem auto;
        background-color: #fff;
        padding: 2rem;
        box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        border-radius: 8px;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 2rem;
    }

    table,
    th,
    td {
        border: 1px solid #ddd;
    }

    th,
    td {
        padding: 12px;
        text-align: left;
    }

    th {
        background-color: #f2f2f2;
    }

    form {
        margin-bottom: 2rem;
    }

    form div {
        margin-bottom: 1rem;
    }

    form label {
        display: block;
        margin-bottom: 0.5rem;
    }

    form input[type="text"],
    form input[type="number"] {
        width: 100%;
        padding: 0.5rem;
        box-sizing: border-box;
    }

    button {
        padding: 0.5rem 1rem;
        color: #fff;
        background-color: #6699CC;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        overflow: hidden;
    }

    button:hover {
        background-color: #0066CC;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
    }
    </style>
</head>

<body>
    <header>
        <a href="dashboard.php"><img src="./img/qdd.png" alt="anh" width="100px"></a>
        <h1>Quản Lý Vật Liệu</h1>
    </header>

    <div class="container">
        <h2>Danh Sách Vật Liệu</h2>
        <table>
            <tr>
                <th>ID</th>
                <th>Tên</th>
                <th>Số Lượng</th>
                <th>Đơn Vị</th>
                <th>Giá</th>
                <th>Hành Động</th>
            </tr>
            <?php foreach ($materials as $material) : ?>
            <tr>
                <td><?php echo $material['id']; ?></td>
                <td><?php echo $material['name']; ?></td>
                <td><?php echo $material['quantity']; ?></td>
                <td><?php echo $material['price']; ?></td>
                <td><?php echo $material['unit']; ?></td>
                <td>
                    <div class="action-buttons">
                        <form method="POST">
                            <input type="hidden" name="id" value="<?php echo $material['id']; ?>">
                            <button type="submit" name="delete">Xóa</button>
                        </form>
                        <button
                            onclick="editMaterial('<?php echo $material['id']; ?>', '<?php echo $material['name']; ?>', '<?php echo $material['quantity']; ?>', '<?php echo $material['price']; ?>', '<?php echo $material['unit']; ?>')">
                            Sửa</button>
                    </div>
                </td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h2>Thêm Vật Liệu Mới</h2>
        <form method="POST">
            <div>
                <label for="name">Tên Vật Liệu:</label>
                <input type="text" id="name" name="name" required>
            </div>
            <div>
                <label for="quantity">Số Lượng:</label>
                <input type="number" id="quantity" name="quantity" required>
            </div>
            <div>
                <label for="unit">Đơn Vị:</label>
                <input type="text" id="unit" name="unit" required>
            </div>
            <div>
                <label for="price">Giá:</label>
                <input type="number" id="price" name="price" step="0.01" required>
            </div>

            <button type="submit" name="add">Thêm</button>
        </form>

        <h2>Sửa Vật Liệu</h2>
        <form method="POST">
            <input type="hidden" id="edit_id" name="id">
            <div>
                <label for="edit_name">Tên Vật Liệu:</label>
                <input type="text" id="edit_name" name="name" required>
            </div>
            <div>
                <label for="edit_quantity">Số Lượng:</label>
                <input type="number" id="edit_quantity" name="quantity" required>
            </div>
            <div>
                <label for="edit_unit">Đơn Vị:</label>
                <input type="text" id="edit_unit" name="unit" required>
            </div>
            <div>
                <label for="edit_price">Giá:</label>
                <input type="number" id="edit_price" name="price" step="0.01" required>
            </div>

            <button type="submit" name="update">Cập Nhật</button>
        </form>
    </div>

    <script>
    function editMaterial(id, name, quantity, price, unit) {
        document.getElementById('edit_id').value = id;
        document.getElementById('edit_name').value = name;
        document.getElementById('edit_quantity').value = quantity;
        document.getElementById('edit_price').value = price;
        document.getElementById('edit_unit').value = unit;
    }
    </script>
</body>

</html>