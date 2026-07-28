<?php
declare(strict_types=1);
session_start();

if (isset($_SESSION['usuario_activo'])) {
    header('Location: sintonizador.php');
    exit;
}

$errorMsg = '';
$errorType = '';
if (isset($_GET['error'])) {
    if ($_GET['error'] === 'credenciales_invalidas') {
        $errorMsg  = 'Usuario o contraseña incorrectos. Verifica tus datos e intenta de nuevo.';
        $errorType = 'credenciales';
    } elseif ($_GET['error'] === 'campos_vacios') {
        $errorMsg  = 'Por favor completa todos los campos antes de continuar.';
        $errorType = 'vacios';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Acceso al sistema de gestión de Radio Stereo Wave FM 98.1 - Plataforma profesional de control de emisiones.">
    <title>Acceso — Radio Stereo Wave 98.1 FM</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="frontend/css/estilos_fm.css">
    <style>
        /* Estilos específicos de la página de login */
        .login-bg {
            min-height: 100vh;
            background:
                radial-gradient(ellipse at 15% 40%, rgba(59,130,246,0.14) 0%, transparent 55%),
                radial-gradient(ellipse at 85% 15%, rgba(6,182,212,0.09) 0%, transparent 50%),
                radial-gradient(ellipse at 50% 90%, rgba(139,92,246,0.07) 0%, transparent 50%),
                #060b14;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem;
        }

        body.light-mode .login-bg {
            background:
                radial-gradient(ellipse at 15% 40%, rgba(29,78,216,0.06) 0%, transparent 55%),
                radial-gradient(ellipse at 85% 15%, rgba(8,145,178,0.05) 0%, transparent 50%),
                #f0f4f8;
        }

        .login-card {
            width: 100%;
            max-width: 430px;
            background: #111e33;
            border: 1px solid rgba(6,182,212,0.25);
            border-radius: 20px;
            padding: 2.5rem 2rem;
            box-shadow: 0 24px 70px rgba(0,0,0,0.55), 0 0 50px rgba(6,182,212,0.06);
            animation: fadeInUp 0.45s ease both;
        }

        body.light-mode .login-card {
            background: #fff;
            box-shadow: 0 12px 40px rgba(15,23,42,0.12);
            border-color: rgba(8,145,178,0.18);
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(24px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* Logo / header de la card */
        .login-logo-badge {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            background: rgba(6,182,212,0.08);
            border: 1px solid rgba(6,182,212,0.3);
            border-radius: 99px;
            padding: 6px 16px;
            margin-bottom: 1.2rem;
        }

        body.light-mode .login-logo-badge {
            background: #e0f2fe;
            border-color: rgba(8,145,178,0.3);
        }

        .login-logo-badge span {
            color: #06b6d4;
            font-weight: 700;
            font-size: 0.82rem;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            font-family: 'JetBrains Mono', monospace;
        }

        /* Campo con ícono de toggle de contraseña */
        .input-pw-wrapper {
            position: relative;
        }

        .input-pw-wrapper .form-control {
            padding-right: 2.8rem;
        }

        .btn-toggle-pw {
            position: absolute;
            right: 0.75rem;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #64748b;
            cursor: pointer;
            padding: 0;
            line-height: 1;
            transition: color 0.2s;
        }

        .btn-toggle-pw:hover { color: #06b6d4; }

        /* Botón de login */
        .btn-login {
            background: linear-gradient(135deg, #1d4ed8 0%, #06b6d4 100%);
            border: none;
            color: #fff;
            font-weight: 700;
            letter-spacing: 0.04em;
            padding: 0.8rem 1.5rem;
            border-radius: 10px;
            width: 100%;
            font-size: 0.95rem;
            box-shadow: 0 6px 20px rgba(59,130,246,0.35);
            transition: all 0.22s cubic-bezier(0.4,0,0.2,1);
            position: relative;
            overflow: hidden;
        }

        .btn-login::before {
            content: '';
            position: absolute;
            top: 0; left: -100%;
            width: 60%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.12), transparent);
            transition: left 0.5s ease;
        }

        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(59,130,246,0.5);
        }

        .btn-login:hover::before { left: 160%; }
        .btn-login:active { transform: scale(0.98); }

        /* Alert de error personalizado */
        .login-alert {
            background: rgba(244,63,94,0.1);
            border: 1px solid rgba(244,63,94,0.35);
            border-left: 4px solid #f43f5e;
            color: #fda4af;
            border-radius: 10px;
            padding: 0.85rem 1rem;
            font-size: 0.88rem;
            font-weight: 500;
            display: flex;
            align-items: flex-start;
            gap: 10px;
            animation: fadeIn 0.3s ease;
        }

        body.light-mode .login-alert {
            background: #fff1f2;
            border-color: #fda4af;
            border-left-color: #f43f5e;
            color: #be123c;
        }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }

        .login-divider {
            border-color: rgba(255,255,255,0.07) !important;
        }

        body.light-mode .login-divider {
            border-color: #e2e8f0 !important;
        }

        /* Footer de la card */
        .login-footer {
            color: #475569;
            font-size: 0.78rem;
            text-align: center;
        }

        /* Spinner de carga en el botón */
        .btn-login .spinner-border {
            width: 1em; height: 1em;
            border-width: 2px;
            display: none;
        }

        .btn-login.loading .spinner-border { display: inline-block; }
        .btn-login.loading .btn-text { display: none; }
    </style>
</head>
<body>
    <div class="login-bg">
        <div class="login-card">

            <!-- Cabecera del card -->
            <div class="text-center mb-4">
                <div class="login-logo-badge">
                    <span class="pulse-red"></span>
                    <span>STEREO WAVE 98.1 FM</span>
                </div>
                <h1 class="text-white fw-black mb-1" style="font-size: 1.65rem; letter-spacing: -0.03em; line-height: 1.1;">
                    Panel de Control
                </h1>
                <p class="mb-0" style="color: #64748b; font-size: 0.88rem;">
                    Inicia sesión para acceder al sistema de transmisión
                </p>
            </div>

            <!-- Mensaje de error -->
            <?php if (!empty($errorMsg)): ?>
                <div class="login-alert mb-4" role="alert" id="login-error-msg">
                    <i class="bi bi-exclamation-circle-fill flex-shrink-0" style="font-size: 1rem; margin-top: 1px;"></i>
                    <div>
                        <strong class="d-block" style="font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 2px;">
                            <?php echo $errorType === 'credenciales' ? 'Acceso denegado' : 'Campos requeridos'; ?>
                        </strong>
                        <?php echo htmlspecialchars($errorMsg); ?>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Formulario -->
            <form method="POST" action="backend/procesar_login.php" class="needs-validation" novalidate id="form-login">

                <!-- Campo Usuario -->
                <div class="mb-3">
                    <label for="usuario" class="form-label">
                        <i class="bi bi-person-fill me-1"></i> Usuario
                    </label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-at"></i>
                        </span>
                        <input
                            type="text"
                            class="form-control form-control-lg"
                            id="usuario"
                            name="usuario"
                            placeholder="Ingresa tu usuario"
                            autocomplete="username"
                            autofocus
                            required
                            minlength="2"
                            maxlength="50"
                        >
                    </div>
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle me-1"></i> El usuario es obligatorio.
                    </div>
                </div>

                <!-- Campo Contraseña -->
                <div class="mb-4">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label for="password" class="form-label mb-0">
                            <i class="bi bi-shield-lock-fill me-1"></i> Contraseña
                        </label>
                    </div>
                    <div class="input-group input-pw-wrapper">
                        <span class="input-group-text">
                            <i class="bi bi-lock-fill"></i>
                        </span>
                        <input
                            type="password"
                            class="form-control form-control-lg"
                            id="password"
                            name="password"
                            placeholder="••••••••"
                            autocomplete="current-password"
                            required
                            minlength="4"
                        >
                        <button type="button" class="btn-toggle-pw" id="btn-toggle-pw" title="Mostrar u ocultar contraseña" aria-label="Mostrar contraseña">
                            <i class="bi bi-eye-fill fs-5" id="icon-toggle-pw"></i>
                        </button>
                    </div>
                    <div class="invalid-feedback">
                        <i class="bi bi-exclamation-circle me-1"></i> Ingresa tu contraseña.
                    </div>
                </div>

                <!-- Botón de acceso -->
                <button type="submit" class="btn-login" id="btn-submit">
                    <span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>
                    <span class="btn-text">
                        <i class="bi bi-broadcast-pin me-2"></i>
                        Entrar a la Emisora
                    </span>
                </button>

            </form>

            <!-- Pie del card -->
            <hr class="my-4 login-divider">
            <p class="login-footer">
                <i class="bi bi-shield-check me-1 text-cyan"></i>
                Acceso restringido · © 2026 Radio Wave FM
            </p>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // ── Aplicar tema guardado ──
        (function() {
            const t = localStorage.getItem('radio_theme') || 'dark';
            if (t === 'light') document.body.classList.add('light-mode');
        })();

        // ── Toggle mostrar/ocultar contraseña ──
        const btnToggle = document.getElementById('btn-toggle-pw');
        const pwInput   = document.getElementById('password');
        const pwIcon    = document.getElementById('icon-toggle-pw');

        btnToggle.addEventListener('click', () => {
            const visible = pwInput.type === 'text';
            pwInput.type  = visible ? 'password' : 'text';
            pwIcon.className = visible ? 'bi bi-eye-fill fs-5' : 'bi bi-eye-slash-fill fs-5';
            btnToggle.setAttribute('aria-label', visible ? 'Mostrar contraseña' : 'Ocultar contraseña');
        });

        // ── Validación con spinner en submit ──
        const form     = document.getElementById('form-login');
        const btnSubmit = document.getElementById('btn-submit');

        form.addEventListener('submit', (e) => {
            if (!form.checkValidity()) {
                e.preventDefault();
                form.classList.add('was-validated');
                return;
            }
            // Mostrar estado de carga
            btnSubmit.classList.add('loading');
            btnSubmit.disabled = true;
        });

        // ── Auto-ocultar mensaje de error tras 7s ──
        const errMsg = document.getElementById('login-error-msg');
        if (errMsg) {
            setTimeout(() => {
                errMsg.style.transition = 'opacity 0.5s ease';
                errMsg.style.opacity = '0';
                setTimeout(() => errMsg.remove(), 500);
            }, 7000);
        }

        // ── Validación en tiempo real ──
        document.querySelectorAll('#form-login input').forEach(input => {
            input.addEventListener('blur', () => {
                if (input.value.trim() === '') {
                    input.classList.remove('is-valid');
                    input.classList.add('is-invalid');
                } else if (input.checkValidity()) {
                    input.classList.remove('is-invalid');
                    input.classList.add('is-valid');
                } else {
                    input.classList.remove('is-valid');
                    input.classList.add('is-invalid');
                }
            });

            input.addEventListener('input', () => {
                if (input.classList.contains('is-invalid') && input.value.trim()) {
                    if (input.checkValidity()) {
                        input.classList.remove('is-invalid');
                        input.classList.add('is-valid');
                    }
                }
            });
        });
    </script>
</body>
</html>