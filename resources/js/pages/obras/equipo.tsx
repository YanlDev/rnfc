import { Head, Link } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import EquipoObra from './_equipo';
import type { InvitacionPendiente, Miembro } from './_equipo';

type RolOpcion = { value: string; label: string };

type Props = {
    obra: { id: number; codigo: string; nombre: string };
    equipo: Miembro[];
    invitacionesPendientes: InvitacionPendiente[];
    rolesObra: RolOpcion[];
    puedeAdministrar: boolean;
};

export default function ObraEquipo({
    obra,
    equipo,
    invitacionesPendientes,
    rolesObra,
    puedeAdministrar,
}: Props) {
    return (
        <>
            <Head title={`Equipo — ${obra.codigo}`} />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div>
                    <Link
                        href={`/obras/${obra.id}`}
                        className="inline-flex items-center gap-1 text-sm text-muted-foreground hover:text-foreground"
                    >
                        <ArrowLeft className="size-3.5" />
                        Volver a la obra
                    </Link>
                    <h1
                        className="mt-2 truncate text-lg font-semibold text-foreground"
                        title={obra.nombre}
                    >
                        {obra.nombre}
                    </h1>
                </div>

                <EquipoObra
                    obraId={obra.id}
                    equipo={equipo}
                    invitacionesPendientes={invitacionesPendientes}
                    rolesObra={rolesObra}
                    puedeAdministrar={puedeAdministrar}
                />
            </div>
        </>
    );
}

ObraEquipo.layout = {
    breadcrumbs: [
        { title: 'Panel', href: '/dashboard' },
        { title: 'Obras', href: '/obras' },
        { title: 'Equipo', href: '' },
    ],
};
