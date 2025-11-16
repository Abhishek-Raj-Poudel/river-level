import { River } from "@/types";


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
