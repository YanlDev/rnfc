import { router } from '@inertiajs/react';
import { Plus, Trash2 } from 'lucide-react';
import { useRef, useState } from 'react';
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

export default function TablaDepositos({
    obraId,
    depositos,
    metodos,
    puedeRegistrar,
    puedeGestionar,
}: Props) {
    const hoy = new Date().toISOString().slice(0, 10);
    const puedeEditar = puedeRegistrar || puedeGestionar;

    const filaVacia = { fecha: hoy, metodo: 'yape', descripcion: '', monto: '' };
    const [nueva, setNueva] = useState(filaVacia);
    const [guardando, setGuardando] = useState(false);
    const primeraCeldaRef = useRef<HTMLInputElement>(null);

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
                    primeraCeldaRef.current?.focus();
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

    const eliminar = (m: Movimiento) => {
        if (!confirm(`¿Eliminar el depósito «${m.descripcion}»?`)) {
            return;
        }

        router.delete(`/obras/${obraId}/caja/${m.id}`, {
            preserveScroll: true,
            onSuccess: recargar,
        });
    };

    return (
        <div className="overflow-x-auto">
            <table className="w-full min-w-[560px] border-collapse text-sm">
                <thead>
                    <tr className="border-b border-border text-left text-xs uppercase tracking-wide text-muted-foreground">
                        <th className="w-36 px-2 py-2 font-medium">Fecha</th>
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
                                    <div className="flex justify-end pr-1">
                                        <button
                                            type="button"
                                            onClick={() => eliminar(m)}
                                            className="rounded p-1 text-muted-foreground/40 opacity-0 transition-opacity hover:bg-muted hover:text-destructive group-hover:opacity-100"
                                            title="Eliminar"
                                        >
                                            <Trash2 className="size-4" />
                                        </button>
                                    </div>
                                )}
                            </td>
                        </tr>
                    ))}

                    {/* Fila de entrada */}
                    {puedeRegistrar && (
                        <tr className="bg-primary/[0.03]">
                            <td>
                                <input
                                    ref={primeraCeldaRef}
                                    type="date"
                                    value={nueva.fecha}
                                    max={hoy}
                                    onChange={(e) =>
                                        setNueva({
                                            ...nueva,
                                            fecha: e.target.value,
                                        })
                                    }
                                    className="w-full rounded-sm bg-transparent px-2 py-1.5 text-sm tabular-nums outline-none focus:bg-primary/5 focus:ring-1 focus:ring-primary/40"
                                />
                            </td>
                            <td>
                                <select
                                    value={nueva.metodo}
                                    onChange={(e) =>
                                        setNueva({
                                            ...nueva,
                                            metodo: e.target.value,
                                        })
                                    }
                                    className="w-full appearance-none rounded-sm bg-transparent px-2 py-1.5 text-sm outline-none focus:bg-primary/5 focus:ring-1 focus:ring-primary/40"
                                >
                                    {metodos.map((m) => (
                                        <option key={m.value} value={m.value}>
                                            {m.label}
                                        </option>
                                    ))}
                                </select>
                            </td>
                            <td>
                                <input
                                    type="text"
                                    value={nueva.descripcion}
                                    placeholder="Detalle (opcional)"
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
                                    className="w-full rounded-sm bg-transparent px-2 py-1.5 text-sm outline-none placeholder:text-muted-foreground/50 focus:bg-primary/5 focus:ring-1 focus:ring-primary/40"
                                />
                            </td>
                            <td>
                                <input
                                    type="number"
                                    step="0.01"
                                    min="0.01"
                                    inputMode="decimal"
                                    value={nueva.monto}
                                    placeholder="0.00"
                                    onChange={(e) =>
                                        setNueva({
                                            ...nueva,
                                            monto: e.target.value,
                                        })
                                    }
                                    onKeyDown={(e) => {
                                        if (e.key === 'Enter') {
                                            guardarNueva();
                                        }
                                    }}
                                    className="w-full rounded-sm bg-transparent px-2 py-1.5 text-right text-sm tabular-nums outline-none placeholder:text-muted-foreground/50 focus:bg-primary/5 focus:ring-1 focus:ring-primary/40"
                                />
                            </td>
                            <td>
                                <div className="flex justify-end pr-1">
                                    <button
                                        type="button"
                                        onClick={guardarNueva}
                                        disabled={!nuevaCompleta || guardando}
                                        className="rounded p-1 text-primary transition-opacity disabled:opacity-25"
                                        title="Agregar (Enter)"
                                    >
                                        <Plus className="size-4" />
                                    </button>
                                </div>
                            </td>
                        </tr>
                    )}
                </tbody>
                {depositos.length > 0 && (
                    <tfoot>
                        <tr className="border-t border-border text-sm font-semibold">
                            <td
                                colSpan={3}
                                className="px-2 py-2 text-right text-xs uppercase tracking-wide text-muted-foreground"
                            >
                                Total depósitos
                            </td>
                            <td className="px-2 py-2 text-right tabular-nums">
                                {soles(
                                    depositos.reduce((s, m) => s + m.monto, 0),
                                )}
                            </td>
                            <td />
                        </tr>
                    </tfoot>
                )}
            </table>
        </div>
    );
}
