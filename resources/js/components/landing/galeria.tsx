import { Eyebrow, Reveal, Section } from '@/components/landing/primitives';

export type GaleriaImagen = {
    id: number;
    url: string;
    titulo: string | null;
};

const ALTURAS = [
    'h-[420px]',
    'h-[200px]',
    'h-[200px]',
    'h-[260px]',
    'h-[260px]',
    'h-[200px]',
    'h-[200px]',
];

export function Galeria({ imagenes }: { imagenes: GaleriaImagen[] }) {
    return (
        <Section
            id="galeria"
            tone="dark"
            className="bg-gradient-to-b from-[#06101e] via-[#071426] to-[#06101e] py-16 md:py-28"
        >
            <div className="relative mx-auto max-w-7xl px-4 md:px-8">
                <Reveal className="mx-auto max-w-2xl text-center">
                    <Eyebrow center tone="dark">
                        Galería de obras
                    </Eyebrow>
                    <h2 className="mt-5 text-2xl font-extrabold tracking-tight text-white sm:text-3xl md:text-4xl">
                        Infraestructura supervisada{' '}
                        <span className="text-white/55">
                            en campo y en digital.
                        </span>
                    </h2>
                </Reveal>

                {imagenes.length > 0 ? (
                    <div className="mt-14 grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        {imagenes.map((img, i) => (
                            <Reveal
                                key={img.id}
                                delay={(i % 5) * 70}
                                className={`gal-item group relative overflow-hidden rounded-2xl border border-white/10 ${
                                    ALTURAS[i % ALTURAS.length]
                                }`}
                            >
                                <img
                                    src={img.url}
                                    alt={img.titulo ?? 'Obra RNFC'}
                                    loading="lazy"
                                    className="absolute inset-0 h-full w-full object-cover"
                                />
                                <div className="absolute inset-0 bg-gradient-to-t from-[#071426] via-[#071426]/40 to-transparent" />
                                <div className="absolute inset-x-0 bottom-0 p-4">
                                    <div className="text-[10px] font-bold tracking-[0.2em] text-white/55 uppercase">
                                        RNFC · campo
                                    </div>
                                    {img.titulo && (
                                        <div className="text-lg font-extrabold text-white">
                                            {img.titulo}
                                        </div>
                                    )}
                                </div>
                            </Reveal>
                        ))}
                    </div>
                ) : (
                    <Reveal className="glass mt-14 rounded-2xl px-6 py-16 text-center">
                        <p className="text-sm text-slate-300">
                            Galería disponible próximamente. Las imágenes se
                            publicarán desde el panel de administración.
                        </p>
                    </Reveal>
                )}
            </div>
        </Section>
    );
}
