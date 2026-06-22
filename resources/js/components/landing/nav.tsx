import { Menu, X } from 'lucide-react';
import { useEffect, useState } from 'react';
import { cn } from '@/lib/utils';

const LINKS = [
    { href: '#servicios', label: 'Servicios' },
    { href: '#plataforma', label: 'Plataforma' },
    { href: '#experiencia', label: 'Experiencia' },
    { href: '#galeria', label: 'Obras' },
    { href: '#certificaciones', label: 'ISO' },
    { href: '/verificar', label: 'Verificar' },
];

export function LandingNav() {
    const [scrolled, setScrolled] = useState(false);
    const [open, setOpen] = useState(false);

    useEffect(() => {
        const onScroll = () => setScrolled(window.scrollY > 40);
        onScroll();
        window.addEventListener('scroll', onScroll, { passive: true });

        return () => window.removeEventListener('scroll', onScroll);
    }, []);

    return (
        <header
            className={cn(
                'nav-shell fixed inset-x-0 top-0 z-50',
                scrolled && 'scrolled',
            )}
        >
            <div className="mx-auto flex max-w-7xl items-center justify-between px-4 py-3 md:px-8">
                <a href="#inicio" className="flex items-center gap-3">
                    <img
                        src="/brand/rnfc-logo.png"
                        alt="RNFC"
                        className="h-10 w-auto drop-shadow-[0_4px_18px_rgba(37,99,235,.45)] md:h-12"
                    />
                    <span className="sr-only">RNFC Consultor de Obras</span>
                </a>

                <nav className="hidden items-center gap-7 lg:flex">
                    {LINKS.map((l) => (
                        <a
                            key={l.href}
                            href={l.href}
                            className="text-sm font-semibold text-slate-200/80 transition-colors hover:text-white"
                        >
                            {l.label}
                        </a>
                    ))}
                </nav>

                <div className="flex items-center gap-2">
                    <a
                        href="/login"
                        className="btn-primary rounded-lg px-3 py-2 text-xs font-semibold md:px-4 md:text-sm"
                    >
                        Iniciar sesión
                    </a>
                    <button
                        type="button"
                        aria-label="Menú"
                        onClick={() => setOpen((v) => !v)}
                        className="btn-ghost inline-flex size-9 items-center justify-center rounded-lg lg:hidden"
                    >
                        {open ? (
                            <X className="size-5" />
                        ) : (
                            <Menu className="size-5" />
                        )}
                    </button>
                </div>
            </div>

            {open && (
                <div className="border-t border-white/5 bg-[#04101e]/95 backdrop-blur-xl lg:hidden">
                    <nav className="mx-auto flex max-w-7xl flex-col px-4 py-3 text-sm font-semibold">
                        {LINKS.map((l) => (
                            <a
                                key={l.href}
                                href={l.href}
                                onClick={() => setOpen(false)}
                                className="border-b border-white/5 py-3 text-white/80 hover:text-white"
                            >
                                {l.label}
                            </a>
                        ))}
                    </nav>
                </div>
            )}
        </header>
    );
}
