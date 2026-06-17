<?php include 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-user-plus me-2"></i>
        Nuevo Cliente
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="/clientes" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Volver a Clientes
        </a>
    </div>
</div>

<?php if (isset($success)): ?>
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        <i class="fas fa-check-circle me-2"></i>
        <?php echo htmlspecialchars($success); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

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
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefono" class="form-label">Teléfono *</label>
                            <input type="tel" class="form-control" id="telefono" name="telefono" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="estado" class="form-label">Estado *</label>
                            <select class="form-select" id="estado" name="estado" required>
                                <option value="activo">Activo</option>
                                <option value="pendiente">Pendiente</option>
                                <option value="suspendido">Suspendido</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="direccion" class="form-label">Dirección *</label>
                        <textarea class="form-control" id="direccion" name="direccion" rows="2" required></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="tipo_conexion" class="form-label">Tipo de Conexión *</label>
                            <select class="form-select" id="tipo_conexion" name="tipo_conexion" required>
                                <option value="">Seleccionar...</option>
                                <option value="fibra_optica">Fibra Óptica</option>
                                <option value="inalambrica">Inalámbrica</option>
                                <option value="cable_coaxial">Cableado (utp)</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="plan_mensual" class="form-label">Plan Mensual ($) *</label>
                            <input type="number" class="form-control" id="plan_mensual" name="plan_mensual" step="0.01" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="megas_contratados" class="form-label">Megas Contratados *</label>
                            <input type="number" class="form-control" id="megas_contratados" name="megas_contratados" min="1" placeholder="Ej. 100" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="fecha_contratacion" class="form-label">Fecha de Contratación *</label>
                        <input type="date" class="form-control" id="fecha_contratacion" name="fecha_contratacion" value="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="dia_corte" class="form-label">Día de Corte *</label>
                        <input type="number" class="form-control" id="dia_corte" name="dia_corte" min="1" max="31" value="5" required>
                        <div class="form-text">Día del mes en que vence el servicio (1-31). Si el mes no tiene ese día, se usa el último día del mes.</div>
                    </div>
                    
                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?php echo url('clientes'); ?>" class="btn btn-secondary me-md-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary" id="btnGuardar">
                            <i class="fas fa-save me-1"></i>
                            <span class="btn-text">Guardar Cliente</span>
                            <span class="btn-loading" style="display: none;">
                                <i class="fas fa-spinner fa-spin me-1"></i>
                                Guardando...
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">Información</h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="fas fa-info-circle me-2"></i>
                    <strong>Campos obligatorios</strong><br>
                    Los campos marcados con (*) son obligatorios para crear el cliente.
                </div>
                
                <div class="alert alert-warning">
                    <i class="fas fa-exclamation-triangle me-2"></i>
                    <strong>Tipos de Conexión</strong><br>
                    • Fibra Óptica: Mayor velocidad<br>
                    • Inalámbrica: Fácil instalación<br>
                    • Cableado (utp): Estable y confiable
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    const btnGuardar = document.getElementById('btnGuardar');
    const btnText = btnGuardar.querySelector('.btn-text');
    const btnLoading = btnGuardar.querySelector('.btn-loading');
    
    form.addEventListener('submit', function(e) {
        // Cambiar el estado del botón
        btnGuardar.disabled = true;
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
                btnGuardar.disabled = false;
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
            }, 100);
        }
    });
});
</script>

<?php include 'views/layouts/footer.php'; ?>
