import { router } from '@inertiajs/react';
import {
    Download,
    Eye,
    File,
    FileImage,
    FileSpreadsheet,
    FileText,
    History,
    Loader2,
    Trash2,
    Upload,
} from 'lucide-react';
import { useRef, useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import type { FaseSubida } from '@/lib/chunked-upload';
import { ACCEPT_ATTR, subirDocumento } from '@/lib/subir-documento';
import type { DocumentoPreview } from './_preview-modal';

export type DocumentoCardData = DocumentoPreview & {
    subido_por: string | null;
    updated_at: string;
};

type Props = {
    documento: DocumentoCardData;
    obraId: number;
    puedeSubir: boolean; // subir nueva versión
    puedeEliminar: boolean; // eliminar documento
    onPreview: () => void;
    variant?: 'grid' | 'lista';
};

function IconoArchivo({
    mime,
    className,
}: {
    mime: string;
    className: string;
}) {
    if (mime.startsWith('image/')) {
        return <FileImage className={className} />;
    }

    if (mime === 'application/pdf') {
        return <FileText className={className} />;
    }

    if (
        mime.includes('spreadsheet') ||
        mime.includes('excel') ||
        mime.includes('csv')
    ) {
        return <FileSpreadsheet className={className} />;
    }

    if (mime.includes('word') || mime.includes('document')) {
        return <FileText className={className} />;
    }

    return <File className={className} />;
}

export default function DocumentoCard({
    documento,
    obraId,
    puedeSubir,
    puedeEliminar,
    onPreview,
    variant = 'grid',
}: Props) {
    const versionInputRef = useRef<HTMLInputElement>(null);
    const [versionFase, setVersionFase] = useState<FaseSubida | null>(null);
    const [versionPct, setVersionPct] = useState(0);

    const subiendoVersion =
        versionFase !== null &&
        versionFase !== 'completado' &&
        versionFase !== 'error';

    const subirVersion = async (file: File) => {
        try {
            setVersionPct(0);
            setVersionFase('iniciando');
            await subirDocumento({
                obraId,
                documentoId: documento.id,
                file,
                onProgreso: setVersionPct,
                onFase: setVersionFase,
            });
            setVersionFase(null);
            router.reload({ only: ['documentos'] });
        } catch (e) {
            setVersionFase('error');
            alert(
                e instanceof Error
                    ? e.message
                    : 'No se pudo subir la nueva versión.',
            );
        }
    };

    const eliminar = () => {
        if (
            !confirm(`¿Eliminar «${documento.nombre}» y todas sus versiones?`)
        ) {
            return;
        }

        router.delete(`/obras/${obraId}/documentos/${documento.id}`, {
            preserveScroll: true,
        });
    };

    const inputVersion = puedeSubir && (
        <input
            ref={versionInputRef}
            type="file"
            accept={ACCEPT_ATTR}
            className="hidden"
            onChange={(e) => {
                const file = e.target.files?.[0];

                if (file) {
                    subirVersion(file);
                }

                e.target.value = '';
            }}
        />
    );

    if (variant === 'lista') {
        return (
            <div className="flex items-center gap-3 border-b border-border px-3 py-2 last:border-0 hover:bg-muted/40">
                <button
                    type="button"
                    onClick={onPreview}
                    className="flex min-w-0 flex-1 items-center gap-3 text-left"
                >
                    {documento.es_imagen ? (
                        <img
                            src={documento.url_preview}
                            alt=""
                            loading="lazy"
                            className="size-9 shrink-0 rounded object-cover"
                        />
                    ) : (
                        <IconoArchivo
                            mime={documento.mime}
                            className="size-7 shrink-0 text-muted-foreground"
                        />
                    )}
                    <div className="min-w-0">
                        <div
                            className="truncate text-sm font-medium"
                            title={documento.nombre}
                        >
                            {documento.nombre}
                            {documento.version > 1 && (
                                <span className="ml-1.5 text-[10px] text-muted-foreground">
                                    v{documento.version}
                                </span>
                            )}
                        </div>
                        <div className="truncate text-xs text-muted-foreground">
                            {documento.tamano_humano} ·{' '}
                            {new Date(documento.updated_at).toLocaleDateString(
                                'es-PE',
                            )}
                            {documento.subido_por
                                ? ` · ${documento.subido_por}`
                                : ''}
                        </div>
                    </div>
                </button>
                <div className="flex shrink-0 items-center gap-0.5">
                    <Button
                        asChild
                        size="icon"
                        variant="ghost"
                        className="size-8"
                        title="Descargar"
                    >
                        <a href={documento.url_descarga}>
                            <Download className="size-4" />
                        </a>
                    </Button>
                    {inputVersion}
                    {puedeSubir && (
                        <Button
                            size="icon"
                            variant="ghost"
                            className="size-8"
                            disabled={subiendoVersion}
                            onClick={() => versionInputRef.current?.click()}
                            title={
                                subiendoVersion
                                    ? versionFase === 'procesando'
                                        ? 'Procesando…'
                                        : `Subiendo… ${versionPct}%`
                                    : 'Subir nueva versión'
                            }
                        >
                            {subiendoVersion ? (
                                <Loader2 className="size-4 animate-spin" />
                            ) : (
                                <Upload className="size-4" />
                            )}
                        </Button>
                    )}
                    {puedeEliminar && (
                        <Button
                            size="icon"
                            variant="ghost"
                            className="size-8"
                            onClick={eliminar}
                            title="Eliminar"
                        >
                            <Trash2 className="size-4 text-destructive" />
                        </Button>
                    )}
                </div>
            </div>
        );
    }

    return (
        <Card className="group overflow-hidden p-0 transition-all hover:shadow-md">
            {/* Thumbnail / preview */}
            <button
                type="button"
                onClick={onPreview}
                className="relative flex h-28 w-full items-center justify-center overflow-hidden bg-muted/40"
            >
                {documento.es_imagen ? (
                    <img
                        src={documento.url_preview}
                        alt={documento.nombre}
                        className="size-full object-cover transition-transform group-hover:scale-105"
                        loading="lazy"
                    />
                ) : (
                    <IconoArchivo
                        mime={documento.mime}
                        className="size-14 text-muted-foreground transition-transform group-hover:scale-110"
                    />
                )}
                <div className="absolute inset-0 flex items-center justify-center bg-black/0 transition-colors group-hover:bg-black/30">
                    <Eye className="size-8 text-white opacity-0 transition-opacity group-hover:opacity-100" />
                </div>
                {documento.version > 1 && (
                    <Badge className="absolute top-2 right-2 bg-[var(--color-brand-azul-oscuro)] text-white">
                        v{documento.version}
                    </Badge>
                )}
            </button>

            {/* Info */}
            <div className="space-y-2 p-3">
                <div
                    className="line-clamp-2 cursor-pointer text-sm font-medium hover:text-primary"
                    onClick={onPreview}
                    title={documento.nombre}
                >
                    {documento.nombre}
                </div>
                <div className="flex items-center justify-between text-xs text-muted-foreground">
                    <span className="tabular-nums">
                        {documento.tamano_humano}
                    </span>
                    <span>
                        {new Date(documento.updated_at).toLocaleDateString(
                            'es-PE',
                        )}
                    </span>
                </div>
                {documento.subido_por && (
                    <div className="text-xs text-muted-foreground">
                        Por {documento.subido_por}
                    </div>
                )}

                {/* Actions */}
                <div className="flex items-center justify-between gap-1 border-t border-border pt-2">
                    <div className="flex gap-0.5">
                        <Button
                            asChild
                            size="sm"
                            variant="ghost"
                            title="Descargar"
                        >
                            <a href={documento.url_descarga}>
                                <Download className="size-4" />
                            </a>
                        </Button>
                        {puedeSubir && (
                            <>
                                <input
                                    ref={versionInputRef}
                                    type="file"
                                    className="hidden"
                                    onChange={(e) => {
                                        const file = e.target.files?.[0];

                                        if (file) {
                                            subirVersion(file);
                                        }

                                        e.target.value = '';
                                    }}
                                />
                                <Button
                                    size="sm"
                                    variant="ghost"
                                    disabled={subiendoVersion}
                                    onClick={() =>
                                        versionInputRef.current?.click()
                                    }
                                    title={
                                        subiendoVersion
                                            ? versionFase === 'procesando'
                                                ? 'Procesando…'
                                                : `Subiendo… ${versionPct}%`
                                            : 'Subir nueva versión'
                                    }
                                >
                                    {subiendoVersion ? (
                                        <Loader2 className="size-4 animate-spin" />
                                    ) : (
                                        <Upload className="size-4" />
                                    )}
                                </Button>
                                {documento.version > 1 && (
                                    <Button
                                        size="sm"
                                        variant="ghost"
                                        title={`Tiene ${documento.version - 1} versión(es) anterior(es)`}
                                    >
                                        <History className="size-4 text-muted-foreground" />
                                    </Button>
                                )}
                            </>
                        )}
                    </div>
                    {puedeEliminar && (
                        <Button
                            size="sm"
                            variant="ghost"
                            onClick={eliminar}
                            title="Eliminar"
                        >
                            <Trash2 className="size-4 text-destructive" />
                        </Button>
                    )}
                </div>
            </div>
        </Card>
    );
}
