import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft } from 'lucide-react';
import { Fragment, useState } from 'react';
import { toast } from 'sonner';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import obras from '@/routes/obras';

type Permiso = { key: string; label: string; solo_administrador: boolean };
type Grupo = { grupo: string; permisos: Permiso[] };
type Rol = { value: string; label: string; editable: boolean };
type Matriz = Record<string, Record<string, boolean>>;
type ObraResumen = { id: number; codigo: string; nombre: string };

type Props = {
    obra: ObraResumen;
    grupos: Grupo[];
    roles: Rol[];
    matriz: Matriz;
    personalizada: boolean;
};

const ROL_ADMINISTRADOR = 'administrador';

export default function ObraPermisos({
    obra,
    grupos,
    roles,
    matriz,
    personalizada,
}: Props) {
    const [estado, setEstado] = useState<Matriz>(matriz);
    const [guardando, setGuardando] = useState(false);

    const toggle = (rol: string, permiso: string, valor: boolean) => {
        setEstado((prev) => ({
            ...prev,
            [rol]: { ...prev[rol], [permiso]: valor },
        }));
    };

    const guardar = () => {
        setGuardando(true);
        router.put(
            `/obras/${obra.id}/permisos`,
            { matriz: estado },
            {
                preserveScroll: true,
                onSuccess: () => toast.success('Permisos del equipo guardados.'),
                onFinish: () => setGuardando(false),
            },
        );
    };

    const restaurar = () => {
        router.delete(`/obras/${obra.id}/permisos`, {
            preserveScroll: true,
            onSuccess: () =>
                toast.success('El equipo vuelve a los permisos por defecto.'),
        });
    };

    return (
        <>
            <Head title={`Permisos del equipo · ${obra.codigo}`} />
            <div className="flex flex-1 flex-col gap-4 p-4 md:p-6">
                <Link
                    href={obras.show(obra.id).url}
                    className="inline-flex items-center gap-1 self-start text-sm text-muted-foreground hover:text-foreground"
                >
                    <ArrowLeft className="size-3.5" />
                    Volver a la obra
                </Link>

                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p className="max-w-2xl text-sm text-muted-foreground">
                        Define qué puede hacer cada rol del equipo{' '}
                        <strong className="text-foreground">
                            en esta obra
                        </strong>
                        . El <strong>Administrador de obra</strong> conserva
                        siempre el control total y no se edita aquí.
                    </p>
                    <div className="flex items-center gap-2">
                        {personalizada && (
                            <Button
                                variant="outline"
                                size="sm"
                                onClick={restaurar}
                            >
                                Restaurar por defecto
                            </Button>
                        )}
                        <Button onClick={guardar} disabled={guardando}>
                            {guardando ? 'Guardando…' : 'Guardar cambios'}
                        </Button>
                    </div>
                </div>

                {personalizada && (
                    <Badge variant="secondary" className="self-start">
                        Esta obra usa permisos personalizados
                    </Badge>
                )}

                <Card className="overflow-hidden p-0">
                    <div className="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="w-[40%]">
                                        Capacidad
                                    </TableHead>
                                    {roles.map((r) => (
                                        <TableHead
                                            key={r.value}
                                            className="text-center"
                                        >
                                            {r.label}
                                            {!r.editable && (
                                                <span className="block text-[10px] font-normal text-muted-foreground">
                                                    (fijo)
                                                </span>
                                            )}
                                        </TableHead>
                                    ))}
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {grupos.map((g) => (
                                    <Fragment key={g.grupo}>
                                        <TableRow className="bg-muted/40 hover:bg-muted/40">
                                            <TableCell
                                                colSpan={roles.length + 1}
                                                className="py-2 text-xs font-semibold tracking-wider text-muted-foreground uppercase"
                                            >
                                                {g.grupo}
                                            </TableCell>
                                        </TableRow>
                                        {g.permisos.map((p) => (
                                            <TableRow key={p.key}>
                                                <TableCell className="font-medium text-foreground">
                                                    {p.label}
                                                </TableCell>
                                                {roles.map((r) => {
                                                    // La caja chica es exclusiva
                                                    // del Administrador de obra.
                                                    const reservado =
                                                        p.solo_administrador &&
                                                        r.value !==
                                                            ROL_ADMINISTRADOR;
                                                    // El rol Administrador se
                                                    // muestra fijo, no editable.
                                                    const bloqueado =
                                                        !r.editable || reservado;

                                                    return (
                                                        <TableCell
                                                            key={r.value}
                                                            className="text-center"
                                                        >
                                                            {reservado ? (
                                                                <span className="text-muted-foreground/40">
                                                                    —
                                                                </span>
                                                            ) : (
                                                                <Checkbox
                                                                    checked={
                                                                        estado[
                                                                            r
                                                                                .value
                                                                        ]?.[
                                                                            p.key
                                                                        ] ??
                                                                        false
                                                                    }
                                                                    disabled={
                                                                        bloqueado
                                                                    }
                                                                    onCheckedChange={(
                                                                        v,
                                                                    ) =>
                                                                        toggle(
                                                                            r.value,
                                                                            p.key,
                                                                            v ===
                                                                                true,
                                                                        )
                                                                    }
                                                                    aria-label={`${p.label} — ${r.label}`}
                                                                />
                                                            )}
                                                        </TableCell>
                                                    );
                                                })}
                                            </TableRow>
                                        ))}
                                    </Fragment>
                                ))}
                            </TableBody>
                        </Table>
                    </div>
                </Card>
            </div>
        </>
    );
}

ObraPermisos.layout = {
    title: 'Permisos del equipo',
    description: 'Configura qué puede hacer cada rol en esta obra.',
    breadcrumbs: [
        { title: 'Panel', href: '/dashboard' },
        { title: 'Obras', href: '/obras' },
        { title: 'Permisos del equipo', href: '' },
    ],
};
