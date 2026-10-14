import { Component, type ErrorInfo, type ReactNode } from 'react';

interface Props {
    children: ReactNode;
    fallback?: ReactNode;
}

interface State {
    hasError: boolean;
}

function DefaultFallback() {
    return (
        <div className="flex min-h-screen items-center justify-center bg-background px-6">
            <div className="max-w-md text-center">
                <p className="font-mono text-xs tracking-[0.18em] text-muted-foreground uppercase">River Monitor</p>
                <h1 className="mt-3 font-serif text-2xl tracking-[-0.02em] text-foreground">Something went wrong</h1>
                <p className="mt-2 text-sm leading-relaxed text-muted-foreground">This page could not be displayed. Reload to try again.</p>
                <button
                    type="button"
                    onClick={() => window.location.reload()}
                    className="mt-6 rounded-md bg-primary px-4 py-2 text-sm font-medium text-primary-foreground transition-colors hover:bg-foreground/90"
                >
                    Reload
                </button>
            </div>
        </div>
    );
}

export class ErrorBoundary extends Component<Props, State> {
    state: State = { hasError: false };

    static getDerivedStateFromError(): State {
        return { hasError: true };
    }

    componentDidCatch(error: Error, info: ErrorInfo): void {
        console.error('Uncaught render error:', error, info.componentStack);
    }

    render(): ReactNode {
        if (this.state.hasError) {
            return this.props.fallback ?? <DefaultFallback />;
        }

        return this.props.children;
    }
}
