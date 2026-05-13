import { Head, useForm, usePage } from '@inertiajs/react';
import { Save } from 'lucide-react';
import { useMemo, useState } from 'react';
import CertificadoPreview, {
    type BrandingUrls,
    type CertificadoPreviewData,
} from '@/components/certificado-preview';
import InputError from '@/components/input-error';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Spinner } from '@/components/ui/spinner';
import { Textarea } from '@/components/ui/textarea';
import certificados from '@/routes/certificados';

type TipoOpcion = {
    value: string;
    label: string;
    titulo: string;
    requiere_obra: boolean;
};

type ObraOpcion = { id: number; label: string };

type Props = {
    tipos: TipoOpcion[];
    obras: ObraOpcion[];
};

type FormData = {
    tipo: string;
    beneficiario_nombre: string;
    beneficiario_documento: string;
    beneficiario_profesion: string;
    obra_id: string;
    obra_nombre_libre: string;
    obra_entidad_libre: string;
    cargo: string;
    fecha_inicio: string;
    fecha_fin: string;
    descripcion: string;
    lugar_emision: string;
    emisor_nombre: string;
    emisor_cargo: string;
    emisor_cip: string;
    fecha_emision: string;
};

type ModoObra = 'ninguna' | 'registrada' | 'manual';

export default function CertificadoCreate({ tipos, obras }: Props) {
    const hoy = new Date().toISOString().slice(0, 10);
    const branding = usePage<{ branding: BrandingUrls }>().props.branding;

    const form = useForm<FormData>({
        tipo: '',
        beneficiario_nombre: '',
        beneficiario_documento: '',
        beneficiario_profesion: '',
        obra_id: '',
        obra_nombre_libre: '',
        obra_entidad_libre: '',
        cargo: '',
        fecha_inicio: '',
        fecha_fin: '',
        descripcion: '',
        lugar_emision: 'Puno, Perú',
        emisor_nombre: 'Ing. Roger Neptali Flores Coaquira',
        emisor_cargo: 'Consultor de Obras',
        emisor_cip: '',
        fecha_emision: hoy,
    });

    const [modoObra, setModoObra] = useState<ModoObra>('ninguna');

    const cambiarModoObra = (m: ModoObra) => {
        setModoObra(m);
        if (m !== 'registrada') form.setData('obra_id', '');
        if (m !== 'manual') {
            form.setData('obra_nombre_libre', '');
            form.setData('obra_entidad_libre', '');
        }
    };

    const tipoActual = useMemo(
        () => tipos.find((t) => t.value === form.data.tipo),
        [tipos, form.data.tipo],
    );

    const obraActual = useMemo(
        () => obras.find((o) => String(o.id) === form.data.obra_id),
        [obras, form.data.obra_id],
    );

    const previewData: CertificadoPreviewData = {
        codigo: 'RNFC-' + new Date().getFullYear() + '-XXXXXX',
        tipo: form.data.tipo,
        tipo_titulo: tipoActual?.titulo,
        tipo_label: tipoActual?.label,
        beneficiario_nombre: form.data.beneficiario_nombre,
        beneficiario_documento: form.data.beneficiario_documento,
        beneficiario_profesion: form.data.beneficiario_profesion,
        obra:
            modoObra === 'registrada' && obraActual
                ? { nombre: obraActual.label.replace(/^\[[^\]]+\]\s*/, '') }
                : null,
        obra_nombre_libre:
            modoObra === 'manual' ? form.data.obra_nombre_libre : '',
        obra_entidad_libre:
            modoObra === 'manual' ? form.data.obra_entidad_libre : '',
        cargo: form.data.cargo,
        fecha_inicio: form.data.fecha_inicio,
        fecha_fin: form.data.fecha_fin,
        descripcion: form.data.descripcion,
        lugar_emision: form.data.lugar_emision,
        emisor_nombre: form.data.emisor_nombre,
        emisor_cargo: form.data.emisor_cargo,
        emisor_cip: form.data.emisor_cip,
        fecha_emision: form.data.fecha_emision,
        vigente: true,
    };

    const onSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        form.post(certificados.store().url);
    };

    return (
        <>
            <Head title="Nuevo certificado" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">

                <div className="grid gap-6 xl:grid-cols-[minmax(0,1fr)_minmax(0,620px)]">
                    {/* Formulario */}
                    <form onSubmit={onSubmit} className="flex flex-col gap-6">
                        <Card>
                            <CardHeader>
                                <CardTitle>Tipo de certificado</CardTitle>
                            </CardHeader>
                            <CardContent>
                                <div className="grid gap-2">
                                    <Label htmlFor="tipo">Tipo *</Label>
                                    <Select
                                        value={form.data.tipo}
                                        onValueChange={(v) =>
                                            form.setData('tipo', v)
                                        }
                                    >
                                        <SelectTrigger id="tipo">
                                            <SelectValue placeholder="Selecciona el tipo de certificado" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {tipos.map((t) => (
                                                <SelectItem
                                                    key={t.value}
                                                    value={t.value}
                                                >
                                                    {t.titulo}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                    <InputError message={form.errors.tipo} />
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Beneficiario</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-4 md:grid-cols-2">
                                <div className="grid gap-2 md:col-span-2">
                                    <Label htmlFor="beneficiario_nombre">
                                        Nombre completo *
                                    </Label>
                                    <Input
                                        id="beneficiario_nombre"
                                        value={form.data.beneficiario_nombre}
                                        onChange={(e) =>
                                            form.setData(
                                                'beneficiario_nombre',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Apellidos y nombres"
                                    />
                                    <InputError
                                        message={form.errors.beneficiario_nombre}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="beneficiario_documento">
                                        DNI / Documento
                                    </Label>
                                    <Input
                                        id="beneficiario_documento"
                                        value={form.data.beneficiario_documento}
                                        onChange={(e) =>
                                            form.setData(
                                                'beneficiario_documento',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="12345678"
                                    />
                                    <InputError
                                        message={form.errors.beneficiario_documento}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="beneficiario_profesion">
                                        Profesión / Especialidad
                                    </Label>
                                    <Input
                                        id="beneficiario_profesion"
                                        value={form.data.beneficiario_profesion}
                                        onChange={(e) =>
                                            form.setData(
                                                'beneficiario_profesion',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Ingeniero Civil"
                                    />
                                    <InputError
                                        message={form.errors.beneficiario_profesion}
                                    />
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>
                                    Detalle del servicio / actividad
                                </CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-4 md:grid-cols-2">
                                <div className="grid gap-3 md:col-span-2">
                                    <Label>
                                        Obra{' '}
                                        {tipoActual?.requiere_obra
                                            ? '*'
                                            : '(opcional)'}
                                    </Label>
                                    <div className="flex flex-wrap gap-2">
                                        <Button
                                            type="button"
                                            size="sm"
                                            variant={
                                                modoObra === 'ninguna'
                                                    ? 'default'
                                                    : 'outline'
                                            }
                                            onClick={() =>
                                                cambiarModoObra('ninguna')
                                            }
                                        >
                                            Sin obra
                                        </Button>
                                        <Button
                                            type="button"
                                            size="sm"
                                            variant={
                                                modoObra === 'registrada'
                                                    ? 'default'
                                                    : 'outline'
                                            }
                                            onClick={() =>
                                                cambiarModoObra('registrada')
                                            }
                                        >
                                            Obra registrada
                                        </Button>
                                        <Button
                                            type="button"
                                            size="sm"
                                            variant={
                                                modoObra === 'manual'
                                                    ? 'default'
                                                    : 'outline'
                                            }
                                            onClick={() =>
                                                cambiarModoObra('manual')
                                            }
                                        >
                                            Escribir nombre manual
                                        </Button>
                                    </div>

                                    {modoObra === 'registrada' && (
                                        <>
                                            <Select
                                                value={form.data.obra_id}
                                                onValueChange={(v) =>
                                                    form.setData('obra_id', v)
                                                }
                                            >
                                                <SelectTrigger id="obra_id">
                                                    <SelectValue placeholder="Selecciona la obra del sistema" />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {obras.length === 0 && (
                                                        <div className="p-2 text-sm text-muted-foreground">
                                                            No hay obras registradas todavía.
                                                        </div>
                                                    )}
                                                    {obras.map((o) => (
                                                        <SelectItem
                                                            key={o.id}
                                                            value={String(o.id)}
                                                        >
                                                            {o.label}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                            <InputError
                                                message={form.errors.obra_id}
                                            />
                                        </>
                                    )}

                                    {modoObra === 'manual' && (
                                        <div className="grid gap-3 md:grid-cols-2">
                                            <div className="grid gap-2">
                                                <Label htmlFor="obra_nombre_libre">
                                                    Nombre de la obra
                                                </Label>
                                                <Input
                                                    id="obra_nombre_libre"
                                                    value={
                                                        form.data
                                                            .obra_nombre_libre
                                                    }
                                                    onChange={(e) =>
                                                        form.setData(
                                                            'obra_nombre_libre',
                                                            e.target.value,
                                                        )
                                                    }
                                                    placeholder="Mejoramiento de pistas y veredas — Av. Los Olivos"
                                                />
                                                <InputError
                                                    message={
                                                        form.errors
                                                            .obra_nombre_libre
                                                    }
                                                />
                                            </div>
                                            <div className="grid gap-2">
                                                <Label htmlFor="obra_entidad_libre">
                                                    Entidad contratante
                                                </Label>
                                                <Input
                                                    id="obra_entidad_libre"
                                                    value={
                                                        form.data
                                                            .obra_entidad_libre
                                                    }
                                                    onChange={(e) =>
                                                        form.setData(
                                                            'obra_entidad_libre',
                                                            e.target.value,
                                                        )
                                                    }
                                                    placeholder="Municipalidad de Lima"
                                                />
                                                <InputError
                                                    message={
                                                        form.errors
                                                            .obra_entidad_libre
                                                    }
                                                />
                                            </div>
                                        </div>
                                    )}

                                    {modoObra === 'ninguna' &&
                                        tipoActual?.requiere_obra && (
                                            <p className="text-xs text-amber-700 dark:text-amber-400">
                                                Este tipo de certificado se redacta
                                                mejor con una obra. Puedes elegir
                                                una registrada o escribir el nombre
                                                manualmente.
                                            </p>
                                        )}
                                </div>
                                <div className="grid gap-2 md:col-span-2">
                                    <Label htmlFor="cargo">
                                        Cargo desempeñado / Actividad
                                    </Label>
                                    <Input
                                        id="cargo"
                                        value={form.data.cargo}
                                        onChange={(e) =>
                                            form.setData('cargo', e.target.value)
                                        }
                                        placeholder="Residente de obra, Curso de seguridad, etc."
                                    />
                                    <InputError message={form.errors.cargo} />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="fecha_inicio">
                                        Fecha de inicio
                                    </Label>
                                    <Input
                                        id="fecha_inicio"
                                        type="date"
                                        value={form.data.fecha_inicio}
                                        onChange={(e) =>
                                            form.setData(
                                                'fecha_inicio',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    <InputError
                                        message={form.errors.fecha_inicio}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="fecha_fin">
                                        Fecha de término
                                    </Label>
                                    <Input
                                        id="fecha_fin"
                                        type="date"
                                        value={form.data.fecha_fin}
                                        onChange={(e) =>
                                            form.setData(
                                                'fecha_fin',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    <InputError
                                        message={form.errors.fecha_fin}
                                    />
                                </div>
                                <div className="grid gap-2 md:col-span-2">
                                    <Label htmlFor="descripcion">
                                        Texto adicional (opcional)
                                    </Label>
                                    <Textarea
                                        id="descripcion"
                                        rows={3}
                                        value={form.data.descripcion}
                                        onChange={(e) =>
                                            form.setData(
                                                'descripcion',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="Detalles complementarios que aparecerán en el cuerpo."
                                    />
                                    <InputError
                                        message={form.errors.descripcion}
                                    />
                                </div>
                            </CardContent>
                        </Card>

                        <Card>
                            <CardHeader>
                                <CardTitle>Datos de emisión</CardTitle>
                            </CardHeader>
                            <CardContent className="grid gap-4 md:grid-cols-2">
                                <div className="grid gap-2">
                                    <Label htmlFor="fecha_emision">
                                        Fecha de emisión *
                                    </Label>
                                    <Input
                                        id="fecha_emision"
                                        type="date"
                                        value={form.data.fecha_emision}
                                        onChange={(e) =>
                                            form.setData(
                                                'fecha_emision',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    <InputError
                                        message={form.errors.fecha_emision}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="lugar_emision">
                                        Lugar de emisión
                                    </Label>
                                    <Input
                                        id="lugar_emision"
                                        value={form.data.lugar_emision}
                                        onChange={(e) =>
                                            form.setData(
                                                'lugar_emision',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    <InputError
                                        message={form.errors.lugar_emision}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="emisor_nombre">
                                        Firmante
                                    </Label>
                                    <Input
                                        id="emisor_nombre"
                                        value={form.data.emisor_nombre}
                                        onChange={(e) =>
                                            form.setData(
                                                'emisor_nombre',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    <InputError
                                        message={form.errors.emisor_nombre}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="emisor_cargo">
                                        Cargo del firmante
                                    </Label>
                                    <Input
                                        id="emisor_cargo"
                                        value={form.data.emisor_cargo}
                                        onChange={(e) =>
                                            form.setData(
                                                'emisor_cargo',
                                                e.target.value,
                                            )
                                        }
                                    />
                                    <InputError
                                        message={form.errors.emisor_cargo}
                                    />
                                </div>
                                <div className="grid gap-2">
                                    <Label htmlFor="emisor_cip">
                                        CIP (opcional)
                                    </Label>
                                    <Input
                                        id="emisor_cip"
                                        value={form.data.emisor_cip}
                                        onChange={(e) =>
                                            form.setData(
                                                'emisor_cip',
                                                e.target.value,
                                            )
                                        }
                                        placeholder="123456"
                                    />
                                    <InputError
                                        message={form.errors.emisor_cip}
                                    />
                                </div>
                            </CardContent>
                        </Card>

                        <div className="flex justify-end gap-2">
                            <Button type="submit" disabled={form.processing}>
                                {form.processing ? (
                                    <Spinner />
                                ) : (
                                    <Save className="size-4" />
                                )}
                                Emitir certificado
                            </Button>
                        </div>
                    </form>

                    {/* Preview en vivo */}
                    <aside>
                        <Card className="overflow-hidden p-0">
                            <CertificadoPreview
                                certificado={previewData}
                                branding={branding}
                                scale={0.72}
                            />
                        </Card>
                    </aside>
                </div>
            </div>
        </>
    );
}

CertificadoCreate.layout = {
    title: 'Nuevo certificado',
    description: 'Edita los campos y observa el certificado actualizarse en vivo. El código y el QR se generan al guardar.',
    breadcrumbs: [
        { title: 'Panel', href: '/dashboard' },
        { title: 'Certificados', href: '/certificados' },
        { title: 'Nuevo', href: '/certificados/create' },
    ],
};
