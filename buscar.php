<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscar Empleado</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h1>🔍 Buscar Empleado</h1>
    
    <?php
    $resultado = null;
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $busqueda = $_POST['busqueda'];
        $sql = "CALL buscar_empleado('$busqueda')";
        $resultado = $conn->query($sql);
    }
    ?>
    
    <div class="form-section">
        <h2> Buscar por ID o Nombre</h2>
        <form method="POST" class="form-crud">
            <div class="form-row">
                <div class="form-group">
                    <label>Buscar</label>
                    <input type="text" name="busqueda" placeholder="DNI o nombre..." required>
                </div>
                <div class="form-group" style="justify-content: flex-end;">
                    <button type="submit" class="btn btn-primary">🔍 Buscar</button>
                </div>
            </div>
        </form>
    </div>
    
    <?php if ($resultado): ?>
    <div class="form-section">
        <h2> Resultados</h2>
        <?php if ($resultado->num_rows > 0): ?>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Sexo</th>
                        <th>Cargo</th>
                        <th>Salario</th>
                        <th>Depto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $resultado->fetch_assoc()): ?>
                    <tr>
                        <td><strong><?php echo $row['nDIEmp']; ?></strong></td>
                        <td><?php echo $row['nomEmp']; ?></td>
                        <td><?php echo $row['sexEmp']; ?></td>
                        <td><?php echo $row['cargoE']; ?></td>
                        <td>$<?php echo number_format($row['salEmp'], 0); ?></td>
                        <td><?php echo $row['codDepto']; ?></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
        <div class="total">Total: <?php echo $resultado->num_rows; ?> empleados encontrados</div>
        <?php else: ?>
            <div class="mensaje error">❌ No se encontraron empleados</div>
        <?php endif; ?>
    </div>
    <?php endif; ?>
    
    <div class="form-actions">
        <button onclick="window.close();" class="btn btn-secondary">❌ Cerrar</button>
    </div>
</div>
</body>
</html>
<?php $conn->close(); ?>