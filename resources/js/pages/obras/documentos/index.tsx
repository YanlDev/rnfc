import { Head, Link, router, useForm } from '@inertiajs/react';
import {
    ArrowLeft,
    ChevronDown,
    ChevronRight,
    Folder,
    FolderOpen,
    FolderPlus,
    FolderTree,
    LayoutGrid,
    List,
    Pencil,
    Plus,
    Search,
    Sparkles,
    Trash2,
} from 'lucide-react';
import { memo, useMemo, useState } from 'react';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import obras from '@/routes/obras';
import AplicarPlantillaDialog from './_aplicar-plantilla';
import type { GrupoPlantilla } from './_aplicar-plantilla';
import DocumentoCard from './_documento-card';
import type { DocumentoCardData } from './_documento-card';
import Dropzone from './_dropzone';
import PreviewModal from './_preview-modal';
import type { DocumentoPreview } from './_preview-modal';

type Carpeta = {
    id: number;
    parent_id: number | null;
    nombre: string;
    ruta: string;
    orden: number;
    documentos_count: number;
};

type ObraResumen = {
    id: number;
    codigo: string;
    nombre: string;
};

type CarpetaActiva = {
    id: number;
    nombre: string;
    ruta: string;
};

type Props = {
    obra: ObraResumen;
    carpetas: Carpeta[];
    plantillaDisponible: GrupoPlantilla[];
    puedeAdministrar: boolean;
    puedeSubir: boolean;
    puedeEliminarDoc: boolean;
    carpetaActiva: CarpetaActiva | null;
    documentos: DocumentoCardData[];
};

type NodoArbol = Carpeta & { hijos: NodoArbol[] };

function construirArbol(carpetas: Carpeta[]): NodoArbol[] {
    const mapa = new Map<number, NodoArbol>();
    carpetas.forEach((c) => mapa.set(c.id, { ...c, hijos: [] }));
    const raices: NodoArbol[] = [];
    mapa.forEach((nodo) => {
        if (nodo.parent_id === null) {
            raices.push(nodo);
        } else {
            const padre = mapa.get(nodo.parent_id);

            if (padre) {
                padre.hijos.push(nodo);
            }
        }
    });

    return raices;
}

/** Cuenta recursivamente archivos y subcarpetas dentro de un nodo. */
function contarContenido(nodo: NodoArbol): {
    archivos: number;
    subcarpetas: number;
} {
    let archivos = nodo.documentos_count ?? 0;
    let subcarpetas = nodo.hijos.length;

    for (const h of nodo.hijos) {
        const r = contarContenido(h);
        archivos += r.archivos;
        subcarpetas += r.subcarpetas;
    }

    return { archivos, subcarpetas };
}

function obtenerLinea(carpetaId: number, carpetas: Carpeta[]): Carpeta[] {
    const mapa = new Map(carpetas.map((c) => [c.id, c]));
    const linea: Carpeta[] = [];
    let actual: Carpeta | undefined = mapa.get(carpetaId);

    while (actual) {
        linea.unshift(actual);
        actual = actual.parent_id ? mapa.get(actual.parent_id) : undefined;
    }

    return linea;
}

const NodoCarpeta = memo(function NodoCarpeta({
    nodo,
    nivel,
    obraId,
    puedeAdministrar,
    expandidos,
    setExpandidos,
    carpetaActivaId,
}: {
    nodo: NodoArbol;
    nivel: number;
    obraId: number;
    puedeAdministrar: boolean;
    expandidos: Set<number>;
    setExpandidos: (s: Set<number>) => void;
    carpetaActivaId: number | null;
}) {
    const [mostrandoNueva, setMostrandoNueva] = useState(false);
    const [editando, setEditando] = useState(false);
    const form = useForm({ nombre: '', parent_id: nodo.id });
    const formRename = useForm({ nombre: nodo.nombre });
    const expandido = expandidos.has(nodo.id);
    const tieneHijos = nodo.hijos.length > 0;
    const esActiva = carpetaActivaId === nodo.id;

    const toggle = () => {
        const next = new Set(expandidos);

        if (next.has(nodo.id)) {
            next.delete(nodo.id);
        } else {
            next.add(nodo.id);
        }

        setExpandidos(next);
    };

    const seleccionar = () => {
        router.get(
            `/obras/${obraId}/documentos`,
            { carpeta: nodo.id },
            { preserveScroll: true, preserveState: true },
        );
    };

    const crearSubcarpeta = (e: React.FormEvent) => {
        e.preventDefault();
        form.post(`/obras/${obraId}/carpetas`, {
            preserveScroll: true,
            onSuccess: () => {
                form.reset('nombre');
                setMostrandoNueva(false);
                const next = new Set(expandidos);
                next.add(nodo.id);
                setExpandidos(next);
            },
        });
    };

    const eliminar = (e: React.MouseEvent) => {
        e.stopPropagation();

        const { archivos, subcarpetas } = contarContenido(nodo);
        const partes: string[] = [];

        if (subcarpetas > 0) {
            partes.push(
                `${subcarpetas} ${subcarpetas === 1 ? 'subcarpeta' : 'subcarpetas'}`,
            );
        }

        if (archivos > 0) {
            partes.push(
                `${archivos} ${archivos === 1 ? 'archivo' : 'archivos'}`,
            );
        }

        const detalle = partes.length
            ? `\n\nSe eliminarán también ${partes.join(' y ')} de forma permanente. Esta acción no se puede deshacer.`
            : '';

        if (!confirm(`¿Eliminar la carpeta «${nodo.nombre}»?${detalle}`)) {
            return;
        }

        router.delete(`/obras/${obraId}/carpetas/${nodo.id}`, {
            preserveScroll: true,
        });
    };

    const iniciarRename = (e: React.MouseEvent) => {
        e.stopPropagation();
        formRename.setData('nombre', nodo.nombre);
        formRename.clearErrors();
        setEditando(true);
    };

    const guardarRename = (e: React.FormEvent) => {
        e.preventDefault();
        const nuevo = formRename.data.nombre.trim();

        if (!nuevo || nuevo === nodo.nombre) {
            setEditando(false);

            return;
        }

        formRename.patch(`/obras/${obraId}/carpetas/${nodo.id}`, {
            preserveScroll: true,
            onSuccess: () => setEditando(false),
        });
    };

    return (
        <li>
            <div
                className={
                    'group flex cursor-pointer items-center gap-1 rounded-md px-1 py-1 text-sm transition-colors ' +
                    (esActiva
                        ? 'bg-primary/10 text-primary'
                        : 'hover:bg-muted/50')
                }
                style={{ paddingLeft: `${nivel * 14 + 4}px` }}
                onClick={seleccionar}
            >
                <button
                    type="button"
                    onClick={(e) => {
                        e.stopPropagation();
                        toggle();
                    }}
                    disabled={!tieneHijos}
                    className={
                        'rounded p-0.5 ' +
                        (tieneHijos ? 'hover:bg-muted' : 'opacity-30')
                    }
                >
                    {expandido ? (
                        <ChevronDown className="size-3.5" />
                    ) : (
                        <ChevronRight className="size-3.5" />
                    )}
                </button>
                {expandido && tieneHijos ? (
                    <FolderOpen
                        className={
                            'size-4 shrink-0 ' +
                            (esActiva
                                ? 'text-primary'
                                : nivel === 0
                                  ? 'text-primary'
                                  : 'text-muted-foreground')
                        }
                    />
                ) : (
                    <Folder
                        className={
                            'size-4 shrink-0 ' +
                            (esActiva
                                ? 'text-primary'
                                : nivel === 0
                                  ? 'text-primary'
                                  : 'text-muted-foreground')
                        }
                    />
                )}
                {editando ? (
                    <form
                        onClick={(e) => e.stopPropagation()}
                        onSubmit={guardarRename}
                        className="flex flex-1 items-center gap-1"
                    >
                        <Input
                            autoFocus
                            value={formRename.data.nombre}
                            onChange={(e) =>
                                formRename.setData('nombre', e.target.value)
                            }
                            onKeyDown={(e) => {
                                if (e.key === 'Escape') {
                                    setEditando(false);
                                }
                            }}
                            onBlur={guardarRename}
                            className="h-6 text-sm"
                        />
                    </form>
                ) : (
                    <span
                        className={
                            'flex-1 truncate ' +
                            (nivel === 0 ? 'text-xs font-semibold' : '')
                        }
                        title={nodo.nombre}
                        onDoubleClick={iniciarRename}
                    >
                        {nodo.nombre}
                    </span>
                )}
                {puedeAdministrar && !editando && (
                    <div className="flex gap-0.5 opacity-0 transition-opacity group-hover:opacity-100">
                        <button
                            type="button"
                            onClick={(e) => {
                                e.stopPropagation();
                                setMostrandoNueva((v) => !v);
                            }}
                            className="rounded p-0.5 hover:bg-muted"
                            title="Nueva subcarpeta"
                        >
                            <FolderPlus className="size-3.5" />
                        </button>
                        <button
                            type="button"
                            onClick={iniciarRename}
                            className="rounded p-0.5 hover:bg-muted"
                            title="Renombrar"
                        >
                            <Pencil className="size-3.5" />
                        </button>
                        <button
                            type="button"
                            onClick={eliminar}
                            className="rounded p-0.5 hover:bg-muted"
                            title="Eliminar"
                        >
                            <Trash2 className="size-3.5 text-destructive" />
                        </button>
                    </div>
                )}
            </div>
            {formRename.errors.nombre && editando && (
                <div
                    className="px-1 text-xs text-destructive"
                    style={{ paddingLeft: `${nivel * 14 + 22}px` }}
                >
                    {formRename.errors.nombre}
                </div>
            )}

            {mostrandoNueva && (
                <form
                    onSubmit={crearSubcarpeta}
                    onClick={(e) => e.stopPropagation()}
                    className="flex items-center gap-1 py-1"
                    style={{ paddingLeft: `${(nivel + 1) * 14 + 22}px` }}
                >
                    <Input
                        autoFocus
                        placeholder="Nombre…"
                        value={form.data.nombre}
                        onChange={(e) => form.setData('nombre', e.target.value)}
                        className="h-7 text-sm"
                    />
                    <Button size="sm" type="submit" disabled={form.processing}>
                        OK
                    </Button>
                </form>
            )}

            {expandido && tieneHijos && (
                <ul>
                    {nodo.hijos.map((h) => (
                        <NodoCarpeta
                            key={h.id}
                            nodo={h}
                            nivel={nivel + 1}
                            obraId={obraId}
                            puedeAdministrar={puedeAdministrar}
                            expandidos={expandidos}
                            setExpandidos={setExpandidos}
                            carpetaActivaId={carpetaActivaId}
                        />
                    ))}
                </ul>
            )}
        </li>
    );
});

export default function DocumentosIndex({
    obra,
    carpetas,
    plantillaDisponible,
    puedeAdministrar,
    puedeSubir,
    puedeEliminarDoc,
    carpetaActiva,
    documentos,
}: Props) {
    const arbol = useMemo(() => construirArbol(carpetas), [carpetas]);
    const lineaActiva = useMemo(
        () => (carpetaActiva ? obtenerLinea(carpetaActiva.id, carpetas) : []),
        [carpetaActiva, carpetas],
    );

    const [expandidos, setExpandidos] = useState<Set<number>>(() => {
        const init = new Set<number>(arbol.map((n) => n.id));
        lineaActiva.forEach((c) => init.add(c.id));

        return init;
    });

    // Al cambiar la carpeta activa, expandimos su línea ascendente. Patrón de
    // ajuste de estado en render (no useEffect) recomendado por React para
    // sincronizar estado con un cambio de prop.
    const [carpetaPrevia, setCarpetaPrevia] = useState(
        carpetaActiva?.id ?? null,
    );

    if (carpetaActiva && carpetaActiva.id !== carpetaPrevia) {
        setCarpetaPrevia(carpetaActiva.id);
        setExpandidos(
            (prev) => new Set([...prev, ...lineaActiva.map((c) => c.id)]),
        );
    }

    const [mostrandoPlantilla, setMostrandoPlantilla] = useState(false);
    const [mostrandoNuevaRaiz, setMostrandoNuevaRaiz] = useState(false);
    const [docPreview, setDocPreview] = useState<DocumentoPreview | null>(null);
    const formRaiz = useForm({ nombre: '', parent_id: null as number | null });

    // Vista (cuadrícula/lista) recordada en localStorage + filtro por nombre.
    const [vista, setVista] = useState<'grid' | 'lista'>(() =>
        typeof window !== 'undefined' &&
        localStorage.getItem('rnfc.docs.vista') === 'lista'
            ? 'lista'
            : 'grid',
    );
    const cambiarVista = (v: 'grid' | 'lista') => {
        setVista(v);
        localStorage.setItem('rnfc.docs.vista', v);
    };
    const [filtro, setFiltro] = useState('');
    const documentosFiltrados = useMemo(() => {
        const q = filtro.trim().toLowerCase();

        return q
            ? documentos.filter((d) => d.nombre.toLowerCase().includes(q))
            : documentos;
    }, [documentos, filtro]);

    // Subcarpetas de la carpeta activa (para verlas como en un explorador).
    const subcarpetas = useMemo(
        () =>
            carpetaActiva
                ? carpetas.filter((c) => c.parent_id === carpetaActiva.id)
                : [],
        [carpetas, carpetaActiva],
    );
    const subcarpetasFiltradas = useMemo(() => {
        const q = filtro.trim().toLowerCase();

        return q
            ? subcarpetas.filter((c) => c.nombre.toLowerCase().includes(q))
            : subcarpetas;
    }, [subcarpetas, filtro]);

    const crearRaiz = (e: React.FormEvent) => {
        e.preventDefault();
        formRaiz.post(`/obras/${obra.id}/carpetas`, {
            preserveScroll: true,
            onSuccess: () => {
                formRaiz.reset('nombre');
                setMostrandoNuevaRaiz(false);
            },
        });
    };

    const recargarDocumentos = () => {
        if (!carpetaActiva) {
            return;
        }

        router.reload({ only: ['documentos'] });
    };

    const irACarpeta = (id: number) =>
        router.get(
            `/obras/${obra.id}/documentos`,
            { carpeta: id },
            { preserveScroll: true, preserveState: true },
        );

    const vacio = carpetas.length === 0;

    return (
        <>
            <Head title={`Documentos · ${obra.codigo}`} />
            <div className="flex flex-1 flex-col gap-4 p-4 md:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <Link
                        href={obras.show(obra.id).url}
                        className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft className="size-3.5" />
                        Volver a la obra
                    </Link>
                    {puedeAdministrar && !vacio && (
                        <div className="flex gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={() => setMostrandoNuevaRaiz(true)}
                            >
                                <Plus className="size-4" />
                                Carpeta manual
                            </Button>
                            <Button
                                size="sm"
                                onClick={() => setMostrandoPlantilla(true)}
                            >
                                <Sparkles className="size-4" />
                                Aplicar plantilla
                            </Button>
                        </div>
                    )}
                </div>

                {vacio ? (
                    <Card className="p-12 text-center">
                        <FolderTree className="mx-auto mb-3 size-10 text-muted-foreground" />
                        <h3 className="mb-1 text-lg font-semibold">
                            Aún no hay carpetas
                        </h3>
                        <p className="mx-auto max-w-md text-sm text-muted-foreground">
                            Aplica la plantilla estándar peruana (18 grupos) y
                            selecciona qué subcarpetas crear.
                        </p>
                        {puedeAdministrar && (
                            <div className="mt-4 flex justify-center gap-2">
                                <Button
                                    onClick={() => setMostrandoPlantilla(true)}
                                >
                                    <Sparkles className="size-4" />
                                    Elegir desde la plantilla
                                </Button>
                                <Button
                                    variant="outline"
                                    onClick={() => setMostrandoNuevaRaiz(true)}
                                >
                                    <Plus className="size-4" />
                                    Carpeta manual
                                </Button>
                            </div>
                        )}
                    </Card>
                ) : (
                    <div className="grid flex-1 gap-4 lg:grid-cols-[320px_1fr]">
                        <Card className="self-start p-3">
                            {mostrandoNuevaRaiz && puedeAdministrar && (
                                <form
                                    onSubmit={crearRaiz}
                                    className="mb-2 flex items-center gap-1 border-b border-border pb-2"
                                >
                                    <Input
                                        autoFocus
                                        placeholder="Nueva carpeta raíz…"
                                        value={formRaiz.data.nombre}
                                        onChange={(e) =>
                                            formRaiz.setData(
                                                'nombre',
                                                e.target.value,
                                            )
                                        }
                                        className="h-7 text-sm"
                                    />
                                    <Button
                                        size="sm"
                                        type="submit"
                                        disabled={formRaiz.processing}
                                    >
                                        OK
                                    </Button>
                                </form>
                            )}
                            <ul className="space-y-0.5">
                                {arbol.map((n) => (
                                    <NodoCarpeta
                                        key={n.id}
                                        nodo={n}
                                        nivel={0}
                                        obraId={obra.id}
                                        puedeAdministrar={puedeAdministrar}
                                        expandidos={expandidos}
                                        setExpandidos={setExpandidos}
                                        carpetaActivaId={
                                            carpetaActiva?.id ?? null
                                        }
                                    />
                                ))}
                            </ul>
                        </Card>

                        <div className="space-y-4">
                            {!carpetaActiva ? (
                                <Card className="p-12 text-center">
                                    <FolderOpen className="mx-auto mb-3 size-10 text-muted-foreground" />
                                    <h3 className="text-base font-semibold">
                                        Selecciona una carpeta
                                    </h3>
                                    <p className="text-sm text-muted-foreground">
                                        Elige una carpeta del árbol para ver y
                                        subir sus archivos.
                                    </p>
                                </Card>
                            ) : (
                                <>
                                    <div className="flex flex-wrap items-center gap-1 text-sm text-muted-foreground">
                                        {lineaActiva.map((c, i) => (
                                            <span
                                                key={c.id}
                                                className="flex items-center gap-1"
                                            >
                                                {i > 0 && (
                                                    <ChevronRight className="size-3.5 opacity-50" />
                                                )}
                                                <button
                                                    type="button"
                                                    onClick={() =>
                                                        router.get(
                                                            `/obras/${obra.id}/documentos`,
                                                            { carpeta: c.id },
                                                            {
                                                                preserveScroll: true,
                                                                preserveState: true,
                                                            },
                                                        )
                                                    }
                                                    className={
                                                        'rounded px-1 hover:bg-muted ' +
                                                        (i ===
                                                        lineaActiva.length - 1
                                                            ? 'font-semibold text-foreground'
                                                            : '')
                                                    }
                                                >
                                                    {c.nombre}
                                                </button>
                                            </span>
                                        ))}
                                    </div>

                                    {puedeSubir && (
                                        <Dropzone
                                            urlSubida={`/obras/${obra.id}/carpetas/${carpetaActiva.id}/documentos`}
                                            onComplete={recargarDocumentos}
                                        />
                                    )}

                                    {subcarpetas.length === 0 &&
                                    documentos.length === 0 ? (
                                        <Card className="p-10 text-center">
                                            <p className="text-sm text-muted-foreground">
                                                Esta carpeta está vacía.
                                            </p>
                                        </Card>
                                    ) : (
                                        <>
                                            {/* barra: buscador + contador + vista */}
                                            <div className="flex flex-wrap items-center gap-2">
                                                <div className="relative min-w-0 flex-1">
                                                    <Search className="pointer-events-none absolute top-1/2 left-2.5 size-4 -translate-y-1/2 text-muted-foreground" />
                                                    <Input
                                                        value={filtro}
                                                        onChange={(e) =>
                                                            setFiltro(
                                                                e.target.value,
                                                            )
                                                        }
                                                        placeholder="Buscar carpeta o archivo…"
                                                        className="h-9 pl-8"
                                                    />
                                                </div>
                                                <span className="shrink-0 text-xs text-muted-foreground tabular-nums">
                                                    {subcarpetas.length > 0 && (
                                                        <>
                                                            {
                                                                subcarpetasFiltradas.length
                                                            }{' '}
                                                            {subcarpetas.length ===
                                                            1
                                                                ? 'carpeta'
                                                                : 'carpetas'}{' '}
                                                            ·{' '}
                                                        </>
                                                    )}
                                                    {documentosFiltrados.length}{' '}
                                                    {documentos.length === 1
                                                        ? 'archivo'
                                                        : 'archivos'}
                                                </span>
                                                <div className="flex shrink-0 items-center rounded-md border border-border p-0.5">
                                                    <Button
                                                        type="button"
                                                        size="icon"
                                                        variant={
                                                            vista === 'grid'
                                                                ? 'secondary'
                                                                : 'ghost'
                                                        }
                                                        className="size-7"
                                                        title="Cuadrícula"
                                                        onClick={() =>
                                                            cambiarVista('grid')
                                                        }
                                                    >
                                                        <LayoutGrid className="size-4" />
                                                    </Button>
                                                    <Button
                                                        type="button"
                                                        size="icon"
                                                        variant={
                                                            vista === 'lista'
                                                                ? 'secondary'
                                                                : 'ghost'
                                                        }
                                                        className="size-7"
                                                        title="Lista"
                                                        onClick={() =>
                                                            cambiarVista(
                                                                'lista',
                                                            )
                                                        }
                                                    >
                                                        <List className="size-4" />
                                                    </Button>
                                                </div>
                                            </div>

                                            {subcarpetasFiltradas.length ===
                                                0 &&
                                            documentosFiltrados.length === 0 ? (
                                                <Card className="p-8 text-center">
                                                    <p className="text-sm text-muted-foreground">
                                                        Nada coincide con «
                                                        {filtro}».
                                                    </p>
                                                </Card>
                                            ) : vista === 'grid' ? (
                                                <div className="space-y-3">
                                                    {subcarpetasFiltradas.length >
                                                        0 && (
                                                        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                                                            {subcarpetasFiltradas.map(
                                                                (c) => (
                                                                    <button
                                                                        key={
                                                                            c.id
                                                                        }
                                                                        type="button"
                                                                        onClick={() =>
                                                                            irACarpeta(
                                                                                c.id,
                                                                            )
                                                                        }
                                                                        className="flex items-center gap-2.5 rounded-lg border border-border bg-card p-3 text-left transition-all hover:bg-muted/50 hover:shadow-md"
                                                                    >
                                                                        <Folder className="size-8 shrink-0 text-primary" />
                                                                        <span
                                                                            className="min-w-0 truncate text-sm font-medium"
                                                                            title={
                                                                                c.nombre
                                                                            }
                                                                        >
                                                                            {
                                                                                c.nombre
                                                                            }
                                                                        </span>
                                                                    </button>
                                                                ),
                                                            )}
                                                        </div>
                                                    )}
                                                    {documentosFiltrados.length >
                                                        0 && (
                                                        <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5">
                                                            {documentosFiltrados.map(
                                                                (d) => (
                                                                    <DocumentoCard
                                                                        key={
                                                                            d.id
                                                                        }
                                                                        documento={
                                                                            d
                                                                        }
                                                                        obraId={
                                                                            obra.id
                                                                        }
                                                                        puedeSubir={
                                                                            puedeSubir
                                                                        }
                                                                        puedeEliminar={
                                                                            puedeEliminarDoc
                                                                        }
                                                                        onPreview={() =>
                                                                            setDocPreview(
                                                                                d,
                                                                            )
                                                                        }
                                                                    />
                                                                ),
                                                            )}
                                                        </div>
                                                    )}
                                                </div>
                                            ) : (
                                                <div className="overflow-hidden rounded-lg border border-border">
                                                    {subcarpetasFiltradas.map(
                                                        (c) => (
                                                            <button
                                                                key={c.id}
                                                                type="button"
                                                                onClick={() =>
                                                                    irACarpeta(
                                                                        c.id,
                                                                    )
                                                                }
                                                                className="flex w-full items-center gap-3 border-b border-border px-3 py-2 text-left hover:bg-muted/40"
                                                            >
                                                                <Folder className="size-7 shrink-0 text-primary" />
                                                                <span
                                                                    className="min-w-0 flex-1 truncate text-sm font-medium"
                                                                    title={
                                                                        c.nombre
                                                                    }
                                                                >
                                                                    {c.nombre}
                                                                </span>
                                                                <ChevronRight className="size-4 shrink-0 text-muted-foreground" />
                                                            </button>
                                                        ),
                                                    )}
                                                    {documentosFiltrados.map(
                                                        (d) => (
                                                            <DocumentoCard
                                                                key={d.id}
                                                                variant="lista"
                                                                documento={d}
                                                                obraId={obra.id}
                                                                puedeSubir={
                                                                    puedeSubir
                                                                }
                                                                puedeEliminar={
                                                                    puedeEliminarDoc
                                                                }
                                                                onPreview={() =>
                                                                    setDocPreview(
                                                                        d,
                                                                    )
                                                                }
                                                            />
                                                        ),
                                                    )}
                                                </div>
                                            )}
                                        </>
                                    )}
                                </>
                            )}
                        </div>
                    </div>
                )}
            </div>

            <AplicarPlantillaDialog
                open={mostrandoPlantilla}
                onOpenChange={setMostrandoPlantilla}
                plantilla={plantillaDisponible}
                obraId={obra.id}
            />

            <PreviewModal
                documento={docPreview}
                onClose={() => setDocPreview(null)}
            />
        </>
    );
}

DocumentosIndex.layout = {
    title: 'Documentos',
    description: 'Carpetas y archivos de la obra.',
    breadcrumbs: [
        { title: 'Panel', href: '/dashboard' },
        { title: 'Obras', href: '/obras' },
        { title: 'Documentos', href: '' },
    ],
};
