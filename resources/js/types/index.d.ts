import { InertiaLinkProps } from '@inertiajs/react';
import { LucideIcon } from 'lucide-react';

export interface Auth {
    user: User;
}

export interface BreadcrumbItem {
    title: string;
    href: string;
}

export interface NavGroup {
    title: string;
    items: NavItem[];
}

export interface NavItem {
    title: string;
    href: NonNullable<InertiaLinkProps['href']>;
    icon?: LucideIcon | null;
    isActive?: boolean;
}

export interface SharedData {
    name: string;
    quote: { message: string; author: string };
    auth: Auth;
    sidebarOpen: boolean;
    [key: string]: unknown;
}

export interface User {
    id: number;
    name: string;
    email: string;
    avatar?: string;
    email_verified_at: string | null;
    lat?: number | null;
    lng?: number | null;
    created_at: string;
    updated_at: string;
    [key: string]: unknown; // This allows for additional properties...
}


interface River {
    uid: string;
    id: string;
    name: string;
    country: string;
    continent: string;
    length: number; // in kilometers
    water_level_current: number;
    water_level_normal: number;
    water_level_status: 'low' | 'normal' | 'high' | 'critical';
    flow_rate_current: number;
    flow_rate_average: number;

    temperature: number; // in Celsius
    lat: number;
    lng: number;

    description: string;
    last_updated: string;
    weeklyData: Array<{
        day: string;
        level: number;
        flow: number;
    }>;

    basin: string;
    discharge: string;
    station_index: string;
    station_name: string;
    water_level: string;
}


interface RiverNew {
    index: string
    basin: string;
    discharge: string;
    district: string;
    station_index: string;
    station_name: string;
    water_level: string;
}
