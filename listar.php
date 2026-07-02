<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Listar Empleados</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h1> Lista de Empleados</h1>
    
    <?php
    $sql = "CALL listar_empleados()";
    $result = $conn->query($sql);
    ?>
    
    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Sexo</th>
                    <th>F. Nac.</th>
                    <th>F. Inc.</th>
                    <th>Salario</th>
                    <th>Comisión</th>
                    <th>Cargo</th>
                    <th>Jefe</th>
                    <th>Depto</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <td><strong><?php echo $row['nDIEmp']; ?></strong></td>
                            <td><?php echo $row['nomEmp']; ?></td>
                            <td><?php echo $row['sexEmp']; ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['fecNac'])); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($row['fecIncorporacion'])); ?></td>
                            <td>$<?php echo number_format($row['salEmp'], 0); ?></td>
                            <td>$<?php echo number_format($row['comisionE'], 0); ?></td>
                            <td><?php echo $row['cargoE']; ?></td>
                            <td><?php echo $row['jefeID'] ? $row['jefeID'] : '-'; ?></td>
                            <td><?php echo $row['codDepto']; ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="10" class="vacio">No hay empleados registrados</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <div class="total">Total: <?php echo $result ? $result->num_rows : 0; ?> empleados</div>
    
    <div class="form-actions" style="margin-top: 20px;">
        <button onclick="window.close();" class="btn btn-secondary">❌ Cerrar</button>
    </div>
</div>
</body>
</html>
<?php $conn->close(); ?>