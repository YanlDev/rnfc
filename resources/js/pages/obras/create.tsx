import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Spinner } from '@/components/ui/spinner';
import obras from '@/routes/obras';
import ObraFormFields, {
    type EstadoOpcion,
    type ObraFormData,
} from './_form';

type Props = {
    estados: EstadoOpcion[];
    codigoSugerido: string;
};

export default function ObraCreate({ estados, codigoSugerido }: Props) {
    const form = useForm<ObraFormData>({
        codigo: codigoSugerido,
        nombre: '',
        descripcion: '',
        ubicacion: '',
        latitud: '',
        longitud: '',
        entidad_contratante: '',
        monto_contractual: '',
        fecha_inicio: '',
        fecha_fin_prevista: '',
        fecha_fin_real: '',
        estado: 'planificacion',
    });

    const onSubmit = (e: React.FormEvent) => {
        e.preventDefault();
        form.post(obras.store().url);
    };

    return (
        <>
            <Head title="Nueva obra" />
            <form onSubmit={onSubmit} className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex items-center justify-between">
                    <Link
                        href={obras.index().url}
                        className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft className="size-3.5" />
                        Volver al listado
                    </Link>
                    <Button type="submit" disabled={form.processing}>
                        {form.processing ? <Spinner /> : <Save className="size-4" />}
                        Crear obra
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

ObraCreate.layout = {
    title: 'Nueva obra',
    description: 'Registra una nueva obra con su ubicación, cronograma y entidad contratante.',
    breadcrumbs: [
        { title: 'Panel', href: '/dashboard' },
        { title: 'Obras', href: '/obras' },
        { title: 'Nueva', href: '/obras/create' },
    ],
};
