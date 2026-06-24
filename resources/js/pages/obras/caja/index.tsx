import { Head, Link, router } from '@inertiajs/react';
import {
    ArrowDownCircle,
    ArrowLeft,
    ArrowUpCircle,
    Paperclip,
    Plus,
    Trash2,
    Wallet,
} from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import obras from '@/routes/obras';
import NuevoMovimientoDialog from './_nuevo-movimiento';
import type { CategoriaOpcion } from './_nuevo-movimiento';

type ObraResumen = { id: number; codigo: string; nombre: string };

type Movimiento = {
    id: number;
    tipo: 'ingreso' | 'egreso';
    categoria: string | null;
    categoria_label: string | null;
    monto: number;
    descripcion: string;
    fecha: string | null;
    registrado_por: string | null;
    tiene_comprobante: boolean;
    url_comprobante: string | null;
    created_at: string | null;
};

type Resumen = { ingresos: number; egresos: number; saldo: number };

type Props = {
    obra: ObraResumen;
    movimientos: Movimiento[];
    resumen: Resumen;
    categorias: CategoriaOpcion[];
    puedeRegistrar: boolean;
    puedeGestionar: boolean;
};

const soles = (n: number) =>
    new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN',
    }).format(n);

function formatearFecha(fecha: string | null) {
    if (!fecha) {
        return '—';
    }

    return new Date(`${fecha}T00:00:00`).toLocaleDateString('es-PE', {
        day: '2-digit',
        month: 'short',
        year: 'numeric',
    });
}

export default function CajaIndex({
    obra,
    movimientos,
    resumen,
    categorias,
    puedeRegistrar,
    puedeGestionar,
}: Props) {
    const [mostrandoNuevo, setMostrandoNuevo] = useState(false);

    const eliminar = (m: Movimiento) => {
        if (!confirm(`¿Eliminar el movimiento «${m.descripcion}»?`)) {
            return;
        }

        router.delete(`/obras/${obra.id}/caja/${m.id}`, {
            preserveScroll: true,
            onSuccess: () =>
                router.reload({ only: ['movimientos', 'resumen'] }),
        });
    };

    return (
        <>
            <Head title={`Caja chica · ${obra.codigo}`} />
            <div className="flex flex-1 flex-col gap-4 p-4 md:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <Link
                        href={obras.show(obra.id).url}
                        className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft className="size-3.5" />
                        Volver a la obra
                    </Link>
                    {puedeRegistrar && (
                        <Button onClick={() => setMostrandoNuevo(true)}>
                            <Plus className="size-4" />
                            Registrar movimiento
                        </Button>
                    )}
                </div>

                {/* Resumen */}
                <div className="grid gap-3 sm:grid-cols-3">
                    <Card>
                        <CardContent className="flex items-center gap-3 p-4">
                            <span className="flex size-10 items-center justify-center rounded-lg bg-primary/10 text-primary">
                                <Wallet className="size-5" />
                            </span>
                            <div>
                                <div className="text-xs text-muted-foreground">
                                    Saldo actual
                                </div>
                                <div
                                    className={
                                        'text-xl font-semibold tabular-nums ' +
                                        (resumen.saldo < 0
                                            ? 'text-rose-600 dark:text-rose-400'
                                            : '')
                                    }
                                >
                                    {soles(resumen.saldo)}
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="flex items-center gap-3 p-4">
                            <span className="flex size-10 items-center justify-center rounded-lg bg-emerald-500/10 text-emerald-600 dark:text-emerald-400">
                                <ArrowUpCircle className="size-5" />
                            </span>
                            <div>
                                <div className="text-xs text-muted-foreground">
                                    Ingresos
                                </div>
                                <div className="text-xl font-semibold tabular-nums">
                                    {soles(resumen.ingresos)}
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="flex items-center gap-3 p-4">
                            <span className="flex size-10 items-center justify-center rounded-lg bg-rose-500/10 text-rose-600 dark:text-rose-400">
                                <ArrowDownCircle className="size-5" />
                            </span>
                            <div>
                                <div className="text-xs text-muted-foreground">
                                    Egresos
                                </div>
                                <div className="text-xl font-semibold tabular-nums">
                                    {soles(resumen.egresos)}
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                {/* Lista de movimientos */}
                {movimientos.length === 0 ? (
                    <Card className="p-10 text-center">
                        <p className="text-sm text-muted-foreground">
                            Aún no hay movimientos en la caja de esta obra.
                        </p>
                    </Card>
                ) : (
                    <Card className="overflow-hidden p-0">
                        <ul className="divide-y divide-border">
                            {movimientos.map((m) => (
                                <li
                                    key={m.id}
                                    className="flex items-center gap-3 px-4 py-3 hover:bg-muted/40"
                                >
                                    <span
                                        className={
                                            'flex size-9 shrink-0 items-center justify-center rounded-full ' +
                                            (m.tipo === 'ingreso'
                                                ? 'bg-emerald-500/10 text-emerald-600 dark:text-emerald-400'
                                                : 'bg-rose-500/10 text-rose-600 dark:text-rose-400')
                                        }
                                    >
                                        {m.tipo === 'ingreso' ? (
                                            <ArrowUpCircle className="size-5" />
                                        ) : (
                                            <ArrowDownCircle className="size-5" />
                                        )}
                                    </span>

                                    <div className="min-w-0 flex-1">
                                        <div className="flex items-center gap-2">
                                            <span className="truncate text-sm font-medium">
                                                {m.descripcion}
                                            </span>
                                            {m.categoria_label && (
                                                <Badge
                                                    variant="secondary"
                                                    className="shrink-0"
                                                >
                                                    {m.categoria_label}
                                                </Badge>
                                            )}
                                        </div>
                                        <div className="truncate text-xs text-muted-foreground">
                                            {formatearFecha(m.fecha)}
                                            {m.registrado_por
                                                ? ` · ${m.registrado_por}`
                                                : ''}
                                        </div>
                                    </div>

                                    {m.tiene_comprobante &&
                                        m.url_comprobante && (
                                            <a
                                                href={m.url_comprobante}
                                                target="_blank"
                                                rel="noreferrer"
                                                className="shrink-0 rounded p-1.5 text-muted-foreground hover:bg-muted hover:text-foreground"
                                                title="Ver comprobante"
                                            >
                                                <Paperclip className="size-4" />
                                            </a>
                                        )}

                                    <div
                                        className={
                                            'shrink-0 text-sm font-semibold tabular-nums ' +
                                            (m.tipo === 'ingreso'
                                                ? 'text-emerald-600 dark:text-emerald-400'
                                                : 'text-rose-600 dark:text-rose-400')
                                        }
                                    >
                                        {m.tipo === 'ingreso' ? '+' : '−'}
                                        {soles(m.monto)}
                                    </div>

                                    {puedeGestionar && (
                                        <button
                                            type="button"
                                            onClick={() => eliminar(m)}
                                            className="shrink-0 rounded p-1.5 text-muted-foreground hover:bg-muted hover:text-destructive"
                                            title="Eliminar"
                                        >
                                            <Trash2 className="size-4" />
                                        </button>
                                    )}
                                </li>
                            ))}
                        </ul>
                    </Card>
                )}
            </div>

            {puedeRegistrar && (
                <NuevoMovimientoDialog
                    open={mostrandoNuevo}
                    onOpenChange={setMostrandoNuevo}
                    obraId={obra.id}
                    categorias={categorias}
                />
            )}
        </>
    );
}

CajaIndex.layout = {
    title: 'Caja chica',
    description: 'Control de ingresos y gastos de la obra.',
    breadcrumbs: [
        { title: 'Panel', href: '/dashboard' },
        { title: 'Obras', href: '/obras' },
        { title: 'Caja chica', href: '' },
    ],
};
