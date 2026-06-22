import {
    Award,
    Building,
    Building2,
    Calendar,
    Check,
    Droplets,
    FileText,
    LayoutGrid,
    Landmark,
    MonitorSmartphone,
    NotebookPen,
    Route,
    ShieldCheck,
    UserCog,
    Users,
    Zap,
} from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { Eyebrow, Reveal, Section } from '@/components/landing/primitives';

/* ============================ SERVICIOS (claro) ============================ */

const SERVICIOS: {
    titulo: string;
    desc: string;
    items: string[];
    Icono: LucideIcon;
}[] = [
    {
        titulo: 'Supervisión de obras',
        desc: 'Control técnico, avance físico, valorizaciones, inspecciones y conformidades de obra.',
        items: [
            'Control técnico',
            'Avance físico',
            'Valorizaciones',
            'Inspecciones',
        ],
        Icono: Building2,
    },
    {
        titulo: 'Gestión documental',
        desc: 'Expedientes digitales, trazabilidad, control de versiones y firmas auditables.',
        items: [
            'Expedientes digitales',
            'Trazabilidad',
            'Control documental',
            'Versionado',
        ],
        Icono: FileText,
    },
    {
        titulo: 'QA/QC y SSOMA',
        desc: 'Gestión de calidad, seguridad, medio ambiente y resolución de no conformidades.',
        items: ['Calidad', 'Seguridad', 'Medio ambiente', 'No conformidades'],
        Icono: ShieldCheck,
    },
    {
        titulo: 'Plataforma digital',
        desc: 'Seguimiento en tiempo real, reportes, alertas y control administrativo de obras.',
        items: ['Tiempo real', 'Reportes', 'Alertas', 'Control admin.'],
        Icono: MonitorSmartphone,
    },
];

export function Servicios() {
    return (
        <Section id="servicios" tone="light" className="py-16 md:py-28">
            <div className="bg-blueprint-light mask-radial absolute inset-0 opacity-70" />
            <div className="relative mx-auto max-w-7xl px-4 md:px-8">
                <Reveal className="mx-auto max-w-2xl text-center">
                    <Eyebrow center tone="light">
                        Qué hacemos
                    </Eyebrow>
                    <h2 className="mt-5 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl md:text-4xl">
                        Servicios técnicos integrados{' '}
                        <span className="text-slate-400">
                            en una sola operación.
                        </span>
                    </h2>
                    <p className="mt-5 text-base text-slate-500 md:text-lg">
                        Supervisión, control documental, calidad, seguridad y
                        plataforma digital — operando bajo procesos
                        certificados.
                    </p>
                </Reveal>

                <div className="mt-14 grid gap-6 md:grid-cols-2 lg:grid-cols-4">
                    {SERVICIOS.map((s, i) => (
                        <Reveal
                            key={s.titulo}
                            delay={i * 80}
                            className="svc-card rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                        >
                            <span className="inline-flex size-12 items-center justify-center rounded-xl bg-blue-600/10 text-blue-700">
                                <s.Icono className="size-6" />
                            </span>
                            <h3 className="mt-5 text-xl font-extrabold text-slate-900">
                                {s.titulo}
                            </h3>
                            <p className="mt-2 text-sm leading-relaxed text-slate-500">
                                {s.desc}
                            </p>
                            <ul className="mt-5 space-y-2 text-[13px] text-slate-700">
                                {s.items.map((it) => (
                                    <li
                                        key={it}
                                        className="flex items-center gap-2"
                                    >
                                        <Check className="size-3.5 shrink-0 text-blue-600" />
                                        {it}
                                    </li>
                                ))}
                            </ul>
                        </Reveal>
                    ))}
                </div>
            </div>
        </Section>
    );
}

/* ============================ PLATAFORMA (oscuro) ============================ */

const MODULOS: { label: string; Icono: LucideIcon }[] = [
    { label: 'Obras', Icono: Building2 },
    { label: 'Certificados', Icono: Award },
    { label: 'Equipo', Icono: Users },
    { label: 'Documentos', Icono: FileText },
    { label: 'Cuaderno de obra', Icono: NotebookPen },
    { label: 'Administración', Icono: LayoutGrid },
    { label: 'Usuarios', Icono: UserCog },
    { label: 'Calendario', Icono: Calendar },
];

export function Plataforma() {
    return (
        <Section
            id="plataforma"
            tone="dark"
            className="bg-gradient-to-b from-[#06101e] via-[#071426] to-[#06101e] py-16 md:py-28"
        >
            <div className="bg-blueprint-fine mask-radial absolute inset-0 opacity-50" />
            <div className="glow-blue absolute top-20 right-0 h-[420px] w-[420px]" />

            <div className="relative mx-auto max-w-7xl px-4 md:px-8">
                <div className="grid items-center gap-14 lg:grid-cols-[1fr_1.1fr]">
                    <Reveal>
                        <Eyebrow tone="dark">Plataforma propia</Eyebrow>
                        <h2 className="mt-5 text-2xl font-extrabold tracking-tight text-white sm:text-3xl md:text-4xl">
                            Control documental y seguimiento técnico
                            <span className="block text-white/55">
                                en una sola plataforma.
                            </span>
                        </h2>
                        <p className="mt-5 text-base leading-relaxed text-slate-300 md:text-lg">
                            Plataforma interna desarrollada para gestionar
                            obras, expedientes, certificados, equipo de obra,
                            calendario, cuaderno de obra digital y documentación
                            auditable — accesible desde laptop, tablet o campo.
                        </p>

                        <div className="mt-8 grid grid-cols-2 gap-3">
                            {MODULOS.map((m) => (
                                <div
                                    key={m.label}
                                    className="glass flex items-center gap-3 rounded-xl px-3 py-2.5"
                                >
                                    <span className="inline-flex size-9 items-center justify-center rounded-lg border border-blue-500/30 bg-blue-500/10 text-blue-200">
                                        <m.Icono className="size-4" />
                                    </span>
                                    <span className="text-[13px] font-bold text-white">
                                        {m.label}
                                    </span>
                                </div>
                            ))}
                        </div>

                        <div className="mt-8 flex flex-wrap gap-3">
                            <a
                                href="/login"
                                className="btn-primary inline-flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-bold"
                            >
                                Acceder a la plataforma
                            </a>
                            <a
                                href="/verificar"
                                className="btn-ghost inline-flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-bold"
                            >
                                Verificar certificado
                            </a>
                        </div>
                    </Reveal>

                    {/* mock plataforma */}
                    <Reveal delay={120} className="relative">
                        <div className="glow-gold absolute -inset-10 -z-10" />
                        <div className="mock-window">
                            <div className="mock-header">
                                <span className="mock-dot bg-red-400/70" />
                                <span className="mock-dot bg-amber-300/70" />
                                <span className="mock-dot bg-emerald-400/70" />
                                <span className="ml-3 text-[11px] font-semibold tracking-wider text-slate-400">
                                    rnfc.site / obras / saneamiento-azangaro
                                </span>
                            </div>
                            <div className="grid grid-cols-1 sm:grid-cols-[160px_1fr]">
                                <aside className="hidden border-r border-white/5 p-3 sm:block">
                                    <div className="px-2 text-[10px] font-bold tracking-wider text-white/40 uppercase">
                                        Menú
                                    </div>
                                    <ul className="mt-2 space-y-1 text-[12px] font-semibold text-white/75">
                                        <li className="rounded-md bg-blue-500/15 px-2 py-1.5 text-white">
                                            Obras
                                        </li>
                                        {[
                                            'Cuaderno',
                                            'Documentos',
                                            'Calendario',
                                            'Equipo',
                                            'Certificados',
                                        ].map((x) => (
                                            <li key={x} className="px-2 py-1.5">
                                                {x}
                                            </li>
                                        ))}
                                    </ul>
                                </aside>
                                <div className="p-4">
                                    <div className="flex items-center justify-between">
                                        <div>
                                            <div className="text-[10px] font-bold tracking-wider text-white/45 uppercase">
                                                Obra activa
                                            </div>
                                            <div className="text-base font-extrabold text-white">
                                                Saneamiento — Azángaro
                                            </div>
                                        </div>
                                        <span className="rounded-full border border-emerald-400/30 bg-emerald-500/10 px-2 py-0.5 text-[10px] font-bold text-emerald-300">
                                            En ejecución
                                        </span>
                                    </div>
                                    <div className="mt-3 grid grid-cols-3 gap-2">
                                        <div className="rounded-lg border border-white/10 bg-white/[0.02] p-2.5">
                                            <div className="text-[9px] font-bold tracking-wider text-white/45 uppercase">
                                                Avance
                                            </div>
                                            <div className="text-lg font-extrabold text-white">
                                                62%
                                            </div>
                                            <div className="mt-1 h-1.5 overflow-hidden rounded-full bg-white/10">
                                                <div className="h-full w-[62%] rounded-full bg-gradient-to-r from-blue-600 to-sky-400" />
                                            </div>
                                        </div>
                                        <div className="rounded-lg border border-white/10 bg-white/[0.02] p-2.5">
                                            <div className="text-[9px] font-bold tracking-wider text-white/45 uppercase">
                                                Valorizado
                                            </div>
                                            <div className="text-lg font-extrabold text-white">
                                                S/ 4.2M
                                            </div>
                                            <div className="text-[10px] text-emerald-300/80">
                                                +S/ 280k
                                            </div>
                                        </div>
                                        <div className="rounded-lg border border-white/10 bg-white/[0.02] p-2.5">
                                            <div className="text-[9px] font-bold tracking-wider text-white/45 uppercase">
                                                NC abiertas
                                            </div>
                                            <div className="text-lg font-extrabold text-white">
                                                3
                                            </div>
                                            <div className="text-[10px] text-orange-300/80">
                                                2 en revisión
                                            </div>
                                        </div>
                                    </div>
                                    <div className="mt-3 rounded-lg border border-white/10 bg-white/[0.02] p-3">
                                        <div className="flex items-center justify-between">
                                            <span className="text-[10px] font-bold tracking-wider text-white/45 uppercase">
                                                Carpetas del expediente
                                            </span>
                                            <span className="text-[10px] font-semibold text-sky-300">
                                                12 carpetas
                                            </span>
                                        </div>
                                        <div className="mt-2 grid grid-cols-3 gap-2 text-[11px] text-white/85">
                                            {[
                                                '00 — Bases',
                                                '01 — Técnico',
                                                '02 — Económico',
                                                '03 — Calidad',
                                                '04 — SSOMA',
                                                '05 — Cierre',
                                            ].map((c) => (
                                                <div
                                                    key={c}
                                                    className="flex items-center gap-1.5 rounded-md border border-white/10 bg-white/[0.02] px-2 py-1.5"
                                                >
                                                    <FileText className="size-3.5 shrink-0 text-amber-300" />
                                                    <span className="truncate">
                                                        {c}
                                                    </span>
                                                </div>
                                            ))}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </Reveal>
                </div>
            </div>
        </Section>
    );
}

/* ============================ EXPERIENCIA (claro) ============================ */

const PILARES: { titulo: string; desc: string; Icono: LucideIcon }[] = [
    {
        titulo: 'Obras viales',
        desc: 'Supervisión técnica en carreteras, vías urbanas y obras de arte.',
        Icono: Route,
    },
    {
        titulo: 'Edificaciones',
        desc: 'Edificios institucionales, educativos, salud y vivienda multifamiliar.',
        Icono: Building2,
    },
    {
        titulo: 'Saneamiento',
        desc: 'Sistemas de agua potable, alcantarillado y plantas de tratamiento.',
        Icono: Droplets,
    },
    {
        titulo: 'Electromecánicas',
        desc: 'Obras electromecánicas, instalaciones y proyectos especiales.',
        Icono: Zap,
    },
    {
        titulo: 'Obras públicas',
        desc: 'Experiencia con Gobiernos Regionales, Municipalidades y Ministerios.',
        Icono: Landmark,
    },
    {
        titulo: 'Obras privadas',
        desc: 'Supervisión y consultoría para constructoras y desarrolladoras.',
        Icono: Building,
    },
];

const ESTANDARES = [
    'Ley de Contrataciones del Estado',
    'Reglamento Nacional de Edificaciones',
    'ISO 9001 · 14001 · 37001 vigentes',
    'Procedimientos QA/QC y SSOMA',
    'Buenas prácticas BIM',
    'Trazabilidad documental auditable',
];

export function Experiencia() {
    return (
        <Section
            id="experiencia"
            tone="light"
            className="bg-slate-50 py-16 md:py-28"
        >
            <div className="relative mx-auto max-w-7xl px-4 md:px-8">
                <Reveal className="mx-auto max-w-3xl text-center">
                    <Eyebrow center tone="light">
                        Experiencia técnica
                    </Eyebrow>
                    <h2 className="mt-5 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl md:text-4xl">
                        Experiencia técnica{' '}
                        <span className="text-slate-400">
                            en obras públicas y privadas.
                        </span>
                    </h2>
                    <p className="mt-5 text-base text-slate-500 md:text-lg">
                        Trabajamos en supervisión y consultoría de proyectos de
                        infraestructura junto a entidades públicas y empresas
                        constructoras a nivel nacional.
                    </p>
                </Reveal>

                <div className="mt-14 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                    {PILARES.map((p, i) => (
                        <Reveal
                            key={p.titulo}
                            delay={(i % 3) * 80}
                            className="rounded-2xl border border-slate-200 bg-white p-6 shadow-sm"
                        >
                            <span className="inline-flex size-11 items-center justify-center rounded-lg bg-slate-100 text-slate-700">
                                <p.Icono className="size-5" />
                            </span>
                            <div className="mt-4 text-lg font-extrabold text-slate-900">
                                {p.titulo}
                            </div>
                            <p className="mt-1.5 text-sm leading-relaxed text-slate-500">
                                {p.desc}
                            </p>
                        </Reveal>
                    ))}
                </div>

                <Reveal className="mt-14 rounded-2xl border border-slate-200 bg-white p-6 shadow-sm md:p-10">
                    <div className="grid gap-8 md:grid-cols-[1fr_1.4fr]">
                        <div>
                            <h3 className="text-xl font-extrabold text-slate-900 md:text-2xl">
                                Estándares de trabajo
                            </h3>
                            <p className="mt-2 text-sm text-slate-500">
                                Operamos bajo la normativa aplicable al sector
                                construcción en el Perú y bajo nuestros sistemas
                                de gestión certificados.
                            </p>
                        </div>
                        <ul className="grid gap-3 sm:grid-cols-2">
                            {ESTANDARES.map((e) => (
                                <li
                                    key={e}
                                    className="flex items-center gap-3 text-sm text-slate-700"
                                >
                                    <span className="inline-flex size-6 items-center justify-center rounded-md bg-blue-600/10 text-blue-700">
                                        <Check className="size-3.5" />
                                    </span>
                                    {e}
                                </li>
                            ))}
                        </ul>
                    </div>
                </Reveal>
            </div>
        </Section>
    );
}

/* ============================ CERTIFICACIONES (oscuro) ======================= */

const ISOS = [
    {
        codigo: 'ISO 9001:2015',
        nombre: 'Sistema de Gestión de la Calidad',
        desc: 'Procesos auditables, control documental y mejora continua aplicada a cada proyecto.',
        img: '/brand/ISO 9001.png',
        glow: 'rgba(37,99,235,.4)',
        accent: '#60a5fa',
        tag: 'Calidad',
    },
    {
        codigo: 'ISO 14001:2015',
        nombre: 'Sistema de Gestión Ambiental',
        desc: 'Identificación, control y mitigación de impactos ambientales en obra.',
        img: '/brand/ISO 14001.png',
        glow: 'rgba(34,197,94,.45)',
        accent: '#4ade80',
        tag: 'Sostenibilidad',
    },
    {
        codigo: 'ISO 37001:2025',
        nombre: 'Sistema de Gestión Antisoborno',
        desc: 'Política de cero soborno, debida diligencia y transparencia con todas las partes.',
        img: '/brand/ISO 37001.png',
        glow: 'rgba(249,115,22,.45)',
        accent: '#fb923c',
        tag: 'Integridad',
    },
];

export function Certificaciones() {
    return (
        <Section
            id="certificaciones"
            tone="dark"
            className="bg-gradient-to-b from-[#06101e] via-[#08182d] to-[#06101e] py-16 md:py-28"
        >
            <div className="glow-gold absolute top-10 left-1/2 h-[400px] w-[400px] -translate-x-1/2" />
            <div className="relative mx-auto max-w-7xl px-4 md:px-8">
                <Reveal className="mx-auto max-w-2xl text-center">
                    <Eyebrow center tone="dark">
                        Certificaciones internacionales
                    </Eyebrow>
                    <h2 className="mt-5 text-2xl font-extrabold tracking-tight text-white sm:text-3xl md:text-4xl">
                        Calidad, sostenibilidad{' '}
                        <span className="text-white/55">
                            e integridad institucional.
                        </span>
                    </h2>
                    <p className="mt-5 text-base text-slate-300 md:text-lg">
                        Operamos bajo sistemas de gestión certificados por
                        organismos acreditados. Procesos auditables, mejora
                        continua y trazabilidad documental.
                    </p>
                </Reveal>

                <div className="mt-14 grid gap-6 md:grid-cols-3">
                    {ISOS.map((iso, i) => (
                        <Reveal
                            key={iso.codigo}
                            delay={i * 100}
                            className="iso-premium glass-strong relative overflow-hidden rounded-2xl p-7"
                            // @ts-expect-error CSS var personalizada
                            style={{ '--iso-glow': iso.glow }}
                        >
                            <div className="relative z-10 flex items-start justify-between gap-3">
                                <div className="size-24 shrink-0 md:size-32">
                                    <img
                                        src={iso.img}
                                        alt={iso.codigo}
                                        className="h-full w-full object-contain drop-shadow-[0_10px_30px_rgba(0,0,0,.5)]"
                                    />
                                </div>
                                <span
                                    className="rounded-full border px-2.5 py-1 text-[10px] font-bold tracking-wider uppercase"
                                    style={{
                                        color: iso.accent,
                                        borderColor: iso.glow,
                                        background: 'rgba(255,255,255,0.03)',
                                    }}
                                >
                                    {iso.tag}
                                </span>
                            </div>
                            <div className="relative z-10 mt-5">
                                <div className="text-2xl font-extrabold text-white">
                                    {iso.codigo}
                                </div>
                                <div
                                    className="mt-1 text-sm font-bold tracking-wider uppercase"
                                    style={{ color: iso.accent }}
                                >
                                    {iso.nombre}
                                </div>
                                <p className="mt-3 text-sm leading-relaxed text-slate-300">
                                    {iso.desc}
                                </p>
                            </div>
                        </Reveal>
                    ))}
                </div>

                <Reveal className="mt-12 grid gap-4 md:grid-cols-4">
                    {[
                        'Calidad',
                        'Sostenibilidad',
                        'Transparencia',
                        'Mejora continua',
                    ].map((v) => (
                        <div
                            key={v}
                            className="glass flex items-center justify-center gap-2 rounded-xl px-4 py-3 text-sm font-bold text-white"
                        >
                            <Check className="size-4 text-white/55" />
                            {v}
                        </div>
                    ))}
                </Reveal>

                <Reveal
                    as="p"
                    className="mx-auto mt-10 max-w-3xl text-center text-sm text-white/55"
                >
                    Certificados emitidos por{' '}
                    <strong className="text-white/80">
                        International Certification Organization (ICO)
                    </strong>{' '}
                    — verificables en{' '}
                    <a
                        href="https://icocert.pe"
                        target="_blank"
                        rel="noopener"
                        className="text-white/85 underline underline-offset-4 hover:text-white"
                    >
                        icocert.pe
                    </a>
                    .
                </Reveal>
            </div>
        </Section>
    );
}

/* ============================ DIFERENCIADORES (claro) ======================= */

const DIFS: [string, string][] = [
    [
        'Supervisión técnica especializada',
        'Equipo multidisciplinario con experiencia en obras viales, edificación, saneamiento y electromecánicas.',
    ],
    [
        'Gestión documental inteligente',
        'Plataforma con versionado, búsqueda, firma y trazabilidad por obra.',
    ],
    [
        'Plataforma digital propia',
        'Sistema desarrollado in-house — no dependemos de software de terceros para el control diario.',
    ],
    [
        'Cumplimiento normativo',
        'Operamos bajo Ley de Contrataciones del Estado, RNE y reglamentos sectoriales.',
    ],
    [
        'Trazabilidad y control',
        'Cada documento, asiento y conformidad queda registrado y auditable.',
    ],
    [
        'Experiencia en obras públicas',
        'Supervisión para gobiernos regionales, municipalidades y ministerios.',
    ],
    [
        'Transparencia institucional',
        'ISO 37001 — política antisoborno y debida diligencia con todas las partes.',
    ],
    [
        'Estándares internacionales ISO',
        'Certificación triple ISO 9001, 14001 y 37001 vigente.',
    ],
];

export function Diferenciadores() {
    return (
        <Section id="diferenciadores" tone="light" className="py-16 md:py-28">
            <div className="bg-blueprint-light mask-radial absolute inset-0 opacity-60" />
            <div className="relative mx-auto max-w-7xl px-4 md:px-8">
                <div className="grid items-start gap-12 lg:grid-cols-[1fr_1.4fr]">
                    <Reveal className="lg:sticky lg:top-28">
                        <Eyebrow tone="light">Por qué RNFC</Eyebrow>
                        <h2 className="mt-5 text-2xl font-extrabold tracking-tight text-slate-900 sm:text-3xl md:text-4xl">
                            Una firma con{' '}
                            <span className="text-blue-600">
                                transformación digital
                            </span>{' '}
                            y procesos certificados.
                        </h2>
                        <p className="mt-5 text-base text-slate-500 md:text-lg">
                            Combinamos experiencia técnica con tecnología propia
                            — algo que las consultoras tradicionales del sector
                            aún no ofrecen.
                        </p>
                    </Reveal>

                    <ul className="grid gap-3 md:grid-cols-2">
                        {DIFS.map(([titulo, desc], i) => (
                            <Reveal
                                key={titulo}
                                as="li"
                                delay={(i % 4) * 70}
                                className="flex items-start gap-4 rounded-2xl border border-slate-200 bg-white p-5 shadow-sm"
                            >
                                <span className="mt-0.5 inline-flex size-7 shrink-0 items-center justify-center rounded-md bg-blue-600 text-white">
                                    <Check className="size-3.5" />
                                </span>
                                <div>
                                    <div className="text-[15px] font-extrabold text-slate-900">
                                        {titulo}
                                    </div>
                                    <div className="mt-1 text-sm text-slate-500">
                                        {desc}
                                    </div>
                                </div>
                            </Reveal>
                        ))}
                    </ul>
                </div>
            </div>
        </Section>
    );
}
