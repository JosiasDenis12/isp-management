<?php include 'views/layouts/header.php'; ?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3">
    <div>
        <h1 class="h2 mb-1 page-title">
            <i class="fas fa-magnifying-glass me-2"></i>
            Buscar
        </h1>
        <div class="text-muted page-subtitle">Busca clientes, pagos y equipos en un solo lugar</div>
    </div>
</div>

<div class="card mb-4">
    <div class="card-body">
        <form action="<?php echo url('search'); ?>" method="GET" class="row g-2 align-items-center">
            <div class="col-lg-8">
                <label for="searchPageInput" class="form-label small text-muted mb-1">Búsqueda</label>
                <div class="input-group">
                    <span class="input-group-text"><i class="fas fa-magnifying-glass"></i></span>
                    <input
                        id="searchPageInput"
                        type="search"
                        name="q"
                        class="form-control"
                        placeholder="Ej: Juan, FAC-001, Huawei, 1234..."
                        value="<?php echo htmlspecialchars($q ?? ''); ?>"
                        autocomplete="off"
                    />
                </div>
                <div class="form-text">Mínimo <?php echo (int)$minLen; ?> caracteres</div>
            </div>
            <div class="col-lg-4 d-flex align-items-end gap-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="fas fa-magnifying-glass me-1"></i>
                    Buscar
                </button>
            </div>
        </form>
    </div>
</div>

<?php
    $qTrim = trim((string)($q ?? ''));
    $hasQuery = mb_strlen($qTrim, 'UTF-8') >= (int)$minLen;
    $total = count($clientes ?? []) + count($pagos ?? []) + count($equipos ?? []);
?>

<?php if (!$hasQuery): ?>
    <div class="card">
        <div class="card-body">
            <div class="empty-state">
                <div class="empty-icon bg-primary-subtle text-primary"><i class="fas fa-wand-magic-sparkles"></i></div>
                <div class="fw-semibold">Escribe para buscar</div>
                <div class="text-muted small">Clientes, facturas, números de serie, marcas y más.</div>
            </div>
        </div>
    </div>
<?php else: ?>
    <div class="d-flex align-items-center justify-content-between mb-3">
        <div class="text-muted">Resultados para <span class="fw-semibold"><?php echo htmlspecialchars($qTrim); ?></span></div>
        <div class="badge rounded-pill bg-light text-dark border"><?php echo (int)$total; ?> resultados</div>
    </div>

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header section-card-header">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <h5 class="mb-0 d-flex align-items-center gap-2">
                            <span class="section-icon text-primary bg-primary-subtle"><i class="fas fa-users"></i></span>
                            Clientes
                            <span class="badge rounded-pill bg-primary-subtle text-primary ms-1"><?php echo count($clientes ?? []); ?></span>
                        </h5>
                        <a class="section-link" href="<?php echo url('clientes'); ?>">Abrir</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($clientes)): ?>
                        <div class="text-muted small">Sin coincidencias</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($clientes as $item): ?>
                                <a class="list-group-item list-group-item-action" href="<?php echo htmlspecialchars($item['url']); ?>">
                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                        <div class="fw-semibold text-truncate"><?php echo htmlspecialchars($item['title']); ?></div>
                                        <?php if (!empty($item['badge'])): ?>
                                            <span class="badge rounded-pill bg-light text-dark border"><?php echo htmlspecialchars($item['badge']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($item['subtitle'])): ?>
                                        <div class="text-muted small text-truncate"><?php echo htmlspecialchars($item['subtitle']); ?></div>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header section-card-header">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <h5 class="mb-0 d-flex align-items-center gap-2">
                            <span class="section-icon text-success bg-success-subtle"><i class="fas fa-file-invoice-dollar"></i></span>
                            Pagos
                            <span class="badge rounded-pill bg-success-subtle text-success ms-1"><?php echo count($pagos ?? []); ?></span>
                        </h5>
                        <a class="section-link" href="<?php echo url('pagos'); ?>">Abrir</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($pagos)): ?>
                        <div class="text-muted small">Sin coincidencias</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($pagos as $item): ?>
                                <a class="list-group-item list-group-item-action" href="<?php echo htmlspecialchars($item['url']); ?>">
                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                        <div class="fw-semibold text-truncate"><?php echo htmlspecialchars($item['title']); ?></div>
                                        <?php if (!empty($item['badge'])): ?>
                                            <span class="badge rounded-pill bg-light text-dark border"><?php echo htmlspecialchars($item['badge']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($item['subtitle'])): ?>
                                        <div class="text-muted small text-truncate"><?php echo htmlspecialchars($item['subtitle']); ?></div>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card h-100">
                <div class="card-header section-card-header">
                    <div class="d-flex align-items-center justify-content-between gap-3">
                        <h5 class="mb-0 d-flex align-items-center gap-2">
                            <span class="section-icon text-info bg-info-subtle"><i class="fas fa-router"></i></span>
                            Equipos
                            <span class="badge rounded-pill bg-info-subtle text-info ms-1"><?php echo count($equipos ?? []); ?></span>
                        </h5>
                        <a class="section-link" href="<?php echo url('equipos'); ?>">Abrir</a>
                    </div>
                </div>
                <div class="card-body">
                    <?php if (empty($equipos)): ?>
                        <div class="text-muted small">Sin coincidencias</div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($equipos as $item): ?>
                                <a class="list-group-item list-group-item-action" href="<?php echo htmlspecialchars($item['url']); ?>">
                                    <div class="d-flex align-items-center justify-content-between gap-2">
                                        <div class="fw-semibold text-truncate"><?php echo htmlspecialchars($item['title']); ?></div>
                                        <?php if (!empty($item['badge'])): ?>
                                            <span class="badge rounded-pill bg-light text-dark border"><?php echo htmlspecialchars($item['badge']); ?></span>
                                        <?php endif; ?>
                                    </div>
                                    <?php if (!empty($item['subtitle'])): ?>
                                        <div class="text-muted small text-truncate"><?php echo htmlspecialchars($item['subtitle']); ?></div>
                                    <?php endif; ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
<?php endif; ?>

<?php include 'views/layouts/footer.php'; ?>
