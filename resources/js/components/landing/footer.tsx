import { Linkedin, Mail, MessageCircle } from 'lucide-react';

const EMPRESA = [
    { href: '#servicios', label: 'Servicios' },
    { href: '#plataforma', label: 'Plataforma' },
    { href: '#experiencia', label: 'Experiencia' },
    { href: '#certificaciones', label: 'Certificaciones' },
];

const PLATAFORMA = [
    { href: '/login', label: 'Iniciar sesión', external: false },
    { href: '/verificar', label: 'Verificar certificado', external: false },
    { href: 'https://icocert.pe', label: 'ICO Cert', external: true },
];

const SOCIALES = [
    { href: 'mailto:contacto@rnfcconsultoria.com', Icono: Mail },
    { href: 'https://wa.me/51999999999', Icono: MessageCircle },
    { href: '#', Icono: Linkedin },
];

export function LandingFooter() {
    return (
        <footer className="relative border-t border-white/5 bg-[#04101e] pt-12 pb-8 text-slate-100 md:pt-16">
            <div className="bg-blueprint-fine absolute inset-0 opacity-20" />
            <div className="relative mx-auto max-w-7xl px-4 md:px-8">
                <div className="grid gap-10 sm:grid-cols-2 md:gap-12 lg:grid-cols-[1.4fr_1fr_1fr_1fr]">
                    <div>
                        <img
                            src="/brand/rnfc-logo.png"
                            alt="RNFC"
                            className="h-12 w-auto"
                        />
                        <p className="mt-5 max-w-sm text-sm leading-relaxed text-slate-400">
                            Firma peruana especializada en supervisión técnica,
                            consultoría de obras y gestión documental
                            inteligente — operando bajo certificaciones ISO
                            9001, 14001 y 37001.
                        </p>
                        <div className="mt-5 flex flex-wrap gap-2">
                            {[
                                'ISO 9001',
                                'ISO 14001',
                                'ISO 37001',
                                'MTPE 089-2025',
                            ].map((b) => (
                                <span
                                    key={b}
                                    className="rounded-full border border-white/10 bg-white/[0.03] px-3 py-1 text-[10px] font-bold tracking-wider text-white/70 uppercase"
                                >
                                    {b}
                                </span>
                            ))}
                        </div>
                    </div>

                    <div>
                        <h4 className="text-[11px] font-bold tracking-[0.2em] text-white uppercase">
                            Empresa
                        </h4>
                        <ul className="mt-4 space-y-2.5 text-sm text-slate-400">
                            {EMPRESA.map((l) => (
                                <li key={l.href}>
                                    <a
                                        href={l.href}
                                        className="hover:text-white"
                                    >
                                        {l.label}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </div>

                    <div>
                        <h4 className="text-[11px] font-bold tracking-[0.2em] text-white uppercase">
                            Plataforma
                        </h4>
                        <ul className="mt-4 space-y-2.5 text-sm text-slate-400">
                            {PLATAFORMA.map((l) => (
                                <li key={l.label}>
                                    <a
                                        href={l.href}
                                        className="hover:text-white"
                                        {...(l.external
                                            ? {
                                                  target: '_blank',
                                                  rel: 'noopener',
                                              }
                                            : {})}
                                    >
                                        {l.label}
                                    </a>
                                </li>
                            ))}
                        </ul>
                    </div>

                    <div>
                        <h4 className="text-[11px] font-bold tracking-[0.2em] text-white uppercase">
                            Contacto
                        </h4>
                        <ul className="mt-4 space-y-2.5 text-sm text-slate-400">
                            <li>Jr. Jauregui 1235, Juliaca · Puno</li>
                            <li>
                                <a
                                    href="mailto:contacto@rnfcconsultoria.com"
                                    className="hover:text-white"
                                >
                                    contacto@rnfcconsultoria.com
                                </a>
                            </li>
                            <li>RUC 10421559029</li>
                        </ul>
                        <div className="mt-5 flex items-center gap-2">
                            {SOCIALES.map((s, i) => (
                                <a
                                    key={i}
                                    href={s.href}
                                    className="inline-flex size-9 items-center justify-center rounded-lg border border-white/10 bg-white/[0.03] text-white/70 transition-colors hover:border-sky-400 hover:text-white"
                                >
                                    <s.Icono className="size-4" />
                                </a>
                            ))}
                        </div>
                    </div>
                </div>

                <div className="mt-12 flex flex-col items-center justify-between gap-3 border-t border-white/5 pt-6 text-xs text-white/45 md:flex-row">
                    <div>
                        © {new Date().getFullYear()} RNFC Consultor de Obras ·
                        Todos los derechos reservados
                    </div>
                    <div>Hecho en Perú · Juliaca, Puno</div>
                </div>
            </div>
        </footer>
    );
}
