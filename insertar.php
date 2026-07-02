<?php include 'config.php'; ?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Insertar Empleado</title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="container">
    <h1> Insertar Empleado</h1>
    
    <?php
    $mensaje = '';
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $ndiemp = $_POST['ndiemp'];
        $nomemp = $_POST['nomemp'];
        $sexemp = $_POST['sexemp'];
        $fecnac = $_POST['fecnac'];
        $fecincorporacion = $_POST['fecincorporacion'];
        $salemp = $_POST['salemp'];
        $comisione = $_POST['comisione'];
        $cargoe = $_POST['cargoe'];
        $jefeid = $_POST['jefeid'];
        $coddepto = $_POST['coddepto'];
        
        $sql = "CALL insertar_empleado('$ndiemp', '$nomemp', '$sexemp', '$fecnac', '$fecincorporacion', $salemp, $comisione, '$cargoe', '$jefeid', '$coddepto')";
        if ($conn->query($sql)) {
            $mensaje = '<div class="mensaje exito">✅ Empleado insertado correctamente</div>';
        } else {
            $mensaje = '<div class="mensaje error">❌ Error: ' . $conn->error . '</div>';
        }
    }
    
    $result_deptos = $conn->query("SELECT * FROM departamentos ORDER BY nombreDpto");
    $result_jefes = $conn->query("SELECT ndiemp, nomemp FROM empleados ORDER BY nomemp");
    ?>
    
    <?php echo $mensaje; ?>
    
    <form method="POST" class="form-crud">
        <div class="form-row">
            <div class="form-group">
                <label>ID *</label>
                <input type="text" name="ndiemp" required>
            </div>
            <div class="form-group">
                <label>Nombre *</label>
                <input type="text" name="nomemp" required>
            </div>
            <div class="form-group">
                <label>Sexo *</label>
                <select name="sexemp" required>
                    <option value="">Seleccionar</option>
                    <option value="M">Masculino</option>
                    <option value="F">Femenino</option>
                </select>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Fecha Nacimiento *</label>
                <input type="date" name="fecnac" required>
            </div>
            <div class="form-group">
                <label>Fecha Incorporación *</label>
                <input type="date" name="fecincorporacion" required>
            </div>
            <div class="form-group">
                <label>Salario *</label>
                <input type="number" step="0.01" name="salemp" required>
            </div>
        </div>
        
        <div class="form-row">
            <div class="form-group">
                <label>Comisión</label>
                <input type="number" step="0.01" name="comisione" value="0">
            </div>
            <div class="form-group">
                <label>Cargo *</label>
                <input type="text" name="cargoe" required>
            </div>
            <div class="form-group">
                <label>Jefe</label>
                <select name="jefeid">
                    <option value="">Sin jefe</option>
                    <?php while ($jefe = $result_jefes->fetch_assoc()): ?>
                        <option value="<?php echo $jefe['ndiemp']; ?>">
                            <?php echo $jefe['ndiemp'] . ' - ' . $jefe['nomemp']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Departamento *</label>
                <select name="coddepto" required>
                    <option value="">Seleccionar</option>
                    <?php while ($depto = $result_deptos->fetch_assoc()): ?>
                        <option value="<?php echo $depto['codDepto']; ?>">
                            <?php echo $depto['codDepto'] . ' - ' . $depto['nombreDpto']; ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </div>
        </div>
        
        <div class="form-actions">
            <button type="submit" class="btn btn-success"> Insertar</button>
            <button type="reset" class="btn btn-secondary"> Limpiar</button>
            <button onclick="window.close();" class="btn btn-secondary"> Cerrar</button>
        </div>
    </form>
</div>
</body>
</html>
<?php $conn->close(); ?>