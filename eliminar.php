<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Eliminar Empleado</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h1> Eliminar Empleado</h1>
    
    <?php
    $mensaje = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $ndiemp = $_POST['ndiemp'];
        
        $check = $conn->query("SELECT nomemp FROM empleados WHERE ndiemp = '$ndiemp'");
        if ($check->num_rows > 0) {
            $empleado = $check->fetch_assoc();
            $sql = "CALL eliminar_empleado('$ndiemp')";
            if ($conn->query($sql)) {
                $mensaje = '<div class="mensaje exito">✅ Empleado "' . $empleado['nomemp'] . '" eliminado correctamente</div>';
            } else {
                $mensaje = '<div class="mensaje error">❌ Error: ' . $conn->error . '</div>';
            }
        } else {
            $mensaje = '<div class="mensaje error">❌ Empleado no encontrado</div>';
        }
    }
    ?>
    
    <?php echo $mensaje; ?>
    
    <div class="form-section">
        <h2>⚠️ Eliminar Empleado</h2>
        <p style="color: #721c24; margin-bottom: 15px; background: #f8d7da; padding: 10px; border-radius: 6px;">
            <strong>Advertencia:</strong> Esta acción es irreversible
        </p>
        
        <form method="POST" class="form-crud">
            <div class="form-row">
                <div class="form-group">
                    <label>ID del empleado a eliminar *</label>
                    <input type="text" name="ndiemp" required placeholder="Ej: 31.840.269">
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-danger" onclick="return confirm('¿Estás seguro de eliminar este empleado?')">
                     Eliminar
                </button>
                <button onclick="window.close();" class="btn btn-secondary">❌ Cerrar</button>
            </div>
        </form>
    </div>
    
    <div class="form-section">
        <h2> Lista de Empleados</h2>
        <div class="table-wrapper">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Cargo</th>
                        <th>Depto</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $result = $conn->query("SELECT ndiemp, nomemp, cargoe, coddepto FROM empleados ORDER BY nomemp");
                    if ($result->num_rows > 0):
                        while ($row = $result->fetch_assoc()):
                    ?>
                        <tr>
                            <td><?php echo $row['ndiemp']; ?></td>
                            <td><?php echo $row['nomemp']; ?></td>
                            <td><?php echo $row['cargoe']; ?></td>
                            <td><?php echo $row['coddepto']; ?></td>
                        </tr>
                    <?php endwhile; else: ?>
                        <tr><td colspan="4" class="vacio">No hay empleados</td></tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
</body>
</html>
<?php $conn->close(); ?>