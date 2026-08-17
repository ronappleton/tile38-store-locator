<script setup lang="ts">
import maplibregl, { Marker } from 'maplibre-gl';
import { onBeforeUnmount, onMounted, ref } from 'vue';
import 'maplibre-gl/dist/maplibre-gl.css';

interface Store {
    id: string;
    lat: number;
    lng: number;
    distance: number | null;
    fields: Record<string, string | number>;
}

const props = defineProps<{
    center: { lat: number; lng: number };
    storeCount: number;
}>();

const mapElement = ref<HTMLDivElement | null>(null);
const stores = ref<Store[]>([]);
const elapsed = ref<number | null>(null);
let map: maplibregl.Map;
let markers: Marker[] = [];
let queryTimer: number | undefined;

const renderMarkers = (results: Store[]) => {
    markers.forEach((marker) => marker.remove());
    markers = results.map((store, index) => {
        const element = document.createElement('button');
        element.className = `store-marker ${index === 0 ? 'store-marker-primary' : ''}`;
        element.title = `${store.id} · ${store.distance ?? 'viewport'}m`;
        element.type = 'button';

        return new Marker({ element })
            .setLngLat([store.lng, store.lat])
            .addTo(map);
    });
};

const fetchNearest = async (lat: number, lng: number) => {
    const response = await fetch(`/api/stores?lat=${lat}&lng=${lng}&limit=12`);

    if (!response.ok) {
        return;
    }

    const payload = await response.json();
    stores.value = payload.results ?? [];
    elapsed.value = payload.elapsed_ms ?? null;
    renderMarkers(stores.value);
};

const fetchViewport = async () => {
    if (typeof map === 'undefined') {
        return;
    }

    const bounds = map.getBounds();
    const payload = {
        south: bounds.getSouth(),
        west: bounds.getWest(),
        north: bounds.getNorth(),
        east: bounds.getEast(),
        limit: 120,
    };
    const response = await fetch('/api/stores/viewport', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            Accept: 'application/json',
        },
        body: JSON.stringify(payload),
    });

    if (!response.ok) {
        return;
    }

    const result = await response.json();
    stores.value = result.results ?? [];
    elapsed.value = result.elapsed_ms ?? null;
    renderMarkers(stores.value);
};

const queueViewportQuery = () => {
    if (queryTimer) {
        window.clearTimeout(queryTimer);
    }

    queryTimer = window.setTimeout(() => void fetchViewport(), 180);
};

onMounted(() => {
    if (!mapElement.value) {
        return;
    }

    map = new maplibregl.Map({
        container: mapElement.value,
        style: 'https://basemaps.cartocdn.com/gl/dark-matter-gl-style/style.json',
        center: [props.center.lng, props.center.lat],
        zoom: 3.5,
        attributionControl: false,
    });
    map.addControl(
        new maplibregl.NavigationControl({ showCompass: false }),
        'bottom-right',
    );
    map.on('load', () => void fetchNearest(props.center.lat, props.center.lng));
    map.on('moveend', queueViewportQuery);
    map.on(
        'click',
        (event) => void fetchNearest(event.lngLat.lat, event.lngLat.lng),
    );
});

onBeforeUnmount(() => {
    if (queryTimer) {
        window.clearTimeout(queryTimer);
    }

    markers.forEach((marker) => marker.remove());

    if (typeof map !== 'undefined') {
        map.remove();
    }
});
</script>

<template>
    <div ref="mapElement" class="store-map h-full min-h-[62vh] lg:min-h-full" />
    <div
        class="pointer-events-none absolute top-5 left-5 rounded-lg border border-[#2dd4bf]/30 bg-[#07110f]/90 px-4 py-3 backdrop-blur"
    >
        <p
            class="font-mono text-[10px] tracking-[0.2em] text-[#78928a] uppercase"
        >
            Live query
        </p>
        <p class="mt-1 font-display text-xl text-[#eefaf5]">
            {{ elapsed ?? '—'
            }}<span class="font-mono text-xs text-[#2dd4bf]"> ms</span>
        </p>
        <p class="mt-1 font-mono text-[10px] text-[#78928a]">
            {{ stores.length }} returned /
            {{ props.storeCount.toLocaleString() }} indexed
        </p>
    </div>
</template>

<style>
.store-marker {
    width: 10px;
    height: 10px;
    border: 2px solid #07110f;
    border-radius: 999px;
    background: #fbbf24;
    box-shadow:
        0 0 0 4px rgb(251 191 36 / 0.22),
        0 0 20px rgb(251 191 36 / 0.55);
}

.store-marker-primary {
    width: 15px;
    height: 15px;
    background: #2dd4bf;
    box-shadow:
        0 0 0 6px rgb(45 212 191 / 0.2),
        0 0 24px rgb(45 212 191 / 0.7);
}
</style>
