import { router } from '@inertiajs/react';
import {
    Download,
    DraftingCompass,
    Eye,
    File,
    FileArchive,
    FileImage,
    FileSpreadsheet,
    FileText,
    History,
    Loader2,
    Presentation,
    Trash2,
    Upload,
} from 'lucide-react';
import { useRef, useState } from 'react';
import { useConfirm } from '@/components/confirm-dialog';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import type { FaseSubida } from '@/lib/chunked-upload';
import { ACCEPT_ATTR, subirDocumento } from '@/lib/subir-documento';
import PdfThumbnail from './_pdf-thumbnail';
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

type TipoVisual = {
    Icono: React.ComponentType<{ className?: string }>;
    color: string; // clase de color del icono y la etiqueta
    etiqueta: string; // extensión visible (PDF, DOCX, ZIP…)
};

/**
 * Icono + color + etiqueta según la extensión (con el MIME de respaldo),
 * para que ZIP, XLSX, DOCX, DWG… se distingan de un vistazo.
 */
function tipoVisual(nombre: string, mime: string): TipoVisual {
    const ext = (nombre.split('.').pop() ?? '').toLowerCase();
    const etiqueta = ext.toUpperCase();

    if (mime.startsWith('image/')) {
        return { Icono: FileImage, color: 'text-violet-500', etiqueta };
    }

    if (ext === 'pdf' || mime === 'application/pdf') {
        return { Icono: FileText, color: 'text-red-500', etiqueta: 'PDF' };
    }

    if (
        ['xls', 'xlsx', 'csv'].includes(ext) ||
        mime.includes('spreadsheet') ||
        mime.includes('excel') ||
        mime.includes('csv')
    ) {
        return { Icono: FileSpreadsheet, color: 'text-emerald-600', etiqueta };
    }

    if (['doc', 'docx'].includes(ext) || mime.includes('word')) {
        return { Icono: FileText, color: 'text-blue-600', etiqueta };
    }

    if (['ppt', 'pptx'].includes(ext) || mime.includes('presentation')) {
        return { Icono: Presentation, color: 'text-orange-500', etiqueta };
    }

    if (
        ['zip', 'rar', '7z'].includes(ext) ||
        mime.includes('zip') ||
        mime.includes('compressed')
    ) {
        return { Icono: FileArchive, color: 'text-amber-600', etiqueta };
    }

    if (['dwg', 'dxf'].includes(ext)) {
        return { Icono: DraftingCompass, color: 'text-cyan-600', etiqueta };
    }

    if (ext === 'txt') {
        return { Icono: FileText, color: 'text-muted-foreground', etiqueta };
    }

    return {
        Icono: File,
        color: 'text-muted-foreground',
        etiqueta: etiqueta || 'ARCHIVO',
    };
}

function IconoArchivo({
    nombre,
    mime,
    className,
    conEtiqueta = false,
}: {
    nombre: string;
    mime: string;
    className: string;
    conEtiqueta?: boolean;
}) {
    const { Icono, color, etiqueta } = tipoVisual(nombre, mime);

    if (!conEtiqueta) {
        return <Icono className={`${className} ${color}`} />;
    }

    return (
        <div className="flex flex-col items-center gap-1">
            <Icono className={`${className} ${color}`} />
            <span className={`text-[10px] font-bold tracking-widest ${color}`}>
                {etiqueta}
            </span>
        </div>
    );
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
    const { confirm, dialog } = useConfirm();
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

    const eliminar = async () => {
        const ok = await confirm({
            titulo: `¿Eliminar «${documento.nombre}»?`,
            destructivo: true,
            confirmar: 'Eliminar archivo',
            descripcion:
                documento.version > 1
                    ? `Se eliminarán el archivo y sus ${documento.version - 1} versión(es) anterior(es) de forma permanente.`
                    : 'El archivo se eliminará de forma permanente.',
        });

        if (!ok) {
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
                {dialog}
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
                            nombre={documento.nombre}
                            mime={documento.mime}
                            className="size-7 shrink-0"
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
            {dialog}
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
                ) : documento.es_pdf ? (
                    <PdfThumbnail
                        url={documento.url_preview}
                        tamano={documento.tamano}
                        fallback={
                            <IconoArchivo
                                nombre={documento.nombre}
                                mime={documento.mime}
                                className="size-12 transition-transform group-hover:scale-110"
                                conEtiqueta
                            />
                        }
                    />
                ) : (
                    <IconoArchivo
                        nombre={documento.nombre}
                        mime={documento.mime}
                        className="size-12 transition-transform group-hover:scale-110"
                        conEtiqueta
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
