export interface River {
    uid:string;
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
}


export const getStatusColor = (status: River['water_level_status']) => {
    switch (status) {
        case 'low':
            return 'water-low';
        case 'normal':
            return 'water-normal';
        case 'high':
            return 'water-high';
        case 'critical':
            return 'water-critical';
        default:
            return 'water-normal';
    }
};

export const getStatusText = (status: River['water_level_status']) => {
    switch (status) {
        case 'low':
            return 'Low Level';
        case 'normal':
            return 'Normal Level';
        case 'high':
            return 'High Level';
        case 'critical':
            return 'Critical Level';
        default:
            return 'Normal Level';
    }
};
