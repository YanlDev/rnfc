# RNFC — Plan de Mejora Integral

> Documento de análisis y hoja de ruta para la evolución del sistema RNFC.
> Basado en auditoría de roles, permisos, seguridad, arquitectura y experiencia de usuario.

---

## 1. Problemas Identificados

### 1.1 Roles y Permisos

| # | Problema | Severidad | ¿Qué pasa hoy? |
|---|----------|-----------|----------------|
| P1 | **Admin = Gerente General** | 🔴 Crítica | Ambos roles tienen exactamente los mismos permisos. `rolesAdministrativos()` y `rolesVisionGlobal()` son el mismo método. No hay separación de responsabilidades. |
| P2 | **No se usan permisos Spatie** | 🟠 Alta | Spatie Permission está instalado pero solo se usan roles. No hay `$user->hasPermissionTo()`, solo `$user->hasAnyRole()`. La autorización es estática y vive en código, no en BD. |
| P3 | **17 roles de obra planos** | 🟡 Media | `RolObra` tiene 17 valores en un solo enum. Todos los especialistas (10 variantes) + JefeTécnico + Asistente + Practicante tienen **exactamente los mismos permisos**. La diferenciación es solo semántica. |
| P4 | **IDOR parcial** | 🟠 Alta | Varios controladores verifican pertenencia con `abort_unless($x->obra_id === $obra->id)` en vez de usar Policies. Patrón propenso a errores. |
| P5 | **Sin permisos granulares en frontend** | 🟡 Media | El sidebar muestra enlaces a Certificados, Equipo, Admin a **todos** los usuarios. Algunos devuelven 403 al hacer clic. Mala UX — la navegación debería ocultarse, no fallar. |

### 1.2 Arquitectura

| # | Problema | Severidad | ¿Qué pasa hoy? |
|---|----------|-----------|----------------|
| P6 | **Contraseña admin hardcodeada** | 🟠 Alta | `admin@rnfc.test` / `rnfc2026` está fija en `DatabaseSeeder.php`. |
| P7 | **Sin rate limiting** | 🟡 Media | Solo `password.update` tiene throttle. Login y registro no tienen protección de fuerza bruta. |
| P8 | **Sin tests de escalamiento** | 🟡 Media | No hay pruebas que verifiquen que un rol X no puede escalar privilegios. |
| P9 | **Sin tests unitarios de servicios** | 🟡 Media | Todo servicio (`DocumentoService`, `CarpetaService`, etc.) no tiene tests aislados. |

### 1.3 UI / UX

| # | Problema | Severidad | ¿Qué pasa hoy? |
|---|----------|-----------|----------------|
| P10 | **Interfaz genérica "shadcn/default"** | 🟡 Media | Cards, colores, tipografía son el skin por defecto de shadcn/ui. Sin identidad de marca RNFC. |
| P11 | **Tipografía inadecuada** | 🟡 Media | Se usa Inter (400-700) para body y Barlow (500-900) para títulos. El usuario reporta que el tamaño de letra es grande por la fuente. Inter a 16px en pantallas grandes se siente pesado. |
| P12 | **Cards de obra sin personalidad** | 🟢 Baja | La card de obra tiene un diseño genérico: borde, sombra, banda de color de estado. Sin diferenciación visual entre obras. |
| P13 | **Navegación no personalizada por rol** | 🟡 Media | El sidebar es igual para todos. Un Asistente ve "Certificados" en el menú aunque no pueda acceder. |
| P14 | **Dashboard único para todos** | 🟡 Media | Todos los usuarios ven el mismo dashboard con KPIs genéricos. Un Residente debería ver sus obras asignadas, documentos recientes, actividad de su equipo. |

---

## 2. Estado Ideal (Target Architecture)

### 2.1 RBAC con Spatie Permission + Panel Dinámico

```
                    ┌──────────────────────────┐
                    │   Panel Admin de Permisos │
                    │  (UI para gestionar roles │
                    │   y asignar permisos)      │
                    └──────────┬───────────────┘
                               │
         ┌─────────────────────┼─────────────────────┐
         │                     │                     │
    ┌────▼─────┐        ┌─────▼─────┐         ┌─────▼─────┐
    │  Roles   │        │ Permisos  │         │ Usuarios  │
    │ Globales │───┐    │ planos    │         │           │
    └──────────┘   │    └───────────┘         └───────────┘
                   │
                   │    ┌───────────────────────────────┐
                   └───►│  Role ─── tiene muchos ───► Permissions │
                        │  User ─── tiene un ───────► Role        │
                        │  User ─── pertenece a ───► Obra (pivot) │
                        └───────────────────────────────┘
```

**Permisos planos (~20):**

```
admin.acceder
obra.ver, obra.crear, obra.editar, obra.eliminar
documento.ver, documento.subir, documento.eliminar
cuaderno.ver, cuaderno.escribir, cuaderno.eliminar
calendario.ver, calendario.crear, calendario.editar
certificado.ver, certificado.emitir, certificado.revocar
equipo.ver, equipo.gestionar
usuario.ver, usuario.gestionar
```

**Roles con sets de permisos:**

| Rol Global | Permisos |
|------------|----------|
| Admin | Todos |
| Gerente General | Todos excepto `usuario.gestionar`, `obra.eliminar` |
| Residente | `obra.ver`, `documento.ver`, `documento.subir`, `cuaderno.ver`, `cuaderno.escribir`, `calendario.ver` |
| Jefe de Oficina Técnica | Ídem Residente |
| Especialista | `obra.ver`, `documento.ver`, `documento.subir`, `cuaderno.ver`, `calendario.ver` |
| Asistente | `obra.ver`, `documento.ver`, `documento.subir`, `cuaderno.ver` |
| Practicante | `obra.ver`, `documento.ver`, `cuaderno.ver` |
| Invitado | `obra.ver` |

### 2.2 Roles de obra simplificados

```
RolesObra (reducido):
├── AdministradorObra    → gestión total de la obra
├── ResidenteObra        → supervisión + escritura cuaderno
├── JefeOficinaTecnica   → supervisión técnica
├── Especialista         → + especialidad (campo adicional)
├── Asistente            → + área opcional
├── Practicante          → solo lectura parcial
└── Invitado             → solo lectura
```

El campo `especialidad` se añade al pivot `obra_user` como nullable. Cuando `rol_obra = especialista`, se requiere seleccionar la especialidad. Esto evita los 17 valores planos.

### 2.3 Paneles personalizados por rol

Cada usuario ve un dashboard adaptado:

| Rol | Dashboard |
|-----|-----------|
| Admin | KPIs globales, usuarios, certificados, obras |
| Gerente General | Ídem Admin (sin gestión de usuarios) |
| Residente | Obras asignadas, documentos recientes, actividad, cuaderno |
| Ingeniero/Especialista | Obras asignadas, documentos, calendario |
| Asistente/Practicante | Obras asignadas, documentos |
| Invitado | Solo obras compartidas |

### 2.4 Sidebar dinámico

Los items del sidebar se renderizan según permisos del usuario:

```tsx
const sidebarItems = [
  { title: 'Dashboard', href: '/dashboard', permiso: null },
  { title: 'Obras', href: '/obras', permiso: 'obra.ver' },
  { title: 'Certificados', href: '/certificados', permiso: 'certificado.ver' },
  { title: 'Equipo', href: '/equipo', permiso: 'equipo.ver' },
  { title: 'Admin', href: '/admin', permiso: 'admin.acceder' },
  { title: 'Usuarios', href: '/admin/usuarios', permiso: 'usuario.ver' },
].filter(item => !item.permiso || permisos[item.permiso]);
```

---

## 3. Mejoras de UX/UI

### 3.1 Tipografía

**Problema:** Inter a 16px se siente grande y pesado. La app se ve "amplia" sin elegancia.

**Propuesta:**

- **Body:** Cambiar de Inter a **DM Sans** o **Onest** — son más compactas, modernas y profesionales
  - `DM Sans`: pesos 400, 500 — excelente legibilidad, más ajustada que Inter
  - `Onest`: similar a Inter pero más condensada verticalmente
- **Títulos:** Mantener **Barlow** (600-800) pero reducir tracking y tamaño en un escalón
- **Tamaño base:** Reducir de `16px` a `15px` (o `text-[0.9375rem]`), que es el estándar de apps profesionales (Linear, Notion, Height)

**Antes:**
```css
body { font-family: 'Inter', sans-serif; font-size: 16px; }
```
**Después:**
```css
body { font-family: 'DM Sans', sans-serif; font-size: 0.9375rem; }
```

### 3.2 Cards de Obra — Rediseño

**Problema:** Las cards de obra son genéricas shadcn/ui: borde gris, sombra suave, banda de color de estado.

**Propuesta** — Diseño tipo "board" / kanban inspirado en Linear y Notion:

```
┌──────────────────────────────────────────┐
│ [Verde] ● EN EJECUCIÓN        ⋮ (menú)   │
│                                          │
│  OBR-2026-0001                           │
│  # Mejoramiento de la Carretera Central  │
│                                          │
│  🏢  MTC Lima   📍 Lima, Perú           │
│  📅 15/01/2026 — 15/01/2028             │
│  💰 PEN 12,500,000.00                    │
│                                          │
│  ┌──────┐ ┌──────┐ ┌──────┐ ┌─────────┐ │
│  │ 📄 24│ │ 📓 12│ │ 📅 8 │ │ 👥 6    │ │
│  │ Docs │ │ Cuad │ │ Cal  │ │ Equipo  │ │
│  └──────┘ └──────┘ └──────┘ └─────────┘ │
│                                          │
│  [Avatar 1] [Avatar 2] [Avatar 3] +3    │
└──────────────────────────────────────────┘
```

**Mejoras concretas:**

- **Estado como dot + label** (no banda lateral) — más limpio
- **Avatar stack** del equipo asignado — da calidez humana
- **Métricas compactas** con iconos + contadores — escaneables visualmente
- **Código de obra** estilizado como badge/etiqueta tipo "issue" (mono, small, uppercase)
- **Hover:** no solo subir, sino un sutil cambio de fondo y border-color (usar primary-200)
- **Animación:** transición suave en hover, nada brusco
- **Responsive:** en mobile, tarjetas full-width; en desktop, grid de 2-3 columnas

### 3.3 Paleta de colores — Menos corporativa, más profesional

**Problema:** `#145694` (azul oscuro) es el típico azul corporativo de banca/seguros.

**Propuesta:**

| Rol | Color actual | Propuesto |
|-----|-------------|-----------|
| Primary | `#145694` | `#1a1a2e` (negro azulado profundo) o `#0f172a` (slate-900) |
| Secondary | `#dee1ec` | `#f1f5f9` (slate-100) |
| Accent | `#ffd21c` (amarillo) | Mantener o `#6366f1` (indigo-500) más moderno |
| Success | `#5da235` | Mantener |
| Background | blanco | `#fafafa` (off-white, menos frío) |

Un enfoque más moderno: **modo oscuro por defecto** con modo claro como alternativa. Esto le daría personalidad inmediata.

### 3.4 Sidebar — Más delgada y con mejor jerarquía

**Problema:** Sidebar de 16rem (256px) es ancha, y todos los items tienen el mismo peso visual.

**Propuesta:**
- Reducir a `14rem` (224px)
- Agrupar items por sección con labels sutiles: "General", "Gestión", "Administración"
- Iconos consistentes (line icons, stroke-width 1.5)
- Active state más visible (background tint + left border accent)
- Ocultar items que el usuario no pueda usar (no mostrarlos y que den 403)

### 3.5 Dashboard personalizado

**Dashboard de Admin** (estilo actual pero mejorado):
- Cards de KPI con iconos y variante de color cada uno
- Gráfico de obras por estado (donut)
- Tabla de actividad reciente con avatares
- Top usuarios activos con barra de contribución

**Dashboard de Residente:**
- "Mis obras" con mini-cards compactas
- Últimos documentos subidos a mis obras
- Últimos asientos de cuaderno
- Calendario con eventos próximos (mini vista)

**Dashboard de Especialista:**
- Obras donde participo
- Documentos pendientes de revisión
- Calendario de hitos técnicos

**Dashboard de Invitado:**
- Solo lista de obras compartidas con acceso rápido

### 3.6 Micro-interacciones y detalles

| Mejora | Descripción |
|--------|-------------|
| **Loading states** | Skeletons con shimmer animation en vez de spinners genéricos |
| **Transiciones de página** | Fade suave entre páginas (Inertia progress bar + CSS transition) |
| **Empty states** | Ilustración + mensaje + CTA en vez de solo texto |
| **Tooltips contextuales** | En badges de estado, tooltip con descripción del estado |
| **Breadcrumbs dinámicos** | Que reflejen la ruta real dentro del módulo |
| **Notificaciones en tiempo real** | Pusher/Laravel Echo para toast sin recargar |
| **Avatar initials** | Fallback con iniciales + color determinístico por email |

---

## 4. Plan de Implementación por Fases

### Fase 1 — Seguridad y RBAC (días 1-5)

| Tarea | Esfuerzo | Dependencias |
|-------|----------|--------------|
| Definir permisos planos (~20) y migración | 4h | — |
| Migrar Policies de `hasAnyRole()` a `hasPermissionTo()` | 8h | Permisos definidos |
| Crear seeder de permisos + asignación a roles | 2h | Roles existentes |
| Diferenciar Admin vs Gerente General | 4h | Permisos listos |
| Mover contraseña admin a `.env` | 1h | — |
| Añadir rate limiting a login/registro | 2h | — |
| Eliminar verificaciones IDOR manuales | 4h | — |

### Fase 2 — Roles de obra y UX (días 6-8)

| Tarea | Esfuerzo | Dependencias |
|-------|----------|--------------|
| Reducir `RolObra` a 7 roles + migración de datos | 6h | Fase 1 |
| Crear campo `especialidad` en pivot + modelo | 3h | Roles reducidos |
| Actualizar frontend con select anidado | 6h | Campo especialidad |
| Actualizar Policies de obra | 2h | Roles simplificados |

### Fase 3 — UI/UX completa (días 9-14)

| Tarea | Esfuerzo | Dependencias |
|-------|----------|--------------|
| Cambiar tipografía (Inter → DM Sans) | 2h | — |
| Rediseñar card de obra | 6h | — |
| Sidebar dinámico por permisos | 4h | Fase 1 |
| Dashboard personalizado por rol | 8h | Fase 1 |
| Refinar paleta de colores | 3h | — |
| Micro-interacciones y estados vacíos | 4h | — |

### Fase 4 — Testing (días 15-16)

| Tarea | Esfuerzo | Dependencias |
|-------|----------|--------------|
| Tests de matriz de acceso con permisos Spatie | 8h | Fase 1 |
| Tests de escalamiento vertical de privilegios | 4h | Fase 1 |
| Tests unitarios de servicios | 6h | — |
| Tests de EventoCalendarioPolicy | 3h | — |

---

## 5. Riesgos y Mitigaciones

| Riesgo | Probabilidad | Impacto | Mitigación |
|--------|-------------|---------|------------|
| Panel dinámico de permisos mal configurado por admin | Media | Alto | Guardrails: Admin siempre tiene todos los permisos. Roles predefinidos con presets. Log de cambios. |
| Migración de datos de roles de obra (17→7) | Media | Alto | Rollback via migration. Pruebas en staging con datos reales primero. |
| Cambio de tipografía rompe layouts | Baja | Medio | Revisar todas las páginas críticas. A/B testing visual. |
| Sidebar dinámico oculta rutas a las que el usuario SÍ debería acceder | Baja | Alto | Los items del sidebar se basan en los mismos permisos que las Policies. Coherencia garantizada. |

---

## 6. Conclusión

RNFC es una aplicación con bases sólidas (Laravel 13, React 19, arquitectura limpia) pero que arrastra deuda técnica de su origen como starter kit genérico. Los principales problemas son:

1. **RBAC incompleto** — Spatie infrautilizado, roles duplicados, permisos estáticos en código
2. **UX genérica** — shadcn/ui default sin identidad RNFC
3. **Seguridad mejorable** — rate limiting ausente, IDOR parcial, contraseña hardcodeada
4. **Testing incompleto** — faltan tests de escalamiento y unitarios

Con las fases propuestas (~16 días de trabajo), la aplicación pasaría de ser un "sistema funcional genérico" a una "plataforma profesional con identidad propia, segura y escalable".
