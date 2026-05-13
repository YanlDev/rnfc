import { Head, router } from '@inertiajs/react';
import { ImagePlus, Trash2, Upload } from 'lucide-react';
import { useRef, useState } from 'react';
import Heading from '@/components/heading';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';

type Slot = 'firma' | 'iso1' | 'iso2' | 'iso3';

type Props = {
    urls: Record<Slot, string | null>;
    slots: Record<Slot, string>;
};

function SlotCard({
    slot,
    titulo,
    url,
    aspecto,
}: {
    slot: Slot;
    titulo: string;
    url: string | null;
    aspecto: 'firma' | 'iso';
}) {
    const inputRef = useRef<HTMLInputElement>(null);
    const [subiendo, setSubiendo] = useState(false);

    const subir = (file: File) => {
        setSubiendo(true);
        router.post(
            '/settings/branding',
            { slot, archivo: file },
            {
                forceFormData: true,
                preserveScroll: true,
                onFinish: () => setSubiendo(false),
            },
        );
    };

    const eliminar = () => {
        if (!confirm(`¿Eliminar la imagen "${titulo}"?`)) return;
        router.delete('/settings/branding', {
            data: { slot },
            preserveScroll: true,
        });
    };

    return (
        <Card>
            <CardHeader className="pb-3">
                <CardTitle className="text-base">{titulo}</CardTitle>
                <CardDescription>
                    PNG con fondo transparente, máx. 2 MB.
                    {aspecto === 'firma'
                        ? ' Idealmente 600×200 px.'
                        : ' Idealmente cuadrado, 400×400 px.'}
                </CardDescription>
            </CardHeader>
            <CardContent className="space-y-3">
                <div
                    className={
                        'flex items-center justify-center rounded-md border border-dashed border-border bg-muted/30 ' +
                        (aspecto === 'firma' ? 'h-32' : 'h-40')
                    }
                >
                    {url ? (
                        <img
                            src={url}
                            alt={titulo}
                            className="max-h-full max-w-full object-contain"
                        />
                    ) : (
                        <div className="flex flex-col items-center gap-2 text-muted-foreground">
                            <ImagePlus className="size-7" />
                            <span className="text-xs">Sin imagen</span>
                        </div>
                    )}
                </div>
                <div className="flex gap-2">
                    <input
                        ref={inputRef}
                        type="file"
                        accept="image/png"
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
                        variant="outline"
                        disabled={subiendo}
                        onClick={() => inputRef.current?.click()}
                    >
                        <Upload className="size-4" />
                        {url ? 'Reemplazar' : 'Subir PNG'}
                    </Button>
                    {url && (
                        <Button
                            type="button"
                            size="sm"
                            variant="ghost"
                            onClick={eliminar}
                        >
                            <Trash2 className="size-4 text-destructive" />
                        </Button>
                    )}
                </div>
            </CardContent>
        </Card>
    );
}

export default function BrandingSettings({ urls, slots }: Props) {
    return (
        <>
            <Head title="Configuración · Marca" />

            <div className="space-y-6">
                <Heading
                    title="Marca institucional"
                    description="Carga la firma digitalizada y los logos de las certificaciones ISO de la empresa. Aparecerán automáticamente en los certificados emitidos."
                />

                <section className="space-y-4">
                    <h2 className="text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                        Firma del titular
                    </h2>
                    <SlotCard
                        slot="firma"
                        titulo={slots.firma}
                        url={urls.firma}
                        aspecto="firma"
                    />
                </section>

                <section className="space-y-4">
                    <h2 className="text-sm font-semibold tracking-wide text-muted-foreground uppercase">
                        Certificaciones ISO
                    </h2>
                    <div className="grid gap-4 sm:grid-cols-3">
                        <SlotCard
                            slot="iso1"
                            titulo={slots.iso1}
                            url={urls.iso1}
                            aspecto="iso"
                        />
                        <SlotCard
                            slot="iso2"
                            titulo={slots.iso2}
                            url={urls.iso2}
                            aspecto="iso"
                        />
                        <SlotCard
                            slot="iso3"
                            titulo={slots.iso3}
                            url={urls.iso3}
                            aspecto="iso"
                        />
                    </div>
                </section>
            </div>
        </>
    );
}

BrandingSettings.layout = {
    breadcrumbs: [
        { title: 'Configuración', href: '/settings/profile' },
        { title: 'Marca', href: '/settings/branding' },
    ],
};
