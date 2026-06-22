import { Head } from '@inertiajs/react';
import '../../css/landing.css';
import { LandingFooter } from '@/components/landing/footer';
import { Galeria } from '@/components/landing/galeria';
import type { GaleriaImagen } from '@/components/landing/galeria';
import { Hero } from '@/components/landing/hero';
import { LandingNav } from '@/components/landing/nav';
import {
    Certificaciones,
    Diferenciadores,
    Experiencia,
    Plataforma,
    Servicios,
} from '@/components/landing/sections';

export default function Landing({ galeria }: { galeria: GaleriaImagen[] }) {
    return (
        <div className="lp min-h-screen bg-[#071426] font-sans text-slate-100">
            <Head title="Supervisión de obras y gestión documental" />

            <LandingNav />

            <main>
                <Hero />
                <Servicios />
                <Plataforma />
                <Experiencia />
                <Certificaciones />
                <Diferenciadores />
                <Galeria imagenes={galeria} />
            </main>

            <LandingFooter />
        </div>
    );
}
