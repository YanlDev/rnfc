import { router } from '@inertiajs/react';
import {
    AlertCircle,
    CheckCircle2,
    FileUp,
    Loader2,
    Upload,
    X,
} from 'lucide-react';
import { useRef, useState } from 'react';
import { Button } from '@/components/ui/button';

type ArchivoEnSubida = {
    id: string;
    file: File;
    progreso: number;
    estado: 'pendiente' | 'subiendo' | 'exito' | 'error';
    error?: string;
};

type Props = {
    urlSubida: string;
    onComplete?: () => void;
};

function formatearTamano(bytes: number): string {
    if (bytes < 1024) return `${bytes} B`;
    if (bytes < 1024 ** 2) return `${(bytes / 1024).toFixed(1)} KB`;
    if (bytes < 1024 ** 3) return `${(bytes / 1024 ** 2).toFixed(1)} MB`;
    return `${(bytes / 1024 ** 3).toFixed(2)} GB`;
}

function genId() {
    return Math.random().toString(36).slice(2, 11);
}

export default function Dropzone({ urlSubida, onComplete }: Props) {
    const inputRef = useRef<HTMLInputElement>(null);
    const [dragOver, setDragOver] = useState(false);
    const [archivos, setArchivos] = useState<ArchivoEnSubida[]>([]);

    const subirArchivo = (item: ArchivoEnSubida) => {
        const form = new FormData();
        form.append('archivo', item.file);

        router.post(urlSubida, form, {
            forceFormData: true,
            preserveScroll: true,
            preserveState: true,
            only: ['documentos'],
            onProgress: (p) => {
                const pct = p?.percentage ?? 0;
                setArchivos((prev) =>
                    prev.map((a) =>
                        a.id === item.id
                            ? { ...a, estado: 'subiendo', progreso: pct }
                            : a,
                    ),
                );
            },
            onSuccess: () => {
                setArchivos((prev) =>
                    prev.map((a) =>
                        a.id === item.id
                            ? { ...a, estado: 'exito', progreso: 100 }
                            : a,
                    ),
                );
                // Auto-limpiar después de un momento
                setTimeout(() => {
                    setArchivos((prev) => prev.filter((a) => a.id !== item.id));
                    onComplete?.();
                }, 1500);
            },
            onError: (errors) => {
                const msg = Object.values(errors)[0] ?? 'Error al subir el archivo';
                setArchivos((prev) =>
                    prev.map((a) =>
                        a.id === item.id
                            ? { ...a, estado: 'error', error: String(msg) }
                            : a,
                    ),
                );
            },
        });
    };

    const agregarArchivos = (files: FileList | File[]) => {
        const nuevos: ArchivoEnSubida[] = Array.from(files).map((f) => ({
            id: genId(),
            file: f,
            progreso: 0,
            estado: 'pendiente',
        }));
        setArchivos((prev) => [...prev, ...nuevos]);
        // Disparar uploads en serie ligera (Inertia uno a la vez evita carreras).
        nuevos.forEach((item) => subirArchivo(item));
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
                    'group relative w-full rounded-lg border-2 border-dashed p-6 text-center transition-all ' +
                    (dragOver
                        ? 'scale-[1.01] border-primary bg-primary/5'
                        : 'border-border hover:border-primary/50 hover:bg-muted/30')
                }
            >
                <input
                    ref={inputRef}
                    type="file"
                    multiple
                    className="hidden"
                    onChange={(e) => {
                        if (e.target.files) agregarArchivos(e.target.files);
                        e.target.value = '';
                    }}
                />
                <div className="flex flex-col items-center gap-2">
                    <div
                        className={
                            'flex size-12 items-center justify-center rounded-full transition-colors ' +
                            (dragOver
                                ? 'bg-primary text-primary-foreground'
                                : 'bg-muted text-muted-foreground group-hover:bg-primary group-hover:text-primary-foreground')
                        }
                    >
                        <Upload className="size-6" />
                    </div>
                    <div className="text-sm">
                        <strong className="text-foreground">
                            Haz clic para elegir archivos
                        </strong>{' '}
                        <span className="text-muted-foreground">o arrástralos aquí</span>
                    </div>
                    <div className="text-xs text-muted-foreground">
                        PDF, imágenes, planos, documentos · máx. 50 MB por archivo
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
                                {a.estado === 'exito' ? (
                                    <CheckCircle2 className="size-5 text-[var(--color-brand-verde)]" />
                                ) : a.estado === 'error' ? (
                                    <AlertCircle className="size-5 text-destructive" />
                                ) : a.estado === 'subiendo' ? (
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
                                        {formatearTamano(a.file.size)}
                                    </div>
                                </div>
                                {a.estado !== 'error' && (
                                    <div className="mt-1.5 h-1.5 w-full overflow-hidden rounded-full bg-muted">
                                        <div
                                            className={
                                                'h-full transition-all duration-200 ' +
                                                (a.estado === 'exito'
                                                    ? 'bg-[var(--color-brand-verde)]'
                                                    : 'bg-primary')
                                            }
                                            style={{ width: `${a.progreso}%` }}
                                        />
                                    </div>
                                )}
                                {a.estado === 'error' && (
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
