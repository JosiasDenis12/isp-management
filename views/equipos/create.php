<?php include 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-plus me-2"></i>
        Registrar Nuevo Equipo
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <a href="<?php echo url('equipos'); ?>" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i>
            Volver a Equipos
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
                <h5 class="mb-0">
                    <i class="fas fa-wifi me-2"></i>
                    Información del Equipo
                </h5>
            </div>
            <div class="card-body">
                <form method="POST">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cliente_id" class="form-label">Cliente *</label>
                            <select class="form-select" id="cliente_id" name="cliente_id" required>
                                <option value="">Seleccionar cliente...</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo $cliente['id']; ?>" <?php echo (isset($_POST['cliente_id']) && $_POST['cliente_id'] == $cliente['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cliente['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="tipo_equipo" class="form-label">Tipo de Equipo *</label>
                            <select class="form-select" id="tipo_equipo" name="tipo_equipo" required>
                                <option value="">Seleccionar tipo...</option>
                                <option value="router" <?php echo (isset($_POST['tipo_equipo']) && $_POST['tipo_equipo'] == 'router') ? 'selected' : ''; ?>>Router</option>
                                <option value="modem" <?php echo (isset($_POST['tipo_equipo']) && $_POST['tipo_equipo'] == 'modem') ? 'selected' : ''; ?>>Módem</option>
                                <option value="switch" <?php echo (isset($_POST['tipo_equipo']) && $_POST['tipo_equipo'] == 'switch') ? 'selected' : ''; ?>>Switch</option>
                                <option value="access_point" <?php echo (isset($_POST['tipo_equipo']) && $_POST['tipo_equipo'] == 'access_point') ? 'selected' : ''; ?>>Access Point</option>
                                <option value="antena" <?php echo (isset($_POST['tipo_equipo']) && $_POST['tipo_equipo'] == 'antena') ? 'selected' : ''; ?>>Antena</option>
                                <option value="otro" <?php echo (isset($_POST['tipo_equipo']) && $_POST['tipo_equipo'] == 'otro') ? 'selected' : ''; ?>>Otro</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="marca" class="form-label">Marca *</label>
                            <input type="text" class="form-control" id="marca" name="marca" 
                                   value="<?php echo isset($_POST['marca']) ? htmlspecialchars($_POST['marca']) : ''; ?>" 
                                   placeholder="Ej: TP-Link, Ubiquiti, Mikrotik" required>
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="modelo" class="form-label">Modelo *</label>
                            <input type="text" class="form-control" id="modelo" name="modelo" 
                                   value="<?php echo isset($_POST['modelo']) ? htmlspecialchars($_POST['modelo']) : ''; ?>" 
                                   placeholder="Ej: AC1200, NanoStation M5" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="numero_serie" class="form-label">Número de Serie</label>
                            <input type="text" class="form-control" id="numero_serie" name="numero_serie" 
                                   value="<?php echo isset($_POST['numero_serie']) ? htmlspecialchars($_POST['numero_serie']) : ''; ?>" 
                                   placeholder="Número de serie del equipo">
                        </div>
                        
                        <div class="col-md-6 mb-3">
                            <label for="estado_tecnico" class="form-label">Estado Técnico *</label>
                            <select class="form-select" id="estado_tecnico" name="estado_tecnico" required>
                                <option value="">Seleccionar estado...</option>
                                <option value="operativo" <?php echo (isset($_POST['estado_tecnico']) && $_POST['estado_tecnico'] == 'operativo') ? 'selected' : ''; ?>>Operativo</option>
                                <option value="necesita_revision" <?php echo (isset($_POST['estado_tecnico']) && $_POST['estado_tecnico'] == 'necesita_revision') ? 'selected' : ''; ?>>Necesita Revisión</option>
                                <option value="fuera_de_servicio" <?php echo (isset($_POST['estado_tecnico']) && $_POST['estado_tecnico'] == 'fuera_de_servicio') ? 'selected' : ''; ?>>Fuera de Servicio</option>
                                <option value="en_mantenimiento" <?php echo (isset($_POST['estado_tecnico']) && $_POST['estado_tecnico'] == 'en_mantenimiento') ? 'selected' : ''; ?>>En Mantenimiento</option>
                            </select>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="fecha_instalacion" class="form-label">Fecha de Instalación</label>
                            <input type="date" class="form-control" id="fecha_instalacion" name="fecha_instalacion" 
                                   value="<?php echo isset($_POST['fecha_instalacion']) ? htmlspecialchars($_POST['fecha_instalacion']) : ''; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="observaciones_tecnico" class="form-label">Observaciones Técnicas</label>
                        <textarea class="form-control" id="observaciones_tecnico" name="observaciones_tecnico" rows="4" 
                                  placeholder="Observaciones adicionales sobre el equipo, configuración, ubicación, etc."><?php echo isset($_POST['observaciones_tecnico']) ? htmlspecialchars($_POST['observaciones_tecnico']) : ''; ?></textarea>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Registrar Equipo
                        </button>
                        <a href="<?php echo url('equipos'); ?>" class="btn btn-secondary">
                            <i class="fas fa-times me-1"></i>
                            Cancelar
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Información Adicional
                </h6>
            </div>
            <div class="card-body">
                <h6>Tipos de Equipos:</h6>
                <ul class="list-unstyled">
                    <li><i class="fas fa-wifi text-primary me-2"></i><strong>Router:</strong> Dispositivo principal de conexión</li>
                    <li><i class="fas fa-ethernet text-info me-2"></i><strong>Módem:</strong> Equipo de conexión a internet</li>
                    <li><i class="fas fa-network-wired text-success me-2"></i><strong>Switch:</strong> Distribuidor de red</li>
                    <li><i class="fas fa-broadcast-tower text-warning me-2"></i><strong>Access Point:</strong> Punto de acceso WiFi</li>
                    <li><i class="fas fa-satellite-dish text-danger me-2"></i><strong>Antena:</strong> Equipo de transmisión</li>
                </ul>
                
                <hr>
                
                <h6>Estados Técnicos:</h6>
                <ul class="list-unstyled">
                    <li><span class="badge bg-success me-2">Operativo</span> Funcionando correctamente</li>
                    <li><span class="badge bg-warning me-2">Necesita Revisión</span> Requiere mantenimiento</li>
                    <li><span class="badge bg-danger me-2">Fuera de Servicio</span> No funcional</li>
                    <li><span class="badge bg-info me-2">En Mantenimiento</span> En proceso de reparación</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<script>
// Validación adicional del formulario
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    
    form.addEventListener('submit', function(e) {
        const marca = document.getElementById('marca').value.trim();
        const modelo = document.getElementById('modelo').value.trim();
        
        if (marca.length < 2) {
            e.preventDefault();
            alert('La marca debe tener al menos 2 caracteres');
            return;
        }
        
        if (modelo.length < 2) {
            e.preventDefault();
            alert('El modelo debe tener al menos 2 caracteres');
            return;
        }
    });
});
</script>

<?php include 'views/layouts/footer.php'; ?>
