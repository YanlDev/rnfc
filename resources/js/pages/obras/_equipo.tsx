import { router, useForm } from '@inertiajs/react';
import { Mail, RotateCcw, Trash2, UserPlus, Users } from 'lucide-react';
import InputError from '@/components/input-error';
import { Badge } from '@/components/ui/badge';
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

type RolOpcion = { value: string; label: string };

export type Miembro = {
    id: number;
    name: string;
    email: string;
    rol_obra: string;
    rol_obra_label: string;
    asignado_at: string;
};

export type InvitacionPendiente = {
    id: number;
    email: string;
    rol_obra: string;
    rol_obra_label: string;
    expira_at: string;
    invitador: string | null;
};

type Props = {
    obraId: number;
    equipo: Miembro[];
    invitacionesPendientes: InvitacionPendiente[];
    rolesObra: RolOpcion[];
    puedeAdministrar: boolean;
};

export default function EquipoObra({
    obraId,
    equipo,
    invitacionesPendientes,
    rolesObra,
    puedeAdministrar,
}: Props) {
    const form = useForm({
        email: '',
        rol_obra: 'asistente',
    });

    const invitar = (e: React.FormEvent) => {
        e.preventDefault();
        form.post(`/obras/${obraId}/equipo/invitar`, {
            preserveScroll: true,
            onSuccess: () => form.reset('email'),
        });
    };

    const cambiarRol = (miembro: Miembro, nuevoRol: string) => {
        if (nuevoRol === miembro.rol_obra) return;
        router.patch(
            `/obras/${obraId}/equipo/${miembro.id}`,
            { rol_obra: nuevoRol },
            { preserveScroll: true },
        );
    };

    const remover = (miembro: Miembro) => {
        if (!confirm(`¿Remover a ${miembro.name} del equipo de esta obra?`)) return;
        router.delete(`/obras/${obraId}/equipo/${miembro.id}`, {
            preserveScroll: true,
        });
    };

    const cancelar = (invitacion: InvitacionPendiente) => {
        if (!confirm(`¿Cancelar la invitación a ${invitacion.email}?`)) return;
        router.delete(`/obras/${obraId}/invitaciones/${invitacion.id}`, {
            preserveScroll: true,
        });
    };

    const reenviar = (invitacion: InvitacionPendiente) => {
        router.post(
            `/obras/${obraId}/invitaciones/${invitacion.id}/reenviar`,
            {},
            { preserveScroll: true },
        );
    };

    return (
        <Card>
            <CardHeader className="flex flex-row items-center justify-between space-y-0">
                <CardTitle className="flex items-center gap-2">
                    <Users className="size-5 text-primary" />
                    Equipo
                </CardTitle>
                <span className="text-xs text-muted-foreground">
                    {equipo.length} {equipo.length === 1 ? 'integrante' : 'integrantes'}
                    {invitacionesPendientes.length > 0 && (
                        <> · {invitacionesPendientes.length} pendiente(s)</>
                    )}
                </span>
            </CardHeader>
            <CardContent className="space-y-5">
                {puedeAdministrar && (
                    <form
                        onSubmit={invitar}
                        className="grid gap-3 rounded-md border border-border bg-muted/30 p-3 md:grid-cols-[minmax(0,1fr)_minmax(0,1fr)_auto] md:items-end"
                    >
                        <div className="min-w-0 space-y-1">
                            <Label htmlFor="email" className="text-xs">
                                Correo del nuevo miembro
                            </Label>
                            <Input
                                id="email"
                                type="email"
                                placeholder="persona@ejemplo.com"
                                value={form.data.email}
                                onChange={(e) => form.setData('email', e.target.value)}
                            />
                            <InputError message={form.errors.email} />
                        </div>
                        <div className="min-w-0 space-y-1">
                            <Label htmlFor="rol_obra" className="text-xs">
                                Rol en la obra
                            </Label>
                            <Select
                                value={form.data.rol_obra}
                                onValueChange={(v) => form.setData('rol_obra', v)}
                            >
                                <SelectTrigger id="rol_obra" className="w-full">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {rolesObra.map((r) => (
                                        <SelectItem key={r.value} value={r.value}>
                                            {r.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <InputError message={form.errors.rol_obra} />
                        </div>
                        <Button
                            type="submit"
                            disabled={form.processing}
                            className="md:self-end"
                        >
                            <UserPlus className="size-4" />
                            Invitar
                        </Button>
                    </form>
                )}

                {equipo.length === 0 && invitacionesPendientes.length === 0 && (
                    <p className="py-4 text-center text-sm text-muted-foreground">
                        Aún no hay integrantes en el equipo.
                    </p>
                )}

                {equipo.length > 0 && (
                    <div className="space-y-2">
                        <h3 className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                            Integrantes
                        </h3>
                        <ul className="divide-y divide-border rounded-md border border-border">
                            {equipo.map((m) => (
                                <li
                                    key={m.id}
                                    className="flex flex-col gap-2 p-3 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div>
                                        <div className="font-medium">{m.name}</div>
                                        <div className="text-xs text-muted-foreground">
                                            {m.email}
                                        </div>
                                    </div>
                                    <div className="flex items-center gap-2">
                                        {puedeAdministrar ? (
                                            <Select
                                                value={m.rol_obra}
                                                onValueChange={(v) => cambiarRol(m, v)}
                                            >
                                                <SelectTrigger className="w-44">
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {rolesObra.map((r) => (
                                                        <SelectItem
                                                            key={r.value}
                                                            value={r.value}
                                                        >
                                                            {r.label}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        ) : (
                                            <Badge variant="secondary">
                                                {m.rol_obra_label}
                                            </Badge>
                                        )}
                                        {puedeAdministrar && (
                                            <Button
                                                size="sm"
                                                variant="ghost"
                                                onClick={() => remover(m)}
                                                title="Remover del equipo"
                                            >
                                                <Trash2 className="size-4 text-destructive" />
                                            </Button>
                                        )}
                                    </div>
                                </li>
                            ))}
                        </ul>
                    </div>
                )}

                {invitacionesPendientes.length > 0 && (
                    <div className="space-y-2">
                        <h3 className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                            Invitaciones pendientes
                        </h3>
                        <ul className="divide-y divide-border rounded-md border border-dashed border-border">
                            {invitacionesPendientes.map((i) => (
                                <li
                                    key={i.id}
                                    className="flex flex-col gap-2 p-3 sm:flex-row sm:items-center sm:justify-between"
                                >
                                    <div>
                                        <div className="flex items-center gap-2 text-sm font-medium">
                                            <Mail className="size-4 text-muted-foreground" />
                                            {i.email}
                                        </div>
                                        <div className="text-xs text-muted-foreground">
                                            {i.rol_obra_label} · Expira el{' '}
                                            {new Date(i.expira_at).toLocaleDateString('es-PE')}
                                            {i.invitador && <> · Invitó {i.invitador}</>}
                                        </div>
                                    </div>
                                    {puedeAdministrar && (
                                        <div className="flex gap-1">
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                onClick={() => reenviar(i)}
                                                title="Reenviar"
                                            >
                                                <RotateCcw className="size-4" />
                                            </Button>
                                            <Button
                                                size="sm"
                                                variant="ghost"
                                                onClick={() => cancelar(i)}
                                                title="Cancelar"
                                            >
                                                <Trash2 className="size-4 text-destructive" />
                                            </Button>
                                        </div>
                                    )}
                                </li>
                            ))}
                        </ul>
                    </div>
                )}
            </CardContent>
        </Card>
    );
}
