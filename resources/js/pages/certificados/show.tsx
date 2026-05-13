import { Head, Link, router, setLayoutProps, usePage } from '@inertiajs/react';
import {
    Building2,
    Calendar,
    CheckCircle2,
    Eye,
    FileDown,
    Hash,
    ShieldCheck,
    Trash2,
    UserRound,
    XCircle,
} from 'lucide-react';
import { useState } from 'react';
import CertificadoPreview, {
    type BrandingUrls,
} from '@/components/certificado-preview';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import certificados from '@/routes/certificados';

type CertificadoData = {
    id: number;
    codigo: string;
    tipo: string;
    tipo_label: string;
    tipo_titulo: string;
    beneficiario_nombre: string;
    beneficiario_documento: string | null;
    beneficiario_profesion: string | null;
    cargo: string | null;
    fecha_inicio: string | null;
    fecha_fin: string | null;
    descripcion: string | null;
    lugar_emision: string;
    emisor_nombre: string;
    emisor_cargo: string;
    emisor_cip: string | null;
    fecha_emision: string | null;
    hash_verificacion: string;
    obra: { id: number; codigo: string; nombre: string } | null;
    vigente: boolean;
    revocado_at: string | null;
    motivo_revocacion: string | null;
    url_verificacion: string;
    url_pdf: string;
    url_preview: string;
};

type Props = { certificado: CertificadoData };

function DatoItem({
    label,
    valor,
    icono: Icono,
}: {
    label: string;
    valor: React.ReactNode;
    icono?: React.ComponentType<{ className?: string }>;
}) {
    return (
        <div className="flex items-start gap-3">
            {Icono && <Icono className="mt-0.5 size-4 text-primary" />}
            <div className="flex-1">
                <div className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                    {label}
                </div>
                <div className="text-sm font-medium text-foreground">{valor}</div>
            </div>
        </div>
    );
}

export default function CertificadoShow({ certificado }: Props) {
    setLayoutProps({
        title: certificado.tipo_titulo,
        description: `${certificado.codigo} · ${certificado.beneficiario_nombre}`,
    });

    const [revocando, setRevocando] = useState(false);
    const branding = usePage<{ branding: BrandingUrls }>().props.branding;

    const eliminar = () => {
        if (!confirm(`¿Eliminar el certificado ${certificado.codigo}?`)) return;
        router.delete(certificados.destroy(certificado.id).url);
    };

    const revocar = () => {
        const motivo = prompt('Motivo de la revocación (opcional):');
        if (motivo === null) return;
        setRevocando(true);
        router.post(
            certificados.revocar(certificado.id).url,
            { motivo },
            { onFinish: () => setRevocando(false) },
        );
    };

    return (
        <>
            <Head title={`Certificado ${certificado.codigo}`} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    {certificado.vigente ? (
                        <Badge className="bg-[var(--color-brand-verde)] text-white">
                            <CheckCircle2 className="mr-1 size-3" />
                            Vigente
                        </Badge>
                    ) : (
                        <Badge variant="destructive">
                            <XCircle className="mr-1 size-3" />
                            Revocado
                        </Badge>
                    )}
                    <div className="flex flex-wrap gap-2">
                        <Button asChild variant="outline">
                            <a href={certificado.url_preview} target="_blank" rel="noreferrer">
                                <Eye className="size-4" />
                                Previsualizar
                            </a>
                        </Button>
                        <Button asChild>
                            <a href={certificado.url_pdf} target="_blank" rel="noreferrer">
                                <FileDown className="size-4" />
                                Descargar PDF
                            </a>
                        </Button>
                        {certificado.vigente && (
                            <Button variant="outline" disabled={revocando} onClick={revocar}>
                                <XCircle className="size-4" />
                                Revocar
                            </Button>
                        )}
                        <Button variant="ghost" onClick={eliminar}>
                            <Trash2 className="size-4 text-destructive" />
                        </Button>
                    </div>
                </div>

                {!certificado.vigente && (
                    <Card className="border-destructive/40 bg-destructive/5">
                        <CardContent className="flex items-start gap-3 pt-6">
                            <XCircle className="mt-1 size-5 text-destructive" />
                            <div>
                                <div className="font-semibold text-destructive">
                                    Este certificado fue revocado
                                </div>
                                {certificado.revocado_at && (
                                    <div className="text-sm text-muted-foreground">
                                        Fecha de revocación: {new Date(certificado.revocado_at).toLocaleString('es-PE')}
                                    </div>
                                )}
                                {certificado.motivo_revocacion && (
                                    <div className="mt-1 text-sm">
                                        <span className="text-muted-foreground">Motivo: </span>
                                        {certificado.motivo_revocacion}
                                    </div>
                                )}
                            </div>
                        </CardContent>
                    </Card>
                )}

                <Card className="mx-auto w-fit overflow-hidden p-0">
                    <CertificadoPreview
                        certificado={certificado}
                        branding={branding}
                        scale={0.85}
                    />
                </Card>

                <div className="grid gap-6 lg:grid-cols-3">
                    <div className="space-y-6 lg:col-span-2">
                        <Card>
                            <CardHeader>
                                <CardTitle>Beneficiario</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-4 sm:grid-cols-2">
                                <DatoItem
                                    label="Nombre completo"
                                    valor={certificado.beneficiario_nombre}
                                    icono={UserRound}
                                />
                                <DatoItem
                                    label="Documento"
                                    valor={certificado.beneficiario_documento ?? '—'}
                                />
                                {certificado.beneficiario_profesion && (
                                    <DatoItem
                                        label="Profesión"
                                        valor={certificado.beneficiario_profesion}
                                    />
                                )}
                                {certificado.cargo && (
                                    <DatoItem label="Cargo / Actividad" valor={certificado.cargo} />
                                )}
                            </CardContent>
                        </Card>

                        {(certificado.obra ||
                            certificado.fecha_inicio ||
                            certificado.descripcion) && (
                            <Card>
                                <CardHeader>
                                    <CardTitle>Detalle del servicio</CardTitle>
                                </CardHeader>
                                <CardContent className="grid gap-4 sm:grid-cols-2">
                                    {certificado.obra && (
                                        <DatoItem
                                            label="Obra"
                                            icono={Building2}
                                            valor={
                                                <>
                                                    <span className="font-mono text-xs text-muted-foreground">
                                                        [{certificado.obra.codigo}]
                                                    </span>{' '}
                                                    {certificado.obra.nombre}
                                                </>
                                            }
                                        />
                                    )}
                                    {certificado.fecha_inicio && certificado.fecha_fin && (
                                        <DatoItem
                                            label="Período"
                                            icono={Calendar}
                                            valor={`${certificado.fecha_inicio} → ${certificado.fecha_fin}`}
                                        />
                                    )}
                                    {certificado.descripcion && (
                                        <div className="sm:col-span-2">
                                            <div className="mb-1 text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                                Descripción
                                            </div>
                                            <p className="text-sm text-foreground">
                                                {certificado.descripcion}
                                            </p>
                                        </div>
                                    )}
                                </CardContent>
                            </Card>
                        )}

                        <Card>
                            <CardHeader>
                                <CardTitle>Emisión</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-4 sm:grid-cols-2">
                                <DatoItem
                                    label="Fecha"
                                    icono={Calendar}
                                    valor={certificado.fecha_emision}
                                />
                                <DatoItem label="Lugar" valor={certificado.lugar_emision} />
                                <DatoItem label="Firmante" valor={certificado.emisor_nombre} />
                                <DatoItem
                                    label="Cargo"
                                    valor={`${certificado.emisor_cargo}${certificado.emisor_cip ? ` · CIP ${certificado.emisor_cip}` : ''}`}
                                />
                            </CardContent>
                        </Card>
                    </div>

                    <Card>
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <ShieldCheck className="size-5 text-primary" />
                                Verificación pública
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4 text-sm">
                            <div>
                                <div className="mb-1 text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                    URL de verificación
                                </div>
                                <Link
                                    href={certificado.url_verificacion}
                                    className="break-all text-primary underline underline-offset-2"
                                >
                                    {certificado.url_verificacion}
                                </Link>
                            </div>
                            <div className="flex items-start gap-2">
                                <Hash className="mt-0.5 size-4 text-primary" />
                                <div>
                                    <div className="text-xs font-medium tracking-wide text-muted-foreground uppercase">
                                        Hash de integridad (SHA-256)
                                    </div>
                                    <code className="block break-all font-mono text-[10px] text-muted-foreground">
                                        {certificado.hash_verificacion}
                                    </code>
                                </div>
                            </div>
                            <div className="rounded-md bg-muted/40 p-3 text-xs text-muted-foreground">
                                Cualquier persona con el código <strong>{certificado.codigo}</strong> o el QR del PDF puede validar este certificado en línea.
                            </div>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </>
    );
}

CertificadoShow.layout = {
    breadcrumbs: [
        { title: 'Panel', href: '/dashboard' },
        { title: 'Certificados', href: '/certificados' },
        { title: 'Detalle', href: '' },
    ],
};
