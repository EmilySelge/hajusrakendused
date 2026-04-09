<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref, onMounted, watch, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Song {
    id: number;
    title: string;
    artist: string;
    genre: string;
    description: string;
    image: string;
    added_by: string;
    created_at: string;
}

const breadcrumbs: BreadcrumbItem[] = [{ title: 'Music', href: '/music' }];

const API_URL =
    'https://laravel-hajusrakendused-main-lidud2.free.laravel.cloud/api/music';

const songs = ref<Song[]>([]);
const loading = ref(false);
const error = ref('');

const search = ref('');
const genre = ref('');
const sortBy = ref('created_at');
const sortOrder = ref('desc');

const availableGenres = computed(() => {
    const set = new Set<string>();
    songs.value.forEach((s) => {
        if (s.genre) set.add(s.genre);
    });
    return Array.from(set).sort();
});

let searchTimeout: ReturnType<typeof setTimeout>;

async function fetchSongs() {
    loading.value = true;
    error.value = '';

    try {
        const params = new URLSearchParams();
        if (search.value) params.append('search', search.value);
        if (genre.value) params.append('genre', genre.value);
        params.append('sort', sortBy.value);
        params.append('order', sortOrder.value);
        params.append('limit', '50');

        const res = await fetch(`${API_URL}?${params.toString()}`);
        const data = await res.json();

        if (!res.ok) throw new Error('Failed to fetch');

        songs.value = data.data || [];
    } catch (e) {
        error.value = 'Could not fetch music data. The API might be down.';
    } finally {
        loading.value = false;
    }
}

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(fetchSongs, 400);
});

watch([genre, sortBy, sortOrder], fetchSongs);

onMounted(() => {
    fetchSongs();
});

function formatDate(dateStr: string): string {
    const d = new Date(dateStr);
    return d.toLocaleDateString('et-EE', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
}
</script>

<template>
    <Head title="Music" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">
                    Music
                </h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Songs fetched from an external API by Kätlin
                </p>
            </div>

            <div class="flex flex-wrap gap-3">
                <input
                    v-model="search"
                    type="text"
                    placeholder="Search by title..."
                    class="max-w-sm min-w-50 flex-1 rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-gray-400 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                />
                <select
                    v-model="genre"
                    class="rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="">All genres</option>
                    <option v-for="g in availableGenres" :key="g" :value="g">
                        {{ g }}
                    </option>
                </select>
                <select
                    v-model="sortBy"
                    class="rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="created_at">Newest</option>
                    <option value="title">Title</option>
                    <option value="artist">Artist</option>
                </select>
                <select
                    v-model="sortOrder"
                    class="rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                >
                    <option value="desc">Descending</option>
                    <option value="asc">Ascending</option>
                </select>
            </div>
            <div
                v-if="loading"
                class="flex flex-1 items-center justify-center text-sm text-gray-400"
            >
                Loading...
            </div>

            <div
                v-else-if="error"
                class="rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600 dark:border-red-800/40 dark:bg-red-950/20 dark:text-red-400"
            >
                {{ error }}
            </div>

            <div
                v-else-if="songs.length === 0"
                class="flex flex-1 items-center justify-center text-sm text-gray-400"
            >
                No songs found
            </div>

            <div
                v-else
                class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4"
            >
                <div
                    v-for="song in songs"
                    :key="song.id"
                    class="flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800/50"
                >
                    <img
                        :src="
                            song.image ||
                            'https://placehold.co/400x400/e5e7eb/9ca3af?text=No+Image'
                        "
                        :alt="song.title"
                        class="h-44 w-full object-cover"
                        @error="
                            ($event.target as HTMLImageElement).src =
                                'https://placehold.co/400x400/e5e7eb/9ca3af?text=No+Image'
                        "
                    />
                    <div class="flex flex-1 flex-col p-4">
                        <h3
                            class="text-sm font-semibold text-gray-900 dark:text-white"
                        >
                            {{ song.title }}
                        </h3>
                        <p class="text-xs text-gray-500 dark:text-gray-400">
                            {{ song.artist }}
                        </p>
                        <p
                            v-if="song.description"
                            class="mt-2 line-clamp-2 text-xs text-gray-500 dark:text-gray-400"
                        >
                            {{ song.description }}
                        </p>

                        <div class="mt-auto flex items-center gap-2 pt-3">
                            <span
                                v-if="song.genre"
                                class="rounded-full bg-gray-100 px-2 py-0.5 text-[10px] text-gray-600 dark:bg-gray-700 dark:text-gray-300"
                                >{{ song.genre }}</span
                            >
                        </div>

                        <div
                            class="mt-2 border-t border-gray-100 pt-2 text-[10px] text-gray-400 dark:border-gray-700/50"
                        >
                            added by {{ song.added_by }} &middot;
                            {{ formatDate(song.created_at) }}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
