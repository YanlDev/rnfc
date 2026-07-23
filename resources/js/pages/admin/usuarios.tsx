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
    puede_crear_obras: boolean;
    last_login_at: string | null;
    activo: boolean;
    desactivado_at: string | null;
    desactivado_por: string | null;
    motivo_desactivacion: string | null;
    created_at: string;
    eliminado_at: string | null;
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
        eliminados: number;
    };
    invitacionesPendientes: InvitacionPendienteGlobal[];
};

const ROL_COLOR: Record<string, string> = {
    admin: 'bg-red-100 text-red-800 hover:bg-red-100 dark:bg-red-950/40 dark:text-red-300',
    gerente:
        'bg-amber-100 text-amber-800 hover:bg-amber-100 dark:bg-amber-950/40 dark:text-amber-300',
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

    // Roles que se pueden otorgar por invitación global (todos menos "usuario",
    // que es el rol base y depende de la obra).
    const rolesInvitables = roles.filter((r) => r.value !== 'usuario');

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

    const eliminarUsuario = async (u: Usuario) => {
        const ok = await confirm({
            titulo: `¿Enviar a ${u.name} a la papelera?`,
            descripcion:
                'Perderá el acceso y se cerrarán sus sesiones. Se conserva su autoría en certificados, cuadernos y caja. Podrás restaurarlo desde la papelera.',
            confirmar: 'Sí, eliminar',
            cancelar: 'Cancelar',
            destructivo: true,
        });

        if (!ok) {
            return;
        }

        router.delete(`/admin/usuarios/${u.id}`, {
            preserveScroll: true,
            onError: (errors) => {
                if (errors.usuario) {
                    toast.error(errors.usuario);
                }
            },
        });
    };

    const restaurarUsuario = (u: Usuario) => {
        router.patch(
            `/admin/usuarios/${u.id}/restaurar`,
            {},
            { preserveScroll: true },
        );
    };

    // Estado del usuario (compartido entre la tabla desktop y las cards mobile).
    const estadoUsuario = (u: Usuario) =>
        u.eliminado_at ? (
            <span
                className="inline-flex items-center gap-1.5 text-xs font-medium text-red-600 dark:text-red-400"
                title={`Eliminado el ${u.eliminado_at}`}
            >
                <Trash2 className="size-3" />
                En papelera
            </span>
        ) : u.activo ? (
            <span className="inline-flex items-center gap-1.5 text-xs font-medium text-emerald-700 dark:text-emerald-400">
                <span className="size-2 rounded-full bg-emerald-500" />
                Activo
            </span>
        ) : (
            <span
                className="inline-flex items-center gap-1.5 text-xs font-medium text-slate-500"
                title={u.motivo_desactivacion ?? 'Sin motivo registrado'}
            >
                <span className="size-2 rounded-full bg-slate-400" />
                Desactivado
            </span>
        );

    // Menú de acciones por usuario (compartido entre tabla y cards).
    const menuAcciones = (u: Usuario) => (
        <DropdownMenu>
            <DropdownMenuTrigger asChild>
                <Button
                    variant="ghost"
                    size="sm"
                    disabled={u.es_yo}
                    aria-label={`Acciones para ${u.name}`}
                    title={
                        u.es_yo ? 'No puedes modificarte a ti mismo' : undefined
                    }
                >
                    <MoreVertical className="size-4" />
                </Button>
            </DropdownMenuTrigger>
            <DropdownMenuContent align="end">
                <DropdownMenuLabel>{u.name}</DropdownMenuLabel>
                <DropdownMenuSeparator />
                {u.eliminado_at ? (
                    <DropdownMenuItem onClick={() => restaurarUsuario(u)}>
                        <RotateCcw className="size-4" />
                        Restaurar
                    </DropdownMenuItem>
                ) : (
                    <>
                        <DropdownMenuItem
                            onClick={() => {
                                setUsuarioRol(u);
                                formRol.setData('rol', u.rol ?? '');
                            }}
                        >
                            <ShieldCheck className="size-4" />
                            Cambiar rol
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            onClick={() =>
                                router.patch(
                                    `/admin/usuarios/${u.id}/crear-obras`,
                                    {},
                                    { preserveScroll: true },
                                )
                            }
                        >
                            <Building2 className="size-4" />
                            {u.puede_crear_obras
                                ? 'Quitar permiso de crear obras'
                                : 'Permitir crear obras'}
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            onClick={() => {
                                setUsuarioObjetivo(u);
                                formToggle.reset();
                            }}
                            className={
                                u.activo
                                    ? 'text-destructive focus:text-destructive'
                                    : ''
                            }
                        >
                            <Power className="size-4" />
                            {u.activo ? 'Desactivar' : 'Reactivar'}
                        </DropdownMenuItem>
                        <DropdownMenuSeparator />
                        <DropdownMenuItem
                            onClick={() => eliminarUsuario(u)}
                            className="text-destructive focus:text-destructive"
                        >
                            <Trash2 className="size-4" />
                            Eliminar
                        </DropdownMenuItem>
                    </>
                )}
            </DropdownMenuContent>
        </DropdownMenu>
    );

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
                            <SelectItem value="eliminados">
                                Papelera{' '}
                                {kpis.eliminados > 0
                                    ? `(${kpis.eliminados})`
                                    : ''}
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
                        Invitar usuario
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
                    <>
                        {/* ============ MOBILE: cards apiladas ============ */}
                        <div className="space-y-3 md:hidden">
                            {usuarios.data.map((u) => (
                                <Card key={u.id} className="gap-0 p-4">
                                    <div className="flex items-start justify-between gap-2">
                                        <div className="min-w-0">
                                            <div className="truncate font-medium">
                                                {u.name}
                                                {u.es_yo && (
                                                    <span className="ml-2 text-[10px] font-bold text-primary uppercase">
                                                        · tú
                                                    </span>
                                                )}
                                            </div>
                                            <div className="flex items-center gap-1 truncate text-xs text-muted-foreground">
                                                <Mail className="size-3 shrink-0" />
                                                {u.email}
                                            </div>
                                        </div>
                                        {menuAcciones(u)}
                                    </div>
                                    <div className="mt-3 flex flex-wrap items-center gap-x-3 gap-y-1.5 border-t border-border pt-3">
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
                                                Sin rol
                                            </span>
                                        )}
                                        {estadoUsuario(u)}
                                        <span className="inline-flex items-center gap-1 text-xs text-muted-foreground">
                                            <Building2 className="size-3" />
                                            {u.obras_count}{' '}
                                            {u.obras_count === 1
                                                ? 'obra'
                                                : 'obras'}
                                        </span>
                                        {u.puede_crear_obras && (
                                            <Badge
                                                variant="secondary"
                                                className="text-[10px]"
                                                title="Puede crear sus propias obras"
                                            >
                                                Crea obras
                                            </Badge>
                                        )}
                                        <span className="inline-flex items-center gap-1 text-xs text-muted-foreground">
                                            <Clock className="size-3" />
                                            {u.last_login_at ?? 'Nunca'}
                                        </span>
                                    </div>
                                </Card>
                            ))}
                        </div>

                        {/* ============ DESKTOP: tabla ============ */}
                        <Card className="hidden overflow-hidden p-0 md:block">
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
                                                            ROL_COLOR[u.rol] ??
                                                            ''
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
                                                    {u.puede_crear_obras && (
                                                        <Badge
                                                            variant="secondary"
                                                            className="ml-1 text-[10px]"
                                                            title="Puede crear sus propias obras"
                                                        >
                                                            Crea obras
                                                        </Badge>
                                                    )}
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
                                                {estadoUsuario(u)}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                {menuAcciones(u)}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </Card>
                    </>
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
                        <DialogTitle>Invitar usuario de plataforma</DialogTitle>
                        <DialogDescription>
                            Crea una cuenta con un rol global (Administrador o
                            Gerente General). Para sumar gente a una obra, hazlo
                            desde el equipo de la obra. El enlace expira en 7
                            días.
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
                        <div className="grid gap-2">
                            <Label htmlFor="invitar-rol">
                                Rol de plataforma
                            </Label>
                            <Select
                                value={formInvitar.data.rol_global}
                                onValueChange={(v) =>
                                    formInvitar.setData('rol_global', v)
                                }
                            >
                                <SelectTrigger id="invitar-rol">
                                    <SelectValue placeholder="Selecciona un rol" />
                                </SelectTrigger>
                                <SelectContent>
                                    {rolesInvitables.map((r) => (
                                        <SelectItem
                                            key={r.value}
                                            value={r.value}
                                        >
                                            {r.label}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            <p className="text-xs text-muted-foreground">
                                El <strong>Gerente General</strong> ve todas las
                                obras y emite certificados, sin administrar la
                                plataforma. El <strong>Administrador</strong>{' '}
                                puede todo.
                            </p>
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
