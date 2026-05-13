import { Head, Link } from '@inertiajs/react';
import { AlertTriangle } from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

const MENSAJES: Record<string, { titulo: string; descripcion: string }> = {
    aceptada: {
        titulo: 'Esta invitación ya fue aceptada',
        descripcion: 'No necesitas hacer nada más. Inicia sesión para acceder a la obra.',
    },
    cancelada: {
        titulo: 'La invitación fue cancelada',
        descripcion: 'Quien te invitó canceló este enlace. Pídele que te envíe uno nuevo.',
    },
    expirada: {
        titulo: 'La invitación ha expirado',
        descripcion: 'Los enlaces de invitación duran 7 días. Pide que te lo reenvíen.',
    },
    inexistente: {
        titulo: 'Invitación no encontrada',
        descripcion: 'El enlace que abriste no corresponde a ninguna invitación.',
    },
};

export default function InvitacionInvalida({ estado }: { estado: string }) {
    const msg = MENSAJES[estado] ?? MENSAJES.inexistente;

    return (
        <>
            <Head title="Invitación no válida" />
            <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6">
                <Link href="/" className="flex flex-col items-center gap-2">
                    <AppLogoIcon className="h-20 w-auto" />
                </Link>

                <Card className="w-full max-w-md">
                    <CardHeader className="text-center">
                        <CardTitle className="flex items-center justify-center gap-2 text-xl">
                            <AlertTriangle className="size-5 text-amber-500" />
                            {msg.titulo}
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4 text-center text-sm text-muted-foreground">
                        <p>{msg.descripcion}</p>
                        <Button asChild>
                            <Link href="/login">Ir al inicio de sesión</Link>
                        </Button>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
