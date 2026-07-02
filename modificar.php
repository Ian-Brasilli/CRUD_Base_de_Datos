<?php
// modificar.php
include 'config.php';

$mensaje = '';
$tipo_mensaje = '';
$empleado_seleccionado = null;
$departamentos = [];
$jefes = [];

// Obtener listas para los selects
try {
    // Obtener departamentos
    $result_deptos = $conn->query("SELECT codDepto, nombreDpto, ciudad FROM departamentos ORDER BY nombreDpto, ciudad");
    while ($row = $result_deptos->fetch_assoc()) {
        $departamentos[] = $row;
    }
    
    // Obtener jefes
    $result_jefes = $conn->query("SELECT nDIEmp, nomEmp, cargoE FROM empleados ORDER BY nomEmp");
    while ($row = $result_jefes->fetch_assoc()) {
        $jefes[] = $row;
    }
} catch (Exception $e) {
    $mensaje = "Error al cargar datos: " . $e->getMessage();
    $tipo_mensaje = "error";
}

// Procesar modificación - SOLO si se envió el formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'modificar') {
    try {
        // Verificar que todos los campos necesarios existan en $_POST
        $campos_requeridos = ['ndiemp_original', 'ndiemp', 'nomemp', 'sexemp', 'fecnac', 
                              'fecincorporacion', 'salemp', 'comisione', 'cargoe', 'coddepto'];
        
        $errores = [];
        foreach ($campos_requeridos as $campo) {
            if (!isset($_POST[$campo]) || $_POST[$campo] === '') {
                $errores[] = "El campo " . str_replace('_', ' ', $campo) . " es obligatorio";
            }
        }
        
        if (empty($errores)) {
            $ndiemp_original = $_POST['ndiemp_original'];
            $ndiemp_nuevo = trim($_POST['ndiemp']);
            $nomemp = trim($_POST['nomemp']);
            $sexemp = $_POST['sexemp'];
            $fecnac = $_POST['fecnac'];
            $fecincorporacion = $_POST['fecincorporacion'];
            $salemp = floatval($_POST['salemp']);
            $comisione = floatval($_POST['comisione']);
            $cargoe = trim($_POST['cargoe']);
            $jefeid = isset($_POST['jefeid']) && $_POST['jefeid'] !== '' ? $_POST['jefeid'] : null;
            $coddepto = $_POST['coddepto'];
            
            // Validar que los campos no estén vacíos después del trim
            if (empty($ndiemp_nuevo)) $errores[] = "El número de identificación es obligatorio";
            if (empty($nomemp)) $errores[] = "El nombre del empleado es obligatorio";
            if (empty($sexemp)) $errores[] = "El sexo es obligatorio";
            if (empty($fecnac)) $errores[] = "La fecha de nacimiento es obligatoria";
            if (empty($fecincorporacion)) $errores[] = "La fecha de incorporación es obligatoria";
            if ($salemp <= 0) $errores[] = "El salario debe ser mayor a 0";
            if (empty($cargoe)) $errores[] = "El cargo es obligatorio";
            if (empty($coddepto)) $errores[] = "El departamento es obligatorio";
        }
        
        if (empty($errores)) {
            // Llamar al procedimiento almacenado
            $stmt = $conn->prepare("CALL modificar_empleado(?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param(
                "sssssddssss",
                $ndiemp_original,
                $ndiemp_nuevo,
                $nomemp,
                $sexemp,
                $fecnac,
                $fecincorporacion,
                $salemp,
                $comisione,
                $cargoe,
                $jefeid,
                $coddepto
            );
            
            if ($stmt->execute()) {
                $result = $stmt->get_result();
                $row = $result->fetch_assoc();
                $mensaje = $row['mensaje'] ?? 'Empleado modificado exitosamente';
                $tipo_mensaje = 'exito';
                
                // Limpiar selección
                $empleado_seleccionado = null;
            } else {
                $mensaje = "Error al modificar el empleado";
                $tipo_mensaje = 'error';
            }
            $stmt->close();
        } else {
            $mensaje = implode("<br>", $errores);
            $tipo_mensaje = 'error';
        }
    } catch (Exception $e) {
        $mensaje = "Error: " . $e->getMessage();
        $tipo_mensaje = 'error';
    }
}

// Obtener empleado si se seleccionó uno para modificar
if (isset($_GET['id']) && !empty($_GET['id'])) {
    $id = $_GET['id'];
    $result = $conn->query("SELECT * FROM empleados WHERE nDIEmp = '$id'");
    if ($result && $result->num_rows > 0) {
        $empleado_seleccionado = $result->fetch_assoc();
        $mensaje = "✅ Empleado encontrado";
        $tipo_mensaje = 'exito';
    } else {
        $mensaje = "❌ No se encontró ningún empleado con el ID: " . htmlspecialchars($id);
        $tipo_mensaje = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Modificar Empleado</title>
    <link rel="stylesheet" href="styles.css">
    <style>
        /* Estilos adicionales específicos para el formulario de modificación */
        .form-busqueda {
            margin-bottom: 20px;
            padding: 15px;
            background: #1a1a1a;
            border-radius: 8px;
            border: 1px solid #333;
        }
        .form-busqueda .form-row {
            grid-template-columns: 2fr 1fr;
        }
        .empleado-info {
            background: #1a1a1a;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #666;
        }
        .empleado-info strong {
            color: #fff;
        }
        .empleado-info .info-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 5px 20px;
            margin-top: 5px;
        }
        .empleado-info .info-row span {
            color: #aaa;
            font-size: 13px;
        }
        .btn-buscar {
            background: #2a3a4a;
            color: #8fcfdf;
            border-color: #3a5a6a;
            height: 38px;
            align-self: end;
        }
        .btn-buscar:hover {
            background: #3a4a5a;
            border-color: #4a6a7a;
        }
        .mensaje-flotante {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 9999;
            max-width: 400px;
            animation: slideIn 0.3s ease;
        }
        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }
        .form-group input[readonly] {
            background: #2a2a2a;
            color: #888;
            cursor: not-allowed;
        }
        .campo-actual {
            font-size: 12px;
            color: #666;
            margin-top: 2px;
        }
        .campo-actual span {
            color: #888;
        }
        .requerido::after {
            content: " *";
            color: #df8f8f;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Modificar Empleado</h1>
        
        <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?> mensaje-flotante" id="mensajeFlotante">
                <?php echo $mensaje; ?>
                <button onclick="this.parentElement.remove()" style="float:right;background:none;border:none;color:inherit;cursor:pointer;font-size:18px;">×</button>
            </div>
            <script>
                setTimeout(function() {
                    var msg = document.getElementById('mensajeFlotante');
                    if (msg) msg.style.display = 'none';
                }, 5000);
            </script>
        <?php endif; ?>
        
        <!-- Formulario de búsqueda de empleado -->
        <div class="form-section">
            <h2>🔍 Buscar Empleado para Modificar</h2>
            <form method="GET" action="modificar.php" class="form-crud">
                <div class="form-row form-busqueda">
                    <div class="form-group">
                        <label for="buscar_id">ID del empleado a modificar</label>
                        <input type="text" id="buscar_id" name="id" 
                               placeholder="Ej: 31.840.269" 
                               value="<?php echo isset($_GET['id']) ? htmlspecialchars($_GET['id']) : ''; ?>">
                    </div>
                    <div class="form-group" style="justify-content: flex-end;">
                        <button type="submit" class="btn btn-buscar">🔍 Buscar</button>
                    </div>
                </div>
            </form>
        </div>
        
        <?php if ($empleado_seleccionado): ?>
            <!-- Mostrar información del empleado seleccionado -->
            <div class="empleado-info">
                <strong> Empleado seleccionado:</strong>
                <div class="info-row">
                    <span><strong>ID:</strong> <?php echo htmlspecialchars($empleado_seleccionado['nDIEmp']); ?></span>
                    <span><strong>Nombre:</strong> <?php echo htmlspecialchars($empleado_seleccionado['nomEmp']); ?></span>
                    <span><strong>Cargo:</strong> <?php echo htmlspecialchars($empleado_seleccionado['cargoE']); ?></span>
                    <span><strong>Departamento:</strong> <?php echo htmlspecialchars($empleado_seleccionado['codDepto']); ?></span>
                </div>
            </div>
            
            <!-- Formulario de modificación -->
            <div class="form-section">
                <h2> Datos del Empleado</h2>
                <form method="POST" action="modificar.php" class="form-crud" id="formModificar">
                    <input type="hidden" name="accion" value="modificar">
                    <input type="hidden" name="ndiemp_original" 
                           value="<?php echo htmlspecialchars($empleado_seleccionado['nDIEmp']); ?>">
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="ndiemp" class="requerido">Número de Identificación</label>
                            <input type="text" id="ndiemp" name="ndiemp" 
                                   value="<?php echo htmlspecialchars($empleado_seleccionado['nDIEmp']); ?>" required
                                   pattern="[0-9.]+" title="Solo números y puntos">
                            <div class="campo-actual">Actual: <span><?php echo htmlspecialchars($empleado_seleccionado['nDIEmp']); ?></span></div>
                        </div>
                        <div class="form-group">
                            <label for="nomemp" class="requerido">Nombre Completo</label>
                            <input type="text" id="nomemp" name="nomemp" 
                                   value="<?php echo htmlspecialchars($empleado_seleccionado['nomEmp']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="sexemp" class="requerido">Sexo</label>
                            <select id="sexemp" name="sexemp" required>
                                <option value="">Seleccione...</option>
                                <option value="M" <?php echo $empleado_seleccionado['sexEmp'] == 'M' ? 'selected' : ''; ?>>Masculino</option>
                                <option value="F" <?php echo $empleado_seleccionado['sexEmp'] == 'F' ? 'selected' : ''; ?>>Femenino</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="fecnac" class="requerido">Fecha Nacimiento</label>
                            <input type="date" id="fecnac" name="fecnac" 
                                   value="<?php echo htmlspecialchars($empleado_seleccionado['fecNac']); ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="fecincorporacion" class="requerido">Fecha Incorporación</label>
                            <input type="date" id="fecincorporacion" name="fecincorporacion" 
                                   value="<?php echo htmlspecialchars($empleado_seleccionado['fecIncorporacion']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="salemp" class="requerido">Salario</label>
                            <input type="number" step="0.01" id="salemp" name="salemp" 
                                   value="<?php echo htmlspecialchars($empleado_seleccionado['salEmp']); ?>" required min="0">
                        </div>
                        <div class="form-group">
                            <label for="comisione">Comisión</label>
                            <input type="number" step="0.01" id="comisione" name="comisione" 
                                   value="<?php echo htmlspecialchars($empleado_seleccionado['comisionE']); ?>" min="0">
                        </div>
                        <div class="form-group">
                            <label for="cargoe" class="requerido">Cargo</label>
                            <input type="text" id="cargoe" name="cargoe" 
                                   value="<?php echo htmlspecialchars($empleado_seleccionado['cargoE']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="jefeid">Jefe</label>
                            <select id="jefeid" name="jefeid">
                                <option value="">Sin Jefe</option>
                                <?php foreach ($jefes as $jefe): ?>
                                    <?php if ($jefe['nDIEmp'] != $empleado_seleccionado['nDIEmp']): ?>
                                        <option value="<?php echo htmlspecialchars($jefe['nDIEmp']); ?>" 
                                            <?php echo $empleado_seleccionado['jefeID'] == $jefe['nDIEmp'] ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($jefe['nomEmp'] . ' (' . $jefe['cargoE'] . ')'); ?>
                                        </option>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="coddepto" class="requerido">Departamento</label>
                            <select id="coddepto" name="coddepto" required>
                                <option value="">Seleccione...</option>
                                <?php foreach ($departamentos as $depto): ?>
                                    <option value="<?php echo htmlspecialchars($depto['codDepto']); ?>" 
                                        <?php echo $empleado_seleccionado['codDepto'] == $depto['codDepto'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($depto['nombreDpto'] . ' - ' . $depto['ciudad']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>&nbsp;</label>
                            <div style="display:flex;gap:10px;align-items:center;height:38px;">
                                <button type="submit" class="btn btn-warning" style="flex:1;">
                                     Actualizar Empleado
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        <?php elseif (isset($_GET['id']) && !empty($_GET['id'])): ?>
            <!-- Ya se muestra el mensaje de error arriba -->
        <?php else: ?>
            <div class="mensaje" style="background:#1a2a1a;color:#8fdfb0;border:1px solid #2a5a3a;padding:20px;border-radius:8px;text-align:center;">
                <p style="font-size:18px;">🔍 Ingresa el ID del empleado que deseas modificar</p>
                <p style="color:#888;font-size:14px;">Ejemplo: 31.840.269</p>
            </div>
        <?php endif; ?>
        
        <!-- Botones de navegación -->
        <div style="margin-top:20px;display:flex;gap:10px;flex-wrap:wrap;">
            <button onclick="window.close()" class="btn btn-secondary">✕ Cerrar</button>
            <button onclick="window.location.href='modificar.php'" class="btn btn-primary"> Limpiar búsqueda</button>
        </div>
    </div>

    <script>
        // Prevenir el envío duplicado del formulario
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('formModificar');
            if (form) {
                let submitted = false;
                form.addEventListener('submit', function(e) {
                    if (submitted) {
                        e.preventDefault();
                        return false;
                    }
                    submitted = true;
                    // Mostrar un mensaje de carga
                    const btn = this.querySelector('button[type="submit"]');
                    if (btn) {
                        btn.textContent = '⏳ Actualizando...';
                        btn.disabled = true;
                    }
                });
            }
        });
        
        // Auto-cerrar después de modificación exitosa
        <?php if ($tipo_mensaje == 'exito' && $_SERVER['REQUEST_METHOD'] == 'POST'): ?>
        setTimeout(function() {
            window.close();
        }, 3000);
        <?php endif; ?>
    </script>
</body>
</html>
<?php $conn->close(); ?>