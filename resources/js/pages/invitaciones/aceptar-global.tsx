import { Head, Link } from '@inertiajs/react';
import { LogIn, Mail, ShieldCheck, UserPlus } from 'lucide-react';
import AppLogoIcon from '@/components/app-logo-icon';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';

type Props = {
    invitacion: {
        email: string;
        rol: string;
        invitador: string | null;
        expira_at: string;
    };
};

export default function InvitacionAceptarGlobal({ invitacion }: Props) {
    return (
        <>
            <Head title="Invitación a RNFC" />
            <div className="flex min-h-svh flex-col items-center justify-center gap-6 bg-background p-6">
                <Link href="/" className="flex flex-col items-center gap-2">
                    <AppLogoIcon className="h-20 w-auto" />
                </Link>

                <Card className="w-full max-w-md">
                    <CardHeader className="text-center">
                        <CardTitle className="flex items-center justify-center gap-2 text-xl">
                            <ShieldCheck className="size-5 text-primary" />
                            Invitación a la plataforma
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4 text-sm">
                        <div className="rounded-md border border-border bg-muted/30 p-3">
                            <div className="text-xs tracking-wide text-muted-foreground uppercase">
                                Rol asignado
                            </div>
                            <div className="font-semibold text-foreground">
                                {invitacion.rol}
                            </div>
                            <div className="text-xs text-muted-foreground">
                                Acceso completo a la plataforma RNFC
                            </div>
                        </div>

                        <div className="space-y-1">
                            <div className="flex items-center gap-2">
                                <Mail className="size-4 text-muted-foreground" />
                                <span>
                                    <strong>{invitacion.email}</strong>
                                </span>
                            </div>
                            {invitacion.invitador && (
                                <div className="text-xs text-muted-foreground">
                                    Te invitó {invitacion.invitador}
                                </div>
                            )}
                            <div className="text-xs text-muted-foreground">
                                Expira el {invitacion.expira_at}
                            </div>
                        </div>

                        <div className="rounded-md bg-muted/40 p-3 text-xs text-muted-foreground">
                            Para aceptar esta invitación necesitas una cuenta
                            con el correo <strong>{invitacion.email}</strong>.
                        </div>

                        <div className="grid gap-2 sm:grid-cols-2">
                            <Button asChild>
                                <Link href="/register">
                                    <UserPlus className="size-4" />
                                    Crear cuenta
                                </Link>
                            </Button>
                            <Button asChild variant="outline">
                                <Link href="/login">
                                    <LogIn className="size-4" />
                                    Ya tengo cuenta
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </>
    );
}
