import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/react';
import { SignIn, UserPlus, Waves } from '@phosphor-icons/react';

export function WelcomeNavbar() {
    return (
        <nav className="sticky top-0 z-40 border-b border-border bg-background/80 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div className="container mx-auto max-w-7xl px-6">
                <div className="flex h-16 items-center justify-between">
                    <Link href="/" className="flex items-center gap-2.5">
                        <span className="flex items-center justify-center rounded-md bg-status-low-bg p-2 text-status-low-fg">
                            <Waves size={18} weight="fill" />
                        </span>
                        <span className="text-[15px] font-medium tracking-tight text-foreground">River Monitor</span>
                    </Link>

                    <div className="flex items-center gap-2">
                        <Button variant="ghost" asChild className="shadow-none">
                            <Link href="/login">
                                <SignIn size={16} weight="bold" />
                                Log In
                            </Link>
                        </Button>
                        <Button asChild className="rounded-md shadow-none active:scale-[0.98]">
                            <Link href="/register">
                                <UserPlus size={16} weight="bold" />
                                Register
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </nav>
    );
}
