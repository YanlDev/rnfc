import { Head, Link, router } from '@inertiajs/react';
import { Eye, FileDown, Plus, Trash2 } from 'lucide-react';
import { useState } from 'react';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
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
import certificados from '@/routes/certificados';

type Certificado = {
    id: number;
    codigo: string;
    tipo: string;
    tipo_label: string;
    beneficiario_nombre: string;
    beneficiario_documento: string | null;
    obra: { id: number; nombre: string; codigo: string } | null;
    fecha_emision: string | null;
    vigente: boolean;
    revocado_at: string | null;
};

type Paginated<T> = {
    data: T[];
    links: { url: string | null; label: string; active: boolean }[];
    meta?: { current_page: number; last_page: number; total: number };
};

type Tipo = { value: string; label: string };

type Props = {
    certificados: Paginated<Certificado>;
    tipos: Tipo[];
};

export default function CertificadosIndex({ certificados: paginado, tipos }: Props) {
    const [busqueda, setBusqueda] = useState('');
    const [filtroTipo, setFiltroTipo] = useState<string>('todos');

    const filtrados = paginado.data.filter((c) => {
        const matchTipo = filtroTipo === 'todos' || c.tipo === filtroTipo;
        const q = busqueda.trim().toLowerCase();
        const matchBusqueda =
            !q ||
            c.codigo.toLowerCase().includes(q) ||
            c.beneficiario_nombre.toLowerCase().includes(q) ||
            (c.beneficiario_documento ?? '').toLowerCase().includes(q);
        return matchTipo && matchBusqueda;
    });

    const eliminar = (c: Certificado) => {
        if (!confirm(`¿Eliminar el certificado ${c.codigo}?`)) return;
        router.delete(certificados.destroy(c.id).url);
    };

    return (
        <>
            <Head title="Certificados" />
            <div className="flex flex-1 flex-col gap-6 p-4 md:p-6">
                <div className="flex justify-end">
                    <Button asChild>
                        <Link href={certificados.create().url}>
                            <Plus className="size-4" />
                            Nuevo certificado
                        </Link>
                    </Button>
                </div>

                <Card className="p-4">
                    <div className="flex flex-col gap-3 sm:flex-row">
                        <Input
                            placeholder="Buscar por código, nombre o documento…"
                            value={busqueda}
                            onChange={(e) => setBusqueda(e.target.value)}
                            className="sm:max-w-sm"
                        />
                        <Select value={filtroTipo} onValueChange={setFiltroTipo}>
                            <SelectTrigger className="sm:w-56">
                                <SelectValue placeholder="Filtrar por tipo" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="todos">Todos los tipos</SelectItem>
                                {tipos.map((t) => (
                                    <SelectItem key={t.value} value={t.value}>
                                        {t.label}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                </Card>

                <Card>
                    <Table>
                        <TableHeader>
                            <TableRow>
                                <TableHead>Código</TableHead>
                                <TableHead>Tipo</TableHead>
                                <TableHead>Beneficiario</TableHead>
                                <TableHead>Obra</TableHead>
                                <TableHead>Emisión</TableHead>
                                <TableHead>Estado</TableHead>
                                <TableHead className="text-right">Acciones</TableHead>
                            </TableRow>
                        </TableHeader>
                        <TableBody>
                            {filtrados.length === 0 && (
                                <TableRow>
                                    <TableCell colSpan={7} className="py-8 text-center text-sm text-muted-foreground">
                                        Sin certificados emitidos todavía.
                                    </TableCell>
                                </TableRow>
                            )}
                            {filtrados.map((c) => (
                                <TableRow key={c.id}>
                                    <TableCell className="font-mono text-xs font-semibold text-primary">
                                        {c.codigo}
                                    </TableCell>
                                    <TableCell>{c.tipo_label}</TableCell>
                                    <TableCell>
                                        <div className="font-medium">{c.beneficiario_nombre}</div>
                                        {c.beneficiario_documento && (
                                            <div className="text-xs text-muted-foreground">
                                                DNI: {c.beneficiario_documento}
                                            </div>
                                        )}
                                    </TableCell>
                                    <TableCell className="text-sm">
                                        {c.obra ? (
                                            <span>
                                                <span className="font-mono text-xs text-muted-foreground">[{c.obra.codigo}]</span>{' '}
                                                {c.obra.nombre}
                                            </span>
                                        ) : (
                                            <span className="text-muted-foreground">—</span>
                                        )}
                                    </TableCell>
                                    <TableCell className="text-sm">{c.fecha_emision}</TableCell>
                                    <TableCell>
                                        {c.vigente ? (
                                            <Badge className="bg-[var(--color-brand-verde)] text-white">Vigente</Badge>
                                        ) : (
                                            <Badge variant="destructive">Revocado</Badge>
                                        )}
                                    </TableCell>
                                    <TableCell className="text-right">
                                        <div className="flex justify-end gap-1">
                                            <Button asChild size="sm" variant="ghost" title="Ver detalle">
                                                <Link href={certificados.show(c.id).url}>
                                                    <Eye className="size-4" />
                                                </Link>
                                            </Button>
                                            <Button asChild size="sm" variant="ghost" title="Descargar PDF">
                                                <a href={certificados.pdf(c.id).url} target="_blank" rel="noreferrer">
                                                    <FileDown className="size-4" />
                                                </a>
                                            </Button>
                                            <Button
                                                size="sm"
                                                variant="ghost"
                                                title="Eliminar"
                                                onClick={() => eliminar(c)}
                                            >
                                                <Trash2 className="size-4 text-destructive" />
                                            </Button>
                                        </div>
                                    </TableCell>
                                </TableRow>
                            ))}
                        </TableBody>
                    </Table>
                </Card>
            </div>
        </>
    );
}

CertificadosIndex.layout = {
    title: 'Certificados',
    description: 'Emite, previsualiza y verifica certificados oficiales de RNFC.',
    breadcrumbs: [
        { title: 'Panel', href: '/dashboard' },
        { title: 'Certificados', href: '/certificados' },
    ],
};
