<?php include 'views/layouts/header.php'; ?>

<?php
$post = $_POST ?? [];
$modo = $post['modo_registro'] ?? 'individual';
$prefillClienteId = (int)($_GET['cliente_id'] ?? 0);
$prefillFecha = trim((string)($_GET['fecha_instalacion'] ?? date('Y-m-d')));

function oldEquipo($name, $default = '') {
    return htmlspecialchars($_POST[$name] ?? $default);
}

function selectedEquipo($name, $value, $default = '') {
    $current = $_POST[$name] ?? $default;
    return ((string)$current === (string)$value) ? 'selected' : '';
}

function checkedEquipo($name, $value = '1') {
    return isset($_POST[$name]) && (string)$_POST[$name] === (string)$value ? 'checked' : '';
}
?>

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

<?php if (isset($error)): ?>
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
    .mode-card {
        border: 1px solid rgba(15, 23, 42, 0.08);
        border-radius: 8px;
        padding: .85rem 1rem;
        height: 100%;
        cursor: pointer;
    }
    .mode-card.is-active {
        border-color: rgba(13, 110, 253, 0.35);
        background: rgba(13, 110, 253, 0.04);
        box-shadow: 0 8px 20px rgba(13, 110, 253, 0.08);
    }
    .mode-card input {
        margin-top: .2rem;
    }
</style>

<div class="row">
    <div class="col-lg-8">
        <div class="card">
            <div class="card-header">
                <h5 class="mb-0">
                    <i class="fas fa-wifi me-2"></i>
                    Información del Equipo
                </h5>
            </div>
            <div class="card-body">
                <form method="POST" id="equipoForm">
                    <div class="mb-3">
                        <label class="form-label">Modo de registro *</label>
                        <div class="row g-2">
                            <div class="col-md-6">
                                <label class="mode-card d-flex gap-3">
                                    <input type="radio" name="modo_registro" value="individual" <?php echo $modo === 'individual' ? 'checked' : ''; ?>>
                                    <span>
                                        <span class="d-block fw-semibold">Equipo individual</span>
                                        <span class="small text-muted">Registra una antena, módem u otro equipo por separado.</span>
                                    </span>
                                </label>
                            </div>
                            <div class="col-md-6">
                                <label class="mode-card d-flex gap-3">
                                    <input type="radio" name="modo_registro" value="instalacion_completa" <?php echo $modo === 'instalacion_completa' ? 'checked' : ''; ?>>
                                    <span>
                                        <span class="d-block fw-semibold">Instalación completa</span>
                                        <span class="small text-muted">Registra antena y módem asociados al mismo cliente e instalación.</span>
                                    </span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="cliente_id" class="form-label">Cliente *</label>
                            <select class="form-select" id="cliente_id" name="cliente_id" required>
                                <option value="">Seleccionar cliente...</option>
                                <?php foreach ($clientes as $cliente): ?>
                                    <option value="<?php echo (int)$cliente['id']; ?>" <?php echo selectedEquipo('cliente_id', $cliente['id'], $prefillClienteId); ?>>
                                        <?php echo htmlspecialchars($cliente['nombre']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="col-md-6 mb-3">
                            <label for="fecha_instalacion" class="form-label">Fecha de Instalación *</label>
                            <input type="date" class="form-control" id="fecha_instalacion" name="fecha_instalacion" value="<?php echo oldEquipo('fecha_instalacion', $prefillFecha); ?>" required>
                        </div>
                    </div>

                    <div id="individualFields">
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="tipo_equipo" class="form-label">Tipo de Equipo *</label>
                                <select class="form-select" id="tipo_equipo" name="tipo_equipo">
                                    <option value="">Seleccionar tipo...</option>
                                    <option value="router" <?php echo selectedEquipo('tipo_equipo', 'router'); ?>>Router</option>
                                    <option value="modem" <?php echo selectedEquipo('tipo_equipo', 'modem'); ?>>Módem</option>
                                    <option value="switch" <?php echo selectedEquipo('tipo_equipo', 'switch'); ?>>Switch</option>
                                    <option value="access_point" <?php echo selectedEquipo('tipo_equipo', 'access_point'); ?>>Access Point</option>
                                    <option value="antena" <?php echo selectedEquipo('tipo_equipo', 'antena'); ?>>Antena</option>
                                    <option value="otro" <?php echo selectedEquipo('tipo_equipo', 'otro'); ?>>Otro</option>
                                </select>
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="estado_tecnico" class="form-label">Estado Técnico *</label>
                                <select class="form-select" id="estado_tecnico" name="estado_tecnico">
                                    <option value="">Seleccionar estado...</option>
                                    <option value="operativo" <?php echo selectedEquipo('estado_tecnico', 'operativo', 'operativo'); ?>>Operativo</option>
                                    <option value="necesita_revision" <?php echo selectedEquipo('estado_tecnico', 'necesita_revision'); ?>>Necesita Revisión</option>
                                    <option value="dañado" <?php echo selectedEquipo('estado_tecnico', 'dañado'); ?>>Dañado</option>
                                    <option value="fuera_de_servicio" <?php echo selectedEquipo('estado_tecnico', 'fuera_de_servicio'); ?>>Fuera de Servicio</option>
                                    <option value="en_mantenimiento" <?php echo selectedEquipo('estado_tecnico', 'en_mantenimiento'); ?>>En Mantenimiento</option>
                                </select>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="marca" class="form-label">Marca *</label>
                                <input type="text" class="form-control" id="marca" name="marca" value="<?php echo oldEquipo('marca'); ?>" placeholder="Ej: TP-Link, Ubiquiti, Mikrotik">
                            </div>

                            <div class="col-md-6 mb-3">
                                <label for="modelo" class="form-label">Modelo *</label>
                                <input type="text" class="form-control" id="modelo" name="modelo" value="<?php echo oldEquipo('modelo'); ?>" placeholder="Ej: AC1200, NanoStation M5">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="numero_serie" class="form-label">Número de Serie</label>
                            <input type="text" class="form-control" id="numero_serie" name="numero_serie" value="<?php echo oldEquipo('numero_serie'); ?>" placeholder="Número de serie del equipo">
                        </div>

                        <div class="device-panel mb-3" id="networkFields">
                            <div class="device-panel-header">
                                <i class="fas fa-network-wired me-2"></i>
                                Datos de acceso y red
                            </div>
                            <div class="device-panel-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="mac_address" class="form-label">MAC Address *</label>
                                        <input type="text" class="form-control" id="mac_address" name="mac_address" value="<?php echo oldEquipo('mac_address'); ?>" placeholder="AA:BB:CC:DD:EE:FF">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="direccion_ip" class="form-label">Dirección IP *</label>
                                        <input type="text" class="form-control" id="direccion_ip" name="direccion_ip" value="<?php echo oldEquipo('direccion_ip'); ?>" placeholder="192.168.1.1">
                                    </div>
                                </div>

                                <div class="row modem-only">
                                    <div class="col-md-6 mb-3">
                                        <label for="ssid" class="form-label">SSID *</label>
                                        <input type="text" class="form-control" id="ssid" name="ssid" value="<?php echo oldEquipo('ssid'); ?>" placeholder="Nombre de la red WiFi">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="usuario_acceso" class="form-label">Usuario de acceso *</label>
                                        <input type="text" class="form-control" id="usuario_acceso" name="usuario_acceso" value="<?php echo oldEquipo('usuario_acceso'); ?>" placeholder="admin">
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="password_acceso" class="form-label">Contraseña *</label>
                                        <input type="text" class="form-control" id="password_acceso" name="password_acceso" value="<?php echo oldEquipo('password_acceso'); ?>" placeholder="Password del dispositivo">
                                    </div>
                                    <div class="col-md-6 mb-3 modem-only">
                                        <label class="form-label d-block">Estado de acceso</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" role="switch" id="acceso_habilitado" name="acceso_habilitado" value="1" <?php echo checkedEquipo('acceso_habilitado'); ?>>
                                            <label class="form-check-label" for="acceso_habilitado">Activado</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div id="completeInstallationFields">
                        <div class="alert alert-info">
                            <i class="fas fa-circle-info me-2"></i>
                            Se creará una instalación y se registrarán dos equipos relacionados: antena y módem.
                        </div>

                        <div class="device-panel mb-3">
                            <div class="device-panel-header">
                                <i class="fas fa-satellite-dish me-2"></i>
                                Antena
                            </div>
                            <div class="device-panel-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Marca *</label>
                                        <input type="text" class="form-control complete-required" name="antena_marca" value="<?php echo oldEquipo('antena_marca'); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Modelo *</label>
                                        <input type="text" class="form-control complete-required" name="antena_modelo" value="<?php echo oldEquipo('antena_modelo'); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Número de serie</label>
                                        <input type="text" class="form-control" name="antena_numero_serie" value="<?php echo oldEquipo('antena_numero_serie'); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Estado *</label>
                                        <select class="form-select complete-required" name="antena_estado_tecnico">
                                            <option value="operativo" <?php echo selectedEquipo('antena_estado_tecnico', 'operativo', 'operativo'); ?>>Operativo</option>
                                            <option value="necesita_revision" <?php echo selectedEquipo('antena_estado_tecnico', 'necesita_revision'); ?>>Necesita Revisión</option>
                                            <option value="fuera_de_servicio" <?php echo selectedEquipo('antena_estado_tecnico', 'fuera_de_servicio'); ?>>Fuera de Servicio</option>
                                            <option value="en_mantenimiento" <?php echo selectedEquipo('antena_estado_tecnico', 'en_mantenimiento'); ?>>En Mantenimiento</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">MAC Address *</label>
                                        <input type="text" class="form-control complete-required" name="antena_mac_address" value="<?php echo oldEquipo('antena_mac_address'); ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Dirección IP *</label>
                                        <input type="text" class="form-control complete-required" name="antena_direccion_ip" value="<?php echo oldEquipo('antena_direccion_ip'); ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Contraseña *</label>
                                        <input type="text" class="form-control complete-required" name="antena_password_acceso" value="<?php echo oldEquipo('antena_password_acceso'); ?>">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Observaciones</label>
                                        <textarea class="form-control" name="antena_observaciones" rows="2"><?php echo oldEquipo('antena_observaciones'); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="device-panel mb-3">
                            <div class="device-panel-header">
                                <i class="fas fa-ethernet me-2"></i>
                                Módem
                            </div>
                            <div class="device-panel-body">
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Marca *</label>
                                        <input type="text" class="form-control complete-required" name="modem_marca" value="<?php echo oldEquipo('modem_marca'); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Modelo *</label>
                                        <input type="text" class="form-control complete-required" name="modem_modelo" value="<?php echo oldEquipo('modem_modelo'); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Número de serie</label>
                                        <input type="text" class="form-control" name="modem_numero_serie" value="<?php echo oldEquipo('modem_numero_serie'); ?>">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Estado *</label>
                                        <select class="form-select complete-required" name="modem_estado_tecnico">
                                            <option value="operativo" <?php echo selectedEquipo('modem_estado_tecnico', 'operativo', 'operativo'); ?>>Operativo</option>
                                            <option value="necesita_revision" <?php echo selectedEquipo('modem_estado_tecnico', 'necesita_revision'); ?>>Necesita Revisión</option>
                                            <option value="fuera_de_servicio" <?php echo selectedEquipo('modem_estado_tecnico', 'fuera_de_servicio'); ?>>Fuera de Servicio</option>
                                            <option value="en_mantenimiento" <?php echo selectedEquipo('modem_estado_tecnico', 'en_mantenimiento'); ?>>En Mantenimiento</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">MAC Address *</label>
                                        <input type="text" class="form-control complete-required" name="modem_mac_address" value="<?php echo oldEquipo('modem_mac_address'); ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Dirección IP *</label>
                                        <input type="text" class="form-control complete-required" name="modem_direccion_ip" value="<?php echo oldEquipo('modem_direccion_ip'); ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">SSID *</label>
                                        <input type="text" class="form-control complete-required" name="modem_ssid" value="<?php echo oldEquipo('modem_ssid'); ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Usuario *</label>
                                        <input type="text" class="form-control complete-required" name="modem_usuario_acceso" value="<?php echo oldEquipo('modem_usuario_acceso'); ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Contraseña *</label>
                                        <input type="text" class="form-control complete-required" name="modem_password_acceso" value="<?php echo oldEquipo('modem_password_acceso'); ?>">
                                    </div>
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label d-block">Estado de acceso</label>
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" role="switch" id="modem_acceso_habilitado" name="modem_acceso_habilitado" value="1" <?php echo checkedEquipo('modem_acceso_habilitado'); ?>>
                                            <label class="form-check-label" for="modem_acceso_habilitado">Activado</label>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label">Observaciones</label>
                                        <textarea class="form-control" name="modem_observaciones" rows="2"><?php echo oldEquipo('modem_observaciones'); ?></textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="observaciones_tecnico" class="form-label">Observaciones Técnicas / Instalación</label>
                        <textarea class="form-control" id="observaciones_tecnico" name="observaciones_tecnico" rows="4" placeholder="Observaciones adicionales sobre instalación, ubicación o configuración."><?php echo oldEquipo('observaciones_tecnico'); ?></textarea>
                    </div>

                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save me-1"></i>
                            Registrar
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

    <div class="col-lg-4">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0">
                    <i class="fas fa-info-circle me-2"></i>
                    Registro por instalación
                </h6>
            </div>
            <div class="card-body">
                <div class="alert alert-light border">
                    <strong>Relación:</strong><br>
                    Cliente → Instalación → Antena y Módem
                </div>
                <p class="small text-muted mb-3">
                    Usa “Instalación completa” cuando el servicio incluya antena en techo y módem dentro de la vivienda.
                </p>
                <h6>Campos por dispositivo</h6>
                <ul class="small text-muted">
                    <li>Antena: MAC, IP y contraseña.</li>
                    <li>Módem: MAC, IP, SSID, usuario, contraseña y acceso habilitado.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

<?php if (!empty($_GET['registrado'])): ?>
<div class="modal fade" id="equipoRegistradoModal" tabindex="-1" aria-labelledby="equipoRegistradoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-body p-4 text-center">
                <div class="rounded-circle bg-success-subtle text-success d-inline-flex align-items-center justify-content-center mb-3" style="width:58px;height:58px">
                    <i class="fas fa-check fa-lg"></i>
                </div>
                <h5 id="equipoRegistradoModalLabel" class="mb-2">Equipo registrado correctamente</h5>
                <p class="text-muted mb-4">El formulario se limpió para que puedas continuar rápido con la instalación.</p>
                <div class="d-grid gap-2 text-start">
                    <a class="btn btn-primary" href="<?php echo url('equipos/create?cliente_id=' . $prefillClienteId . '&fecha_instalacion=' . urlencode($prefillFecha)); ?>">
                        <i class="fas fa-user-plus me-2"></i>
                        Registrar otro equipo para el mismo cliente
                    </a>
                    <a class="btn btn-outline-primary" href="<?php echo url('equipos/create'); ?>">
                        <i class="fas fa-broom me-2"></i>
                        Registrar equipo para otro cliente
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('equipoForm');
    const modeInputs = document.querySelectorAll('input[name="modo_registro"]');
    const individualFields = document.getElementById('individualFields');
    const completeFields = document.getElementById('completeInstallationFields');
    const tipoEquipo = document.getElementById('tipo_equipo');
    const networkFields = document.getElementById('networkFields');
    const modemOnly = Array.from(document.querySelectorAll('.modem-only'));
    const modeCards = Array.from(document.querySelectorAll('.mode-card'));

    const individualRequired = ['tipo_equipo', 'estado_tecnico', 'marca', 'modelo'];
    const networkRequired = ['mac_address', 'direccion_ip', 'password_acceso'];
    const modemRequired = ['ssid', 'usuario_acceso'];
    const conditionalInputs = ['mac_address', 'direccion_ip', 'password_acceso', 'ssid', 'usuario_acceso'];
    const conditionalCheckboxes = ['acceso_habilitado'];
    let previousTipo = tipoEquipo ? tipoEquipo.value : '';

    function currentMode() {
        const checked = document.querySelector('input[name="modo_registro"]:checked');
        return checked ? checked.value : 'individual';
    }

    function setRequired(ids, required) {
        ids.forEach((id) => {
            const el = document.getElementById(id);
            if (el) el.required = required;
        });
    }

    function setCompleteRequired(required) {
        document.querySelectorAll('.complete-required').forEach((el) => {
            el.required = required;
        });
    }

    function clearConditionalFields() {
        conditionalInputs.forEach((id) => {
            const el = document.getElementById(id);
            if (el) el.value = '';
        });

        conditionalCheckboxes.forEach((id) => {
            const el = document.getElementById(id);
            if (el) el.checked = false;
        });
    }

    function syncModeCards() {
        modeCards.forEach((card) => {
            const input = card.querySelector('input[type="radio"]');
            card.classList.toggle('is-active', !!(input && input.checked));
        });
    }

    function syncTypeChangeBehavior() {
        const currentType = tipoEquipo ? tipoEquipo.value : '';
        if (currentType !== previousTipo) {
            clearConditionalFields();
            previousTipo = currentType;
        }
    }

    function updateDeviceFields() {
        const type = tipoEquipo ? tipoEquipo.value : '';
        const isNetworkDevice = type === 'antena' || type === 'modem';
        const isModem = type === 'modem';

        if (networkFields) networkFields.style.display = isNetworkDevice ? '' : 'none';
        modemOnly.forEach((el) => { el.style.display = isModem ? '' : 'none'; });

        setRequired(networkRequired, currentMode() === 'individual' && isNetworkDevice);
        setRequired(modemRequired, currentMode() === 'individual' && isModem);
    }

    function updateMode() {
        const mode = currentMode();
        const isIndividual = mode === 'individual';

        individualFields.style.display = isIndividual ? '' : 'none';
        completeFields.style.display = isIndividual ? 'none' : '';

        setRequired(individualRequired, isIndividual);
        setCompleteRequired(!isIndividual);
        updateDeviceFields();
        syncModeCards();
    }

    modeInputs.forEach((input) => input.addEventListener('change', updateMode));
    if (tipoEquipo) {
        tipoEquipo.addEventListener('change', function() {
            syncTypeChangeBehavior();
            updateDeviceFields();
        });
    }

    form.addEventListener('submit', function(e) {
        const mode = currentMode();
        if (mode === 'individual') {
            const marca = document.getElementById('marca').value.trim();
            const modelo = document.getElementById('modelo').value.trim();
            if (marca.length < 2 || modelo.length < 2) {
                e.preventDefault();
                alert('La marca y el modelo deben tener al menos 2 caracteres.');
            }
        }
    });

    updateMode();
    syncModeCards();

    const registeredModal = document.getElementById('equipoRegistradoModal');
    if (registeredModal && window.bootstrap) {
        new bootstrap.Modal(registeredModal).show();
    }
});
</script>

<?php include 'views/layouts/footer.php'; ?>
