import { router } from '@inertiajs/react';
import { Check, Plus, Trash2, X } from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import type { Alquiler, Opcion } from './index';

type Props = {
    obraId: number;
    alquileres: Alquiler[];
    formasPago: Opcion[];
    puedeRegistrar: boolean;
    puedeGestionar: boolean;
};

const soles = (n: number) =>
    new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN',
    }).format(n);

const MESES = [
    'Ene',
    'Feb',
    'Mar',
    'Abr',
    'May',
    'Jun',
    'Jul',
    'Ago',
    'Set',
    'Oct',
    'Nov',
    'Dic',
];

/** Lista de periodos YYYY-MM desde el inicio del alquiler hasta el mes actual. */
function periodosDesde(fechaInicio: string): string[] {
    const [anio, mes] = fechaInicio.split('-').map(Number);
    const ahora = new Date();
    const fin = ahora.getFullYear() * 12 + ahora.getMonth();
    const periodos: string[] = [];

    for (let i = anio * 12 + (mes - 1); i <= fin; i++) {
        const a = Math.floor(i / 12);
        const m = i % 12;
        periodos.push(`${a}-${String(m + 1).padStart(2, '0')}`);
    }

    return periodos;
}

const etiquetaPeriodo = (periodo: string) => {
    const [anio, mes] = periodo.split('-').map(Number);

    return `${MESES[mes - 1]} ${anio}`;
};

const recargar = () =>
    router.reload({ only: ['alquileres', 'movimientos', 'resumen'] });

export default function Alquileres({
    obraId,
    alquileres,
    formasPago,
    puedeRegistrar,
    puedeGestionar,
}: Props) {
    const hoy = new Date().toISOString().slice(0, 10);

    const [nuevo, setNuevo] = useState({
        inquilino: '',
        monto_mensual: '',
        forma_pago: 'fin_de_mes',
        fecha_inicio: hoy,
    });
    const [guardando, setGuardando] = useState(false);
    // Mini-formulario de pago abierto: `${alquilerId}:${periodo}`
    const [pagando, setPagando] = useState<string | null>(null);
    const [pago, setPago] = useState({ fecha_pago: hoy, monto: '' });

    const nuevoCompleto =
        nuevo.inquilino.trim() !== '' && parseFloat(nuevo.monto_mensual) > 0;

    const crearAlquiler = () => {
        if (!nuevoCompleto || guardando) {
            return;
        }

        setGuardando(true);
        router.post(`/obras/${obraId}/alquileres`, nuevo, {
            preserveScroll: true,
            onSuccess: () => {
                setNuevo({
                    inquilino: '',
                    monto_mensual: '',
                    forma_pago: 'fin_de_mes',
                    fecha_inicio: hoy,
                });
                recargar();
            },
            onFinish: () => setGuardando(false),
        });
    };

    const eliminarAlquiler = (a: Alquiler) => {
        if (!confirm(`¿Eliminar el alquiler de «${a.inquilino}»?`)) {
            return;
        }

        router.delete(`/obras/${obraId}/alquileres/${a.id}`, {
            preserveScroll: true,
            onSuccess: recargar,
        });
    };

    const abrirPago = (a: Alquiler, periodo: string) => {
        setPagando(`${a.id}:${periodo}`);
        setPago({ fecha_pago: hoy, monto: a.monto_mensual.toFixed(2) });
    };

    const confirmarPago = (a: Alquiler, periodo: string) => {
        const monto = parseFloat(pago.monto);
        if (!(monto > 0) || pago.fecha_pago === '') {
            return;
        }

        router.post(
            `/obras/${obraId}/alquileres/${a.id}/pagos`,
            { periodo, fecha_pago: pago.fecha_pago, monto },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setPagando(null);
                    recargar();
                },
            },
        );
    };

    const anularPago = (a: Alquiler, pagoId: number, periodo: string) => {
        if (
            !confirm(
                `¿Anular el pago de ${etiquetaPeriodo(periodo)} de «${a.inquilino}»? También se eliminará el egreso en caja.`,
            )
        ) {
            return;
        }

        router.delete(`/obras/${obraId}/alquileres/${a.id}/pagos/${pagoId}`, {
            preserveScroll: true,
            onSuccess: recargar,
        });
    };

    return (
        <div className="flex flex-col gap-4">
            {/* Alta rápida */}
            {puedeRegistrar && (
                <div className="flex flex-wrap items-end gap-2 rounded-lg border border-dashed border-border bg-primary/[0.03] p-3">
                    <div className="min-w-40 flex-1">
                        <label className="mb-1 block text-xs text-muted-foreground">
                            Inquilino / concepto
                        </label>
                        <input
                            type="text"
                            value={nuevo.inquilino}
                            placeholder="ING GENARO, OFICINA…"
                            onChange={(e) =>
                                setNuevo({
                                    ...nuevo,
                                    inquilino: e.target.value,
                                })
                            }
                            onKeyDown={(e) => {
                                if (e.key === 'Enter') {
                                    crearAlquiler();
                                }
                            }}
                            className="w-full rounded-md border border-border bg-background px-2 py-1.5 text-sm outline-none placeholder:text-muted-foreground/50 focus:ring-1 focus:ring-primary/40"
                        />
                    </div>
                    <div className="w-28">
                        <label className="mb-1 block text-xs text-muted-foreground">
                            Monto S/ mes
                        </label>
                        <input
                            type="number"
                            step="0.01"
                            min="0.01"
                            inputMode="decimal"
                            value={nuevo.monto_mensual}
                            placeholder="0.00"
                            onChange={(e) =>
                                setNuevo({
                                    ...nuevo,
                                    monto_mensual: e.target.value,
                                })
                            }
                            onKeyDown={(e) => {
                                if (e.key === 'Enter') {
                                    crearAlquiler();
                                }
                            }}
                            className="w-full rounded-md border border-border bg-background px-2 py-1.5 text-right text-sm tabular-nums outline-none focus:ring-1 focus:ring-primary/40"
                        />
                    </div>
                    <div className="w-36">
                        <label className="mb-1 block text-xs text-muted-foreground">
                            Forma de pago
                        </label>
                        <select
                            value={nuevo.forma_pago}
                            onChange={(e) =>
                                setNuevo({
                                    ...nuevo,
                                    forma_pago: e.target.value,
                                })
                            }
                            className="w-full rounded-md border border-border bg-background px-2 py-1.5 text-sm outline-none focus:ring-1 focus:ring-primary/40"
                        >
                            {formasPago.map((f) => (
                                <option key={f.value} value={f.value}>
                                    {f.label}
                                </option>
                            ))}
                        </select>
                    </div>
                    <div className="w-36">
                        <label className="mb-1 block text-xs text-muted-foreground">
                            Inicio
                        </label>
                        <input
                            type="date"
                            value={nuevo.fecha_inicio}
                            onChange={(e) =>
                                setNuevo({
                                    ...nuevo,
                                    fecha_inicio: e.target.value,
                                })
                            }
                            className="w-full rounded-md border border-border bg-background px-2 py-1.5 text-sm tabular-nums outline-none focus:ring-1 focus:ring-primary/40"
                        />
                    </div>
                    <Button
                        size="sm"
                        onClick={crearAlquiler}
                        disabled={!nuevoCompleto || guardando}
                    >
                        <Plus className="size-4" />
                        Agregar
                    </Button>
                </div>
            )}

            {alquileres.length === 0 ? (
                <p className="p-6 text-center text-sm text-muted-foreground">
                    Sin alquileres registrados. Agrega la oficina o las
                    habitaciones de los ingenieros y marca cada mes cuando se
                    pague.
                </p>
            ) : (
                <div className="grid gap-3 lg:grid-cols-2">
                    {alquileres.map((a) => {
                        const pagosPorPeriodo = new Map(
                            a.pagos.map((p) => [p.periodo, p]),
                        );
                        const periodos = periodosDesde(a.fecha_inicio);

                        return (
                            <Card key={a.id} className="p-0">
                                <CardContent className="p-4">
                                    <div className="mb-3 flex items-start justify-between gap-2">
                                        <div>
                                            <div className="font-medium">
                                                {a.inquilino}
                                            </div>
                                            <div className="text-xs text-muted-foreground">
                                                {soles(a.monto_mensual)} ·{' '}
                                                {a.forma_pago_label} · desde{' '}
                                                {a.fecha_inicio}
                                            </div>
                                        </div>
                                        <div className="flex items-center gap-1">
                                            {!a.activo && (
                                                <Badge variant="secondary">
                                                    Inactivo
                                                </Badge>
                                            )}
                                            {puedeGestionar && (
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        eliminarAlquiler(a)
                                                    }
                                                    className="rounded p-1 text-muted-foreground/50 hover:bg-muted hover:text-destructive"
                                                    title="Eliminar alquiler"
                                                >
                                                    <Trash2 className="size-4" />
                                                </button>
                                            )}
                                        </div>
                                    </div>

                                    <div className="flex flex-wrap gap-1.5">
                                        {periodos.map((periodo) => {
                                            const p =
                                                pagosPorPeriodo.get(periodo);
                                            const clave = `${a.id}:${periodo}`;

                                            if (p) {
                                                return (
                                                    <button
                                                        key={periodo}
                                                        type="button"
                                                        onClick={() =>
                                                            puedeRegistrar &&
                                                            anularPago(
                                                                a,
                                                                p.id,
                                                                periodo,
                                                            )
                                                        }
                                                        className="rounded-md bg-emerald-500/10 px-2 py-1 text-xs font-medium text-emerald-700 hover:bg-emerald-500/20 dark:text-emerald-400"
                                                        title={`Pagado el ${p.fecha_pago} · ${soles(p.monto)} — clic para anular`}
                                                    >
                                                        {etiquetaPeriodo(
                                                            periodo,
                                                        )}{' '}
                                                        ✓
                                                    </button>
                                                );
                                            }

                                            if (pagando === clave) {
                                                return (
                                                    <span
                                                        key={periodo}
                                                        className="inline-flex items-center gap-1 rounded-md border border-primary/40 bg-primary/5 px-1.5 py-0.5"
                                                    >
                                                        <span className="text-xs font-medium">
                                                            {etiquetaPeriodo(
                                                                periodo,
                                                            )}
                                                        </span>
                                                        <input
                                                            type="date"
                                                            value={
                                                                pago.fecha_pago
                                                            }
                                                            max={hoy}
                                                            onChange={(e) =>
                                                                setPago({
                                                                    ...pago,
                                                                    fecha_pago:
                                                                        e.target
                                                                            .value,
                                                                })
                                                            }
                                                            className="rounded-sm bg-transparent text-xs tabular-nums outline-none"
                                                        />
                                                        <input
                                                            type="number"
                                                            step="0.01"
                                                            min="0.01"
                                                            value={pago.monto}
                                                            autoFocus
                                                            onChange={(e) =>
                                                                setPago({
                                                                    ...pago,
                                                                    monto: e
                                                                        .target
                                                                        .value,
                                                                })
                                                            }
                                                            onKeyDown={(e) => {
                                                                if (
                                                                    e.key ===
                                                                    'Enter'
                                                                ) {
                                                                    confirmarPago(
                                                                        a,
                                                                        periodo,
                                                                    );
                                                                }
                                                                if (
                                                                    e.key ===
                                                                    'Escape'
                                                                ) {
                                                                    setPagando(
                                                                        null,
                                                                    );
                                                                }
                                                            }}
                                                            className="w-16 rounded-sm bg-transparent text-right text-xs tabular-nums outline-none"
                                                        />
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                confirmarPago(
                                                                    a,
                                                                    periodo,
                                                                )
                                                            }
                                                            className="rounded p-0.5 text-emerald-600 hover:bg-emerald-500/10"
                                                            title="Confirmar pago"
                                                        >
                                                            <Check className="size-3.5" />
                                                        </button>
                                                        <button
                                                            type="button"
                                                            onClick={() =>
                                                                setPagando(null)
                                                            }
                                                            className="rounded p-0.5 text-muted-foreground hover:bg-muted"
                                                            title="Cancelar"
                                                        >
                                                            <X className="size-3.5" />
                                                        </button>
                                                    </span>
                                                );
                                            }

                                            return (
                                                <button
                                                    key={periodo}
                                                    type="button"
                                                    disabled={!puedeRegistrar}
                                                    onClick={() =>
                                                        abrirPago(a, periodo)
                                                    }
                                                    className="rounded-md border border-dashed border-border px-2 py-1 text-xs text-muted-foreground transition-colors hover:border-primary/50 hover:text-foreground disabled:cursor-default disabled:hover:border-border"
                                                    title="Marcar como pagado"
                                                >
                                                    {etiquetaPeriodo(periodo)}
                                                </button>
                                            );
                                        })}
                                    </div>
                                </CardContent>
                            </Card>
                        );
                    })}
                </div>
            )}
        </div>
    );
}
