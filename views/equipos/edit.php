<?php include 'views/layouts/header.php'; ?>

<?php
$equipo = $equipo ?? [];

function equipoEditNormalizeTipo($tipo) {
    $tipo = trim((string)$tipo);
    $lower = function_exists('mb_strtolower') ? mb_strtolower($tipo, 'UTF-8') : strtolower($tipo);
    $compact = preg_replace('/[^a-z0-9_]+/', '', $lower);

    if ($compact === '') return '';
    if (strpos($compact, 'antena') !== false) return 'antena';
    if (strpos($compact, 'modem') !== false || strpos($compact, 'mdem') !== false || strpos($compact, 'dem') !== false) return 'modem';
    if (strpos($compact, 'router') !== false) return 'router';
    if (strpos($compact, 'switch') !== false) return 'switch';
    if (strpos($compact, 'access') !== false || strpos($compact, 'ap') === 0) return 'access_point';

    $allowedCompact = ['router', 'modem', 'switch', 'access_point', 'antena', 'otro'];
    if (in_array($compact, $allowedCompact, true)) return $compact;

    if ($lower === '') return '';
    if (strpos($lower, 'antena') !== false) return 'antena';
    if (strpos($lower, 'modem') !== false || strpos($lower, 'módem') !== false || strpos($lower, 'm¢dem') !== false || strpos($lower, 'dem') !== false) return 'modem';
    if (strpos($lower, 'router') !== false) return 'router';
    if (strpos($lower, 'switch') !== false) return 'switch';
    if (strpos($lower, 'access') !== false || strpos($lower, 'ap') === 0) return 'access_point';

    $allowed = ['router', 'modem', 'switch', 'access_point', 'antena', 'otro'];
    return in_array($lower, $allowed, true) ? $lower : 'otro';
}

function equipoEditValue($field, $default = '') {
    global $equipo;
    return htmlspecialchars($equipo[$field] ?? $default);
}

function equipoEditSelected($field, $value, $default = '') {
    global $equipo;
    $current = $equipo[$field] ?? $default;
    if ($field === 'tipo_equipo') {
        $current = equipoEditNormalizeTipo($current);
    }
    return ((string)$current === (string)$value) ? 'selected' : '';
}

$tipoEquipoActual = equipoEditNormalizeTipo($equipo['tipo_equipo'] ?? '');
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">
        <i class="fas fa-edit me-2"></i>
        Editar Equipo
        <small class="text-muted">#<?php echo (int)($equipo['id'] ?? 0); ?></small>
    </h1>
    <div class="btn-toolbar mb-2 mb-md-0">
        <div class="btn-group me-2">
            <a href="<?php echo url('equipos/' . (int)$equipo['id']); ?>" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i>
                Volver al Equipo
            </a>
            <a href="<?php echo url('equipos'); ?>" class="btn btn-outline-info">
                <i class="fas fa-list me-1"></i>
                Lista de Equipos
            </a>
        </div>
    </div>
</div>

<?php if (isset($error) && $error): ?>
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="fas fa-exclamation-triangle me-2"></i>
        <?php echo htmlspecialchars($error); ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
<?php endif; ?>

<style>
    .device-panel {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 8px;
        background: #fff;
    }
    .device-panel .device-panel-header {
        border-bottom: 1px solid rgba(15, 23, 42, 0.08);
        padding: .85rem 1rem;
        font-weight: 700;
    }
    .device-panel .device-panel-body {
        padding: 1rem;
    }
</style>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-router me-2"></i>
                    Información del Equipo
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" id="equipoEditForm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cliente_id" class="form-label">Cliente asociado *</label>
                            <select class="form-select" id="cliente_id" name="cliente_id" required>
                                <option value="">Seleccionar cliente...</option>
                                <?php foreach (($clientes ?? []) as $cliente): ?>
                                    <option value="<?php echo (int)$cliente['id']; ?>" <?php echo ((int)($equipo['cliente_id'] ?? 0) === (int)$cliente['id']) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($cliente['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="tipo_equipo" class="form-label">Tipo de Equipo *</label>
                            <select class="form-select" id="tipo_equipo" name="tipo_equipo" required>
                                <option value="">Seleccionar tipo...</option>
                                <option value="router" <?php echo equipoEditSelected('tipo_equipo', 'router'); ?>>Router</option>
                                <option value="modem" <?php echo equipoEditSelected('tipo_equipo', 'modem'); ?>>Módem</option>
                                <option value="switch" <?php echo equipoEditSelected('tipo_equipo', 'switch'); ?>>Switch</option>
                                <option value="access_point" <?php echo equipoEditSelected('tipo_equipo', 'access_point'); ?>>Access Point</option>
                                <option value="antena" <?php echo equipoEditSelected('tipo_equipo', 'antena'); ?>>Antena</option>
                                <option value="otro" <?php echo equipoEditSelected('tipo_equipo', 'otro'); ?>>Otro</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="marca" class="form-label">Marca *</label>
                            <input type="text" class="form-control" id="marca" name="marca" value="<?php echo equipoEditValue('marca'); ?>" required>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="modelo" class="form-label">Modelo *</label>
                            <input type="text" class="form-control" id="modelo" name="modelo" value="<?php echo equipoEditValue('modelo'); ?>" required>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="numero_serie" class="form-label">Número de Serie</label>
                            <input type="text" class="form-control" id="numero_serie" name="numero_serie" value="<?php echo equipoEditValue('numero_serie'); ?>">
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="estado_tecnico" class="form-label">Estado *</label>
                            <select class="form-select" id="estado_tecnico" name="estado_tecnico" required>
                                <option value="">Seleccionar estado...</option>
                                <option value="operativo" <?php echo equipoEditSelected('estado_tecnico', 'operativo'); ?>>Operativo</option>
                                <option value="necesita_revision" <?php echo equipoEditSelected('estado_tecnico', 'necesita_revision'); ?>>Necesita Revisión</option>
                                <option value="dañado" <?php echo equipoEditSelected('estado_tecnico', 'dañado'); ?>>Dañado</option>
                                <option value="fuera_de_servicio" <?php echo equipoEditSelected('estado_tecnico', 'fuera_de_servicio'); ?>>Fuera de Servicio</option>
                                <option value="en_mantenimiento" <?php echo equipoEditSelected('estado_tecnico', 'en_mantenimiento'); ?>>En Mantenimiento</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="fecha_instalacion" class="form-label">Fecha de Instalación</label>
                        <input type="date" class="form-control" id="fecha_instalacion" name="fecha_instalacion" value="<?php echo equipoEditValue('fecha_instalacion'); ?>">
                    </div>

                    <div class="device-panel mb-3" id="networkFields">
                        <div class="device-panel-header">
                            <i class="fas fa-network-wired me-2"></i>
                            Datos específicos del dispositivo
                        </div>
                        <div class="device-panel-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="mac_address" class="form-label">MAC Address *</label>
                                    <input type="text" class="form-control" id="mac_address" name="mac_address" value="<?php echo equipoEditValue('mac_address'); ?>" placeholder="AA:BB:CC:DD:EE:FF">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="direccion_ip" class="form-label">Dirección IP *</label>
                                    <input type="text" class="form-control" id="direccion_ip" name="direccion_ip" value="<?php echo equipoEditValue('direccion_ip'); ?>" placeholder="192.168.1.1">
                                </div>
                            </div>

                            <div class="row modem-only">
                                <div class="col-md-6 mb-3">
                                    <label for="ssid" class="form-label">SSID *</label>
                                    <input type="text" class="form-control" id="ssid" name="ssid" value="<?php echo equipoEditValue('ssid'); ?>" placeholder="Nombre de la red WiFi">
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="usuario_acceso" class="form-label">Usuario de acceso *</label>
                                    <input type="text" class="form-control" id="usuario_acceso" name="usuario_acceso" value="<?php echo equipoEditValue('usuario_acceso'); ?>" placeholder="admin">
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="password_acceso" class="form-label">Contraseña *</label>
                                    <input type="text" class="form-control" id="password_acceso" name="password_acceso" value="<?php echo equipoEditValue('password_acceso'); ?>" placeholder="Password del dispositivo">
                                </div>
                                <div class="col-md-6 mb-3 modem-only">
                                    <label class="form-label d-block">Estado de acceso</label>
                                    <div class="form-check form-switch mt-2">
                                        <input class="form-check-input" type="checkbox" role="switch" id="acceso_habilitado" name="acceso_habilitado" value="1" <?php echo !empty($equipo['acceso_habilitado']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="acceso_habilitado">Activado</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="observaciones_tecnico" class="form-label">Observaciones</label>
                        <textarea class="form-control" id="observaciones_tecnico" name="observaciones_tecnico" rows="4"><?php echo htmlspecialchars($equipo['observaciones_tecnico'] ?? ''); ?></textarea>
                    </div>

                    <div class="d-grid gap-2 d-md-flex justify-content-md-end">
                        <a href="<?php echo url('equipos/' . (int)$equipo['id']); ?>" class="btn btn-secondary me-md-2">Cancelar</a>
                        <button type="submit" class="btn btn-primary" id="btnActualizar">
                            <i class="fas fa-save me-1"></i>
                            <span class="btn-text">Actualizar Equipo</span>
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

    <div class="col-lg-4">
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
                    <span class="fw-bold">#<?php echo (int)($equipo['id'] ?? 0); ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Tipo:</span>
                    <span class="fw-bold"><?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $tipoEquipoActual))); ?></span>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Cliente:</span>
                    <span class="fw-bold text-end"><?php echo htmlspecialchars($equipo['cliente_nombre'] ?? ''); ?></span>
                </div>
                <?php if (!empty($equipo['instalacion_id'])): ?>
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span>Instalación:</span>
                    <span class="fw-bold">#<?php echo (int)$equipo['instalacion_id']; ?></span>
                </div>
                <?php endif; ?>
                <hr>
                <div class="small text-muted">
                    Los campos de red se activan automáticamente para Antena y Módem. En otros tipos se limpian al guardar.
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-question-circle me-2"></i>
                    Validaciones
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-info mb-3">
                    <strong>Antena:</strong><br>
                    MAC Address, Dirección IP y Contraseña son obligatorios.
                </div>
                <div class="alert alert-secondary mb-0">
                    <strong>Módem:</strong><br>
                    También requiere SSID, Usuario y Estado de acceso.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('equipoEditForm');
    const tipoEquipo = document.getElementById('tipo_equipo');
    const tipoInicial = <?php echo json_encode($tipoEquipoActual); ?>;
    const networkFields = document.getElementById('networkFields');
    const modemOnly = Array.from(document.querySelectorAll('.modem-only'));
    const btnActualizar = document.getElementById('btnActualizar');
    const btnText = btnActualizar.querySelector('.btn-text');
    const btnLoading = btnActualizar.querySelector('.btn-loading');

    const networkRequired = ['mac_address', 'direccion_ip', 'password_acceso'];
    const modemRequired = ['ssid', 'usuario_acceso'];

    if (tipoEquipo && tipoInicial && !tipoEquipo.value) {
        tipoEquipo.value = tipoInicial;
    }

    function setRequired(ids, required) {
        ids.forEach((id) => {
            const el = document.getElementById(id);
            if (el) el.required = required;
        });
    }

    function updateDeviceFields() {
        const type = tipoEquipo.value;
        const isNetworkDevice = type === 'antena' || type === 'modem';
        const isModem = type === 'modem';

        networkFields.style.display = isNetworkDevice ? '' : 'none';
        modemOnly.forEach((el) => { el.style.display = isModem ? '' : 'none'; });

        setRequired(networkRequired, isNetworkDevice);
        setRequired(modemRequired, isModem);
    }

    tipoEquipo.addEventListener('change', updateDeviceFields);

    form.addEventListener('submit', function() {
        btnActualizar.disabled = true;
        btnText.style.display = 'none';
        btnLoading.style.display = 'inline';

        setTimeout(() => {
            if (!form.checkValidity()) {
                btnActualizar.disabled = false;
                btnText.style.display = 'inline';
                btnLoading.style.display = 'none';
            }
        }, 100);
    });

    const originalValues = {};
    const inputs = form.querySelectorAll('input, select, textarea');
    inputs.forEach((input) => {
        originalValues[input.name] = input.type === 'checkbox' ? input.checked : input.value;
        input.addEventListener('change', function() {
            let hasChanges = false;
            inputs.forEach((inp) => {
                const value = inp.type === 'checkbox' ? inp.checked : inp.value;
                if (originalValues[inp.name] !== value) hasChanges = true;
            });
            btnActualizar.classList.toggle('btn-warning', hasChanges);
            btnActualizar.classList.toggle('btn-primary', !hasChanges);
            btnText.innerHTML = hasChanges ? '<i class="fas fa-save me-1"></i>Guardar Cambios' : '<i class="fas fa-save me-1"></i>Actualizar Equipo';
        });
    });

    updateDeviceFields();
});
</script>

<?php include 'views/layouts/footer.php'; ?>
