<!DOCTYPE html>

<html class="dark" lang="es">

<head>
    <meta charset="utf-8" />
    <meta content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover"
        name="viewport" />
    <meta content="mobile_tab" name="shell-type" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:opsz,wght,FILL,GRAD@20..48,100..700,0..1,-50..200"
        rel="stylesheet" />
    <link href="https://fonts.googleapis.com" rel="preconnect" />
    <link crossorigin="" href="https://fonts.gstatic.com" rel="preconnect" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&amp;display=swap"
        rel="stylesheet" />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Outlined:wght,FILL@100..700,0..1&amp;display=swap"
        rel="stylesheet" />
    <script src="https://cdn.tailwindcss.com"></script>
    <script id="tailwind-config">
        tailwind.config = {
            darkMode: "class",
            theme: {
                extend: {
                    "colors": {
                        "on-error": "#690005",
                        "primary-fixed": "#ffddb8",
                        "secondary-fixed-dim": "#4edea3",
                        "on-primary-container": "#613b00",
                        "on-tertiary-container": "#584000",
                        "on-primary-fixed": "#2a1700",
                        "on-tertiary-fixed": "#261a00",
                        "slate-border": "#334155",
                        "emerald-revenue": "#10b981",
                        "slate-surface": "#1e293b",
                        "amber-deep": "#d7706",
                        "on-primary": "#472a00",
                        "primary": "#ffc174",
                        "surface-variant": "#2d3449",
                        "amber-vibrant": "#f59e0b",
                        "surface-tint": "#ffb95f",
                        "on-tertiary": "#402d00",
                        "inverse-primary": "#855300",
                        "surface-bright": "#31394d",
                        "surface": "#0b1326",
                        "text-muted": "#94a3b8",
                        "tertiary-container": "#e0a800",
                        "tertiary-fixed-dim": "#f9bd22",
                        "on-surface": "#dae2fd",
                        "surface-container-low": "#131b2e",
                        "on-secondary-container": "#00311f",
                        "danger-border": "rgba(239, 68, 68, 0.3)",
                        "surface-container-lowest": "#060e20",
                        "primary-container": "#f59e0b",
                        "on-secondary-fixed": "#002113",
                        "text-primary": "#f8fafc",
                        "slate-canvas": "#0f172a",
                        "surface-container-high": "#222a3d",
                        "surface-dim": "#0b1326",
                        "emerald-surface": "rgba(16, 185, 129, 0.12)",
                        "inverse-on-surface": "#283044",
                        "on-surface-variant": "#d8c3ad",
                        "on-secondary": "#003824",
                        "danger-text": "#f87171",
                        "surface-container": "#171f33",
                        "secondary": "#4edea3",
                        "on-error-container": "#ffdad6",
                        "on-background": "#dae2fd",
                        "amber-light": "#fbbf24",
                        "on-primary-fixed-variant": "#653e00",
                        "tertiary": "#ffc32d",
                        "surface-container-highest": "#2d3449",
                        "secondary-fixed": "#6ffbbe",
                        "inverse-surface": "#dae2fd",
                        "background": "#0b1326",
                        "danger-surface": "rgba(239, 68, 68, 0.15)",
                        "outline": "#a08e7a",
                        "outline-variant": "#534434",
                        "on-secondary-fixed-variant": "#005236",
                        "secondary-container": "#00a572",
                        "tertiary-fixed": "#ffdf9f",
                        "primary-fixed-dim": "#ffb95f",
                        "on-tertiary-fixed-variant": "#5c4300",
                        "error-container": "#93000a",
                        "error": "#ffb4ab"
                    },
                    "borderRadius": {
                        "DEFAULT": "0.25rem",
                        "lg": "0.5rem",
                        "xl": "0.75rem",
                        "full": "9999px"
                    },
                    "spacing": {
                        "space-xl": "1.5rem",
                        "space-lg": "1rem",
                        "margin": "1.5rem",
                        "space-xs": "0.25rem",
                        "space-md": "0.75rem",
                        "space-sm": "0.5rem",
                        "gutter": "1rem",
                        "gutter-sm": "0.75rem",
                        "margin-sm": "1rem"
                    },
                    "fontFamily": {
                        "display-stat-mobile": ["Inter"],
                        "body-md": ["Inter"],
                        "badge-label": ["Inter"],
                        "action-label": ["Inter"],
                        "body-lg": ["Inter"],
                        "headline-lg": ["Inter"],
                        "headline-md": ["Inter"],
                        "caption": ["Inter"],
                        "display-stat": ["Inter"],
                        "headline-lg-mobile": ["Inter"]
                    },
                    "fontSize": {
                        "display-stat-mobile": ["28px", {
                            "lineHeight": "36px",
                            "letterSpacing": "-0.01em",
                            "fontWeight": "800"
                        }],
                        "body-md": ["14px", {
                            "lineHeight": "20px",
                            "fontWeight": "400"
                        }],
                        "badge-label": ["11px", {
                            "lineHeight": "14px",
                            "letterSpacing": "0.04em",
                            "fontWeight": "700"
                        }],
                        "action-label": ["15px", {
                            "lineHeight": "20px",
                            "letterSpacing": "0.01em",
                            "fontWeight": "700"
                        }],
                        "body-lg": ["16px", {
                            "lineHeight": "24px",
                            "fontWeight": "400"
                        }],
                        "headline-lg": ["26px", {
                            "lineHeight": "34px",
                            "letterSpacing": "-0.015em",
                            "fontWeight": "700"
                        }],
                        "headline-md": ["19px", {
                            "lineHeight": "26px",
                            "letterSpacing": "-0.005em",
                            "fontWeight": "700"
                        }],
                        "caption": ["13px", {
                            "lineHeight": "18px",
                            "fontWeight": "500"
                        }],
                        "display-stat": ["36px", {
                            "lineHeight": "44px",
                            "letterSpacing": "-0.02em",
                            "fontWeight": "800"
                        }],
                        "headline-lg-mobile": ["22px", {
                            "lineHeight": "30px",
                            "letterSpacing": "-0.01em",
                            "fontWeight": "700"
                        }]
                    }
                }
            }
        };
    </script>
    <style>
        @layer base {

            html,
            body {
                width: 100vw;
                margin: 0;
                padding: 0;
            }

            body {
                overscroll-behavior: none;
            }

            .pb-safe {
                padding-bottom: env(safe-area-inset-bottom, 0px);
            }

            .pt-safe {
                padding-top: env(safe-area-inset-top, 0px);
            }

            main>:first-child {
                margin-top: 0 !important;
            }

            main>:last-child {
                margin-bottom: 0 !important;
            }
        }

        ::-webkit-scrollbar {
            display: none;
        }
    </style>
</head>

<body
    class="bg-background font-body-md text-body-md text-on-surface antialiased flex flex-col min-h-screen selection:bg-primary selection:text-on-primary">
    <header class="fixed top-0 w-full z-50 pt-safe bg-surface/80 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)]">
        <div class="h-16 px-margin-sm flex items-center justify-between gap-space-sm">
            <div class="flex items-center gap-space-sm min-w-0"><button aria-label="Abrir menú lateral"
                    class="w-11 h-11 flex items-center justify-center rounded-xl bg-surface-container text-on-surface hover:bg-surface-container-high transition-colors"
                    onclick="document.getElementById('mobile-drawer').classList.remove('hidden')" type="button"><span
                        class="material-symbols-outlined text-[22px]">menu</span></button>
                <div class="flex items-center gap-space-xs"><img alt="MR Yhon Barber Studio Logo"
                        class="h-8 w-auto object-contain"
                        src="https://lh3.googleusercontent.com/aida/AEtjO1VTmRlRe2DSdqIC1WjPEtj19rYWYQT6yASeDFx3K30ukKu0uKVuoetUdQasQIsIKi0eyAvJvUBIL6hiK11pDPQKsPA_cnbvMDZH1Z_L2MU_0WQrmN7MipjyTxIdCIGnUSCBDiccGZych8p7dHNJDWu2Hr46idHX5zU6MinGnk_RqmaROTq6jiQ6BASx8rtHWGCEFVJV7poB5XZsWN95BzRZ407ve9hsw1_lc4u0lC_XQ9rC3lat2oYSqw" />
                    <div class="flex flex-col min-w-0"><span
                            class="font-badge-label text-badge-label text-amber-vibrant tracking-wider uppercase truncate">MR
                            Yhon Studio</span><span
                            class="font-headline-md text-headline-md text-on-surface truncate">Panel</span></div>
                </div>
            </div>
            <div class="flex items-center gap-space-xs"><button aria-label="Notificaciones"
                    class="relative w-11 h-11 flex items-center justify-center rounded-xl bg-surface-container text-on-surface-variant hover:text-on-surface hover:bg-surface-container-high transition-colors"
                    type="button"><span class="material-symbols-outlined text-[22px]">notifications</span><span
                        class="absolute top-2.5 right-2.5 w-2 h-2 rounded-full bg-amber-vibrant shadow-[0_0_8px_rgba(245,158,11,0.6)]"></span></button>
                <div class="flex items-center justify-center pl-1"><img alt="Profile"
                        class="w-8 h-8 rounded-full object-cover shadow-[0_0_0_1px_rgba(255,193,116,0.2)]"
                        src="https://lh3.googleusercontent.com/aida-public/AB6AXuA3Dy7DmYyVoMDlozn56pf3HQx0vx5_8Yc-GBxR9dem9zdRK5K9iHQdM1XxjEoFBTlvKFbmRfORkk4yt7ckcZLyp4V0kFMt2I7yrKQ0Q1Cu_G2WZOBeWS1TshyuiGA1ZyL6obK5EJ3yO6Jorx7m1JWtRiwitGkuJFkj-XRPUcYnrgJb58M30CL24FLXgCYftC65tEJCdqnsAeDNvcpj-juFSkgO70XGmZHJC-6nj3a8zKHrNyOpjSzy" />
                </div>
            </div>
        </div>
    </header>
    <div class="hidden fixed inset-0 z-50" id="mobile-drawer">
        <div class="absolute inset-0 bg-surface-container-lowest/80 backdrop-blur-sm"
            onclick="document.getElementById('mobile-drawer').classList.add('hidden')"></div>
        <aside
            class="relative w-4/5 max-w-[320px] h-full bg-surface-container flex flex-col justify-between p-margin pt-safe pb-safe shadow-[0_12px_32px_-4px_rgba(0,0,0,0.55)]">
            <div class="flex flex-col gap-space-lg">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-space-sm"><img alt="MR Yhon Barber Studio Logo"
                            class="h-8 w-auto object-contain"
                            src="https://lh3.googleusercontent.com/aida/AEtjO1VTmRlRe2DSdqIC1WjPEtj19rYWYQT6yASeDFx3K30ukKu0uKVuoetUdQasQIsIKi0eyAvJvUBIL6hiK11pDPQKsPA_cnbvMDZH1Z_L2MU_0WQrmN7MipjyTxIdCIGnUSCBDiccGZych8p7dHNJDWu2Hr46idHX5zU6MinGnk_RqmaROTq6jiQ6BASx8rtHWGCEFVJV7poB5XZsWN95BzRZ407ve9hsw1_lc4u0lC_XQ9rC3lat2oYSqw" /><span
                            class="font-headline-md text-headline-md text-primary font-bold">MR Yhon</span></div><button
                        class="w-11 h-11 flex items-center justify-center text-on-surface-variant hover:text-on-surface"
                        onclick="document.getElementById('mobile-drawer').classList.add('hidden')" type="button"><span
                            class="material-symbols-outlined text-[24px]">close</span></button>
                </div>
                <div class="flex items-center gap-space-md p-space-md rounded-xl bg-surface-container-high"><img
                        alt="Profile" class="w-11 h-11 rounded-full object-cover"
                        src="https://lh3.googleusercontent.com/aida-public/AB6AXuA3Dy7DmYyVoMDlozn56pf3HQx0vx5_8Yc-GBxR9dem9zdRK5K9iHQdM1XxjEoFBTlvKFbmRfORkk4yt7ckcZLyp4V0kFMt2I7yrKQ0Q1Cu_G2WZOBeWS1TshyuiGA1ZyL6obK5EJ3yO6Jorx7m1JWtRiwitGkuJFkj-XRPUcYnrgJb58M30CL24FLXgCYftC65tEJCdqnsAeDNvcpj-juFSkgO70XGmZHJC-6nj3a8zKHrNyOpjSzy" />
                    <div class="flex flex-col"><span class="font-action-label text-action-label text-text-primary">Yhon
                            Barber</span><span class="font-caption text-caption text-text-muted">Director de
                            Estudio</span></div>
                </div>
                <div class="flex flex-col gap-space-xs"><a
                        class="flex items-center gap-space-md px-space-md h-12 rounded-xl text-on-surface hover:bg-surface-container-high font-action-label text-action-label transition-colors"
                        data-path="panel" href="#"><span
                            class="material-symbols-outlined text-[20px] text-amber-vibrant">dashboard</span><span>Panel
                            Principal</span></a><a
                        class="flex items-center gap-space-md px-space-md h-12 rounded-xl text-on-surface hover:bg-surface-container-high font-action-label text-action-label transition-colors"
                        data-path="cortes" href="#"><span
                            class="material-symbols-outlined text-[20px] text-primary">content_cut</span><span>Cortes y
                            Servicios</span></a><a
                        class="flex items-center gap-space-md px-space-md h-12 rounded-xl text-on-surface hover:bg-surface-container-high font-action-label text-action-label transition-colors"
                        data-path="barberos" href="#"><span
                            class="material-symbols-outlined text-[20px] text-secondary">badge</span><span>Staff &amp;
                            Comisiones</span></a><a
                        class="flex items-center gap-space-md px-space-md h-12 rounded-xl text-on-surface hover:bg-surface-container-high font-action-label text-action-label transition-colors"
                        data-path="cierres" href="#"><span
                            class="material-symbols-outlined text-[20px] text-emerald-revenue">payments</span><span>Cierres
                            de Caja</span></a><a
                        class="flex items-center gap-space-md px-space-md h-12 rounded-xl text-on-surface hover:bg-surface-container-high font-action-label text-action-label transition-colors"
                        data-path="ajustes" href="#"><span
                            class="material-symbols-outlined text-[20px] text-on-surface-variant">settings</span><span>Ajustes
                            Generales</span></a></div>
            </div>
            <div class="pt-space-md"><a
                    class="flex items-center gap-space-md px-space-md h-12 rounded-xl text-danger-text hover:bg-danger-surface font-action-label text-action-label transition-colors"
                    data-path="login" href="#"><span
                        class="material-symbols-outlined text-[20px]">logout</span><span>Cerrar Sesión</span></a></div>
        </aside>
    </div>
    <main class="flex flex-col relative w-full px-margin-sm pt-16 pb-24 bg-surface min-h-screen">
        <div class="flex flex-col w-full gap-space-lg">
            <!-- Operational Shift Header & Live Daily Goal Progress -->
            <section class="flex flex-col gap-space-sm p-space-md rounded-xl bg-surface-container shadow-md">
                <div class="flex items-center justify-between gap-space-xs flex-wrap">
                    <div
                        class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-surface-container-high shadow-sm">
                        <span
                            class="w-2.5 h-2.5 rounded-full bg-emerald-revenue animate-pulse shadow-[0_0_8px_rgba(16,185,129,0.8)]"></span>
                        <span class="font-badge-label text-badge-label text-text-primary tracking-wider uppercase">Turno
                            Activo · Hoy</span>
                    </div>
                    <span class="font-caption text-caption text-text-muted">Martes, 24 Oct · 09:00 - 20:00</span>
                </div>
                <div class="flex flex-col gap-space-xs pt-1">
                    <div class="flex items-baseline justify-between">
                        <div class="flex items-baseline gap-1.5">
                            <span
                                class="font-display-stat-mobile text-display-stat-mobile text-primary tracking-tight">$340.00</span>
                            <span class="font-caption text-caption text-text-muted">/ $500.00 meta</span>
                        </div>
                        <span
                            class="font-badge-label text-badge-label text-amber-vibrant bg-amber-deep/10 px-2 py-0.5 rounded-full">68%
                            Alcanzado</span>
                    </div>
                    <!-- Goal Progress Bar -->
                    <div class="w-full h-2 rounded-full bg-surface-container-high overflow-hidden">
                        <div class="h-full rounded-full bg-gradient-to-r from-amber-vibrant to-primary shadow-[0_0_12px_rgba(245,158,11,0.5)] transition-all duration-500"
                            style="width: 68%;"></div>
                    </div>
                    <div class="flex items-center justify-between text-text-muted font-caption text-caption pt-0.5">
                        <span>22 cortes ejecutados</span>
                        <span class="text-secondary flex items-center gap-0.5">
                            <span class="material-symbols-outlined text-[14px]">trending_up</span> +$60 vs ayer
                        </span>
                    </div>
                </div>
            </section>
            <!-- Primary Action & Quick Operations Shortcuts -->
            <section class="flex flex-col gap-space-sm">
                <!-- Big Primary Quick Entry Action -->
                <button
                    class="w-full min-h-[52px] px-space-lg flex items-center justify-center gap-space-sm rounded-xl bg-amber-vibrant hover:bg-amber-light text-slate-canvas font-action-label text-action-label uppercase tracking-wider shadow-[0_4px_20px_rgba(245,158,11,0.35)] active:scale-[0.985] transition-transform"
                    onclick="document.getElementById('quick-register-card').scrollIntoView({behavior: 'smooth'})"
                    type="button">
                    <span class="material-symbols-outlined text-[24px]">content_cut</span>
                    <span>+ Registrar Nuevo Corte</span>
                </button>
                <!-- Operational Secondary Pills (Scrollable touch bar) -->
                <div class="flex items-center gap-space-xs overflow-x-auto pb-1 -mx-margin-sm px-margin-sm">
                    <button
                        class="flex-shrink-0 min-h-[48px] px-4 rounded-xl bg-surface-container hover:bg-surface-container-high text-text-primary flex items-center gap-2 font-action-label text-action-label shadow-sm active:scale-95 transition-transform"
                        onclick="alert('Iniciando arqueo y precierre de caja')" type="button">
                        <span class="material-symbols-outlined text-[18px] text-amber-vibrant">point_of_sale</span>
                        <span>Cierre Rápido</span>
                    </button>
                    <button
                        class="flex-shrink-0 min-h-[48px] px-4 rounded-xl bg-surface-container hover:bg-surface-container-high text-text-primary flex items-center gap-2 font-action-label text-action-label shadow-sm active:scale-95 transition-transform"
                        onclick="alert('Abriendo métricas de rendimiento semanal')" type="button">
                        <span class="material-symbols-outlined text-[18px] text-secondary">query_stats</span>
                        <span>Reporte Semanal</span>
                    </button>
                    <button
                        class="flex-shrink-0 min-h-[48px] px-4 rounded-xl bg-surface-container hover:bg-surface-container-high text-text-primary flex items-center gap-2 font-action-label text-action-label shadow-sm active:scale-95 transition-transform"
                        onclick="alert('Generando backup cifrado en la nube')" type="button">
                        <span
                            class="material-symbols-outlined text-[18px] text-on-surface-variant">cloud_download</span>
                        <span>Exportar Copia</span>
                    </button>
                </div>
            </section>
            <!-- Financial & Volume KPI Matrix (2 cols on mobile, 4 on desktop) -->
            <section class="grid grid-cols-2 lg:grid-cols-4 gap-space-sm">
                <!-- KPI 1: Producción Bruta -->
                <div class="p-space-md rounded-xl bg-surface-container flex flex-col justify-between shadow-sm">
                    <div class="flex items-center justify-between text-text-muted mb-2">
                        <span class="font-caption text-caption font-medium">Producción Hoy</span>
                        <span class="material-symbols-outlined text-[18px] text-amber-vibrant">storefront</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-headline-lg-mobile text-headline-lg-mobile text-text-primary">$340.00</span>
                        <div class="flex items-center gap-1 mt-1">
                            <span
                                class="font-badge-label text-badge-label text-emerald-revenue bg-emerald-surface px-1.5 py-0.5 rounded-full">+14%</span>
                            <span class="font-caption text-caption text-text-muted">vs ayer</span>
                        </div>
                    </div>
                </div>
                <!-- KPI 2: Margen Estudio (40%) -->
                <div class="p-space-md rounded-xl bg-surface-container flex flex-col justify-between shadow-sm">
                    <div class="flex items-center justify-between text-text-muted mb-2">
                        <span class="font-caption text-caption font-medium">Local (40%)</span>
                        <span class="material-symbols-outlined text-[18px] text-primary">savings</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-headline-lg-mobile text-headline-lg-mobile text-primary">$136.00</span>
                        <div class="flex items-center gap-1 mt-1">
                            <span
                                class="font-badge-label text-badge-label text-primary bg-on-primary-fixed/20 px-1.5 py-0.5 rounded-full">Neto
                                Dueño</span>
                        </div>
                    </div>
                </div>
                <!-- KPI 3: Nómina Barberos (60%) -->
                <div class="p-space-md rounded-xl bg-surface-container flex flex-col justify-between shadow-sm">
                    <div class="flex items-center justify-between text-text-muted mb-2">
                        <span class="font-caption text-caption font-medium">Equipo (60%)</span>
                        <span class="material-symbols-outlined text-[18px] text-secondary">group</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-headline-lg-mobile text-headline-lg-mobile text-secondary">$204.00</span>
                        <div class="flex items-center gap-1 mt-1">
                            <span class="font-caption text-caption text-text-muted">5 barberos activos</span>
                        </div>
                    </div>
                </div>
                <!-- KPI 4: Ticket Promedio -->
                <div class="p-space-md rounded-xl bg-surface-container flex flex-col justify-between shadow-sm">
                    <div class="flex items-center justify-between text-text-muted mb-2">
                        <span class="font-caption text-caption font-medium">Ticket Medio</span>
                        <span class="material-symbols-outlined text-[18px] text-amber-vibrant">speed</span>
                    </div>
                    <div class="flex flex-col">
                        <span class="font-headline-lg-mobile text-headline-lg-mobile text-text-primary">$15.45</span>
                        <div class="flex items-center gap-1 mt-1">
                            <span class="font-caption text-caption text-text-muted">~32 min / sillón</span>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Quick Register Form Drawer -->
            <section class="flex flex-col gap-space-md p-space-md rounded-xl bg-surface-container shadow-md"
                id="quick-register-card">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <div
                            class="w-8 h-8 rounded-lg bg-amber-vibrant/10 flex items-center justify-center text-amber-vibrant">
                            <span class="material-symbols-outlined text-[20px]">add_circle</span>
                        </div>
                        <span class="font-headline-md text-headline-md text-text-primary">Registro Express de
                            Corte</span>
                    </div>
                    <span
                        class="font-badge-label text-badge-label uppercase text-amber-vibrant bg-surface-container-high px-2 py-1 rounded-md">P.O.S.
                        Silla</span>
                </div>
                <!-- Barber Selection -->
                <div class="flex flex-col gap-space-xs">
                    <label class="font-caption text-caption text-text-muted">Seleccionar Barbero</label>
                    <div class="grid grid-cols-5 gap-1.5" id="barber-selector">
                        <button
                            class="barber-pill min-h-[48px] flex flex-col items-center justify-center py-1.5 px-1 rounded-lg bg-surface-container-high text-text-primary hover:bg-surface-bright active:scale-95 transition-all"
                            onclick="selectBarber(this, 'Josue')" type="button">
                            <span class="font-action-label text-action-label">Josué</span>
                            <span class="font-badge-label text-badge-label text-text-muted">6 cuts</span>
                        </button>
                        <button
                            class="barber-pill min-h-[48px] flex flex-col items-center justify-center py-1.5 px-1 rounded-lg bg-amber-vibrant text-slate-canvas font-bold shadow-[0_0_12px_rgba(245,158,11,0.4)] active:scale-95 transition-all"
                            onclick="selectBarber(this, 'Marlon')" type="button">
                            <span class="font-action-label text-action-label">Marlon</span>
                            <span class="font-badge-label text-badge-label text-slate-canvas">5 cuts</span>
                        </button>
                        <button
                            class="barber-pill min-h-[48px] flex flex-col items-center justify-center py-1.5 px-1 rounded-lg bg-surface-container-high text-text-primary hover:bg-surface-bright active:scale-95 transition-all"
                            onclick="selectBarber(this, 'Ismary')" type="button">
                            <span class="font-action-label text-action-label">Ismary</span>
                            <span class="font-badge-label text-badge-label text-text-muted">4 cuts</span>
                        </button>
                        <button
                            class="barber-pill min-h-[48px] flex flex-col items-center justify-center py-1.5 px-1 rounded-lg bg-surface-container-high text-text-primary hover:bg-surface-bright active:scale-95 transition-all"
                            onclick="selectBarber(this, 'Kon')" type="button">
                            <span class="font-action-label text-action-label">Kon</span>
                            <span class="font-badge-label text-badge-label text-text-muted">4 cuts</span>
                        </button>
                        <button
                            class="barber-pill min-h-[48px] flex flex-col items-center justify-center py-1.5 px-1 rounded-lg bg-surface-container-high text-text-primary hover:bg-surface-bright active:scale-95 transition-all"
                            onclick="selectBarber(this, 'Geremy')" type="button">
                            <span class="font-action-label text-action-label">Geremy</span>
                            <span class="font-badge-label text-badge-label text-text-muted">3 cuts</span>
                        </button>
                    </div>
                </div>
                <!-- Quick Price Buttons + Custom Price Input -->
                <div class="flex flex-col gap-space-xs">
                    <div class="flex items-center justify-between">
                        <label class="font-caption text-caption text-text-muted">Monto del Servicio ($)</label>
                        <span class="font-caption text-caption text-amber-vibrant" id="split-preview">Local: $7.20
                            (40%) · Barbero: $10.80 (60%)</span>
                    </div>
                    <div class="grid grid-cols-4 gap-2">
                        <button
                            class="quick-amt-btn min-h-[48px] rounded-lg bg-surface-container-high hover:bg-surface-bright text-text-primary font-action-label text-action-label active:scale-95 transition-transform"
                            onclick="setQuickAmount(10)" type="button">$10</button>
                        <button
                            class="quick-amt-btn min-h-[48px] rounded-lg bg-surface-container-high hover:bg-surface-bright text-text-primary font-action-label text-action-label active:scale-95 transition-transform"
                            onclick="setQuickAmount(15)" type="button">$15</button>
                        <button
                            class="quick-amt-btn min-h-[48px] rounded-lg bg-amber-vibrant/20 text-amber-vibrant font-action-label text-action-label shadow-sm active:scale-95 transition-transform"
                            onclick="setQuickAmount(18)" type="button">$18</button>
                        <button
                            class="quick-amt-btn min-h-[48px] rounded-lg bg-surface-container-high hover:bg-surface-bright text-text-primary font-action-label text-action-label active:scale-95 transition-transform"
                            onclick="setQuickAmount(25)" type="button">$25</button>
                    </div>
                    <div class="relative mt-1">
                        <span
                            class="absolute left-4 top-1/2 -translate-y-1/2 font-display-stat-mobile text-display-stat-mobile text-text-muted pointer-events-none">$</span>
                        <input
                            class="w-full min-h-[52px] pl-10 pr-4 bg-surface-container-high rounded-xl text-text-primary font-display-stat-mobile text-display-stat-mobile tracking-tight focus:outline-none focus:bg-surface-container-highest transition-colors"
                            id="custom-amount-input" oninput="updateSplit(this.value)" placeholder="0.00"
                            type="number" value="18" />
                    </div>
                </div>
                <!-- Confirm Registration Button -->
                <button
                    class="w-full min-h-[50px] rounded-xl bg-amber-vibrant text-slate-canvas font-action-label text-action-label flex items-center justify-center gap-2 hover:bg-amber-light active:scale-[0.985] shadow-[0_4px_16px_rgba(245,158,11,0.25)] transition-all"
                    onclick="triggerToast('¡Corte guardado con éxito y asignado a Marlon!')" type="button">
                    <span class="material-symbols-outlined text-[20px]">check_circle</span>
                    <span>Confirmar y Liquidar Ticket</span>
                </button>
            </section>
            <!-- Barber Team Daily Performance -->
            <section class="flex flex-col gap-space-sm p-space-md rounded-xl bg-surface-container shadow-md">
                <div class="flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="material-symbols-outlined text-[20px] text-secondary">groups</span>
                        <span class="font-headline-md text-headline-md text-text-primary">Rendimiento del Equipo</span>
                    </div>
                    <span class="font-caption text-caption text-text-muted">60% Nómina</span>
                </div>
                <div class="flex flex-col gap-space-xs">
                    <!-- Barber 1: Josue -->
                    <div class="p-space-sm rounded-xl bg-surface-container-high flex flex-col gap-1.5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 rounded-full bg-amber-vibrant/20 text-amber-vibrant flex items-center justify-center font-bold text-caption">
                                    J</div>
                                <div class="flex flex-col">
                                    <span class="font-action-label text-action-label text-text-primary">Josué</span>
                                    <span class="font-caption text-caption text-text-muted">6 servicios hoy</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-action-label text-action-label text-secondary">$58.80</span>
                                <span class="font-caption text-caption text-text-muted block">de $98.00 total</span>
                            </div>
                        </div>
                        <div class="w-full h-1.5 rounded-full bg-surface-container-lowest overflow-hidden">
                            <div class="h-full rounded-full bg-secondary" style="width: 85%;"></div>
                        </div>
                    </div>
                    <!-- Barber 2: Marlon -->
                    <div class="p-space-sm rounded-xl bg-surface-container-high flex flex-col gap-1.5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 rounded-full bg-primary/20 text-primary flex items-center justify-center font-bold text-caption">
                                    M</div>
                                <div class="flex flex-col">
                                    <span class="font-action-label text-action-label text-text-primary">Marlon</span>
                                    <span class="font-caption text-caption text-text-muted">5 servicios hoy</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-action-label text-action-label text-secondary">$51.00</span>
                                <span class="font-caption text-caption text-text-muted block">de $85.00 total</span>
                            </div>
                        </div>
                        <div class="w-full h-1.5 rounded-full bg-surface-container-lowest overflow-hidden">
                            <div class="h-full rounded-full bg-secondary" style="width: 74%;"></div>
                        </div>
                    </div>
                    <!-- Barber 3: Ismary -->
                    <div class="p-space-sm rounded-xl bg-surface-container-high flex flex-col gap-1.5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 rounded-full bg-amber-light/20 text-amber-light flex items-center justify-center font-bold text-caption">
                                    I</div>
                                <div class="flex flex-col">
                                    <span class="font-action-label text-action-label text-text-primary">Ismary</span>
                                    <span class="font-caption text-caption text-text-muted">4 servicios hoy</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-action-label text-action-label text-secondary">$38.40</span>
                                <span class="font-caption text-caption text-text-muted block">de $64.00 total</span>
                            </div>
                        </div>
                        <div class="w-full h-1.5 rounded-full bg-surface-container-lowest overflow-hidden">
                            <div class="h-full rounded-full bg-secondary" style="width: 56%;"></div>
                        </div>
                    </div>
                    <!-- Barber 4: Kon -->
                    <div class="p-space-sm rounded-xl bg-surface-container-high flex flex-col gap-1.5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 rounded-full bg-on-surface-variant/20 text-on-surface-variant flex items-center justify-center font-bold text-caption">
                                    K</div>
                                <div class="flex flex-col">
                                    <span class="font-action-label text-action-label text-text-primary">Kon</span>
                                    <span class="font-caption text-caption text-text-muted">4 servicios hoy</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-action-label text-action-label text-secondary">$32.40</span>
                                <span class="font-caption text-caption text-text-muted block">de $54.00 total</span>
                            </div>
                        </div>
                        <div class="w-full h-1.5 rounded-full bg-surface-container-lowest overflow-hidden">
                            <div class="h-full rounded-full bg-secondary" style="width: 48%;"></div>
                        </div>
                    </div>
                    <!-- Barber 5: Geremy -->
                    <div class="p-space-sm rounded-xl bg-surface-container-high flex flex-col gap-1.5 shadow-sm">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-2">
                                <div
                                    class="w-8 h-8 rounded-full bg-amber-vibrant/20 text-amber-vibrant flex items-center justify-center font-bold text-caption">
                                    G</div>
                                <div class="flex flex-col">
                                    <span class="font-action-label text-action-label text-text-primary">Geremy</span>
                                    <span class="font-caption text-caption text-text-muted">3 servicios hoy</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-action-label text-action-label text-secondary">$23.40</span>
                                <span class="font-caption text-caption text-text-muted block">de $39.00 total</span>
                            </div>
                        </div>
                        <div class="w-full h-1.5 rounded-full bg-surface-container-lowest overflow-hidden">
                            <div class="h-full rounded-full bg-secondary" style="width: 35%;"></div>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Live Chair & Recent Appointments Feed -->
            <section class="flex flex-col gap-space-sm">
                <div class="flex items-center justify-between flex-wrap gap-2">
                    <div class="flex items-center gap-2">
                        <span
                            class="material-symbols-outlined text-[20px] text-amber-vibrant">history_toggle_off</span>
                        <span class="font-headline-md text-headline-md text-text-primary">Últimos Cortes de Hoy</span>
                    </div>
                    <!-- Segmented Tabs -->
                    <div class="inline-flex p-1 rounded-xl bg-surface-container-high text-caption font-caption">
                        <button class="px-3 py-1 rounded-lg bg-amber-vibrant text-slate-canvas font-bold shadow-sm"
                            type="button">Todos (22)</button>
                        <button class="px-3 py-1 rounded-lg text-text-muted hover:text-text-primary transition-colors"
                            type="button">En Silla (3)</button>
                        <button class="px-3 py-1 rounded-lg text-text-muted hover:text-text-primary transition-colors"
                            type="button">Listos (19)</button>
                    </div>
                </div>
                <!-- Feed Rows -->
                <div class="flex flex-col gap-space-xs">
                    <!-- Item 1 (En Silla) -->
                    <div
                        class="p-space-md rounded-xl bg-surface-container flex items-center justify-between gap-space-sm shadow-sm">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="relative flex-shrink-0">
                                <img class="w-12 h-12 rounded-xl object-cover"
                                    data-alt="High-end client sitting in vintage leather barber chair getting skin fade cut, soft amber barbershop lighting, high contrast cinematic styling"
                                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuAxxODyrMMj9j59xXLEXHRaHD2DY-yps_GL07HyJfbwv2D4Ve-blc2upORelMF4748PCLocJYPYyMd3Gd7B0D2P8iRNlsBoHlzCi0BJ95_EhUjD915ZU0zJGMUZe4HUNLNYE21-cbqdJqUR6A62Ll3SK5Vk0VHO3Bl5LL0fC-pqpJItdntbWaDhAbWE95PSdhYuIgbnL2lWGzkCNQkJBFhO6iNZuD15fguVxtV7m1Y4fs5eTvEEgipd" />
                                <span
                                    class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full bg-amber-vibrant shadow-[0_0_8px_rgba(245,158,11,0.8)] animate-pulse"></span>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="font-action-label text-action-label text-text-primary truncate">Degradado
                                        Alto + Barba</span>
                                    <span
                                        class="font-badge-label text-badge-label px-2 py-0.5 rounded-full bg-amber-deep/20 text-amber-vibrant flex-shrink-0">En
                                        Silla</span>
                                </div>
                                <div class="flex items-center gap-2 font-caption text-caption text-text-muted mt-0.5">
                                    <span class="text-primary font-medium">Marlon</span>
                                    <span>·</span>
                                    <span>18:15 hrs</span>
                                    <span>·</span>
                                    <span>40% Local ($8.00)</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end flex-shrink-0">
                            <span class="font-headline-md text-headline-md text-primary">$20.00</span>
                            <span class="font-badge-label text-badge-label text-secondary font-bold">Barbero
                                $12.00</span>
                        </div>
                    </div>
                    <!-- Item 2 (Completado) -->
                    <div
                        class="p-space-md rounded-xl bg-surface-container flex items-center justify-between gap-space-sm shadow-sm">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="relative flex-shrink-0">
                                <img class="w-12 h-12 rounded-xl object-cover"
                                    data-alt="Satisfied grooming studio client with sharp line beard and pompadour hairstyle under warm spotlight, dark luxury barbershop setting"
                                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuBc6VCTFJ4pOd9_0Z6JZit7CPOO2VGhdKyKmFjWvERv-6tR01BdGtBVmS1HR4ggpVgiHa7M75ufCa4sH8DICrkCmEOl0W_2q8Gf1gQUG-jvX6mZaH5TXeH-ifrZyteBfcmEAWfnXXMBYhViocJ4P_QXIWPalFYKZn7IZ-bLVB7vKs37mHBDlpzWPTOIE6o2cfrGB-WdkeNb0NZIwyEUmiuvDOS0bIeNT_WYAqMVWLP2yQX3TfIqcliR" />
                                <span
                                    class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full bg-emerald-revenue"></span>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-action-label text-action-label text-text-primary truncate">Corte
                                        Clásico Ejecutivo</span>
                                    <span
                                        class="font-badge-label text-badge-label px-2 py-0.5 rounded-full bg-emerald-surface text-emerald-revenue flex-shrink-0">Completado</span>
                                </div>
                                <div class="flex items-center gap-2 font-caption text-caption text-text-muted mt-0.5">
                                    <span class="text-primary font-medium">Josué</span>
                                    <span>·</span>
                                    <span>17:40 hrs</span>
                                    <span>·</span>
                                    <span>40% Local ($6.00)</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end flex-shrink-0">
                            <span class="font-headline-md text-headline-md text-primary">$15.00</span>
                            <span class="font-badge-label text-badge-label text-secondary font-bold">Barbero
                                $9.00</span>
                        </div>
                    </div>
                    <!-- Item 3 (Completado) -->
                    <div
                        class="p-space-md rounded-xl bg-surface-container flex items-center justify-between gap-space-sm shadow-sm">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="relative flex-shrink-0">
                                <img class="w-12 h-12 rounded-xl object-cover"
                                    data-alt="Modern taper fade haircut close up on young adult male client, dark background with subtle amber backlight studio detailing"
                                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuCPfuhGy8yIEEBp2gQXKNSSzqlgQ_Zg9YeFz1GhgfL7AEf5vm9L32fLR5ysYes4bX4rbtsnkEkIKR3V0-UrPNySwd5_DuYUE6LPloUPOuNzobqI0aF1oeXaU30tV5V2o94cBQ0B39Mh44GsV2xnyKA1vCGIgh8oP2mLsScPdYBNVA-rpDPMhtLYP1H70_iewzBgSQ9NVkMMrKaJ-7t49PSnFDQrB2g7mbBXGrrRT8lNR1njUDMB7yw2" />
                                <span
                                    class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full bg-emerald-revenue"></span>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="font-action-label text-action-label text-text-primary truncate">Taper
                                        Fade + Marcado Navaja</span>
                                    <span
                                        class="font-badge-label text-badge-label px-2 py-0.5 rounded-full bg-emerald-surface text-emerald-revenue flex-shrink-0">Completado</span>
                                </div>
                                <div class="flex items-center gap-2 font-caption text-caption text-text-muted mt-0.5">
                                    <span class="text-primary font-medium">Ismary</span>
                                    <span>·</span>
                                    <span>17:10 hrs</span>
                                    <span>·</span>
                                    <span>40% Local ($7.20)</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end flex-shrink-0">
                            <span class="font-headline-md text-headline-md text-primary">$18.00</span>
                            <span class="font-badge-label text-badge-label text-secondary font-bold">Barbero
                                $10.80</span>
                        </div>
                    </div>
                    <!-- Item 4 (Completado) -->
                    <div
                        class="p-space-md rounded-xl bg-surface-container flex items-center justify-between gap-space-sm shadow-sm">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="relative flex-shrink-0">
                                <img class="w-12 h-12 rounded-xl object-cover"
                                    data-alt="Barber hands trimming clean beard line using precision straight razor with warm lather, upscale dark slate salon background"
                                    src="https://lh3.googleusercontent.com/aida-public/AB6AXuA5hL9y_yScr-lA_Mr7nVghvOrWUzOqacrxar4OCBl3Xd0KLsbvsH_SbCv5hLb3bt1Fk27gBn_uIIQZ2ZwnHZD46rFrTsPtQ_aM03cQ9npQcVbTa4v06r7bSM4exnHBp4btRlHxd_j_CNB0BwxDFUXgCPWY8a65xSKG6lnrgn_ZiIGc_SgFYsZxi7n6slmgB83xlLuYNRiVhF1VqIwOYyV1sS-AZpHbNkCb4iKKW1arLfKj2c44gHuG" />
                                <span
                                    class="absolute -bottom-1 -right-1 w-3.5 h-3.5 rounded-full bg-emerald-revenue"></span>
                            </div>
                            <div class="flex flex-col min-w-0">
                                <div class="flex items-center gap-2">
                                    <span
                                        class="font-action-label text-action-label text-text-primary truncate">Perfilado
                                        de Barba y Toalla</span>
                                    <span
                                        class="font-badge-label text-badge-label px-2 py-0.5 rounded-full bg-emerald-surface text-emerald-revenue flex-shrink-0">Completado</span>
                                </div>
                                <div class="flex items-center gap-2 font-caption text-caption text-text-muted mt-0.5">
                                    <span class="text-primary font-medium">Kon</span>
                                    <span>·</span>
                                    <span>16:30 hrs</span>
                                    <span>·</span>
                                    <span>40% Local ($4.80)</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex flex-col items-end flex-shrink-0">
                            <span class="font-headline-md text-headline-md text-primary">$12.00</span>
                            <span class="font-badge-label text-badge-label text-secondary font-bold">Barbero
                                $7.20</span>
                        </div>
                    </div>
                </div>
            </section>
            <!-- Interactive Feedback Toast Placeholder -->
            <div class="hidden fixed bottom-20 left-1/2 -translate-x-1/2 z-50 bg-slate-surface text-text-primary px-4 py-2.5 rounded-xl shadow-[0_12px_32px_-4px_rgba(0,0,0,0.7)] flex items-center gap-2 text-action-label font-action-label"
                id="status-toast">
                <span class="material-symbols-outlined text-secondary text-[20px]">task_alt</span>
                <span id="toast-message">Operación exitosa</span>
            </div>
        </div>
        <script>
            let currentBarber = 'Marlon';

            function selectBarber(btn, name) {
                currentBarber = name;
                document.querySelectorAll('.barber-pill').forEach(el => {
                    el.className =
                        'barber-pill min-h-[48px] flex flex-col items-center justify-center py-1.5 px-1 rounded-lg bg-surface-container-high text-text-primary hover:bg-surface-bright active:scale-95 transition-all';
                    const sub = el.querySelector('span:last-child');
                    if (sub) sub.className = 'font-badge-label text-badge-label text-text-muted';
                });
                btn.className =
                    'barber-pill min-h-[48px] flex flex-col items-center justify-center py-1.5 px-1 rounded-lg bg-amber-vibrant text-slate-canvas font-bold shadow-[0_0_12px_rgba(245,158,11,0.4)] active:scale-95 transition-all';
                const activeSub = btn.querySelector('span:last-child');
                if (activeSub) activeSub.className = 'font-badge-label text-badge-label text-slate-canvas';
            }

            function setQuickAmount(amount) {
                const input = document.getElementById('custom-amount-input');
                if (input) {
                    input.value = amount;
                    updateSplit(amount);
                }
            }

            function updateSplit(val) {
                const num = parseFloat(val) || 0;
                const local = (num * 0.40).toFixed(2);
                const barber = (num * 0.60).toFixed(2);
                const el = document.getElementById('split-preview');
                if (el) {
                    el.innerText = `Local: $${local} (40%) · Barbero: $${barber} (60%)`;
                }
            }

            function triggerToast(msg) {
                const toast = document.getElementById('status-toast');
                const msgEl = document.getElementById('toast-message');
                if (toast && msgEl) {
                    msgEl.innerText = msg;
                    toast.classList.remove('hidden');
                    setTimeout(() => {
                        toast.classList.add('hidden');
                    }, 3200);
                }
            }
        </script>
    </main>
    <nav class="fixed bottom-0 w-full z-50 pb-safe bg-surface/80 backdrop-blur-xl shadow-[0_1px_8px_rgba(0,0,0,0.04)]"
        data-active-classes="text-primary font-bold">
        <div class="flex items-center justify-around h-16 px-space-xs"><a aria-current="page"
                class="flex flex-col items-center justify-center min-w-[54px] min-h-[48px] px-1 transition-colors text-primary font-bold"
                data-path="panel" href="#"><span
                    class="material-symbols-outlined text-[22px]">dashboard</span><span
                    class="font-caption text-caption tracking-tight">Panel</span></a><a
                class="flex flex-col items-center justify-center min-w-[54px] min-h-[48px] px-1 text-on-surface-variant hover:text-on-surface transition-colors"
                data-path="cortes" href="#"><span
                    class="material-symbols-outlined text-[22px]">content_cut</span><span
                    class="font-caption text-caption tracking-tight">Cortes</span></a><a
                class="flex flex-col items-center justify-center min-w-[54px] min-h-[48px] px-1 text-on-surface-variant hover:text-on-surface transition-colors"
                data-path="barberos" href="#"><span
                    class="material-symbols-outlined text-[22px]">badge</span><span
                    class="font-caption text-caption tracking-tight">Barberos</span></a><a
                class="flex flex-col items-center justify-center min-w-[54px] min-h-[48px] px-1 text-on-surface-variant hover:text-on-surface transition-colors"
                data-path="cierres" href="#"><span
                    class="material-symbols-outlined text-[22px]">payments</span><span
                    class="font-caption text-caption tracking-tight">Cierres</span></a><a
                class="flex flex-col items-center justify-center min-w-[54px] min-h-[48px] px-1 text-on-surface-variant hover:text-on-surface transition-colors"
                data-path="ajustes" href="#"><span
                    class="material-symbols-outlined text-[22px]">settings</span><span
                    class="font-caption text-caption tracking-tight">Ajustes</span></a></div>
    </nav>
</body>

</html>
