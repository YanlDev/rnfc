# Guía de Desarrollo — Plataforma RNFC Gestión de Obras

Documento de referencia para mantener un avance coherente del proyecto. Léelo al inicio de cada sesión y actualízalo cuando una decisión cambie.

---

## 1. Contexto del proyecto

**Cliente:** RNFC Consultor de Obras (Sr. Roger Neptali Flores Coaquira).
**Objetivo:** Plataforma privada en la nube para centralizar documentación técnica, coordinar equipos y dar seguimiento a proyectos de supervisión y consultoría de obra.
**Alcance contratado:** 8 módulos base + landing institucional + dominio.
**Entrega:** 4–5 semanas + 1 mes de personalización sin costo.

---

## 2. Stack tecnológico

> **Cambio de arquitectura (2026-05-11):** se migró de Livewire/Flux a **Inertia.js + React 19**. El proyecto parte del starter kit oficial `laravel/react-starter-kit`. Toda decisión previa hecha bajo Livewire queda invalidada.

| Capa | Tecnología | Versión |
|---|---|---|
| Lenguaje | PHP | 8.4 |
| Framework backend | Laravel | 13.7 |
| Auth | Laravel Fortify | 1.34 |
| Puente SPA | Inertia.js (laravel + react) | 3.0 |
| Rutas tipadas | Laravel Wayfinder | 0.1.14 |
| Frontend | React | 19.2 |
| Lenguaje frontend | TypeScript | 5.7 |
| UI primitives | Radix UI (shadcn/ui) | varios |
| Iconos | lucide-react | 0.475 |
| Toasts | sonner | 2.0 |
| CSS | Tailwind | 4.1 |
| Bundler | Vite | 8.0 |
| Permisos | spatie/laravel-permission | _por instalar_ |
| Tests backend | Pest | 4.7 |
| Tests frontend / E2E | _pendiente decidir_ (Pest 4 `visit()` con Playwright o Vitest + React Testing Library) | — |
| BD desarrollo | PostgreSQL | 18.x |
| Almacenamiento dev | Local (`storage/app/private/documentos`) | — |
| Almacenamiento prod | **Bunny.net** (Edge Storage, S3-compatible) | — |
| CDN prod (opcional) | **Bunny.net** CDN | — |
| Servidor prod | VPS + Cloudflare | — |
| MCP de apoyo | laravel/boost, context7 | — |

### Dependencias React ya instaladas (starter kit)
`@inertiajs/react`, `@inertiajs/vite`, `@headlessui/react`, `@radix-ui/*` (dialog, dropdown-menu, select, tooltip, navigation-menu, etc.), `class-variance-authority`, `clsx`, `tailwind-merge`, `lucide-react`, `sonner`, `input-otp`, `@laravel/vite-plugin-wayfinder`, `babel-plugin-react-compiler`.

---

## 3. Estado actual del proyecto

> **Estado general (corte 2026-05-12):** **Los 8 módulos del plan completados** + landing pública + bonus (Certificados, Branding) entregados. Sólo quedan los pendientes contractuales no-módulo (§5b.2 a §5b.8). **81 feature tests Pest pasando** (267 aserciones). TypeScript estricto sin errores. **Almacenamiento prod: Bunny.net Edge Storage** (S3-compatible).

### Resumen

Plataforma operativa con stack **Laravel 13 + Inertia.js 3 + React 19 + PostgreSQL 18 + Tailwind 4 + shadcn/ui**.

- Auth completo (login, registro, password reset, 2FA, verificación de email) vía Fortify + páginas Inertia, todas traducidas al español y con marca RNFC.
- Configuración (perfil, seguridad, apariencia, **marca**: logo y firma).
- Layout base con sidebar colapsable, dropdown de usuario, toggle de tema, **encabezado con título y descripción dinámicos por página** (via `Component.layout` props y `setLayoutProps()`).
- Wayfinder genera rutas tipadas en `resources/js/routes/`.

### Módulos de negocio

| # | Módulo | Estado | Notas |
|---|---|---|---|
| 1 | Autenticación y Roles | ✅ Hecho | Spatie + 6 roles globales (Admin, Gerente General, Supervisor, Residente, Ingeniero, Invitado). 15 roles por obra. |
| 2 | Gestión de Obras | ✅ Hecho | CRUD + cards responsivas + mapa Leaflet + cascade delete (sin soft delete por decisión del cliente). |
| 3 | Equipo e Invitaciones | ✅ Hecho | Pivot `obra_user`, mail encolado, auto-attach al registrarse, vista global `/equipo`. |
| 4 | Gestor Documental | ✅ Hecho | Plantilla peruana de 17 grupos + carpetas anidadas (rename, eliminar, crear) + upload drag&drop con progreso + versionado raíz-como-actual + preview de PDF/imágenes. |
| 5 | Cuaderno de Obra Digital | ✅ Hecho | Dos cuadernos paralelos, numeración independiente, vista lista + calendario, upload de PDF de OSCE, soft delete. |
| 6 | Calendario y Cronograma | ✅ Hecho | Grid mensual con 8 tipos de evento coloreados, sidebar de próximos + vencidos, mini calendario en detalle de obra, vista global cruzando obras. |
| 7 | Notificaciones | ✅ Hecho | Campanita en sidebar header con badge de no leídas, dropdown con últimas 8, página completa `/notificaciones`. Triggers cableados: documento subido, asiento creado, certificado revocado, evento próximo a vencer (Scheduler diario `rnfc:notificar-vencimientos`). 5 clases de `Notification` con `ShouldQueue` enviando `database + mail`. |
| 8 | Panel de Administración | ✅ Hecho | `/admin` con KPIs cruzados, donut charts (shadcn/recharts), ranking de almacenamiento, top usuarios, feed de actividad reciente cross-módulo. |

### Módulos bonus (no estaban en el plan original, ya entregados)

| Módulo | Notas |
|---|---|
| **Certificados** | 7 tipos (Trabajo, Especialista, Residente, Supervisor, Capacitación, Participación, Servicios). PDF generado con DomPDF + plantilla A4 vertical. QR de verificación (endroid/qr-code) → URL pública. Hash SHA-256 de integridad. Preview en vivo en React + revocación. Página pública `/verificar/{codigo}`. |
| **Branding** | `/settings/branding` — subida de firma PNG del titular + 3 logos ISO. Almacenados en `storage/app/public/branding/`. Aparecen automáticamente en certificados. |
| **Landing institucional** | Single-page Blade + Tailwind con 6 secciones obligatorias (§5b.1). SEO completo (OG, Twitter, JSON-LD Organization/LocalBusiness, sitemap.xml, robots.txt). Formulario de contacto encolado con honeypot anti-spam y botón WhatsApp flotante. |

---

## 4. Arquitectura y decisiones clave

### 4.1 Puente Inertia (backend ↔ frontend)
- Controladores devuelven `Inertia::render('Modulo/Pagina', [...props])`. Sin JSON APIs salvo para uploads y descargas.
- Cada página vive en `resources/js/pages/{modulo}/{Pagina}.tsx` y exporta `default function Page(props) {}`.
- Layouts compartidos en `resources/js/layouts/`. Aplicar con `Page.layout = (page) => <AppLayout>{page}</AppLayout>` o el patrón persistent layout de Inertia.
- **Wayfinder es obligatorio** para navegar: `import { dashboard } from '@/routes'` y usar `dashboard.url()` / `dashboard()` en `<Link href={...}>`. No hardcodear strings de URL.
- Validación: Form Requests en backend. El error vuelve en `props.errors` y se renderiza con los componentes de `components/ui/form` (shadcn).
- Forms del frontend con `useForm` de `@inertiajs/react`. Para forms simples basta `router.post(...)`.

### 4.2 Modelo de permisos
- **Roles globales** (Spatie, enum `RolGlobal`): `admin`, `gerente_general`, `supervisor`, `residente`, `ingeniero`, `invitado`. **Admin y Gerente General** son administrativos (equivalentes). Helpers en el enum:
  - `RolGlobal::rolesAdministrativos()` → admin + gerente general.
  - `RolGlobal::rolesVisionGlobal()` → admin + gerente general + supervisor.
- **Rol por obra** (pivot `obra_user.rol_obra`, enum `RolObra`, 15 valores agrupados en 4 categorías):
  - **Dirección:** `supervisor_obra`, `residente_obra`, `jefe_oficina_tecnica`.
  - **Especialistas:** `especialista_calidad`, `especialista_ssoma`, `especialista_seguridad`, `especialista_ambiental`, `especialista_riesgos`, `especialista_bim`, `especialista_compatibilizacion`, `especialista_metrados_costos`, `especialista_valorizaciones`, `especialista_liquidaciones`.
  - **Apoyo:** `asistente`, `practicante`.
  - **Lectura:** `invitado`.
- **Policies de Laravel** son la única vía oficial. Patrón general: admin/gerente general ven todo; supervisor global también; el resto sólo si están vinculados via pivot.
- El frontend recibe `puedeAdministrar`/`puedeEscribir`/`puedeEliminar` por cada vista que lo necesita (calculado en el controlador, no via shared props).

### 4.3 Almacenamiento de archivos
- En desarrollo todo va a `storage/app/private/documentos`.
- En producción se cambia `DOCUMENTOS_DRIVER=s3` y se completan variables `BUNNY_*`. Cero cambios de código.
- **Proveedor:** [Bunny.net Edge Storage](https://bunny.net) (S3-compatible). El driver `s3` de Laravel funciona apuntando a `endpoint` regional de Bunny (`https://storage.bunnycdn.com` o `https://{region}.storage.bunnycdn.com`). Más económico que Wasabi y con CDN incluida.
- Estructura de rutas: `obras/{obra_id}/{carpeta_path}/{nombre_archivo_unico}`.
- Nombres únicos vía ULID; `nombre_original` se conserva en la BD.
- **CDN opcional**: si se activa Pull Zone de Bunny apuntando a la storage zone, se puede servir previews/imágenes públicas por CDN. Para documentos privados de obras seguimos sirviéndolos via controlador HTTP con autorización (no exponer URLs directas).

### 4.4 Versionado de documentos
**Patrón "raíz como actual":** la fila con `documento_padre_id IS NULL` siempre representa la **versión vigente**. Las versiones anteriores se guardan como filas hijas (`documento_padre_id = root.id`).

Flujo al subir nueva versión:
1. Snapshot del estado actual del documento raíz como nueva fila hija.
2. La fila raíz se actualiza con el nuevo archivo y `version + 1`.

Ventajas: listado simple (`WHERE documento_padre_id IS NULL`), cascada de eliminación natural, sin columnas extra.

### 4.5 Soft deletes
- Aplicado **sólo en `asientos_cuaderno` y `certificados`** (trazabilidad legal).
- **NO aplicado en `obras`** (decisión del cliente: eliminar una obra borra todo en cascada).
- NO aplicado en `carpetas`, `documentos`, `eventos_calendario`, `invitaciones`.
- Cascade delete en FKs: `obra_id` en carpetas, documentos, asientos, eventos, certificados, obra_user, invitaciones → `cascadeOnDelete()`.

### 4.6 Cuaderno de obra
- Numeración separada por `tipo_autor` (supervisor/residente). Constraint único `(obra_id, tipo_autor, numero)`.
- **El PDF descargado de OSCE es el archivo principal del asiento** — se sube como `archivo_path` (también acepta JPEG/PNG si es escaneo físico).
- Cálculo del siguiente número usa `withTrashed()`: asientos eliminados **no liberan su número** (auditoría legal).
- Vista doble: lista cronológica + grid mensual nativo de calendario.
- Modal de detalle con resumen a la izquierda + PDF embebido vía iframe a la derecha.
- Autorización:
  - Tab "Supervisor": admin, gerente general, supervisor (global o de la obra).
  - Tab "Residente": admin, gerente general, supervisor (global o de la obra), residente de la obra.
  - Otros roles: solo lectura.
  - **Solo admin/gerente general puede eliminar asientos** (trazabilidad legal).

### 4.7 Frontend (React + Inertia)
- **Components Library**: shadcn/ui sobre Radix. Los componentes viven en `resources/js/components/ui/`. Para añadir uno nuevo usar la CLI de shadcn (`npx shadcn@latest add ...`). `components.json` ya está configurado.
- **Estructura de carpetas**:
  - `resources/js/pages/` — páginas Inertia (rutas).
  - `resources/js/components/` — componentes reutilizables; `components/ui/` para primitives shadcn.
  - `resources/js/layouts/` — layouts persistentes.
  - `resources/js/hooks/` — hooks custom (`useAppearance`, `useMobileNavigation`, etc.).
  - `resources/js/lib/` — helpers puros (`cn()` con tailwind-merge).
  - `resources/js/types/` — tipos globales (`User`, `PageProps`, etc.).
  - `resources/js/routes/` y `resources/js/actions/` — generados por Wayfinder; **nunca editar a mano**.
- **Estado**: estado local con `useState`/`useReducer`. Para estado de servidor, los props de Inertia son la fuente de verdad — recargar con `router.reload({ only: [...] })` o `partial reload`. No introducir TanStack Query.
- **Tipado**: TypeScript estricto. Definir `interface PageProps` por página para tipar `usePage<PageProps>()`.
- **Tema**: dark mode vía `useAppearance` hook (system/light/dark, persistido en cookie).
- **Toasts**: `sonner` ya configurado; usar `toast.success(...)` / `toast.error(...)`.

### 4.8 Subida de archivos con UX visible
- Inertia soporta progreso de upload nativo: `router.post(url, data, { forceFormData: true, onProgress: (e) => setProgress(e.percentage) })`.
- O `useForm({ archivo: null }).post(url, { onProgress: ... })`.
- Validación browser-side con tipo/tamaño antes de POST (input `accept`, check `file.size`).
- Disco `documentos` con driver intercambiable local↔Wasabi sin cambios de código.
- Descarga y preview siempre vía controladores HTTP dedicados (no exponer rutas de Inertia para binarios).

### 4.9 Colas y correo
- `QUEUE_CONNECTION=database` por ahora. En producción evaluar Redis si crece volumen de notificaciones.
- Notificaciones por email SIEMPRE encoladas (`ShouldQueue`).
- En desarrollo, `MAIL_MAILER=log` — los correos van a `storage/logs/laravel.log`. En producción cambiar a `resend`.

### 4.10 Notificaciones in-app
- Tabla `notifications` estándar de Laravel.
- Endpoint Inertia que retorna las últimas N para la campanita; partial reload del prop `notifications` al abrir el dropdown.
- Marcado como leído vía `router.patch(...)`.

---

## 5. Hoja de ruta por módulo

### Núcleo
1. ✅ **Autenticación y Roles** — Spatie integrado, 6 roles globales sembrados, policies en uso (Obra, Carpeta, Documento, AsientoCuaderno, EventoCalendario, Certificado).
2. ✅ **Gestión de Obras** — CRUD con cards, filtros, búsqueda debounced, mapa Leaflet (OSM + Nominatim), cascade delete a documentos/carpetas/asientos/eventos/certificados.
3. ✅ **Equipo e Invitaciones** — pivot `obra_user` con 15 roles por obra (Supervisor, Residente, Jefe de OT, 10 Especialistas, Asistente, Practicante, Invitado), email encolado, auto-attach en `CreateNewUser`, vista global `/equipo` agrupada por obra.

### Documentos
4. ✅ **Gestor Documental** — plantilla con 17 grupos peruanos (selector con checkbox indeterminado por grupo + subcarpetas), CRUD de carpetas con rename recursivo de `ruta` y descendientes, dropzone moderno con progreso por archivo, versionado raíz-como-actual, preview de PDF en iframe e imágenes inline.

### Operación diaria
5. ✅ **Cuaderno de Obra Digital** — `TipoAutorCuaderno` enum (Supervisor/Residente), constraint único `(obra_id, tipo_autor, numero)`, **withTrashed** al calcular siguiente número (asientos eliminados no liberan su número), upload de PDF de OSCE, vista lista + vista calendario nativo, modal con resumen + PDF embebido, selector global `/cuaderno`.
6. ✅ **Calendario y Cronograma** — `TipoEvento` enum (Hito, Vencimiento, Reunión, Inspección, Entrega, Paralización, Reinicio, Otro) con colores de marca, grid mensual nativo con eventos multi-día, sidebar de próximos 14 días + vencimientos pasados, CRUD inline con diálogo, mini calendario en detalle de obra, vista global `/calendario`.
7. ✅ **Notificaciones** — campanita con badge de no leídas en `AppSidebarHeader`, dropdown con últimas 8 + link "Ver todas". Página `/notificaciones` con filtro Todas/No leídas y eliminar individual. 5 `Notification` classes (`DocumentoSubido`, `AsientoCuadernoCreado`, `EventoProximoAVencer`, `InvitacionRecibida`, `CertificadoRevocado`) todas con `ShouldQueue` y `via(['database', 'mail'])`. Comando Artisan `rnfc:notificar-vencimientos` programado diario en `routes/console.php`. Shared prop `notificacionesHeader` (no `notificaciones` para evitar colisión con prop de página).

### Cierre
8. ✅ **Panel de Administración** — `/admin` restringido a admin/gerente general. 15 KPIs cruzados, donut charts (recharts/shadcn) de estados de obras y certificados por tipo, bar chart de top 5 almacenamiento, ranking de usuarios activos, feed cross-módulo de actividad reciente con `diffForHumans()`.
9. ✅ **Landing institucional** — ver §5b.1 (entregada).
10. ⏳ **QA + deploy + capacitación** — pendiente, ver §5b.8.

### Bonus (no estaba en el plan original, entregado por pedido del cliente)
- ✅ **Certificados** — 7 tipos, PDF DomPDF A4 vertical, QR endroid, hash SHA-256, preview en vivo en React (con qrcode.react), revocación, verificación pública.
- ✅ **Marca** — `/settings/branding` para subir firma y 3 logos ISO, integrados al PDF de certificado.

---

## 5b. Entregables contractuales no-módulo

Estos puntos están comprometidos en la cotización (firmada con el cliente) pero no son módulos de la plataforma. Hay que ejecutarlos sí o sí antes de la entrega final.

### 5b.1 Landing institucional (cotización §V) — ✅ Hecho
Sitio público en `/` (Blade + Tailwind, sin Inertia). Las 6 secciones obligatorias están en single-page con anclas. Pendiente: reemplazar contenido placeholder (proyectos, números de WhatsApp, fotos reales) con material real del cliente.

| Sección | Contenido |
|---|---|
| Inicio | Hero con propuesta de valor de RNFC, accesos rápidos (login, contacto). |
| Nosotros | Reseña institucional, misión, visión, trayectoria del Ing. Roger Neptali Flores Coaquira. |
| Servicios | Consultoría en arquitectura, ingeniería y supervisión de obras (descripción detallada). |
| Proyectos / Trayectoria | Galería con imágenes, ubicación, rol desempeñado, resultados. Diseñar como grid responsive. |
| Experiencia Profesional | Entidades contratantes, reconocimientos, certificaciones. |
| Contacto | Formulario (nombre, correo, mensaje) + datos de ubicación + correo institucional + botón directo a WhatsApp. |

**Stack:** Blade + Tailwind. Sin Inertia/React para mantenerla ligera y SEO-friendly. Vive en `routes/web.php` como rutas públicas (`/`, `/nosotros`, `/servicios`, etc.) o como single-page con anclas.

**SEO on-page obligatorio (cotización §V):**
- Meta tags (`<title>`, `<meta description>`, Open Graph, Twitter Card) por página.
- `sitemap.xml` y `robots.txt` generados.
- Schema.org `Organization` + `LocalBusiness` en JSON-LD.
- URLs limpias, encabezados jerárquicos (H1 único por página).
- Imágenes con `alt` descriptivo y formato moderno (WebP/AVIF).

### 5b.2 Dominio + correo corporativo (cotización §V, §VI-A) — ⏳ Pendiente
- Registrar dominio (opciones sugeridas: `rnfcconsultoria.com`, `rnfcconsultordeobras.com`, etc. — verificar disponibilidad antes).
- Dominio queda a nombre del cliente (cotización §V).
- Configurar **correo electrónico corporativo** asociado (`contacto@rnfcconsultoria.com` u equivalente). Opciones: Zoho Mail Free, Cloudflare Email Routing → Gmail, o buzón en el VPS (no recomendado por reputación de IP).
- Configurar SPF, DKIM, DMARC para que el correo no caiga en spam.
- Certificado SSL vía Let's Encrypt o Cloudflare (HTTPS obligatorio).

### 5b.3 Backups automáticos (cotización §X) — ⏳ Pendiente
- **Backup diario de BD PostgreSQL.** Usar `spatie/laravel-backup` con tarea programada en `routes/console.php`:
  ```php
  Schedule::command('backup:clean')->daily()->at('01:00');
  Schedule::command('backup:run')->daily()->at('01:30');
  ```
- Destino: **storage zone Bunny.net separada** (`rnfc-backups`) para no mezclar con documentos de obras.
- Retención: 30 backups diarios + 12 mensuales.
- Notificación por correo al admin si un backup falla.
- Archivos de documentos: redundancia garantizada por Bunny.net (replicación geo configurable a nivel de storage zone).

### 5b.4 Comando de export de datos del cliente (cotización §XI — titularidad) — ⏳ Pendiente
- Comando Artisan `php artisan rnfc:export-cliente`:
  - Dump SQL de la BD.
  - Copia completa de la storage zone de Bunny.net (documentos + asientos + adjuntos) a un archivo `.zip` o directorio descargable.
  - Inventario en CSV: obras, documentos, asientos, eventos.
- Documentar en README cómo entregar la información si el cliente termina el servicio.

### 5b.5 Capacitación inicial (cotización §VI-A) — ⏳ Pendiente
- **Sesiones virtuales necesarias** (sin tope explícito — recomendable acotarlo en correo al cliente: ej. hasta 3 sesiones de 1 hora).
- Material mínimo:
  - Manual de usuario PDF por rol (admin, supervisor, residente, ingeniero).
  - Videos cortos (Loom) por flujo clave: crear obra, invitar equipo, subir documento, registrar asiento.
  - Agenda de capacitación firmable (acta de capacitación al final).

### 5b.6 Modalidad NAS alternativa (cotización §VI-B) — ⏳ Pendiente (sólo si el cliente lo solicita)
- El cliente puede solicitar usar NAS propio en lugar de Bunny.net (reduce el plan mensual a S/ 25).
- Estrategia: el disco `documentos` apunta a un punto de montaje SMB/NFS del NAS desde el VPS. Driver `local` de Laravel con ruta apuntando al mount.
- Documentar comando de migración Bunny.net → NAS (con verificación de hash) por si el cambio se solicita después del go-live.

### 5b.7 Roles — "Ingenieros especialistas" (cotización §III) — ✅ Hecho
Resuelto vía expansión del enum `RolObra` con 10 categorías de especialista (Calidad QA/QC, SSOMA, Seguridad y Salud Ocupacional, Ambiental, Gestión de Riesgos, BIM, Compatibilización, Metrados y Costos, Valorizaciones, Liquidaciones) + Asistente + Practicante + dirección (Supervisor, Residente, Jefe de OT). El rol por obra muestra automáticamente la categoría en la UI de equipo.

### 5b.8 QA y entrega — ⏳ Pendiente
- **2 días hábiles de pruebas y ajustes** (cotización §VIII).
- Checklist de aceptación firmable por el cliente, agrupado por módulo. Sin este checklist firmado, el segundo 50% del pago no se cobra correctamente.
- Deploy a VPS + apuntar dominio + Cloudflare + SSL + smoke test.

---

## 6. Convenciones de código

### 6.1 PHP
- PHP 8 constructor property promotion siempre.
- Tipos en parámetros y retornos siempre.
- Enum `TitleCase` (ej. `RolObra::Supervisor`).
- PHPDoc sobre comentarios inline. Inline solo para lógica no obvia.

### 6.2 Laravel
- `php artisan make:*` para crear archivos. Nada de stubs manuales si Artisan tiene el comando.
- Form Requests para validación, no validación inline.
- Policies para autorización, no `if ($user->role)` en controllers.
- Factories y seeders para todo modelo.
- Rutas siempre nombradas. Después de crear/cambiar rutas correr el build de Wayfinder (`npm run dev` lo hace en vivo) para regenerar `resources/js/routes/`.

### 6.3 React / TypeScript
- Componentes funcionales con tipos explícitos en props (`type Props = {...}`).
- Hooks custom van a `resources/js/hooks/`.
- Para forms: `useForm` de Inertia. Para validación cliente puramente UI, lógica simple inline; no añadir Zod/Yup salvo necesidad real.
- Imports absolutos con alias `@/` (configurado en `tsconfig.json` y `vite.config.ts`).
- Estilos: Tailwind utility-first + variantes con `cva` (class-variance-authority) cuando un componente tenga variantes.
- **No usar `any`.** Si Inertia devuelve algo desconocido, tipar el prop.
- **React Compiler** está activo (babel-plugin); no usar `useMemo`/`useCallback` manualmente salvo que el compilador no pueda inferirlo.

### 6.4 Inertia
- Props de página tipados con un `interface` exportado desde el archivo de la página.
- Partial reloads: `router.reload({ only: ['key'] })` para refrescar solo un prop.
- Shared props (auth, flash, errors) configuradas en `app/Http/Middleware/HandleInertiaRequests.php`.
- Para acciones destructivas usar `router.delete(url, { onBefore: () => confirm(...) })`.

### 6.5 Tests
- **Backend (Pest)**: cada feature requiere al menos un feature test. `RefreshDatabase` en tests que tocan BD. Naming: `it can create an obra`. Carpeta `tests/Feature/{Modulo}/`.
- **Inertia tests**: usar `assertInertia(fn (Assert $page) => $page->component('Obras/Index')->has('obras', 3))`.
- **Frontend**: decidir cuando se llegue al módulo 4. Opciones: Pest 4 browser tests (`visit()`) para flujos críticos E2E, o Vitest + React Testing Library para unitarios de componentes complejos.

### 6.6 Naming
- **Tablas en español plural:** `obras`, `carpetas`, `documentos`. Tablas pivot en singular juntas (`obra_user`).
- **Modelos en español singular:** `Obra`, `Carpeta`.
- **Rutas:** `/obras`, `/obras/{obra}/documentos`.
- **Páginas React:** `PascalCase`, agrupadas por módulo: `pages/obras/Index.tsx`, `pages/obras/Detalle.tsx`.
- **Variables PHP:** `camelCase` en español (`fechaInicio`, `montoContractual`).
- **Variables TS:** `camelCase`, idealmente en español para el dominio (`fechaInicio`), inglés para utilidades técnicas (`isLoading`).

---

## 7. Flujo de trabajo

### 7.1 Antes de empezar una feature
1. Revisar este documento — ¿afecta la decisión global? Actualizar.
2. Migración → Modelo → Factory → Seeder → Policy → Test → Controlador (Inertia) → Página React.

### 7.2 Antes de hacer commit
1. `vendor/bin/pint --dirty` (formato PHP).
2. `npm run lint` y `npm run format` (formato JS/TS).
3. `npm run types:check` (TS).
4. `php artisan test --compact` (tests).
5. Mensaje de commit descriptivo en español.

### 7.3 Convención de commits
```
add: gestión CRUD de obras
update: validación de fecha_fin en EventoCalendario
fix: error de N+1 en listado de documentos
refactor: extraer lógica de invitación a action
test: cobertura de policy de cuaderno
```

---

## 8. Comandos útiles

```powershell
# Setup inicial
composer install
npm install
copy .env.example .env
php artisan key:generate
php artisan migrate --seed
npm run dev

# Día a día
composer run dev              # serve + queue + vite en paralelo (concurrently)
npm run dev                   # solo vite (HMR + Wayfinder watcher)
php artisan test --compact
php artisan test --filter=ObraTest --compact
vendor/bin/pint --dirty
npm run lint
npm run format
npm run types:check

# Frontend
npx shadcn@latest add <componente>   # agregar primitive shadcn
npm run build                         # build producción
npm run build:ssr                     # build con SSR (si se activa)

# Inspección
php artisan route:list --except-vendor
php artisan config:show database.default
php artisan about
```

---

## 9. Variables de entorno críticas

| Variable | Local | Producción |
|---|---|---|
| `APP_ENV` | local | production |
| `APP_DEBUG` | true | false |
| `DB_CONNECTION` | pgsql | pgsql |
| `DOCUMENTOS_DRIVER` | local | s3 |
| `BUNNY_ACCESS_KEY_ID` | vacía | API Key de la storage zone |
| `BUNNY_SECRET_ACCESS_KEY` | vacía | API Key (misma que arriba en Bunny) |
| `BUNNY_BUCKET` | vacía | nombre de la storage zone (ej. `rnfc-documentos`) |
| `BUNNY_ENDPOINT` | vacía | `https://storage.bunnycdn.com` o regional (`ny`, `la`, `sg`, `syd`) |
| `BUNNY_DEFAULT_REGION` | vacía | `us-east-1` (cualquiera, Bunny lo ignora pero el SDK lo requiere) |
| `BUNNY_CDN_URL` | vacía | URL pública de la Pull Zone (opcional, sólo si se usa CDN) |
| `MAIL_MAILER` | log | resend / smtp |
| `QUEUE_CONNECTION` | database | redis (si escala) |
| `SESSION_DRIVER` | database | database o redis |

---

## 10. Riesgos y mitigaciones

| Riesgo | Mitigación |
|---|---|
| Gestor Documental se atrasa | Semana completa dedicada. No empezar otros módulos hasta cerrarlo. |
| Permisos finos por obra | Policies + tests por cada acción crítica. Exponer flags en `props.auth.permissions`. |
| Pérdida de archivos al migrar a Bunny.net | Comando Artisan idempotente para mover `local`→`s3` con verificación de hash. |
| Latencia previsualización PDF | Cache de miniaturas en `storage/app/public/previews/`. |
| Cliente pide cambios masivos durante personalización | Documentar pedidos con criterios de aceptación; priorizar antes de codear. |
| Bundle JS crece mucho | Code-splitting por página ya lo hace Inertia. Vigilar tamaño en `npm run build`. |
| Rutas tipadas desactualizadas tras cambiar `routes/web.php` | El watcher de Wayfinder regenera; si falla, `npm run dev` reinicia. Nunca editar `resources/js/routes/` a mano. |
| Backups fallan silenciosamente | `spatie/laravel-backup` con notificación por correo al admin si un backup falla; revisar mensual. |
| Correo corporativo cae en spam | Configurar SPF + DKIM + DMARC desde el día 1. Probar con mail-tester.com antes de entregar. |
| "Personalización ilimitada" del mes sin costo se desborda | Enviar al cliente al inicio del mes la lista cerrada de cambios acordados (correo). Funcionalidades nuevas → cotización aparte. |
| Cliente pide migrar a NAS después del go-live | Comando de migración Bunny.net→NAS con verificación de hash, idempotente. |

---

## 11. Próxima sesión

Estado actual: **Los 8 módulos del plan completados + landing + bonus de certificados/branding/notificaciones. 81 tests Pest pasando.**

### Pendiente para entrega final (§5b)

1. **§5b.2 Dominio + correo corporativo** — registrar dominio, configurar SPF/DKIM/DMARC, SSL.
2. **§5b.3 Backups automáticos** — instalar `spatie/laravel-backup`, configurar `routes/console.php` + storage zone Bunny.net `rnfc-backups`.
3. **§5b.4 Comando export de datos** — `php artisan rnfc:export-cliente` (dump SQL + archivos + CSV inventario).
4. **§5b.5 Capacitación** — material (manuales PDF por rol, videos Loom, acta firmable).
5. **§5b.8 QA + deploy** — checklist firmable, deploy a VPS, dominio, Cloudflare, SSL, smoke test. **Crear storage zone Bunny.net y configurar `BUNNY_*` env vars.**

### Pulido sugerido antes de QA
- Reemplazar contenido placeholder de la landing (proyectos reales, fotos, número WhatsApp, descripción institucional final).
- Acta de capacitación firmable como template Blade.
- Tests E2E con Pest 4 `visit()` para los flujos críticos (login → crear obra → subir documento → emitir certificado).
- Pulir empty states con CTAs claros donde aún se usan textos genéricos.

### Decisiones del cliente registradas durante el desarrollo
- **Sin soft delete en obras** — eliminar una obra elimina en cascada documentos, carpetas, asientos, eventos y certificados.
- **Cuaderno de obra**: el PDF descargado de OSCE es el archivo principal del asiento; vista lista + calendario.
- **Plantilla de carpetas** sin prefijos numéricos (orden por array, no por nombre). Carpetas renombrables con propagación recursiva de `ruta`.
- **Rol "Gerente General"** equivalente a admin a nivel global. 15 roles por obra (no sólo 4).
- **Certificados** completamente off-script — pedido del cliente como prioridad inicial; entregado con preview en vivo, QR, hash y verificación pública.
- **Almacenamiento en Bunny.net** (no Wasabi) — Edge Storage S3-compatible más económica y con CDN integrada. El cambio sólo afecta variables de entorno, no código.

---

_Última actualización: 2026-05-12 (todos los módulos 1-8 + landing + certificados + branding + notificaciones completos; 81 tests Pest pasando. Almacenamiento prod: Bunny.net Edge Storage)._
