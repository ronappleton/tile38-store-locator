export type DeviceType = 'truck' | 'van' | 'drone' | 'courier';

export interface Device {
    id: string;
    type: DeviceType;
    /** [lng, lat] as GeoJSON */
    coordinates: [number, number];
    fields: Record<string, string | number>;
}

export interface Fence {
    name: string;
    label: string;
    color: string;
    geojson: {
        type: 'Feature';
        properties: Record<string, unknown>;
        geometry: {
            type: 'Polygon';
            coordinates: [number, number][][];
        };
    };
}

export interface FenceEventPayload {
    device: string;
    fence: string;
    action: string;
    coordinates: [number, number];
    type: string;
}

export interface SearchResult {
    id: string;
    coordinates: [number, number];
    distance?: number | null;
    fields: Record<string, string | number>;
}

export interface ServerStatus {
    ok: boolean;
    generated_at: string;
    count: number;
    devices: Device[];
    fences: Fence[];
    center: { lat: number; lng: number };
    server: {
        version?: string | null;
        uptime_seconds?: number | null;
        cpu?: number | null;
        memory?: number | null;
    };
}

export const DEVICE_COLORS: Record<DeviceType, string> = {
    truck: '#2dd4bf',
    van: '#14b8a6',
    drone: '#fbbf24',
    courier: '#7dd3fc',
};

export const DEVICE_LABELS: Record<DeviceType, string> = {
    truck: 'Truck',
    van: 'Van',
    drone: 'Drone',
    courier: 'Courier',
};
