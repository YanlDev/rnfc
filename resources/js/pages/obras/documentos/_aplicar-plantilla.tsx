import { router } from '@inertiajs/react';
import {
    ChevronDown,
    ChevronRight,
    FolderTree,
    Sparkles,
} from 'lucide-react';
import { useMemo, useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import { Spinner } from '@/components/ui/spinner';

export type SubcarpetaPlantilla = {
    nombre: string;
    hijos: string[];
};

export type GrupoPlantilla = {
    nombre: string;
    descripcion: string | null;
    subcarpetas: SubcarpetaPlantilla[];
};

type Props = {
    open: boolean;
    onOpenChange: (open: boolean) => void;
    plantilla: GrupoPlantilla[];
    obraId: number;
};

export default function AplicarPlantillaDialog({
    open,
    onOpenChange,
    plantilla,
    obraId,
}: Props) {
    const [seleccion, setSeleccion] = useState<Record<string, Set<string>>>({});
    const [expandidos, setExpandidos] = useState<Set<string>>(new Set());
    const [enviando, setEnviando] = useState(false);

    const totalSeleccionadas = useMemo(
        () =>
            Object.values(seleccion).reduce((acc, set) => acc + set.size, 0),
        [seleccion],
    );

    const toggleExpandido = (nombre: string) => {
        const next = new Set(expandidos);
        if (next.has(nombre)) next.delete(nombre);
        else next.add(nombre);
        setExpandidos(next);
    };

    const grupoSeleccionado = (nombre: string) => {
        const set = seleccion[nombre];
        return set && set.size > 0;
    };

    const grupoCompleto = (g: GrupoPlantilla) => {
        const set = seleccion[g.nombre];
        return set && set.size === g.subcarpetas.length;
    };

    const toggleGrupo = (g: GrupoPlantilla) => {
        const next = { ...seleccion };
        if (grupoCompleto(g)) {
            delete next[g.nombre];
        } else {
            next[g.nombre] = new Set(g.subcarpetas.map((s) => s.nombre));
        }
        setSeleccion(next);
        // Auto-expandir al seleccionar.
        if (!expandidos.has(g.nombre) && !grupoCompleto(g)) {
            const nuevos = new Set(expandidos);
            nuevos.add(g.nombre);
            setExpandidos(nuevos);
        }
    };

    const toggleSub = (g: GrupoPlantilla, sub: string) => {
        const next = { ...seleccion };
        const set = new Set(next[g.nombre] ?? []);
        if (set.has(sub)) set.delete(sub);
        else set.add(sub);
        if (set.size === 0) delete next[g.nombre];
        else next[g.nombre] = set;
        setSeleccion(next);
    };

    const seleccionarTodo = () => {
        const next: Record<string, Set<string>> = {};
        for (const g of plantilla) {
            next[g.nombre] = new Set(g.subcarpetas.map((s) => s.nombre));
        }
        setSeleccion(next);
    };

    const limpiar = () => setSeleccion({});

    const aplicar = () => {
        if (totalSeleccionadas === 0) return;
        const payload: Record<string, string[]> = {};
        for (const [grupo, subs] of Object.entries(seleccion)) {
            payload[grupo] = Array.from(subs);
        }
        setEnviando(true);
        router.post(
            `/obras/${obraId}/carpetas/plantilla`,
            { seleccion: payload },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setSeleccion({});
                    setExpandidos(new Set());
                    onOpenChange(false);
                },
                onFinish: () => setEnviando(false),
            },
        );
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-w-3xl">
                <DialogHeader>
                    <DialogTitle className="flex items-center gap-2">
                        <FolderTree className="size-5 text-primary" />
                        Aplicar plantilla de carpetas
                    </DialogTitle>
                    <DialogDescription>
                        Selecciona los grupos y subcarpetas que quieres crear.
                        Las que ya existan se respetarán; no se duplicará nada.
                    </DialogDescription>
                </DialogHeader>

                <div className="flex items-center justify-between border-y border-border py-2 text-sm">
                    <div className="text-muted-foreground">
                        <strong className="text-foreground">{totalSeleccionadas}</strong>{' '}
                        subcarpeta(s) seleccionada(s) en{' '}
                        <strong className="text-foreground">
                            {Object.keys(seleccion).length}
                        </strong>{' '}
                        grupo(s).
                    </div>
                    <div className="flex gap-2">
                        <Button
                            type="button"
                            size="sm"
                            variant="ghost"
                            onClick={seleccionarTodo}
                        >
                            Seleccionar todo
                        </Button>
                        <Button
                            type="button"
                            size="sm"
                            variant="ghost"
                            onClick={limpiar}
                            disabled={totalSeleccionadas === 0}
                        >
                            Limpiar
                        </Button>
                    </div>
                </div>

                <div className="max-h-[55vh] overflow-y-auto pr-1">
                    <ul className="space-y-1">
                        {plantilla.map((g) => {
                            const expandido = expandidos.has(g.nombre);
                            const completoGrupo = grupoCompleto(g);
                            const parcial =
                                grupoSeleccionado(g.nombre) && !completoGrupo;

                            return (
                                <li
                                    key={g.nombre}
                                    className="rounded-md border border-border"
                                >
                                    <div className="flex items-center gap-2 p-2.5">
                                        <button
                                            type="button"
                                            onClick={() => toggleExpandido(g.nombre)}
                                            className="rounded p-0.5 hover:bg-muted"
                                            aria-label="Expandir grupo"
                                        >
                                            {expandido ? (
                                                <ChevronDown className="size-4" />
                                            ) : (
                                                <ChevronRight className="size-4" />
                                            )}
                                        </button>
                                        <Checkbox
                                            id={`grupo-${g.nombre}`}
                                            checked={
                                                completoGrupo
                                                    ? true
                                                    : parcial
                                                      ? 'indeterminate'
                                                      : false
                                            }
                                            onCheckedChange={() => toggleGrupo(g)}
                                        />
                                        <label
                                            htmlFor={`grupo-${g.nombre}`}
                                            className="flex flex-1 cursor-pointer flex-col"
                                        >
                                            <span className="text-sm font-semibold">
                                                {g.nombre}
                                            </span>
                                            {g.descripcion && (
                                                <span className="text-xs text-muted-foreground">
                                                    {g.descripcion}
                                                </span>
                                            )}
                                        </label>
                                        <Badge
                                            variant="secondary"
                                            className="text-[10px]"
                                        >
                                            {g.subcarpetas.length} subcarpetas
                                        </Badge>
                                    </div>

                                    {expandido && (
                                        <ul className="grid gap-1 border-t border-border bg-muted/30 px-3 py-2 sm:grid-cols-2">
                                            {g.subcarpetas.map((s) => {
                                                const checked = seleccion[g.nombre]?.has(s.nombre) ?? false;
                                                return (
                                                    <li
                                                        key={s.nombre}
                                                        className="flex items-center gap-2"
                                                    >
                                                        <Checkbox
                                                            id={`sub-${g.nombre}-${s.nombre}`}
                                                            checked={checked}
                                                            onCheckedChange={() =>
                                                                toggleSub(g, s.nombre)
                                                            }
                                                        />
                                                        <label
                                                            htmlFor={`sub-${g.nombre}-${s.nombre}`}
                                                            className="flex-1 cursor-pointer text-sm"
                                                        >
                                                            {s.nombre}
                                                            {s.hijos.length > 0 && (
                                                                <span className="ml-1 text-[10px] text-muted-foreground">
                                                                    (+{s.hijos.length} sub)
                                                                </span>
                                                            )}
                                                        </label>
                                                    </li>
                                                );
                                            })}
                                        </ul>
                                    )}
                                </li>
                            );
                        })}
                    </ul>
                </div>

                <DialogFooter>
                    <Button
                        variant="outline"
                        onClick={() => onOpenChange(false)}
                        disabled={enviando}
                    >
                        Cancelar
                    </Button>
                    <Button
                        onClick={aplicar}
                        disabled={totalSeleccionadas === 0 || enviando}
                    >
                        {enviando ? <Spinner /> : <Sparkles className="size-4" />}
                        Crear {totalSeleccionadas} carpeta(s)
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
