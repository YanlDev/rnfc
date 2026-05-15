import { Head, Link } from '@inertiajs/react';
import { LogOut, ShieldAlert } from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { logout } from '@/routes';

type Props = {
    invitacion: { email: string; obra: string };
    usuarioActual: string;
};

export default function InvitacionConflicto({
    invitacion,
    usuarioActual,
}: Props) {
    const cerrarSesion = () => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = logout().url;
        document.body.appendChild(form);
        form.submit();
    };

    return (
        <>
            <Head title="Cuenta diferente" />
            <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6">
                <Link href="/" className="flex flex-col items-center gap-2">
                    <AppLogoIcon className="h-20 w-auto" />
                </Link>

                <Card className="w-full max-w-md">
                    <CardHeader className="text-center">
                        <CardTitle className="flex items-center justify-center gap-2 text-xl">
                            <ShieldAlert className="size-5 text-amber-500" />
                            Cuenta diferente
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4 text-sm">
                        <p className="text-muted-foreground">
                            La invitación a la obra <strong>{invitacion.obra}</strong> fue
                            enviada a <strong>{invitacion.email}</strong>, pero
                            estás autenticado(a) como{' '}
                            <strong>{usuarioActual}</strong>.
                        </p>
                        <p className="text-muted-foreground">
                            Cierra sesión y vuelve a abrir el enlace del correo,
                            o accede con la cuenta correcta.
                        </p>
                        <Button onClick={cerrarSesion} className="w-full">
                            <LogOut className="size-4" />
                            Cerrar sesión
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
