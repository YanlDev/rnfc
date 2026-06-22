import { ArrowRight, BadgeCheck, ShieldCheck } from 'lucide-react';
import { Blueprint } from '@/components/landing/blueprint';
import { Eyebrow } from '@/components/landing/primitives';

const ISO_STRIP = [
    ['ISO 9001', 'Calidad'],
    ['ISO 14001', 'Ambiental'],
    ['ISO 37001', 'Antisoborno'],
    ['QA/QC', 'Control técnico'],
    ['SSOMA', 'Seguridad'],
    ['BIM', 'Modelado digital'],
    ['Ley 32069', 'Contrataciones'],
];

export function Hero() {
    return (
        <section
            id="inicio"
            className="relative isolate overflow-hidden bg-[#071426] pt-28 pb-0 text-slate-100"
        >
            {/* fondo */}
            <div className="absolute inset-0 -z-20">
                <img
                    src="/brand/supervision.png"
                    alt=""
                    className="h-full w-full object-cover opacity-20 md:opacity-25"
                />
            </div>
            <div className="absolute inset-0 -z-10 bg-gradient-to-br from-[#071426]/96 via-[#0a1a30]/94 to-[#071426]" />
            <div className="bg-blueprint-fine mask-radial absolute inset-0 -z-10 opacity-60" />
            <div className="glow-blue absolute -top-20 -left-20 -z-10 h-[500px] w-[500px]" />
            <div className="glow-gold absolute top-40 -right-20 -z-10 h-[450px] w-[450px]" />

            <div className="mx-auto grid w-full max-w-7xl items-center gap-10 px-4 pb-16 md:gap-12 md:px-8 md:pb-24 lg:grid-cols-[1.05fr_1fr]">
                {/* texto */}
                <div className="min-w-0">
                    <Eyebrow tone="dark">RNFC · Consultor de obras</Eyebrow>

                    <h1 className="display-tight mt-5 text-[2rem] font-extrabold text-white sm:text-[2.6rem] md:text-[3rem] lg:text-[3.4rem]">
                        Hacemos ingeniería{' '}
                        <span className="text-white/55">
                            de principio a fin.
                        </span>
                    </h1>

                    <p className="mt-6 max-w-xl text-base leading-relaxed text-slate-300 md:text-lg">
                        Formulación, planificación y supervisión técnica de
                        obras públicas y privadas — con respaldo ISO y una
                        plataforma digital propia para el control técnico y
                        documental.
                    </p>

                    <div className="mt-8 flex flex-wrap gap-3">
                        <a
                            href="#experiencia"
                            className="btn-primary inline-flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-bold"
                        >
                            Conoce nuestro trabajo
                            <ArrowRight className="size-4" />
                        </a>
                        <a
                            href="/login"
                            className="btn-ghost inline-flex items-center gap-2 rounded-xl px-5 py-3 text-sm font-bold"
                        >
                            Acceder a la plataforma
                        </a>
                    </div>

                    <div className="mt-8 flex flex-wrap items-center gap-x-6 gap-y-2 text-xs font-semibold text-slate-400">
                        <span className="inline-flex items-center gap-1.5">
                            <ShieldCheck className="size-4 text-emerald-400" />
                            Triple certificación ISO
                        </span>
                        <span className="inline-flex items-center gap-1.5">
                            <BadgeCheck className="size-4 text-sky-400" />
                            Plataforma propia in-house
                        </span>
                    </div>
                </div>

                {/* plano estructural animado */}
                <div className="relative min-w-0">
                    <div className="glow-blue absolute -inset-10 -z-10" />
                    <div className="bg-blueprint-fine relative overflow-hidden rounded-2xl border border-[var(--c-border-strong)] bg-[#08182d]/40 p-4 md:p-6">
                        <div className="mb-2 flex items-center justify-between text-[10px] font-bold tracking-[0.2em] text-slate-400 uppercase">
                            <span>Planta · trazo vial</span>
                            <span className="text-sky-400/70">RNFC</span>
                        </div>
                        <Blueprint />
                    </div>
                </div>
            </div>

            {/* franja de confianza ISO */}
            <div className="relative border-t border-white/5 bg-[#050f1f]/80">
                <div className="mx-auto max-w-7xl px-4 py-6 md:px-8 md:py-7">
                    <div className="grid grid-cols-3 items-center gap-4 text-center sm:grid-cols-4 md:gap-6 lg:grid-cols-7">
                        {ISO_STRIP.map(([code, label], i) => (
                            <div
                                key={code}
                                className="group flex flex-col items-center gap-1.5 px-2 lg:border-l lg:border-white/5"
                                style={
                                    i === 0 ? { borderLeft: 'none' } : undefined
                                }
                            >
                                <span className="text-base font-extrabold tracking-tight text-white transition-colors group-hover:text-sky-400">
                                    {code}
                                </span>
                                <span className="text-[10px] font-semibold tracking-[0.18em] text-slate-400 uppercase">
                                    {label}
                                </span>
                            </div>
                        ))}
                    </div>
                </div>
            </div>
        </section>
    );
}
