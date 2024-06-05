<?php
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "quan_ly_vat_lieu";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Kết nối thất bại: " . $conn->connect_error);
}

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

function updateMaterialQuantity($material_id, $quantity, $unit, $type) {
    global $conn;
    $sql = "SELECT quantity FROM materials WHERE id = $material_id";
    $result = $conn->query($sql);
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $current_quantity = $row['quantity'];

        $new_quantity = ($type == 'Nhap') ? $current_quantity + $quantity : $current_quantity - $quantity;

        $update_sql = "UPDATE materials SET quantity = $new_quantity, unit = '$unit' WHERE id = $material_id";
        $conn->query($update_sql);

        addInventoryHistory($material_id, $quantity, $unit, $type);
    }
}

function addInventoryHistory($material_id, $quantity, $unit, $type) {
    global $conn;
    // Lấy số lượng tồn kho hiện tại
    $sql = "SELECT quantity FROM materials WHERE id = $material_id";
    $result = $conn->query($sql);
    $row = $result->fetch_assoc();
    $current_quantity = $row['quantity'];

    $sql = "INSERT INTO inventory_history (material_id, quantity, unit, type, remaining_quantity) 
            VALUES ($material_id, $quantity, '$unit', '$type', $current_quantity)";
    $conn->query($sql);
}


function getInventoryHistory() {
    global $conn;
    $sql = "SELECT ih.*, m.name, m.unit 
            FROM inventory_history ih 
            JOIN materials m ON ih.material_id = m.id 
            ORDER BY ih.id";
    $result = $conn->query($sql);
    $history = array();
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $history[] = $row;
        }
    }
    return $history;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['clear_history'])) {
        $sql = "DELETE FROM inventory_history";
        if ($conn->query($sql) === TRUE) {
            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        } else {
            echo "Lỗi khi xóa lịch sử: " . $conn->error;
        }
    } else {
        // Xử lý khi nhấn nút nhập
        if (isset($_POST['import'])) {
            $material_id = $_POST['material'];
            $quantity = $_POST['quantity'];
            $unit = $_POST['unit'];
            $type = 'Nhap';

            updateMaterialQuantity($material_id, $quantity, $unit, $type);

            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
        
        // Xử lý khi nhấn nút xuất
        if (isset($_POST['export'])) {
            $material_id = $_POST['material'];
            $quantity = $_POST['quantity'];
            $unit = $_POST['unit'];
            $type = 'Xuat';

            updateMaterialQuantity($material_id, $quantity, $unit, $type);

            header("Location: ".$_SERVER['PHP_SELF']);
            exit();
        }
    }
}

$materials = getMaterials();
$history = getInventoryHistory();
?>

<!DOCTYPE html>
<html lang="vi">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nhập và Xuất Kho</title>
    <style>
    body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 0;
        background-color: #eef2f7;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    header {
        background-color: #006064;
        width: 100%;
        color: #fff;
        padding: 1.5rem 0;
        text-align: center;
        position: relative;
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }

    header img {
        position: absolute;
        left: 10px;
        top: 50%;
        transform: translateY(-50%);
        width: 60px;
    }

    header h1 {
        margin: 0;
        margin-left: 70px;
        font-size: 1.8rem;
    }

    .container {
        width: 90%;
        max-width: 800px;
        margin: 2rem auto;
        background-color: #fff;
        padding: 2rem;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 12px;
    }

    h2 {
        color: #333;
        border-bottom: 2px solid #006064;
        padding-bottom: 0.5rem;
    }

    form {
        margin-bottom: 2rem;
    }

    form div {
        margin-bottom: 1rem;
    }

    label {
        display: block;
        margin-bottom: 0.5rem;
        font-weight: bold;
        color: #555;
    }

    input[type="number"],
    select,
    input[type="text"] {
        width: 100%;
        padding: 0.8rem;
        box-sizing: border-box;
        border: 1px solid #ccc;
        border-radius: 6px;
        transition: border-color 0.3s;
    }

    input[type="number"]:focus,
    select:focus,
    input[type="text"]:focus {
        border-color: #006064;
        outline: none;
    }

    button {
        padding: 0.75rem 1.5rem;
        color: #fff;
        background-color: #006064;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.3s;
        margin-right: 0.5rem;
    }

    button:hover {
        background-color: #004d40;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 1rem;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
    }

    table,
    th,
    td {
        border: 1px solid #ddd;
    }

    th,
    td {
        padding: 0.75rem;
        text-align: left;
    }

    th {
        background-color: #f4f4f4;
        color: #333;
        border-bottom: 2px solid #006064;
    }

    tr:nth-child(even) {
        background-color: #f9f9f9;
    }

    .nhap {
        background-color: #e0f7fa;
        color: #006064;
    }

    .xuat {
        background-color: #ffcdd2;
        color: #b71c1c;
    }

    .action-buttons {
        display: flex;
        gap: 0.5rem;
        margin-top: 1rem;
    }

    .action-buttons button {
        padding: 0.5rem 1rem;
        color: #fff;
        background-color: #ff7043;
        border: none;
        border-radius: 6px;
        cursor: pointer;
        transition: background-color 0.3s;
    }

    .action-buttons button:hover {
        background-color: #d84315;
    }
    </style>
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const materials = <?php echo json_encode($materials); ?>;
        const materialSelect = document.getElementById("material");
        const unitInput = document.getElementById("unit");

        materialSelect.addEventListener("change", function() {
            const selectedMaterial = materials.find(material => material.id == this.value);
            if (selectedMaterial) {
                unitInput.value = selectedMaterial.unit;
            }
        });

        // Trigger change event on page load to set the initial unit value
        materialSelect.dispatchEvent(new Event("change"));
    });
    </script>
</head>

<body>
    <header>
        <a href="dashboard.php"><img src="./img/qdd.png" alt="Logo"></a>
        <h1>Nhập và Xuất Kho</h1>
    </header>

    <div class="container">
        <h2>Nhập và Xuất Vật Liệu</h2>
        <form method="POST">
            <div>
                <label for="material">Chọn Vật Liệu:</label>
                <select id="material" name="material" required>
                    <?php foreach ($materials as $material) : ?>
                    <option value="<?php echo $material['id']; ?>"><?php echo $material['name']; ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div>
                <label for="quantity">Số Lượng:</label>
                <input type="number" id="quantity" name="quantity" required>
            </div>
            <div>
                <label for="unit">Đơn Vị:</label>
                <input type="text" id="unit" name="unit" readonly required>
            </div>
            <button type="submit" name="import">Nhập</button>
            <button type="submit" name="export">Xuất</button>
        </form>

        <h2>Lịch Sử Nhập và Xuất Vật Liệu</h2>
        <table>
            <thead>
                <tr>
                    <th>Loại</th>
                    <th>Vật Liệu</th>
                    <th>Số Lượng</th>
                    <th>Đơn Vị</th>
                    <th>Kho</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($history as $item) : ?>
                <tr class="<?php echo strtolower($item['type']); ?>">
                    <td><?php echo $item['type']; ?></td>
                    <td><?php echo $item['name']; ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo $item['unit']; ?></td>
                    <td><?php echo $item['remaining_quantity']; ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div class="action-buttons">
        <form method="POST">
            <button type="submit" name="clear_history">Xóa Lịch Sử</button>
        </form>
    </div>
</body>

</html>