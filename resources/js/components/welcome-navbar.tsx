import { Button } from '@/components/ui/button';
import { Link } from '@inertiajs/react';
import { LogIn, UserPlus, Waves } from 'lucide-react';

export function WelcomeNavbar() {
    return (
        <nav className="border-b border-border/40 bg-background/95 backdrop-blur supports-[backdrop-filter]:bg-background/60">
            <div className="container mx-auto max-w-7xl px-4">
                <div className="flex h-16 items-center justify-between">
                    {/* Logo */}
                    <Link href="/" className="flex items-center gap-2 font-semibold">
                        <div className="rounded-lg bg-primary/20 p-2">
                            <Waves className="h-5 w-5 text-primary" />
                        </div>
                        <span className="text-lg">River Monitor</span>
                    </Link>

                    {/* Auth Buttons */}
                    <div className="flex items-center gap-3">
                        <Button variant="ghost" asChild>
                            <Link href="/login">
                                <LogIn className="mr-2 h-4 w-4" />
                                Log In
                            </Link>
                        </Button>
                        <Button asChild>
                            <Link href="/register">
                                <UserPlus className="mr-2 h-4 w-4" />
                                Register
                            </Link>
                        </Button>
                    </div>
                </div>
            </div>
        </nav>
    );
}

