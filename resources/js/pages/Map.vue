<script setup lang="ts">
import L from 'leaflet';
import { ref, onMounted, nextTick, watch } from 'vue';
import { Head, router } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';
import 'leaflet/dist/leaflet.css';

const customIcon = L.divIcon({
    className: 'custom-marker',
    html: '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="#3b82f6" width="32" height="32"><path d="M12 0C7.03 0 3 4.03 3 9c0 7.5 9 15 9 15s9-7.5 9-15c0-4.97-4.03-9-9-9zm0 12.75a3.75 3.75 0 110-7.5 3.75 3.75 0 010 7.5z"/></svg>',
    iconSize: [32, 32],
    iconAnchor: [16, 32],
    popupAnchor: [0, -32],
});

interface Marker {
    id: number;
    name: string;
    latitude: number;
    longitude: number;
    description: string | null;
    added: string;
    edited: string | null;
}

const props = defineProps<{
    markers: Marker[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Map', href: '/map' },
];

const mapContainer = ref<HTMLDivElement | null>(null);
let map: L.Map | null = null;
let leafletMarkers: L.Marker[] = [];

// Modal state
const showModal = ref(false);
const editingMarker = ref<Marker | null>(null);
const form = ref({
    name: '',
    latitude: 0,
    longitude: 0,
    description: '',
});

// Marker list panel
const showList = ref(true);

function initMap() {
    if (!mapContainer.value) return;

    map = L.map(mapContainer.value).setView([58.25, 22.49], 7);

    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
        maxZoom: 19,
    }).addTo(map);

    // Click on map to add marker
    map.on('click', (e: L.LeafletMouseEvent) => {
        editingMarker.value = null;
        form.value = {
            name: '',
            latitude: e.latlng.lat,
            longitude: e.latlng.lng,
            description: '',
        };
        showModal.value = true;
    });

    addMarkersToMap();
}

function addMarkersToMap() {
    if (!map) return;

    // Clear existing
    leafletMarkers.forEach(m => m.remove());
    leafletMarkers = [];

    props.markers.forEach(marker => {
        const m = L.marker([marker.latitude, marker.longitude], { icon: customIcon })
            .addTo(map!)
            .bindPopup(`
                <div style="min-width: 150px">
                    <strong>${marker.name}</strong>
                    ${marker.description ? `<p style="margin: 4px 0 0">${marker.description}</p>` : ''}
                </div>
            `);
        leafletMarkers.push(m);
    });
}

function openEdit(marker: Marker) {
    editingMarker.value = marker;
    form.value = {
        name: marker.name,
        latitude: marker.latitude,
        longitude: marker.longitude,
        description: marker.description || '',
    };
    showModal.value = true;
}

function flyTo(marker: Marker) {
    if (!map) return;
    map.flyTo([marker.latitude, marker.longitude], 14);
    // Open the popup
    leafletMarkers.forEach(m => {
        const latlng = m.getLatLng();
        if (Math.abs(latlng.lat - marker.latitude) < 0.0001 && Math.abs(latlng.lng - marker.longitude) < 0.0001) {
            m.openPopup();
        }
    });
}

function submitForm() {
    if (editingMarker.value) {
        router.put(`/markers/${editingMarker.value.id}`, form.value, {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    } else {
        router.post('/markers', form.value, {
            onSuccess: () => {
                showModal.value = false;
            },
        });
    }
}

function deleteMarker(marker: Marker) {
    if (!confirm(`Delete "${marker.name}"?`)) return;
    router.delete(`/markers/${marker.id}`);
}

onMounted(() => {
    watch(() => props.markers, () => {
    addMarkersToMap();
}, { deep: true });
    nextTick(() => initMap());
});
</script>

<template>
    <Head title="Map" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-[calc(100vh-8rem)] gap-4 p-4">

            <!-- Sidebar: Marker List -->
            <div
                v-if="showList"
                class="flex w-80 flex-col rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800/50"
            >
                <div class="flex items-center justify-between border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                    <h2 class="text-sm font-semibold text-gray-900 dark:text-white">
                        Markers ({{ markers.length }})
                    </h2>
                    <button
                        @click="showList = false"
                        class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                    >
                        <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <div class="flex-1 overflow-y-auto">
                    <div v-if="markers.length === 0" class="flex flex-col items-center justify-center p-8 text-gray-400">
                        <svg class="mb-2 h-10 w-10 opacity-40" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1115 0z" />
                        </svg>
                        <p class="text-xs">Click the map to add a marker</p>
                    </div>
                    <div
                        v-for="marker in markers"
                        :key="marker.id"
                        class="group border-b border-gray-100 px-4 py-3 transition-colors hover:bg-gray-50 dark:border-gray-700/50 dark:hover:bg-gray-700/30"
                    >
                        <div class="flex items-start justify-between">
                            <button @click="flyTo(marker)" class="text-left">
                                <p class="text-sm font-medium text-gray-900 dark:text-white">{{ marker.name }}</p>
                                <p v-if="marker.description" class="mt-0.5 text-xs text-gray-500 dark:text-gray-400 line-clamp-2">{{ marker.description }}</p>
                                <p class="mt-1 text-[10px] text-gray-400">{{ marker.latitude.toFixed(4) }}, {{ marker.longitude.toFixed(4) }}</p>
                            </button>
                            <div class="flex gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                                <button
                                    @click="openEdit(marker)"
                                    class="rounded-md p-1 text-gray-400 hover:bg-gray-200 hover:text-gray-600 dark:hover:bg-gray-600 dark:hover:text-gray-300"
                                >
                                    <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                    </svg>
                                </button>
                                <button
                                    @click="deleteMarker(marker)"
                                    class="rounded-md p-1 text-gray-400 hover:bg-red-100 hover:text-red-500 dark:hover:bg-red-900/30 dark:hover:text-red-400"
                                >
                                    <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Toggle list button (when hidden) -->
            <button
                v-if="!showList"
                @click="showList = true"
                class="absolute left-6 top-20 z-[1000] rounded-xl border border-gray-200 bg-white px-3 py-2 text-xs font-medium shadow-md dark:border-gray-700 dark:bg-gray-800 dark:text-white"
            >
                Show markers
            </button>

            <!-- Map -->
            <div class="relative flex-1 overflow-hidden rounded-xl border border-gray-200 shadow-sm dark:border-gray-700">
                <div ref="mapContainer" class="h-full w-full" />
            </div>
        </div>

        <!-- Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-[2000] flex items-center justify-center bg-black/50" @click.self="showModal = false">
                <div class="w-full max-w-md rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        {{ editingMarker ? 'Edit Marker' : 'Add Marker' }}
                    </h3>

                    <div class="flex flex-col gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Name</label>
                            <input
                                v-model="form.name"
                                type="text"
                                placeholder="Marker name"
                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Latitude</label>
                                <input
                                    v-model.number="form.latitude"
                                    type="number"
                                    step="0.0000001"
                                    class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Longitude</label>
                                <input
                                    v-model.number="form.longitude"
                                    type="number"
                                    step="0.0000001"
                                    class="w-full rounded-lg border border-gray-200 bg-gray-50 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                                />
                            </div>
                        </div>

                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Description</label>
                            <textarea
                                v-model="form.description"
                                rows="3"
                                placeholder="Optional description..."
                                class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                            />
                        </div>
                    </div>

                    <div class="mt-5 flex justify-end gap-2">
                        <button
                            @click="showModal = false"
                            class="rounded-lg px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700"
                        >
                            Cancel
                        </button>
                        <button
                            @click="submitForm"
                            class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600"
                        >
                            {{ editingMarker ? 'Save Changes' : 'Add Marker' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>

<style>
.leaflet-container {
    z-index: 1;
}
.custom-marker {
    background: none;
    border: none;
}
</style>