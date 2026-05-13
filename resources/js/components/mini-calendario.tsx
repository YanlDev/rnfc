import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useMemo, useState } from 'react';

export type MiniEvento = {
    id: number;
    fecha_inicio_iso: string | null;
    fecha_fin_iso: string | null;
    color: string;
};

type Props = {
    eventos: MiniEvento[];
    /** Iso strings YYYY-MM-DD para marcar el día seleccionado opcional. */
    diaSeleccionado?: string | null;
    onClickDia?: (iso: string) => void;
};

const DIAS_SEMANA = ['L', 'M', 'X', 'J', 'V', 'S', 'D'];
const MESES = [
    'Enero',
    'Febrero',
    'Marzo',
    'Abril',
    'Mayo',
    'Junio',
    'Julio',
    'Agosto',
    'Setiembre',
    'Octubre',
    'Noviembre',
    'Diciembre',
];

function expandir(ev: MiniEvento): string[] {
    if (!ev.fecha_inicio_iso) return [];
    const inicio = new Date(ev.fecha_inicio_iso + 'T00:00:00');
    const fin = ev.fecha_fin_iso
        ? new Date(ev.fecha_fin_iso + 'T00:00:00')
        : inicio;
    const result: string[] = [];
    const cursor = new Date(inicio);
    while (cursor <= fin) {
        const y = cursor.getFullYear();
        const m = String(cursor.getMonth() + 1).padStart(2, '0');
        const d = String(cursor.getDate()).padStart(2, '0');
        result.push(`${y}-${m}-${d}`);
        cursor.setDate(cursor.getDate() + 1);
        // Safety net en caso de fechas raras
        if (result.length > 366) break;
    }
    return result;
}

export default function MiniCalendario({
    eventos,
    diaSeleccionado,
    onClickDia,
}: Props) {
    const hoy = new Date();
    const [anio, setAnio] = useState(hoy.getFullYear());
    const [mes, setMes] = useState(hoy.getMonth());

    const coloresPorDia = useMemo(() => {
        const mapa = new Map<string, string[]>();
        for (const ev of eventos) {
            for (const iso of expandir(ev)) {
                const colores = mapa.get(iso) ?? [];
                if (!colores.includes(ev.color)) {
                    colores.push(ev.color);
                }
                mapa.set(iso, colores);
            }
        }
        return mapa;
    }, [eventos]);

    const primerDia = new Date(anio, mes, 1);
    const ultimoDia = new Date(anio, mes + 1, 0);
    const numDias = ultimoDia.getDate();
    const offsetInicial = (primerDia.getDay() + 6) % 7;

    const celdas: (number | null)[] = [];
    for (let i = 0; i < offsetInicial; i++) celdas.push(null);
    for (let d = 1; d <= numDias; d++) celdas.push(d);
    while (celdas.length % 7 !== 0) celdas.push(null);

    const fechaIso = (dia: number) => {
        const m = String(mes + 1).padStart(2, '0');
        const d = String(dia).padStart(2, '0');
        return `${anio}-${m}-${d}`;
    };

    const irMesAnterior = () => {
        if (mes === 0) {
            setMes(11);
            setAnio(anio - 1);
        } else {
            setMes(mes - 1);
        }
    };
    const irMesSiguiente = () => {
        if (mes === 11) {
            setMes(0);
            setAnio(anio + 1);
        } else {
            setMes(mes + 1);
        }
    };

    return (
        <div className="space-y-2">
            <div className="flex items-center justify-between">
                <button
                    type="button"
                    onClick={irMesAnterior}
                    className="rounded p-1 hover:bg-muted"
                    aria-label="Mes anterior"
                >
                    <ChevronLeft className="size-3.5" />
                </button>
                <div className="text-xs font-semibold">
                    {MESES[mes]} {anio}
                </div>
                <button
                    type="button"
                    onClick={irMesSiguiente}
                    className="rounded p-1 hover:bg-muted"
                    aria-label="Mes siguiente"
                >
                    <ChevronRight className="size-3.5" />
                </button>
            </div>

            <div className="grid grid-cols-7 gap-0.5 text-center text-[9px] font-semibold text-muted-foreground">
                {DIAS_SEMANA.map((d) => (
                    <div key={d}>{d}</div>
                ))}
            </div>

            <div className="grid grid-cols-7 gap-0.5">
                {celdas.map((dia, i) => {
                    if (dia === null) {
                        return <div key={i} className="aspect-square" />;
                    }
                    const iso = fechaIso(dia);
                    const colores = coloresPorDia.get(iso) ?? [];
                    const esHoy =
                        anio === hoy.getFullYear() &&
                        mes === hoy.getMonth() &&
                        dia === hoy.getDate();
                    const esSeleccionado = iso === diaSeleccionado;

                    return (
                        <button
                            key={i}
                            type="button"
                            onClick={() => onClickDia?.(iso)}
                            className={
                                'group relative flex aspect-square flex-col items-center justify-center rounded text-[11px] transition-colors ' +
                                (esSeleccionado
                                    ? 'bg-primary text-primary-foreground'
                                    : esHoy
                                      ? 'bg-primary/10 font-semibold text-primary'
                                      : colores.length > 0
                                        ? 'bg-muted/50 hover:bg-muted'
                                        : 'hover:bg-muted/50')
                            }
                        >
                            <span>{dia}</span>
                            {colores.length > 0 && (
                                <div className="absolute bottom-0.5 flex gap-0.5">
                                    {colores.slice(0, 3).map((c, idx) => (
                                        <span
                                            key={idx}
                                            className="inline-block size-1 rounded-full"
                                            style={{ backgroundColor: c }}
                                        />
                                    ))}
                                </div>
                            )}
                        </button>
                    );
                })}
            </div>
        </div>
    );
}
