import { ChevronLeft, ChevronRight } from 'lucide-react';
import { useMemo, useState } from 'react';
import { Button } from '@/components/ui/button';

export type EventoCalendarioBase = {
    id: number;
    titulo: string;
    color: string;
    tipo_label: string;
    fecha_inicio_iso: string | null;
    fecha_fin_iso: string | null;
    vencido?: boolean;
    obra?: { codigo: string; nombre: string };
};

type Props<T extends EventoCalendarioBase> = {
    eventos: T[];
    onSeleccionar: (evento: T) => void;
    onClickDia?: (iso: string) => void;
};

const MESES = [
    'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
    'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre',
];

const DIAS_SEMANA = ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb', 'Dom'];

export default function CalendarioMes<T extends EventoCalendarioBase>({
    eventos,
    onSeleccionar,
    onClickDia,
}: Props<T>) {
    const hoy = new Date();
    const [anio, setAnio] = useState(hoy.getFullYear());
    const [mes, setMes] = useState(hoy.getMonth());

    /**
     * Mapa fecha-iso → eventos. Si un evento abarca un rango, aparece en
     * todos los días del rango.
     */
    const eventosPorDia = useMemo(() => {
        const mapa = new Map<string, T[]>();
        const push = (iso: string, ev: T) => {
            const lista = mapa.get(iso) ?? [];
            lista.push(ev);
            mapa.set(iso, lista);
        };

        for (const ev of eventos) {
            if (!ev.fecha_inicio_iso) continue;
            const fin = ev.fecha_fin_iso ?? ev.fecha_inicio_iso;
            const inicio = new Date(ev.fecha_inicio_iso + 'T00:00:00');
            const final = new Date(fin + 'T00:00:00');
            for (
                let d = new Date(inicio);
                d <= final;
                d.setDate(d.getDate() + 1)
            ) {
                const iso =
                    d.getFullYear() +
                    '-' +
                    String(d.getMonth() + 1).padStart(2, '0') +
                    '-' +
                    String(d.getDate()).padStart(2, '0');
                push(iso, ev);
            }
        }
        return mapa;
    }, [eventos]);

    const primerDia = new Date(anio, mes, 1);
    const ultimoDia = new Date(anio, mes + 1, 0);
    const numDias = ultimoDia.getDate();
    const offset = (primerDia.getDay() + 6) % 7;

    const celdas: (number | null)[] = [];
    for (let i = 0; i < offset; i++) celdas.push(null);
    for (let d = 1; d <= numDias; d++) celdas.push(d);
    while (celdas.length % 7 !== 0) celdas.push(null);

    const fechaIso = (dia: number) =>
        `${anio}-${String(mes + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;

    const irMesAnterior = () => {
        if (mes === 0) {
            setMes(11);
            setAnio(anio - 1);
        } else setMes(mes - 1);
    };
    const irMesSiguiente = () => {
        if (mes === 11) {
            setMes(0);
            setAnio(anio + 1);
        } else setMes(mes + 1);
    };
    const irHoy = () => {
        setMes(hoy.getMonth());
        setAnio(hoy.getFullYear());
    };

    return (
        <div className="space-y-3">
            <div className="flex items-center justify-between">
                <h3 className="text-base font-semibold">
                    {MESES[mes]} {anio}
                </h3>
                <div className="flex gap-1">
                    <Button size="sm" variant="outline" onClick={irMesAnterior}>
                        <ChevronLeft className="size-4" />
                    </Button>
                    <Button size="sm" variant="outline" onClick={irHoy}>
                        Hoy
                    </Button>
                    <Button size="sm" variant="outline" onClick={irMesSiguiente}>
                        <ChevronRight className="size-4" />
                    </Button>
                </div>
            </div>

            <div className="grid grid-cols-7 gap-1 text-center text-[11px] font-semibold tracking-wide text-muted-foreground uppercase">
                {DIAS_SEMANA.map((d) => (
                    <div key={d} className="py-1">
                        {d}
                    </div>
                ))}
            </div>

            <div className="grid grid-cols-7 gap-1">
                {celdas.map((dia, i) => {
                    if (dia === null) {
                        return <div key={i} className="min-h-[96px]" />;
                    }
                    const iso = fechaIso(dia);
                    const evs = eventosPorDia.get(iso) ?? [];
                    const esHoy =
                        anio === hoy.getFullYear() &&
                        mes === hoy.getMonth() &&
                        dia === hoy.getDate();

                    return (
                        <button
                            type="button"
                            key={i}
                            onClick={() => onClickDia?.(iso)}
                            className={
                                'flex min-h-[96px] flex-col gap-1 rounded-md border p-1.5 text-left transition-colors hover:bg-muted/30 ' +
                                (esHoy
                                    ? 'border-primary bg-primary/5'
                                    : 'border-border')
                            }
                        >
                            <div
                                className={
                                    'text-xs font-semibold ' +
                                    (esHoy ? 'text-primary' : 'text-foreground')
                                }
                            >
                                {dia}
                            </div>
                            <div className="space-y-0.5">
                                {evs.slice(0, 3).map((ev, j) => (
                                    <button
                                        key={`${ev.id}-${j}`}
                                        type="button"
                                        onClick={(e) => {
                                            e.stopPropagation();
                                            onSeleccionar(ev);
                                        }}
                                        className="flex w-full items-center gap-1 truncate rounded px-1 py-0.5 text-left text-[10px] font-medium text-white hover:opacity-90"
                                        style={{ backgroundColor: ev.color }}
                                        title={ev.titulo}
                                    >
                                        <span className="truncate">
                                            {ev.titulo}
                                        </span>
                                    </button>
                                ))}
                                {evs.length > 3 && (
                                    <div className="text-[10px] text-muted-foreground">
                                        +{evs.length - 3} más
                                    </div>
                                )}
                            </div>
                        </button>
                    );
                })}
            </div>
        </div>
    );
}
