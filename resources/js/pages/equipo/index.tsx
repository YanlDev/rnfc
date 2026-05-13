import { Head, Link } from '@inertiajs/react';
import {
    Building2,
    ChevronRight,
    Mail,
    UserPlus,
    Users,
} from 'lucide-react';
import { useMemo, useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import {
    Card,
    CardContent,
    CardHeader,
    CardTitle,
} from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import obras from '@/routes/obras';

type Miembro = {
    id: number;
    name: string;
    email: string;
    rol_obra: string;
    rol_obra_label: string;
};

type InvitacionPendiente = {
    id: number;
    email: string;
    rol_obra_label: string;
    expira_at: string;
};

type ObraConEquipo = {
    id: number;
    codigo: string;
    nombre: string;
    entidad_contratante: string | null;
    estado: string;
    estado_label: string;
    miembros: Miembro[];
    invitaciones: InvitacionPendiente[];
};

type Props = {
    obras: ObraConEquipo[];
    totales: {
        obras: number;
        miembrosUnicos: number;
        invitacionesPendientes: number;
    };
};

const ESTADO_BADGE: Record<string, string> = {
    planificacion: 'bg-slate-200 text-slate-800 hover:bg-slate-200',
    en_ejecucion: 'bg-[var(--color-brand-verde)] text-white hover:bg-[var(--color-brand-verde)]',
    paralizada: 'bg-[var(--color-brand-amarillo)] text-[#3e4142] hover:bg-[var(--color-brand-amarillo)]',
    finalizada: 'bg-[var(--color-brand-azul-oscuro)] text-white hover:bg-[var(--color-brand-azul-oscuro)]',
    archivada: 'bg-slate-500 text-white hover:bg-slate-500',
};

function ObraEquipoCard({ obra }: { obra: ObraConEquipo }) {
    const vacia = obra.miembros.length === 0 && obra.invitaciones.length === 0;

    return (
        <Card>
            <CardHeader className="pb-3">
                <div className="flex flex-col gap-2 sm:flex-row sm:items-start sm:justify-between">
                    <div className="space-y-1">
                        <div className="flex items-center gap-2">
                            <Link
                                href={obras.show(obra.id).url}
                                className="font-mono text-xs font-semibold tracking-wider text-primary hover:underline"
                            >
                                {obra.codigo}
                            </Link>
                            <Badge className={ESTADO_BADGE[obra.estado] ?? ''}>
                                {obra.estado_label}
                            </Badge>
                        </div>
                        <CardTitle className="text-base leading-snug">
                            {obra.nombre}
                        </CardTitle>
                        {obra.entidad_contratante && (
                            <p className="text-xs text-muted-foreground">
                                {obra.entidad_contratante}
                            </p>
                        )}
                    </div>
                    <Button asChild variant="outline" size="sm">
                        <Link href={obras.show(obra.id).url}>
                            <UserPlus className="size-4" />
                            Gestionar
                            <ChevronRight className="size-3.5" />
                        </Link>
                    </Button>
                </div>
            </CardHeader>
            <CardContent className="space-y-4">
                {vacia ? (
                    <p className="rounded-md border border-dashed border-border bg-muted/30 p-4 text-center text-sm text-muted-foreground">
                        Sin integrantes ni invitaciones. Usa{' '}
                        <strong>Gestionar</strong> para invitar a alguien.
                    </p>
                ) : (
                    <>
                        {obra.miembros.length > 0 && (
                            <div className="space-y-2">
                                <div className="flex items-center gap-2 text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    <Users className="size-3.5" />
                                    Integrantes ({obra.miembros.length})
                                </div>
                                <ul className="divide-y divide-border rounded-md border border-border">
                                    {obra.miembros.map((m) => (
                                        <li
                                            key={m.id}
                                            className="flex flex-col gap-1 p-2.5 text-sm sm:flex-row sm:items-center sm:justify-between"
                                        >
                                            <div>
                                                <div className="font-medium">
                                                    {m.name}
                                                </div>
                                                <div className="text-xs text-muted-foreground">
                                                    {m.email}
                                                </div>
                                            </div>
                                            <Badge
                                                variant="secondary"
                                                className="text-[11px]"
                                            >
                                                {m.rol_obra_label}
                                            </Badge>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}
                        {obra.invitaciones.length > 0 && (
                            <div className="space-y-2">
                                <div className="flex items-center gap-2 text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                    <Mail className="size-3.5" />
                                    Invitaciones pendientes (
                                    {obra.invitaciones.length})
                                </div>
                                <ul className="divide-y divide-border rounded-md border border-dashed border-border">
                                    {obra.invitaciones.map((i) => (
                                        <li
                                            key={i.id}
                                            className="flex flex-col gap-1 p-2.5 text-sm sm:flex-row sm:items-center sm:justify-between"
                                        >
                                            <div>
                                                <div className="font-medium">
                                                    {i.email}
                                                </div>
                                                <div className="text-xs text-muted-foreground">
                                                    Expira el{' '}
                                                    {new Date(i.expira_at).toLocaleDateString('es-PE')}
                                                </div>
                                            </div>
                                            <Badge
                                                variant="outline"
                                                className="text-[11px]"
                                            >
                                                {i.rol_obra_label}
                                            </Badge>
                                        </li>
                                    ))}
                                </ul>
                            </div>
                        )}
                    </>
                )}
            </CardContent>
        </Card>
    );
}

export default function EquipoIndex({ obras: obrasConEquipo, totales }: Props) {
    const [busqueda, setBusqueda] = useState('');

    const obrasFiltradas = useMemo(() => {
        const q = busqueda.trim().toLowerCase();
        if (!q) return obrasConEquipo;
        return obrasConEquipo.filter(
            (o) =>
                o.codigo.toLowerCase().includes(q) ||
                o.nombre.toLowerCase().includes(q) ||
                (o.entidad_contratante ?? '').toLowerCase().includes(q) ||
                o.miembros.some(
                    (m) =>
                        m.name.toLowerCase().includes(q) ||
                        m.email.toLowerCase().includes(q),
                ) ||
                o.invitaciones.some((i) =>
                    i.email.toLowerCase().includes(q),
                ),
        );
    }, [obrasConEquipo, busqueda]);

    if (totales.obras === 0) {
        return (
            <>
                <Head title="Equipo" />
                <div className="flex flex-1 flex-col items-center justify-center gap-4 p-8 text-center">
                    <Users className="size-12 text-muted-foreground" />
                    <h2 className="text-xl font-semibold">
                        Aún no hay obras registradas
                    </h2>
                    <p className="max-w-md text-sm text-muted-foreground">
                        El equipo se administra por obra: cada persona se invita
                        a una obra específica con un rol determinado. Crea tu
                        primera obra para empezar a invitar colaboradores.
                    </p>
                    <Button asChild>
                        <Link href={obras.create().url}>
                            <Building2 className="size-4" />
                            Crear primera obra
                        </Link>
                    </Button>
                </div>
            </>
        );
    }

    return (
        <>
            <Head title="Equipo" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                {/* Resumen */}
                <div className="grid gap-3 sm:grid-cols-3">
                    <Card className="p-4">
                        <div className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                            Obras activas
                        </div>
                        <div className="mt-1 text-2xl font-bold tabular-nums">
                            {totales.obras}
                        </div>
                    </Card>
                    <Card className="p-4">
                        <div className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                            Personas únicas
                        </div>
                        <div className="mt-1 text-2xl font-bold tabular-nums">
                            {totales.miembrosUnicos}
                        </div>
                    </Card>
                    <Card className="p-4">
                        <div className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                            Invitaciones pendientes
                        </div>
                        <div className="mt-1 text-2xl font-bold tabular-nums">
                            {totales.invitacionesPendientes}
                        </div>
                    </Card>
                </div>

                <Input
                    placeholder="Buscar por obra, código, nombre o correo…"
                    value={busqueda}
                    onChange={(e) => setBusqueda(e.target.value)}
                    className="max-w-md"
                />

                {obrasFiltradas.length === 0 ? (
                    <Card className="p-8 text-center text-sm text-muted-foreground">
                        Sin coincidencias con el filtro actual.
                    </Card>
                ) : (
                    <div className="grid gap-4 lg:grid-cols-2">
                        {obrasFiltradas.map((o) => (
                            <ObraEquipoCard key={o.id} obra={o} />
                        ))}
                    </div>
                )}
            </div>
        </>
    );
}

EquipoIndex.layout = {
    title: 'Equipo',
    description:
        'Cada obra con su equipo e invitaciones pendientes. Usa "Gestionar" para invitar o modificar roles.',
    breadcrumbs: [
        { title: 'Panel', href: '/dashboard' },
        { title: 'Equipo', href: '/equipo' },
    ],
};
