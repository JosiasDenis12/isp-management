<?php include 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-edit me-2"></i>
        Editar Cliente: <?php echo htmlspecialchars($cliente['nombre']); ?>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?php echo url('clientes/' . $cliente['id']); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Volver al Cliente
            </a>
            <a href="<?php echo url('clientes'); ?>" class="btn btn-outline-info">
                <i class="fas fa-list me-1"></i>
                Lista de Clientes
            </a>
        </div>
    </div>
</div>

<?php if (isset($error)): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<div class="row">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">Información del Cliente</h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombre" class="form-label">Nombre Completo *</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" 
                                   value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label">Teléfono *</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" 
                                   value="<?php echo htmlspecialchars($cliente['telefono']); ?>" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo htmlspecialchars($cliente['email']); ?>">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="estado" class="form-label">Estado *</label>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="activo" <?php echo $cliente['estado'] === 'activo' ? 'selected' : ''; ?>>Activo</option>
                                <option value="pendiente" <?php echo $cliente['estado'] === 'pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                                <option value="suspendido" <?php echo $cliente['estado'] === 'suspendido' ? 'selected' : ''; ?>>Suspendido</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección *</label>
                        <textarea class="form-control" id="direccion" name="direccion" rows="2" required><?php echo htmlspecialchars($cliente['direccion']); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo_conexion" class="form-label">Tipo de Conexión *</label>
                            <select class="form-select" id="tipo_conexion" name="tipo_conexion" required>
                                <option value="">Seleccionar...</option>
                                <option value="fibra_optica" <?php echo $cliente['tipo_conexion'] === 'fibra_optica' ? 'selected' : ''; ?>>Fibra Óptica</option>
                                <option value="inalambrica" <?php echo $cliente['tipo_conexion'] === 'inalambrica' ? 'selected' : ''; ?>>Inalámbrica</option>
                                <option value="cableado_utp" <?php echo $cliente['tipo_conexion'] === 'cableado_utp' ? 'selected' : ''; ?>>Cableado (UTP)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="plan_mensual" class="form-label">Plan Mensual ($) *</label>
                            <input type="number" class="form-control" id="plan_mensual" name="plan_mensual" 
                                   step="0.01" value="<?php echo $cliente['plan_mensual']; ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="megas_contratados" class="form-label">Megas Contratados *</label>
                            <input type="number" class="form-control" id="megas_contratados" name="megas_contratados" min="1" value="<?php echo htmlspecialchars($cliente['megas_contratados'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="fecha_contratacion" class="form-label">Fecha de Contratación *</label>
                        <input type="date" class="form-control" id="fecha_contratacion" name="fecha_contratacion" 
                               value="<?php echo $cliente['fecha_contratacion']; ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="dia_corte" class="form-label">Día de Corte *</label>
                        <input type="number" class="form-control" id="dia_corte" name="dia_corte" min="0" max="31"
                               value="<?php echo htmlspecialchars($cliente['dia_corte'] ?? 5); ?>" required>
                        <div class="form-text">Día del mes en que vence el servicio (1-31). Si el mes no tiene ese día, se usa el último día del mes.</div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?php echo url('clientes/' . $cliente['id']); ?>" class="btn btn-secondary me-md-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary" id="btnActualizar">
                            <i class="fas fa-save me-1"></i>
                            <span class="btn-text">Actualizar Cliente</span>
                            <span class="btn-loading" style="display: none;">
                                <i class="fas fa-spinner fa-spin me-1"></i>
                                Actualizando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <!-- Información del Cliente Actual -->
        <div class="card mb-4">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Información Actual
                </h6>
            </div>
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>ID:</span>
                    <span class="fw-bold">#<?php echo $cliente['id']; ?></span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Estado Actual:</span>
                    <span class="badge bg-<?php 
                        echo $cliente['estado'] === 'activo' ? 'success' : 
                             ($cliente['estado'] === 'suspendido' ? 'danger' : 'warning'); 
                    ?>">
                        <?php echo ucfirst($cliente['estado']); ?>
                    </span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Plan Actual:</span>
                    <span class="fw-bold text-success">$<?php echo number_format($cliente['plan_mensual'], 2); ?></span>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Megas Actuales:</span>
                    <span class="fw-bold text-info">
                        <?php echo !empty($cliente['megas_contratados']) ? htmlspecialchars($cliente['megas_contratados']) . ' Mbps' : 'No especificado'; ?>
                    </span>
                </div>
                
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Cliente desde:</span>
                    <span class="text-muted"><?php echo date('d/m/Y', strtotime($cliente['fecha_contratacion'])); ?></span>
                </div>
                
                <hr>
                
                <div class="d-flex justify-content-between align-items-center">
                    <span>Última actualización:</span>
                    <span class="text-muted small">
                        <?php echo date('d/m/Y H:i', strtotime($cliente['updated_at'])); ?>
                    </span>
                </div>
            </div>
        </div>
        
        <!-- Guía de Campos -->
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-question-circle me-2"></i>
                    Guía de Edición
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Campos obligatorios</strong><br>
                    Los campos marcados con (*) son obligatorios.
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Estados del Cliente</strong><br>
                    • <strong>Activo:</strong> Cliente con servicio normal<br>
                    • <strong>Suspendido:</strong> Servicio temporalmente deshabilitado<br>
                    • <strong>Pendiente:</strong> En proceso de activación
                </div>
                
                <div class="alert alert-secondary">
                    <i class="fas fa-clock me-2"></i>
                    <strong>Tip:</strong> Los cambios se guardarán automáticamente al hacer clic en "Actualizar Cliente".
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const btnActualizar = document.getElementById('btnActualizar');
    const btnText = btnActualizar.querySelector('.btn-text');
    const btnLoading = btnActualizar.querySelector('.btn-loading');
    
    form.addEventListener('submit', function(e) {
        // Cambiar el estado del botón
        btnActualizar.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline';
        
        // Si hay algún error de validación básica, restaurar el botón
        const requiredFields = form.querySelectorAll('[required]');
        let hasEmptyFields = false;
        
        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                hasEmptyFields = true;
            }
        });
        
        if (hasEmptyFields) {
            setTimeout(() => {
                btnActualizar.disabled = false;
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
            }, 100);
        }
    });
    
    // Detectar cambios en el formulario
    const originalValues = {};
    const inputs = form.querySelectorAll('input, select, textarea');
    
    inputs.forEach(input => {
        originalValues[input.name] = input.value;
        
        input.addEventListener('change', function() {
            let hasChanges = false;
            inputs.forEach(inp => {
                if (originalValues[inp.name] !== inp.value) {
                    hasChanges = true;
                }
            });
            
            if (hasChanges) {
                btnActualizar.classList.remove('btn-primary');
                btnActualizar.classList.add('btn-warning');
                btnText.innerHTML = '<i class="fas fa-save me-1"></i>Guardar Cambios';
            } else {
                btnActualizar.classList.remove('btn-warning');
                btnActualizar.classList.add('btn-primary');
                btnText.innerHTML = '<i class="fas fa-save me-1"></i>Actualizar Cliente';
            }
        });
    });
});
</script>

<?php include 'views/layouts/footer.php'; ?>
