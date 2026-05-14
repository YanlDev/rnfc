import { Head, router } from '@inertiajs/react';
import { ImagePlus, Trash2, Upload } from 'lucide-react';
import { useRef, useState } from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';

type Imagen = {
    id: number;
    ruta: string;
    titulo: string | null;
    orden: number;
    url: string | null;
};

type Props = {
    imagenes: Imagen[];
};

export default function HomeSettings({ imagenes }: Props) {
    const inputRef = useRef<HTMLInputElement>(null);
    const [subiendo, setSubiendo] = useState(false);
    const [titulo, setTitulo] = useState('');

    const subir = (file: File) => {
        setSubiendo(true);
        router.post(
            '/settings/home',
            { archivo: file, titulo },
            {
                forceFormData: true,
                preserveScroll: true,
                onFinish: () => {
                    setSubiendo(false);
                    setTitulo('');
                },
            },
        );
    };

    const eliminar = (img: Imagen) => {
        if (!confirm('¿Eliminar esta imagen de la galería?')) return;
        router.delete(`/settings/home/${img.id}`, { preserveScroll: true });
    };

    return (
        <>
            <Head title="Configuración · Galería del home" />

            <div className="space-y-6">
                <Heading
                    title="Galería del home"
                    description="Carga las imágenes de obras que se mostrarán en la galería de la página principal. Se actualizan automáticamente en el sitio público."
                />

                <Card>
                    <CardContent className="space-y-4 pt-6">
                        <div>
                            <label className="text-sm font-semibold">Título (opcional)</label>
                            <input
                                type="text"
                                value={titulo}
                                onChange={(e) => setTitulo(e.target.value)}
                                placeholder="Ej: Carretera PE-3S, Saneamiento Azángaro..."
                                className="mt-1 w-full rounded-md border border-input bg-background px-3 py-2 text-sm focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring/40"
                            />
                        </div>
                        <div>
                            <input
                                ref={inputRef}
                                type="file"
                                accept="image/jpeg,image/png,image/webp"
                                className="hidden"
                                onChange={(e) => {
                                    const file = e.target.files?.[0];
                                    if (file) subir(file);
                                    e.target.value = '';
                                }}
                            />
                            <Button
                                type="button"
                                size="sm"
                                disabled={subiendo}
                                onClick={() => inputRef.current?.click()}
                            >
                                <Upload className="size-4" />
                                {subiendo ? 'Subiendo...' : 'Agregar imagen'}
                            </Button>
                            <p className="mt-2 text-xs text-muted-foreground">
                                JPG, PNG o WEBP. Máx. 5 MB. Recomendado 1600×1000 px.
                            </p>
                        </div>
                    </CardContent>
                </Card>

                <section>
                    <h2 className="mb-3 text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                        Imágenes actuales · {imagenes.length}
                    </h2>

                    {imagenes.length === 0 ? (
                        <Card>
                            <CardContent className="flex flex-col items-center gap-2 py-12 text-muted-foreground">
                                <ImagePlus className="size-8" />
                                <span className="text-sm">No hay imágenes en la galería.</span>
                            </CardContent>
                        </Card>
                    ) : (
                        <div className="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                            {imagenes.map((img) => (
                                <Card key={img.id} className="overflow-hidden">
                                    <div className="aspect-[4/3] overflow-hidden bg-muted">
                                        {img.url && (
                                            <img
                                                src={img.url}
                                                alt={img.titulo ?? ''}
                                                className="h-full w-full object-cover"
                                            />
                                        )}
                                    </div>
                                    <CardContent className="flex items-center justify-between gap-2 p-3">
                                        <div className="min-w-0 flex-1">
                                            <div className="truncate text-sm font-semibold">
                                                {img.titulo ?? 'Sin título'}
                                            </div>
                                            <div className="text-xs text-muted-foreground">
                                                Orden #{img.orden}
                                            </div>
                                        </div>
                                        <Button
                                            type="button"
                                            size="sm"
                                            variant="ghost"
                                            onClick={() => eliminar(img)}
                                        >
                                            <Trash2 className="size-4 text-destructive" />
                                        </Button>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                    )}
                </section>
            </div>
        </>
    );
}

HomeSettings.layout = {
    breadcrumbs: [
        { title: 'Configuración', href: '/settings/profile' },
        { title: 'Galería del home', href: '/settings/home' },
    ],
};
