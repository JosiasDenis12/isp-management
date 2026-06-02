<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?php echo $title ?? 'Login'; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet" />
  <style>
    body { min-height:100vh; background: radial-gradient(circle at 20% 20%, #2d3250, #1a1c2c 60%); color:#fff; font-family: system-ui,-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,'Helvetica Neue',Arial,sans-serif; }
    .login-wrapper { position:relative; display:flex; align-items:center; justify-content:center; min-height:100vh; overflow:hidden; }
    .orb { position:absolute; border-radius:50%; filter:blur(60px); opacity:.35; animation: float 14s ease-in-out infinite; }
    .orb.one { width:420px; height:420px; background:linear-gradient(135deg,#6366f1,#8b5cf6); top:-120px; left:-120px; }
    .orb.two { width:400px; height:400px; background:linear-gradient(135deg,#0ea5e9,#6366f1); bottom:-140px; right:-140px; animation-delay:3s; }
    @keyframes float { 0%,100% { transform:translate(0,0); } 25% { transform:translate(30px,-10px); } 50% { transform:translate(-25px,20px);} 75% { transform:translate(10px,-15px);} }
    .glass-card { position:relative; width:100%; max-width:420px; background:rgba(255,255,255,0.06); backdrop-filter:blur(26px) saturate(160%); border:1px solid rgba(255,255,255,0.15); border-radius:28px; padding:2.25rem 2.25rem 2rem; box-shadow:0 20px 40px -15px rgba(0,0,0,.55); }
    .logo-box { width:88px; height:88px; border-radius:22px; background:linear-gradient(135deg,#6366f1,#8b5cf6); display:flex; align-items:center; justify-content:center; margin:0 auto 1.25rem; position:relative; }
    .logo-box::after { content:''; position:absolute; inset:0; border-radius:inherit; background:linear-gradient(135deg,#6366f1,#8b5cf6); filter:blur(24px); opacity:.45; }
    .logo-box i { font-size:42px; color:#fff; position:relative; z:2; }
    h1 { font-size:2.25rem; letter-spacing:.5px; background:linear-gradient(90deg,#60a5fa,#a78bfa,#f472b6); -webkit-background-clip:text; color:transparent; text-align:center; font-weight:700; margin-bottom:.25rem; }
    .subtitle { text-align:center; font-size:.85rem; letter-spacing:.5px; color:#cbd5e1; margin-bottom:1.25rem; }
    .form-label { font-weight:500; font-size:.8rem; letter-spacing:.5px; text-transform:uppercase; color:#e2e8f0; margin-bottom:.35rem; }
    .form-control { background:rgba(255,255,255,0.08); border:1px solid rgba(255,255,255,.2); color:#fff; padding:0.9rem 1rem; border-radius:14px; font-size:.95rem; }
    .form-control:focus { background:rgba(255,255,255,0.12); border-color:#8b5cf6; box-shadow:0 0 0 2px rgba(139,92,246,.35); }
    .input-group-text { background:transparent; border:1px solid rgba(255,255,255,.2); border-radius:14px; color:#94a3b8; }
    .toggle-pass { position:absolute; right:14px; top:50%; transform:translateY(-50%); background:none; border:none; color:#94a3b8; }
    .toggle-pass:hover { color:#e2e8f0; }
    .btn-primary { background:linear-gradient(90deg,#6366f1,#8b5cf6,#6366f1); background-size:200% 100%; border:none; padding:.95rem 1.2rem; font-weight:600; letter-spacing:.5px; border-radius:16px; box-shadow:0 10px 25px -10px rgba(99,102,241,.6); animation: shimmer 6s linear infinite; }
    .btn-primary:hover { transform:translateY(-1px); box-shadow:0 15px 35px -10px rgba(99,102,241,.7); }
    @keyframes shimmer { 0% { background-position:-200% 0;} 100% { background-position:200% 0;} }
    .divider { position:relative; text-align:center; margin:1.75rem 0 1.25rem; }
    .divider span { font-size:.7rem; letter-spacing:2px; text-transform:uppercase; color:#94a3b8; background:rgba(255,255,255,0.08); padding:.4rem .9rem; border-radius:999px; }
    .footer-text { text-align:center; font-size:.65rem; color:#64748b; margin-top:1.5rem; letter-spacing:.5px; }
    .error-box { background:rgba(255,62,62,.15); border:1px solid rgba(255,62,62,.4); color:#fecaca; padding:.65rem .9rem; font-size:.8rem; border-radius:12px; margin-bottom:.85rem; }
  </style>
</head>
<body>
  <div class="login-wrapper">
    <div class="orb one"></div>
    <div class="orb two"></div>

    <div class="glass-card">
      <div class="logo-box">
        <i class="fas fa-wifi"></i>
      </div>
      <h1>Skynetwork</h1>
      <div class="subtitle">Sistema de Gestión de Internet</div>

      <?php if (!empty($error)): ?>
        <div class="error-box" role="alert"><?php echo htmlspecialchars($error); ?></div>
      <?php endif; ?>

      <form method="POST" action="<?php echo url('login'); ?>" autocomplete="off" novalidate>
        <div class="mb-3 position-relative">
          <label class="form-label" for="username">Nombre de usuario</label>
          <input type="text" id="username" name="username" required class="form-control" placeholder="Ing. Tomas" autofocus />
        </div>
        <div class="mb-3 position-relative">
          <label class="form-label" for="password">Contraseña</label>
          <input type="password" id="password" name="password" required class="form-control" placeholder="••••••••" />
          <button type="button" class="toggle-pass" aria-label="Mostrar/Ocultar" onclick="togglePassword()"><i class="fas fa-eye"></i></button>
        </div>
        <div class="d-flex justify-content-between align-items-center mb-3">
          <label class="d-flex align-items-center" style="font-size:.75rem; letter-spacing:.5px;">
            <input type="checkbox" class="form-check-input me-2" /> Recordarme
          </label>
          <a href="#" style="font-size:.7rem; color:#a5b4fc; text-decoration:none;">¿Olvidaste tu contraseña?</a>
        </div>
        <button type="submit" class="btn btn-primary w-100 mb-2">
          <i class="fas fa-bolt me-2"></i> Iniciar Sesión
        </button>
      </form>

      <div class="divider"><span>ACCESO RESTRINGIDO</span></div>
      <div class="footer-text">© <?php echo date('Y'); ?> Skynetwork. Conectando el futuro.</div>
    </div>
  </div>
  <script>
    function togglePassword(){
      const input = document.getElementById('password');
      const btn = document.querySelector('.toggle-pass i');
      if (input.type === 'password') { input.type = 'text'; btn.classList.remove('fa-eye'); btn.classList.add('fa-eye-slash'); }
      else { input.type = 'password'; btn.classList.remove('fa-eye-slash'); btn.classList.add('fa-eye'); }
    }
  </script>
</body>
</html>
