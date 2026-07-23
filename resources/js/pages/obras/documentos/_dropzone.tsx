import {
    AlertCircle,
    CheckCircle2,
    FileUp,
    Loader2,
    Upload,
    X,
} from 'lucide-react';
import { useRef, useState } from 'react';
import { formatearBytes } from '@/lib/bytes';
import type { FaseSubida } from '@/lib/chunked-upload';
import { ACCEPT_ATTR, subirDocumento } from '@/lib/subir-documento';

type ArchivoEnSubida = {
    id: string;
    file: File;
    progreso: number;
    fase: FaseSubida;
    error?: string;
};

type Props = {
    obraId: number;
    carpetaId: number;
    onComplete?: () => void;
};

function genId() {
    return Math.random().toString(36).slice(2, 11);
}

export default function Dropzone({ obraId, carpetaId, onComplete }: Props) {
    const inputRef = useRef<HTMLInputElement>(null);
    const [dragOver, setDragOver] = useState(false);
    const [archivos, setArchivos] = useState<ArchivoEnSubida[]>([]);

    const actualizar = (id: string, cambios: Partial<ArchivoEnSubida>) => {
        setArchivos((prev) =>
            prev.map((a) => (a.id === id ? { ...a, ...cambios } : a)),
        );
    };

    const subirArchivo = async (item: ArchivoEnSubida) => {
        try {
            await subirDocumento({
                obraId,
                carpetaId,
                file: item.file,
                onProgreso: (pct) => actualizar(item.id, { progreso: pct }),
                onFase: (fase) => actualizar(item.id, { fase }),
            });

            actualizar(item.id, { fase: 'completado', progreso: 100 });
            setTimeout(() => {
                setArchivos((prev) => prev.filter((a) => a.id !== item.id));
                onComplete?.();
            }, 1500);
        } catch (e) {
            actualizar(item.id, {
                fase: 'error',
                error: e instanceof Error ? e.message : 'Error al subir.',
            });
        }
    };

    const agregarArchivos = async (files: FileList | File[]) => {
        const nuevos: ArchivoEnSubida[] = Array.from(files).map((f) => ({
            id: genId(),
            file: f,
            progreso: 0,
            fase: 'iniciando',
        }));
        setArchivos((prev) => [...prev, ...nuevos]);

        // En serie: un archivo a la vez para no saturar el ancho de banda de
        // subida del server ni el disco temporal con varios GB simultáneos.
        for (const item of nuevos) {
            await subirArchivo(item);
        }
    };

    const onDrop = (e: React.DragEvent) => {
        e.preventDefault();
        setDragOver(false);

        if (e.dataTransfer.files.length > 0) {
            agregarArchivos(e.dataTransfer.files);
        }
    };

    const removerArchivo = (id: string) => {
        setArchivos((prev) => prev.filter((a) => a.id !== id));
    };

    return (
        <div className="space-y-3">
            <button
                type="button"
                onClick={() => inputRef.current?.click()}
                onDragOver={(e) => {
                    e.preventDefault();
                    setDragOver(true);
                }}
                onDragLeave={() => setDragOver(false)}
                onDrop={onDrop}
                className={
                    'group relative w-full rounded-lg border-2 border-dashed p-3 transition-all ' +
                    (dragOver
                        ? 'scale-[1.01] border-primary bg-primary/5'
                        : 'border-border hover:border-primary/50 hover:bg-muted/30')
                }
            >
                <input
                    ref={inputRef}
                    type="file"
                    multiple
                    accept={ACCEPT_ATTR}
                    className="hidden"
                    onChange={(e) => {
                        if (e.target.files) {
                            agregarArchivos(e.target.files);
                        }

                        e.target.value = '';
                    }}
                />
                <div className="flex items-center justify-center gap-3">
                    <div
                        className={
                            'flex size-9 shrink-0 items-center justify-center rounded-full transition-colors ' +
                            (dragOver
                                ? 'bg-primary text-primary-foreground'
                                : 'bg-muted text-muted-foreground group-hover:bg-primary group-hover:text-primary-foreground')
                        }
                    >
                        <Upload className="size-4" />
                    </div>
                    <div className="text-left leading-tight">
                        <div className="text-sm">
                            <strong className="text-foreground">
                                Haz clic para elegir archivos
                            </strong>{' '}
                            <span className="text-muted-foreground">
                                o arrástralos aquí
                            </span>
                        </div>
                        <div className="text-xs text-muted-foreground">
                            PDF, imágenes, planos, documentos · archivos grandes
                            permitidos
                        </div>
                    </div>
                </div>
            </button>

            {archivos.length > 0 && (
                <ul className="space-y-2">
                    {archivos.map((a) => (
                        <li
                            key={a.id}
                            className="flex items-center gap-3 rounded-md border border-border bg-card p-3"
                        >
                            <div className="shrink-0">
                                {a.fase === 'completado' ? (
                                    <CheckCircle2 className="size-5 text-[var(--color-brand-verde)]" />
                                ) : a.fase === 'error' ? (
                                    <AlertCircle className="size-5 text-destructive" />
                                ) : a.fase === 'subiendo' ||
                                  a.fase === 'procesando' ||
                                  a.fase === 'iniciando' ? (
                                    <Loader2 className="size-5 animate-spin text-primary" />
                                ) : (
                                    <FileUp className="size-5 text-muted-foreground" />
                                )}
                            </div>
                            <div className="min-w-0 flex-1">
                                <div className="flex items-center justify-between gap-2">
                                    <div className="truncate text-sm font-medium">
                                        {a.file.name}
                                    </div>
                                    <div className="shrink-0 text-xs text-muted-foreground tabular-nums">
                                        {formatearBytes(a.file.size)}
                                    </div>
                                </div>
                                {a.fase !== 'error' && (
                                    <div className="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-muted">
                                        <div
                                            className={
                                                'h-full transition-all duration-200 ' +
                                                (a.fase === 'completado'
                                                    ? 'bg-[var(--color-brand-verde)]'
                                                    : 'bg-primary') +
                                                (a.fase === 'procesando'
                                                    ? ' animate-pulse'
                                                    : '')
                                            }
                                            style={{ width: `${a.progreso}%` }}
                                        />
                                    </div>
                                )}
                                {a.fase === 'procesando' && (
                                    <div className="mt-1 text-xs text-muted-foreground">
                                        Procesando en el servidor…
                                    </div>
                                )}
                                {a.fase === 'error' && (
                                    <div className="mt-1 text-xs text-destructive">
                                        {a.error}
                                    </div>
                                )}
                            </div>
                            <button
                                type="button"
                                onClick={() => removerArchivo(a.id)}
                                className="rounded p-1 text-muted-foreground hover:bg-muted hover:text-foreground"
                            >
                                <X className="size-4" />
                            </button>
                        </li>
                    ))}
                </ul>
            )}
        </div>
    );
}
