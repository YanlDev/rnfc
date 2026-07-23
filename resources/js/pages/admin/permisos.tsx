import { Head, router } from '@inertiajs/react';
import { Fragment, useState } from 'react';
import { toast } from 'sonner';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';

type Permiso = { key: string; label: string; solo_administrador: boolean };

const ROL_ADMINISTRADOR = 'administrador';
type Grupo = { grupo: string; permisos: Permiso[] };
type Rol = { value: string; label: string };
type Matriz = Record<string, Record<string, boolean>>;
type ObraOpcion = { id: number; nombre: string; personalizada: boolean };

type Props = {
    grupos: Grupo[];
    roles: Rol[];
    matriz: Matriz;
    obras: ObraOpcion[];
    obraSeleccionada: number | null;
    matrizPropia: boolean;
};

const DEFECTO = 'defecto';

export default function AdminPermisos({
    grupos,
    roles,
    matriz,
    obras,
    obraSeleccionada,
    matrizPropia,
}: Props) {
    const [estado, setEstado] = useState<Matriz>(matriz);
    const [guardando, setGuardando] = useState(false);

    const obra = obras.find((o) => o.id === obraSeleccionada) ?? null;

    const cambiarObra = (valor: string) => {
        router.get(
            '/admin/permisos',
            valor === DEFECTO ? {} : { obra: valor },
            { preserveScroll: true, preserveState: false },
        );
    };

    const toggle = (rol: string, permiso: string, valor: boolean) => {
        setEstado((prev) => ({
            ...prev,
            [rol]: { ...prev[rol], [permiso]: valor },
        }));
    };

    const guardar = () => {
        setGuardando(true);
        router.put(
            '/admin/permisos',
            { matriz: estado, obra: obra?.id ?? null },
            {
                preserveScroll: true,
                onSuccess: () => toast.success('Permisos actualizados.'),
                onFinish: () => setGuardando(false),
            },
        );
    };

    const restaurar = () => {
        if (!obra) return;
        router.delete(`/admin/permisos/${obra.id}`, {
            preserveScroll: true,
            onSuccess: () =>
                toast.success('La obra vuelve a los permisos por defecto.'),
        });
    };

    return (
        <>
            <Head title="Permisos por rol" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                    <p className="max-w-2xl text-sm text-muted-foreground">
                        Define qué puede hacer cada rol{' '}
                        <strong className="text-foreground">
                            dentro de una obra
                        </strong>
                        . El Administrador de plataforma siempre puede todo.
                    </p>
                    <Button onClick={guardar} disabled={guardando}>
                        {guardando ? 'Guardando…' : 'Guardar cambios'}
                    </Button>
                </div>

                <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <Select
                        value={obra ? String(obra.id) : DEFECTO}
                        onValueChange={cambiarObra}
                    >
                        <SelectTrigger className="w-full sm:w-80">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value={DEFECTO}>
                                Por defecto (todas las obras)
                            </SelectItem>
                            {obras.map((o) => (
                                <SelectItem key={o.id} value={String(o.id)}>
                                    {o.nombre}
                                    {o.personalizada ? ' — personalizada' : ''}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>

                    {obra &&
                        (matrizPropia ? (
                            <div className="flex items-center gap-2">
                                <Badge variant="secondary">
                                    Matriz personalizada
                                </Badge>
                                <Button
                                    variant="outline"
                                    size="sm"
                                    onClick={restaurar}
                                >
                                    Restaurar por defecto
                                </Button>
                            </div>
                        ) : (
                            <p className="text-sm text-muted-foreground">
                                Esta obra usa la matriz por defecto. Al guardar
                                cambios aquí, pasará a tener su propia matriz.
                            </p>
                        ))}
                </div>

                <Card className="overflow-hidden p-0">
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
                                            {roles.map((r) => (
                                                <TableCell
                                                    key={r.value}
                                                    className="text-center"
                                                >
                                                    {p.solo_administrador &&
                                                    r.value !==
                                                        ROL_ADMINISTRADOR ? (
                                                        <span
                                                            className="text-muted-foreground/40"
                                                            title="Exclusivo del Administrador de obra"
                                                        >
                                                            —
                                                        </span>
                                                    ) : (
                                                        <Checkbox
                                                            checked={
                                                                estado[
                                                                    r.value
                                                                ]?.[p.key] ??
                                                                false
                                                            }
                                                            onCheckedChange={(
                                                                v,
                                                            ) =>
                                                                toggle(
                                                                    r.value,
                                                                    p.key,
                                                                    v === true,
                                                                )
                                                            }
                                                            aria-label={`${p.label} — ${r.label}`}
                                                        />
                                                    )}
                                                </TableCell>
                                            ))}
                                        </TableRow>
                                    ))}
                                </Fragment>
                            ))}
                        </TableBody>
                    </Table>
                </Card>
            </div>
        </>
    );
}

AdminPermisos.layout = {
    title: 'Permisos por rol',
    description: 'Configura qué puede hacer cada rol dentro de las obras.',
    breadcrumbs: [
        { title: 'Panel', href: '/dashboard' },
        { title: 'Administración', href: '/admin' },
        { title: 'Permisos', href: '/admin/permisos' },
    ],
};
