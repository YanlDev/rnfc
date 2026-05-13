import { Head, router } from '@inertiajs/react';
import {
    Building2,
    CheckCircle2,
    Clock,
    Mail,
    MoreVertical,
    Power,
    Search,
    ShieldAlert,
    ShieldCheck,
    UserX,
    Users,
} from 'lucide-react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';
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
import { Textarea } from '@/components/ui/textarea';

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

type Paginado<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    meta: {
        current_page: number;
        last_page: number;
        total: number;
        from: number | null;
        to: number | null;
    };
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
};

const ROL_COLOR: Record<string, string> = {
    admin: 'bg-red-100 text-red-800 hover:bg-red-100 dark:bg-red-950/40 dark:text-red-300',
    gerente_general:
        'bg-purple-100 text-purple-800 hover:bg-purple-100 dark:bg-purple-950/40 dark:text-purple-300',
    supervisor:
        'bg-blue-100 text-blue-800 hover:bg-blue-100 dark:bg-blue-950/40 dark:text-blue-300',
    residente:
        'bg-amber-100 text-amber-800 hover:bg-amber-100 dark:bg-amber-950/40 dark:text-amber-300',
    ingeniero:
        'bg-emerald-100 text-emerald-800 hover:bg-emerald-100 dark:bg-emerald-950/40 dark:text-emerald-300',
    invitado:
        'bg-slate-100 text-slate-700 hover:bg-slate-100 dark:bg-slate-800 dark:text-slate-300',
};

export default function AdminUsuarios({ usuarios, filtros, roles, kpis }: Props) {
    const [busqueda, setBusqueda] = useState(filtros.q);
    const [estado, setEstado] = useState(filtros.estado);
    const [rol, setRol] = useState(filtros.rol);

    // Modal desactivar
    const [usuarioObjetivo, setUsuarioObjetivo] = useState<Usuario | null>(null);
    const [motivo, setMotivo] = useState('');

    // Modal cambiar rol
    const [usuarioRol, setUsuarioRol] = useState<Usuario | null>(null);
    const [nuevoRol, setNuevoRol] = useState<string>('');

    useEffect(() => {
        const t = setTimeout(() => {
            router.get(
                '/admin/usuarios',
                {
                    q: busqueda || undefined,
                    estado: estado === 'todos' ? undefined : estado,
                    rol: rol === 'todos' ? undefined : rol,
                },
                { preserveState: true, preserveScroll: true, replace: true },
            );
        }, 250);
        return () => clearTimeout(t);
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [busqueda, estado, rol]);

    const confirmarToggle = () => {
        if (!usuarioObjetivo) return;
        router.patch(
            `/admin/usuarios/${usuarioObjetivo.id}/toggle-activo`,
            { motivo: motivo.trim() || null },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setUsuarioObjetivo(null);
                    setMotivo('');
                },
                onError: (errors) => {
                    if (errors.usuario) toast.error(errors.usuario);
                },
            },
        );
    };

    const confirmarCambioRol = () => {
        if (!usuarioRol || !nuevoRol) return;
        router.patch(
            `/admin/usuarios/${usuarioRol.id}/rol`,
            { rol: nuevoRol },
            {
                preserveScroll: true,
                onSuccess: () => {
                    setUsuarioRol(null);
                    setNuevoRol('');
                },
                onError: (errors) => {
                    if (errors.rol) toast.error(errors.rol);
                },
            },
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
                        icon={<CheckCircle2 className="size-5 text-emerald-600" />}
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
                            value={busqueda}
                            onChange={(e) => setBusqueda(e.target.value)}
                            className="pl-9"
                        />
                    </div>
                    <Select value={estado} onValueChange={setEstado}>
                        <SelectTrigger className="sm:w-48">
                            <SelectValue placeholder="Estado" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="todos">Todos los estados</SelectItem>
                            <SelectItem value="activos">Solo activos</SelectItem>
                            <SelectItem value="desactivados">Solo desactivados</SelectItem>
                        </SelectContent>
                    </Select>
                    <Select value={rol} onValueChange={setRol}>
                        <SelectTrigger className="sm:w-52">
                            <SelectValue placeholder="Rol" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="todos">Todos los roles</SelectItem>
                            {roles.map((r) => (
                                <SelectItem key={r.value} value={r.value}>
                                    {r.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                </div>

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
                        <div className="overflow-x-auto">
                            <table className="w-full text-sm">
                                <thead className="border-b border-border bg-muted/30 text-xs font-semibold tracking-wider text-muted-foreground uppercase">
                                    <tr>
                                        <th className="px-4 py-3 text-left">Usuario</th>
                                        <th className="px-4 py-3 text-left">Rol</th>
                                        <th className="px-4 py-3 text-left">Obras</th>
                                        <th className="px-4 py-3 text-left">Último acceso</th>
                                        <th className="px-4 py-3 text-left">Estado</th>
                                        <th className="px-4 py-3 text-right">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-border">
                                    {usuarios.data.map((u) => (
                                        <tr key={u.id} className="hover:bg-muted/20">
                                            <td className="px-4 py-3">
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
                                                        <Mail className="size-3" /> {u.email}
                                                    </span>
                                                </div>
                                            </td>
                                            <td className="px-4 py-3">
                                                {u.rol ? (
                                                    <Badge className={ROL_COLOR[u.rol] ?? ''}>
                                                        {u.rol_label}
                                                    </Badge>
                                                ) : (
                                                    <span className="text-xs text-muted-foreground">—</span>
                                                )}
                                            </td>
                                            <td className="px-4 py-3">
                                                <span className="inline-flex items-center gap-1 text-sm">
                                                    <Building2 className="size-3.5 text-muted-foreground" />
                                                    {u.obras_count}
                                                </span>
                                            </td>
                                            <td className="px-4 py-3 text-xs text-muted-foreground">
                                                {u.last_login_at ? (
                                                    <span className="inline-flex items-center gap-1">
                                                        <Clock className="size-3" />
                                                        {u.last_login_at}
                                                    </span>
                                                ) : (
                                                    'Nunca'
                                                )}
                                            </td>
                                            <td className="px-4 py-3">
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
                                            </td>
                                            <td className="px-4 py-3 text-right">
                                                <DropdownMenu>
                                                    <DropdownMenuTrigger asChild>
                                                        <Button
                                                            variant="ghost"
                                                            size="sm"
                                                            disabled={u.es_yo}
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
                                                        <DropdownMenuLabel>{u.name}</DropdownMenuLabel>
                                                        <DropdownMenuSeparator />
                                                        <DropdownMenuItem
                                                            onClick={() => {
                                                                setUsuarioRol(u);
                                                                setNuevoRol(u.rol ?? '');
                                                            }}
                                                        >
                                                            <ShieldCheck className="size-4" />
                                                            Cambiar rol
                                                        </DropdownMenuItem>
                                                        <DropdownMenuItem
                                                            onClick={() => {
                                                                setUsuarioObjetivo(u);
                                                                setMotivo('');
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
                                                    </DropdownMenuContent>
                                                </DropdownMenu>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </Card>
                )}

                {/* Paginación */}
                {usuarios.meta.last_page > 1 && (
                    <div className="flex items-center justify-between text-sm text-muted-foreground">
                        <div>
                            {usuarios.meta.from ?? 0}–{usuarios.meta.to ?? 0} de{' '}
                            {usuarios.meta.total}
                        </div>
                        <div className="flex gap-1">
                            {usuarios.links.map((l, i) => (
                                <Button
                                    key={i}
                                    size="sm"
                                    variant={l.active ? 'default' : 'outline'}
                                    disabled={!l.url}
                                    onClick={() =>
                                        l.url &&
                                        router.visit(l.url, { preserveScroll: true })
                                    }
                                >
                                    <span dangerouslySetInnerHTML={{ __html: l.label }} />
                                </Button>
                            ))}
                        </div>
                    </div>
                )}
            </div>

            {/* === Modal: desactivar/reactivar === */}
            <Dialog
                open={!!usuarioObjetivo}
                onOpenChange={(open) => {
                    if (!open) {
                        setUsuarioObjetivo(null);
                        setMotivo('');
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
                                    <strong>{usuarioObjetivo?.name}</strong> ya no podrá iniciar
                                    sesión. Sus datos y participaciones en obras se conservan.
                                    Se cerrarán todas sus sesiones activas.
                                </>
                            ) : (
                                <>
                                    <strong>{usuarioObjetivo?.name}</strong> podrá volver a iniciar
                                    sesión con sus credenciales actuales.
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
                                value={motivo}
                                onChange={(e) => setMotivo(e.target.value)}
                                placeholder="Ej. Dejó de trabajar con RNFC el 30/04/2026"
                                maxLength={250}
                            />
                            <p className="text-xs text-muted-foreground">
                                Queda registrado en auditoría. No es visible para el usuario.
                            </p>
                        </div>
                    )}

                    <DialogFooter>
                        <Button
                            variant="outline"
                            onClick={() => {
                                setUsuarioObjetivo(null);
                                setMotivo('');
                            }}
                        >
                            Cancelar
                        </Button>
                        <Button
                            variant={usuarioObjetivo?.activo ? 'destructive' : 'default'}
                            onClick={confirmarToggle}
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
                        setNuevoRol('');
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
                        <Select value={nuevoRol} onValueChange={setNuevoRol}>
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
                                setNuevoRol('');
                            }}
                        >
                            Cancelar
                        </Button>
                        <Button
                            onClick={confirmarCambioRol}
                            disabled={!nuevoRol || nuevoRol === usuarioRol?.rol}
                        >
                            Confirmar
                        </Button>
                    </DialogFooter>
                </DialogContent>
            </Dialog>
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
    description:
        'Administra cuentas, roles globales y acceso a la plataforma.',
    breadcrumbs: [
        { title: 'Panel', href: '/dashboard' },
        { title: 'Administración', href: '/admin' },
        { title: 'Usuarios', href: '/admin/usuarios' },
    ],
};
