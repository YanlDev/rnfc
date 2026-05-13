import { Head, Link, setLayoutProps, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import obras from '@/routes/obras';
import ObraFormFields, {
    type EstadoOpcion,
    type ObraFormData,
} from './_form';

type ObraData = {
    id: number;
    codigo: string;
    nombre: string;
    descripcion: string | null;
    ubicacion: string | null;
    latitud: number | null;
    longitud: number | null;
    entidad_contratante: string | null;
    monto_contractual: number | null;
    fecha_inicio: string | null;
    fecha_fin_prevista: string | null;
    fecha_fin_real: string | null;
    estado: string;
};

type Props = {
    obra: ObraData;
    estados: EstadoOpcion[];
};

export default function ObraEdit({ obra, estados }: Props) {
    setLayoutProps({
        title: `Editar ${obra.codigo}`,
        description: obra.nombre,
    });

    const form = useForm<ObraFormData>({
        codigo: obra.codigo,
        nombre: obra.nombre,
        descripcion: obra.descripcion ?? '',
        ubicacion: obra.ubicacion ?? '',
        latitud: obra.latitud !== null ? String(obra.latitud) : '',
        longitud: obra.longitud !== null ? String(obra.longitud) : '',
        entidad_contratante: obra.entidad_contratante ?? '',
        monto_contractual:
            obra.monto_contractual !== null ? String(obra.monto_contractual) : '',
        fecha_inicio: obra.fecha_inicio ?? '',
        fecha_fin_prevista: obra.fecha_fin_prevista ?? '',
        fecha_fin_real: obra.fecha_fin_real ?? '',
        estado: obra.estado,
    });

    const onSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        form.put(obras.update(obra.id).url);
    };

    return (
        <>
            <Head title={`Editar ${obra.codigo}`} />
            <form onSubmit={onSubmit} className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex items-center justify-between">
                    <Link
                        href={obras.show(obra.id).url}
                        className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft className="size-3.5" />
                        Volver al detalle
                    </Link>
                    <Button type="submit" disabled={form.processing}>
                        {form.processing ? <Spinner /> : <Save className="size-4" />}
                        Guardar cambios
                    </Button>
                </div>

                <ObraFormFields
                    data={form.data}
                    errors={form.errors}
                    setData={form.setData}
                    estados={estados}
                />
            </form>
        </>
    );
}

ObraEdit.layout = {
    title: 'Editar obra',
    breadcrumbs: [
        { title: 'Panel', href: '/dashboard' },
        { title: 'Obras', href: '/obras' },
    ],
};
