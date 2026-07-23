import { router } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { useConfirm } from '@/components/confirm-dialog';
import { CeldaFecha, CeldaMonto, CeldaSelect, CeldaTexto } from './_celdas';
import type { Movimiento, Opcion } from './index';

type Props = {
    obraId: number;
    depositos: Movimiento[];
    metodos: Opcion[];
    puedeRegistrar: boolean;
    puedeGestionar: boolean;
};

const soles = (n: number) =>
    new Intl.NumberFormat('es-PE', {
        style: 'currency',
        currency: 'PEN',
    }).format(n);

const recargar = () => router.reload({ only: ['movimientos', 'resumen'] });

const entrada =
    'w-full rounded-md border border-border bg-background px-2.5 py-2 text-sm outline-none placeholder:text-muted-foreground/50 focus:ring-1 focus:ring-primary/40';

export default function TablaDepositos({
    obraId,
    depositos,
    metodos,
    puedeRegistrar,
    puedeGestionar,
}: Props) {
    const hoy = new Date().toISOString().slice(0, 10);
    const puedeEditar = puedeRegistrar || puedeGestionar;
    const { confirm, dialog } = useConfirm();

    const filaVacia = {
        fecha: hoy,
        metodo: 'yape',
        descripcion: '',
        monto: '',
    };
    const [nueva, setNueva] = useState(filaVacia);
    const [guardando, setGuardando] = useState(false);

    const nuevaCompleta = nueva.fecha !== '' && parseFloat(nueva.monto) > 0;

    const etiquetaMetodo = (valor: string) =>
        metodos.find((m) => m.value === valor)?.label ?? valor;

    const guardarNueva = () => {
        if (!nuevaCompleta || guardando) {
            return;
        }

        const metodoLabel = etiquetaMetodo(nueva.metodo).toUpperCase();

        setGuardando(true);
        router.post(
            `/obras/${obraId}/caja`,
            {
                tipo: 'ingreso',
                fecha: nueva.fecha,
                metodo: nueva.metodo,
                descripcion:
                    nueva.descripcion.trim() || `DEPÓSITO ${metodoLabel}`,
                monto: nueva.monto,
            },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setNueva({ ...filaVacia, fecha: nueva.fecha });
                    recargar();
                },
                onFinish: () => setGuardando(false),
            },
        );
    };

    const actualizar = (
        m: Movimiento,
        campo: string,
        valor: string | number | null,
    ) => {
        const datos: Record<string, string | number | null> = {
            [campo]: valor,
        };
        router.patch(`/obras/${obraId}/caja/${m.id}`, datos, {
            preserveScroll: true,
            onSuccess: recargar,
        });
    };

    const eliminar = async (m: Movimiento) => {
        const ok = await confirm({
            titulo: `¿Eliminar el depósito «${m.descripcion}»?`,
            destructivo: true,
            confirmar: 'Eliminar depósito',
            descripcion: `Se eliminará el depósito de ${soles(m.monto)} de forma permanente.`,
        });

        if (!ok) {
            return;
        }

        router.delete(`/obras/${obraId}/caja/${m.id}`, {
            preserveScroll: true,
            onSuccess: recargar,
        });
    };

    const total = depositos.reduce((s, m) => s + m.monto, 0);

    return (
        <div>
            {dialog}
            {/* ===================== DESKTOP: tabla ===================== */}
            <div className="hidden overflow-x-auto md:block">
                <table className="w-full border-collapse text-sm">
                    <thead>
                        <tr className="border-b border-border text-left text-xs tracking-wide text-muted-foreground uppercase">
                            <th className="w-36 px-2 py-2 font-medium">
                                Fecha
                            </th>
                            <th className="w-36 px-2 py-2 font-medium">Vía</th>
                            <th className="px-2 py-2 font-medium">Detalle</th>
                            <th className="w-28 px-2 py-2 text-right font-medium">
                                Monto S/
                            </th>
                            <th className="w-12 px-2 py-2" />
                        </tr>
                    </thead>
                    <tbody className="divide-y divide-border/60">
                        {depositos.map((m) => (
                            <tr key={m.id} className="group hover:bg-muted/30">
                                <td>
                                    <CeldaFecha
                                        valor={m.fecha ?? ''}
                                        max={hoy}
                                        deshabilitado={!puedeEditar}
                                        onConfirmar={(v) =>
                                            actualizar(m, 'fecha', v)
                                        }
                                    />
                                </td>
                                <td>
                                    <CeldaSelect
                                        valor={m.metodo ?? ''}
                                        opciones={metodos}
                                        deshabilitado={!puedeEditar}
                                        placeholder="—"
                                        onConfirmar={(v) =>
                                            actualizar(m, 'metodo', v)
                                        }
                                    />
                                </td>
                                <td>
                                    <CeldaTexto
                                        valor={m.descripcion}
                                        deshabilitado={!puedeEditar}
                                        onConfirmar={(v) => {
                                            if (v.trim() !== '') {
                                                actualizar(
                                                    m,
                                                    'descripcion',
                                                    v.trim(),
                                                );
                                            }
                                        }}
                                    />
                                </td>
                                <td>
                                    <CeldaMonto
                                        valor={m.monto}
                                        deshabilitado={!puedeEditar}
                                        onConfirmar={(v) =>
                                            actualizar(m, 'monto', v)
                                        }
                                    />
                                </td>
                                <td>
                                    {puedeGestionar && (
                                        <div className="flex justify-end pr-1 transition-opacity pointer-fine:opacity-0 pointer-fine:group-hover:opacity-100 pointer-fine:focus-within:opacity-100">
                                            <button
                                                type="button"
                                                onClick={() => eliminar(m)}
                                                className="rounded p-1.5 text-muted-foreground hover:bg-muted hover:text-destructive"
                                                title="Eliminar"
                                            >
                                                <Trash2 className="size-4" />
                                            </button>
                                        </div>
                                    )}
                                </td>
                            </tr>
                        ))}
                    </tbody>
                    {depositos.length > 0 && (
                        <tfoot>
                            <tr className="border-t border-border text-sm font-semibold">
                                <td
                                    colSpan={3}
                                    className="px-2 py-2 text-right text-xs tracking-wide text-muted-foreground uppercase"
                                >
                                    Total depósitos
                                </td>
                                <td className="px-2 py-2 text-right tabular-nums">
                                    {soles(total)}
                                </td>
                                <td />
                            </tr>
                        </tfoot>
                    )}
                </table>
            </div>

            {/* ===================== MÓVIL: tarjetas ===================== */}
            <div className="divide-y divide-border/60 md:hidden">
                {depositos.map((m) => (
                    <div key={m.id} className="flex flex-col gap-2 p-3">
                        <div className="flex items-start justify-between gap-2">
                            <div className="min-w-0 flex-1">
                                <CeldaTexto
                                    valor={m.descripcion}
                                    deshabilitado={!puedeEditar}
                                    className="!px-2 font-medium"
                                    onConfirmar={(v) => {
                                        if (v.trim() !== '') {
                                            actualizar(
                                                m,
                                                'descripcion',
                                                v.trim(),
                                            );
                                        }
                                    }}
                                />
                            </div>
                            <div className="shrink-0 text-right text-base font-semibold text-emerald-600 tabular-nums dark:text-emerald-400">
                                {soles(m.monto)}
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-2">
                            <label className="flex flex-col gap-0.5">
                                <span className="px-1 text-[11px] text-muted-foreground uppercase">
                                    Fecha
                                </span>
                                <CeldaFecha
                                    valor={m.fecha ?? ''}
                                    max={hoy}
                                    deshabilitado={!puedeEditar}
                                    onConfirmar={(v) =>
                                        actualizar(m, 'fecha', v)
                                    }
                                />
                            </label>
                            <label className="flex flex-col gap-0.5">
                                <span className="px-1 text-[11px] text-muted-foreground uppercase">
                                    Vía
                                </span>
                                <CeldaSelect
                                    valor={m.metodo ?? ''}
                                    opciones={metodos}
                                    deshabilitado={!puedeEditar}
                                    placeholder="—"
                                    onConfirmar={(v) =>
                                        actualizar(m, 'metodo', v)
                                    }
                                />
                            </label>
                            <label className="flex flex-col gap-0.5">
                                <span className="px-1 text-[11px] text-muted-foreground uppercase">
                                    Monto S/
                                </span>
                                <CeldaMonto
                                    valor={m.monto}
                                    deshabilitado={!puedeEditar}
                                    onConfirmar={(v) =>
                                        actualizar(m, 'monto', v)
                                    }
                                />
                            </label>
                        </div>
                        {puedeGestionar && (
                            <div className="flex items-center justify-end gap-1 border-t border-border/50 pt-1">
                                <button
                                    type="button"
                                    onClick={() => eliminar(m)}
                                    className="rounded p-1.5 text-muted-foreground hover:bg-muted hover:text-destructive"
                                    title="Eliminar"
                                >
                                    <Trash2 className="size-4" />
                                </button>
                            </div>
                        )}
                    </div>
                ))}
                {depositos.length > 0 && (
                    <div className="flex items-center justify-between px-3 py-2.5 text-sm font-semibold">
                        <span className="text-xs tracking-wide text-muted-foreground uppercase">
                            Total depósitos
                        </span>
                        <span className="tabular-nums">{soles(total)}</span>
                    </div>
                )}
            </div>

            {/* ===================== Agregar nuevo ===================== */}
            {puedeRegistrar && (
                <div className="border-t-2 border-dashed border-border bg-primary/[0.03] p-3">
                    <div className="mb-2 text-xs font-medium tracking-wide text-muted-foreground uppercase">
                        Agregar depósito
                    </div>
                    <div className="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-[9rem_9rem_1fr_8rem_auto]">
                        <input
                            type="date"
                            value={nueva.fecha}
                            max={hoy}
                            aria-label="Fecha"
                            onChange={(e) =>
                                setNueva({ ...nueva, fecha: e.target.value })
                            }
                            className={`${entrada} tabular-nums`}
                        />
                        <select
                            value={nueva.metodo}
                            aria-label="Vía"
                            onChange={(e) =>
                                setNueva({ ...nueva, metodo: e.target.value })
                            }
                            className={entrada}
                        >
                            {metodos.map((m) => (
                                <option key={m.value} value={m.value}>
                                    {m.label}
                                </option>
                            ))}
                        </select>
                        <input
                            type="text"
                            value={nueva.descripcion}
                            placeholder="Detalle (opcional)"
                            aria-label="Detalle"
                            onChange={(e) =>
                                setNueva({
                                    ...nueva,
                                    descripcion: e.target.value,
                                })
                            }
                            onKeyDown={(e) => {
                                if (e.key === 'Enter') {
                                    guardarNueva();
                                }
                            }}
                            className={`${entrada} col-span-full sm:col-span-2 lg:col-span-1`}
                        />
                        <input
                            type="number"
                            step="0.01"
                            min="0.01"
                            inputMode="decimal"
                            value={nueva.monto}
                            placeholder="Monto S/"
                            aria-label="Monto"
                            onChange={(e) =>
                                setNueva({ ...nueva, monto: e.target.value })
                            }
                            onKeyDown={(e) => {
                                if (e.key === 'Enter') {
                                    guardarNueva();
                                }
                            }}
                            className={`${entrada} text-right tabular-nums`}
                        />
                        <button
                            type="button"
                            onClick={guardarNueva}
                            disabled={!nuevaCompleta || guardando}
                            className="col-span-full inline-flex items-center justify-center gap-1.5 rounded-md bg-primary px-3 py-2 text-sm font-medium text-primary-foreground transition-opacity disabled:opacity-40 lg:col-span-1"
                        >
                            <Plus className="size-4" />
                            Agregar
                        </button>
                    </div>
                </div>
            )}

            {depositos.length === 0 && !puedeRegistrar && (
                <p className="p-6 text-center text-sm text-muted-foreground">
                    Aún no hay depósitos registrados.
                </p>
            )}
        </div>
    );
}
