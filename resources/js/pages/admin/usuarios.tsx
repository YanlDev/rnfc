import { Head, router, useForm } from '@inertiajs/react';
import {
    Building2,
    CheckCircle2,
    Clock,
    Mail,
    MoreVertical,
    Power,
    RotateCcw,
    Search,
    ShieldAlert,
    ShieldCheck,
    Trash2,
    UserPlus,
    UserX,
    Users,
} from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import { useConfirm } from '@/components/confirm-dialog';
import Paginacion from '@/components/paginacion';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/components/ui/dropdown-menu';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
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
import { Textarea } from '@/components/ui/textarea';
import { useFiltrosUrl } from '@/hooks/use-filtros-url';
import type { Paginado } from '@/types';

type Usuario = {
    id: number;
    name: string;
    email: string;
    rol: string | null;
    rol_label: string;
    obras_count: number;
    last_login_at: string | null;
    activo: boolean;
    desactivado_at: string | null;
    desactivado_por: string | null;
    motivo_desactivacion: string | null;
    created_at: string;
    es_yo: boolean;
};

type RolOpcion = { value: string; label: string };

type InvitacionPendienteGlobal = {
    id: number;
    email: string;
    rol_global: string;
    rol_global_label: string;
    expira_at: string;
    invitador: string | null;
};

type Props = {
    usuarios: Paginado<Usuario>;
    filtros: { q: string; estado: string; rol: string };
    roles: RolOpcion[];
    kpis: {
        total: number;
        activos: number;
        desactivados: number;
        admins: number;
    };
    invitacionesPendientes: InvitacionPendienteGlobal[];
};

const ROL_COLOR: Record<string, string> = {
    admin: 'bg-red-100 text-red-800 hover:bg-red-100 dark:bg-red-950/40 dark:text-red-300',
    usuario:
        'bg-slate-100 text-slate-700 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300',
};

export default function AdminUsuarios({
    usuarios,
    filtros,
    roles,
    kpis,
    invitacionesPendientes,
}: Props) {
    const { filtros: f, set } = useFiltrosUrl('/admin/usuarios', {
        q: filtros.q,
        estado: filtros.estado,
        rol: filtros.rol,
    });

    // Modal desactivar — useForm da processing/errors y evita doble submit.
    const [usuarioObjetivo, setUsuarioObjetivo] = useState<Usuario | null>(
        null,
    );
    const formToggle = useForm<{ motivo: string }>({ motivo: '' });

    // Modal cambiar rol
    const [usuarioRol, setUsuarioRol] = useState<Usuario | null>(null);
    const formRol = useForm<{ rol: string }>({ rol: '' });

    // Modal invitar usuario global
    const [invitarOpen, setInvitarOpen] = useState(false);
    const formInvitar = useForm<{ email: string; rol_global: string }>({
        email: '',
        rol_global: 'admin',
    });

    const { confirm, dialog } = useConfirm();

    const confirmarToggle = () => {
        if (!usuarioObjetivo || formToggle.processing) {
            return;
        }

        formToggle.transform((d) => ({ motivo: d.motivo.trim() || null }));
        formToggle.patch(
            `/admin/usuarios/${usuarioObjetivo.id}/toggle-activo`,
            {
                preserveScroll: true,
                onSuccess: () => {
                    setUsuarioObjetivo(null);
                    formToggle.reset();
                },
                onError: (errors) => {
                    if (errors.usuario) {
                        toast.error(errors.usuario);
                    }
                },
            },
        );
    };

    const confirmarCambioRol = () => {
        if (!usuarioRol || !formRol.data.rol || formRol.processing) {
            return;
        }

        formRol.patch(`/admin/usuarios/${usuarioRol.id}/rol`, {
            preserveScroll: true,
            onSuccess: () => {
                setUsuarioRol(null);
                formRol.reset();
            },
            onError: (errors) => {
                if (errors.rol) {
                    toast.error(errors.rol);
                }
            },
        });
    };

    const confirmarInvitacionGlobal = () => {
        if (!formInvitar.data.email || formInvitar.processing) {
            return;
        }

        formInvitar.post('/admin/invitar', {
            preserveScroll: true,
            onSuccess: () => {
                setInvitarOpen(false);
                formInvitar.reset();
            },
            onError: (errors) => {
                if (errors.email) {
                    toast.error(errors.email);
                }
            },
        });
    };

    const cancelarGlobal = async (invitacion: InvitacionPendienteGlobal) => {
        const ok = await confirm({
            titulo: `¿Cancelar la invitación a ${invitacion.email}?`,
            confirmar: 'Cancelar invitación',
            cancelar: 'Volver',
            destructivo: true,
        });

        if (!ok) {
            return;
        }

        router.delete(`/admin/invitaciones/${invitacion.id}`, {
            preserveScroll: true,
        });
    };

    const reenviarGlobal = (invitacion: InvitacionPendienteGlobal) => {
        router.post(
            `/admin/invitaciones/${invitacion.id}/reenviar`,
            {},
            { preserveScroll: true },
        );
    };

    return (
        <>
            <Head title="Usuarios" />

            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                {/* KPIs */}
                <div className="grid gap-3 sm:grid-cols-2 lg:grid-cols-4">
                    <KpiCard
                        icon={<Users className="size-5 text-primary" />}
                        label="Total"
                        value={kpis.total}
                    />
                    <KpiCard
                        icon={
                            <CheckCircle2 className="size-5 text-emerald-600" />
                        }
                        label="Activos"
                        value={kpis.activos}
                    />
                    <KpiCard
                        icon={<UserX className="size-5 text-slate-500" />}
                        label="Desactivados"
                        value={kpis.desactivados}
                    />
                    <KpiCard
                        icon={<ShieldAlert className="size-5 text-red-600" />}
                        label="Administradores"
                        value={kpis.admins}
                    />
                </div>

                {/* Filtros */}
                <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                    <div className="relative flex-1 sm:max-w-md">
                        <Search className="absolute top-1/2 left-3 size-4 -translate-y-1/2 text-muted-foreground" />
                        <Input
                            placeholder="Buscar por nombre o correo…"
                            value={f.q}
                            onChange={(e) => set('q', e.target.value)}
                            className="pl-9"
                        />
                    </div>
                    <Select
                        value={f.estado}
                        onValueChange={(v) => set('estado', v)}
                    >
                        <SelectTrigger className="sm:w-48">
                            <SelectValue placeholder="Estado" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="todos">
                                Todos los estados
                            </SelectItem>
                            <SelectItem value="activos">
                                Solo activos
                            </SelectItem>
                            <SelectItem value="desactivados">
                                Solo desactivados
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <Select value={f.rol} onValueChange={(v) => set('rol', v)}>
                        <SelectTrigger className="sm:w-52">
                            <SelectValue placeholder="Rol" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="todos">
                                Todos los roles
                            </SelectItem>
                            {roles.map((r) => (
                                <SelectItem key={r.value} value={r.value}>
                                    {r.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    <Button
                        variant="default"
                        onClick={() => setInvitarOpen(true)}
                    >
                        <UserPlus className="mr-2 size-4" />
                        Invitar administrador
                    </Button>
                </div>

                {/* Invitaciones globales pendientes */}
                {invitacionesPendientes.length > 0 && (
                    <Card>
                        <CardContent className="space-y-2 p-4">
                            <h3 className="text-xs font-semibold tracking-wide text-muted-foreground uppercase">
                                Invitaciones globales pendientes
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
                                                {i.rol_global_label} · Expira el{' '}
                                                {new Date(
                                                    i.expira_at,
                                                ).toLocaleDateString('es-PE')}
                                                {i.invitador && (
                                                    <> · Invitó {i.invitador}</>
                                                )}
                                            </div>
                                        </div>
                                        <div className="flex gap-1">
                                            <Button
                                                size="sm"
                                                variant="outline"
                                                onClick={() =>
                                                    reenviarGlobal(i)
                                                }
                                                title="Reenviar"
                                                aria-label={`Reenviar invitación a ${i.email}`}
                                            >
                                                <RotateCcw className="size-4" />
                                            </Button>
                                            <Button
                                                size="sm"
                                                variant="ghost"
                                                onClick={() =>
                                                    cancelarGlobal(i)
                                                }
                                                title="Cancelar"
                                                aria-label={`Cancelar invitación a ${i.email}`}
                                            >
                                                <Trash2 className="size-4 text-destructive" />
                                            </Button>
                                        </div>
                                    </li>
                                ))}
                            </ul>
                        </CardContent>
                    </Card>
                )}

                {/* Tabla */}
                {usuarios.data.length === 0 ? (
                    <Card className="p-12 text-center">
                        <Users className="mx-auto mb-3 size-10 text-muted-foreground" />
                        <p className="text-sm text-muted-foreground">
                            No hay usuarios que coincidan con los filtros.
                        </p>
                    </Card>
                ) : (
                    <Card className="overflow-hidden p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Usuario</TableHead>
                                    <TableHead>Rol</TableHead>
                                    <TableHead>Obras</TableHead>
                                    <TableHead>Último acceso</TableHead>
                                    <TableHead>Estado</TableHead>
                                    <TableHead className="text-right">
                                        Acciones
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {usuarios.data.map((u) => (
                                    <TableRow key={u.id}>
                                        <TableCell>
                                            <div className="flex flex-col">
                                                <span className="font-medium text-foreground">
                                                    {u.name}
                                                    {u.es_yo && (
                                                        <span className="ml-2 text-[10px] font-bold text-primary uppercase">
                                                            · tú
                                                        </span>
                                                    )}
                                                </span>
                                                <span className="flex items-center gap-1 text-xs text-muted-foreground">
                                                    <Mail className="size-3" />{' '}
                                                    {u.email}
                                                </span>
                                            </div>
                                        </TableCell>
                                        <TableCell>
                                            {u.rol ? (
                                                <Badge
                                                    className={
                                                        ROL_COLOR[u.rol] ?? ''
                                                    }
                                                >
                                                    {u.rol_label}
                                                </Badge>
                                            ) : (
                                                <span className="text-xs text-muted-foreground">
                                                    —
                                                </span>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <span className="inline-flex items-center gap-1 text-sm">
                                                <Building2 className="size-3.5 text-muted-foreground" />
                                                {u.obras_count}
                                            </span>
                                        </TableCell>
                                        <TableCell className="text-xs text-muted-foreground">
                                            {u.last_login_at ? (
                                                <span className="inline-flex items-center gap-1">
                                                    <Clock className="size-3" />
                                                    {u.last_login_at}
                                                </span>
                                            ) : (
                                                'Nunca'
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            {u.activo ? (
                                                <span className="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 dark:text-emerald-400">
                                                    <span className="size-2 rounded-full bg-emerald-500" />
                                                    Activo
                                                </span>
                                            ) : (
                                                <span
                                                    className="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500"
                                                    title={
                                                        u.motivo_desactivacion ??
                                                        'Sin motivo registrado'
                                                    }
                                                >
                                                    <span className="size-2 rounded-full bg-slate-400" />
                                                    Desactivado
                                                </span>
                                            )}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <DropdownMenu>
                                                <DropdownMenuTrigger asChild>
                                                    <Button
                                                        variant="ghost"
                                                        size="sm"
                                                        disabled={u.es_yo}
                                                        aria-label={`Acciones para ${u.name}`}
                                                        title={
                                                            u.es_yo
                                                                ? 'No puedes modificarte a ti mismo'
                                                                : undefined
                                                        }
                                                    >
                                                        <MoreVertical className="size-4" />
                                                    </Button>
                                                </DropdownMenuTrigger>
                                                <DropdownMenuContent align="end">
                                                    <DropdownMenuLabel>
                                                        {u.name}
                                                    </DropdownMenuLabel>
                                                    <DropdownMenuSeparator />
                                                    <DropdownMenuItem
                                                        onClick={() => {
                                                            setUsuarioRol(u);
                                                            formRol.setData(
                                                                'rol',
                                                                u.rol ?? '',
                                                            );
                                                        }}
                                                    >
                                                        <ShieldCheck className="size-4" />
                                                        Cambiar rol
                                                    </DropdownMenuItem>
                                                    <DropdownMenuItem
                                                        onClick={() => {
                                                            setUsuarioObjetivo(
                                                                u,
                                                            );
                                                            formToggle.reset();
                                                        }}
                                                        className={
                                                            u.activo
                                                                ? 'text-destructive focus:text-destructive'
                                                                : ''
                                                        }
                                                    >
                                                        <Power className="size-4" />
                                                        {u.activo
                                                            ? 'Desactivar'
                                                            : 'Reactivar'}
                                                    </DropdownMenuItem>
                                                </DropdownMenuContent>
                                            </DropdownMenu>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </Card>
                )}

                {/* Paginación */}
                <Paginacion meta={usuarios.meta} links={usuarios.links} />
            </div>

            {/* === Modal: desactivar/reactivar === */}
            <Dialog
                open={!!usuarioObjetivo}
                onOpenChange={(open) => {
                    if (!open) {
                        setUsuarioObjetivo(null);
                        formToggle.reset();
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>
                            {usuarioObjetivo?.activo
                                ? 'Desactivar usuario'
                                : 'Reactivar usuario'}
                        </DialogTitle>
                        <DialogDescription>
                            {usuarioObjetivo?.activo ? (
                                <>
                                    <strong>{usuarioObjetivo?.name}</strong> ya
                                    no podrá iniciar sesión. Sus datos y
                                    participaciones en obras se conservan. Se
                                    cerrarán todas sus sesiones activas.
                                </>
                            ) : (
                                <>
                                    <strong>{usuarioObjetivo?.name}</strong>{' '}
                                    podrá volver a iniciar sesión con sus
                                    credenciales actuales.
                                </>
                            )}
                        </DialogDescription>
                    </DialogHeader>

                    {usuarioObjetivo?.activo && (
                        <div className="grid gap-2">
                            <Label htmlFor="motivo">Motivo (opcional)</Label>
                            <Textarea
                                id="motivo"
                                rows={3}
                                value={formToggle.data.motivo}
                                onChange={(e) =>
                                    formToggle.setData('motivo', e.target.value)
                                }
                                placeholder="Ej. Dejó de trabajar con RNFC el 30/04/2026"
                                maxLength={250}
                            />
                            <p className="text-xs text-muted-foreground">
                                Queda registrado en auditoría. No es visible
                                para el usuario.
                            </p>
                        </div>
                    )}

                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => {
                                setUsuarioObjetivo(null);
                                formToggle.reset();
                            }}
                        >
                            Cancelar
                        </Button>
                        <Button
                            variant={
                                usuarioObjetivo?.activo
                                    ? 'destructive'
                                    : 'default'
                            }
                            onClick={confirmarToggle}
                            disabled={formToggle.processing}
                        >
                            {usuarioObjetivo?.activo
                                ? 'Sí, desactivar'
                                : 'Sí, reactivar'}
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* === Modal: cambiar rol === */}
            <Dialog
                open={!!usuarioRol}
                onOpenChange={(open) => {
                    if (!open) {
                        setUsuarioRol(null);
                        formRol.reset();
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Cambiar rol global</DialogTitle>
                        <DialogDescription>
                            Selecciona el nuevo rol para{' '}
                            <strong>{usuarioRol?.name}</strong>. Rol actual:{' '}
                            <strong>{usuarioRol?.rol_label}</strong>.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="grid gap-2">
                        <Label>Nuevo rol</Label>
                        <Select
                            value={formRol.data.rol}
                            onValueChange={(v) => formRol.setData('rol', v)}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="Selecciona un rol" />
                            </SelectTrigger>
                            <SelectContent>
                                {roles.map((r) => (
                                    <SelectItem key={r.value} value={r.value}>
                                        {r.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>

                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => {
                                setUsuarioRol(null);
                                formRol.reset();
                            }}
                        >
                            Cancelar
                        </Button>
                        <Button
                            onClick={confirmarCambioRol}
                            disabled={
                                formRol.processing ||
                                !formRol.data.rol ||
                                formRol.data.rol === usuarioRol?.rol
                            }
                        >
                            Confirmar
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {/* === Modal: invitar usuario global === */}
            <Dialog
                open={invitarOpen}
                onOpenChange={(open) => {
                    if (!open) {
                        setInvitarOpen(false);
                        formInvitar.reset();
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>
                            Invitar administrador
                        </DialogTitle>
                        <DialogDescription>
                            Crea una cuenta con permisos de administrador de
                            plataforma. Para sumar gente a una obra, hazlo desde
                            el equipo de la obra. El enlace expira en 7 días.
                        </DialogDescription>
                    </DialogHeader>

                    <div className="grid gap-4">
                        <div className="grid gap-2">
                            <Label htmlFor="invitar-email">
                                Correo electrónico
                            </Label>
                            <Input
                                id="invitar-email"
                                type="email"
                                value={formInvitar.data.email}
                                onChange={(e) =>
                                    formInvitar.setData('email', e.target.value)
                                }
                                placeholder="correo@ejemplo.com"
                                autoFocus
                            />
                        </div>
                    </div>

                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => {
                                setInvitarOpen(false);
                                formInvitar.reset();
                            }}
                        >
                            Cancelar
                        </Button>
                        <Button
                            onClick={confirmarInvitacionGlobal}
                            disabled={
                                formInvitar.processing ||
                                !formInvitar.data.email
                            }
                        >
                            Enviar invitación
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>

            {dialog}
        </>
    );
}

function KpiCard({
    icon,
    label,
    value,
}: {
    icon: React.ReactNode;
    label: string;
    value: number;
}) {
    return (
        <Card>
            <CardContent className="flex items-center gap-3 p-5">
                <div className="flex size-10 items-center justify-center rounded-lg bg-muted">
                    {icon}
                </div>
                <div>
                    <div className="text-xs font-medium tracking-wider text-muted-foreground uppercase">
                        {label}
                    </div>
                    <div className="font-display text-2xl font-bold tabular-nums">
                        {value}
                    </div>
                </div>
            </CardContent>
        </Card>
    );
}

AdminUsuarios.layout = {
    title: 'Usuarios',
    description: 'Administra cuentas, roles globales y acceso a la plataforma.',
    breadcrumbs: [
        { title: 'Panel', href: '/dashboard' },
        { title: 'Administración', href: '/admin' },
        { title: 'Usuarios', href: '/admin/usuarios' },
    ],
};
