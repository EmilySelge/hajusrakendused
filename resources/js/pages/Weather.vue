<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Weather', href: '/weather' },
];

const city = ref('');
const weatherData = ref<any>(null);
const errorMsg = ref('');
const loading = ref(false);

async function searchWeather() {
    if (!city.value.trim()) return;

    loading.value = true;
    errorMsg.value = '';
    weatherData.value = null;

    try {
        const response = await fetch(
            `/api/weather?city=${encodeURIComponent(city.value)}`,
        );
        const data = await response.json();

        if (!response.ok) {
            errorMsg.value = data.error || 'City not found';
            return;
        }

        weatherData.value = data;
    } catch (e) {
        errorMsg.value = 'Something went wrong';
    } finally {
        loading.value = false;
    }
}

function windDir(deg: number): string {
    const dirs = ['N', 'NE', 'E', 'SE', 'S', 'SW', 'W', 'NW'];
    return dirs[Math.round(deg / 45) % 8];
}

function formatTime(ts: number, offset: number): string {
    const date = new Date((ts + offset) * 1000);
    return date.toUTCString().slice(17, 22);
}
</script>

<template>
    <Head title="Weather" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">

            <!-- Search -->
            <div class="flex gap-2 max-w-md">
                <input
                    v-model="city"
                    @keyup.enter="searchWeather"
                    type="text"
                    placeholder="Enter city name..."
                    class="flex-1 rounded-lg border border-gray-200 px-4 py-2.5 text-sm focus:border-gray-400 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white"
                />
                <button
                    @click="searchWeather"
                    :disabled="loading"
                    class="rounded-lg bg-gray-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-gray-800 disabled:opacity-50 dark:bg-white dark:text-gray-900 dark:hover:bg-gray-100"
                >
                    {{ loading ? '...' : 'Search' }}
                </button>
            </div>

            <!-- Error -->
            <p v-if="errorMsg" class="text-sm text-red-500">{{ errorMsg }}</p>

            <!-- Empty -->
            <div v-if="!weatherData && !errorMsg && !loading" class="flex flex-1 items-center justify-center">
                <p class="text-sm text-gray-400">Search for a city to see the weather</p>
            </div>

            <!-- Results -->
            <div v-if="weatherData" class="max-w-2xl">

                <!-- City + Temp -->
                <div class="flex items-start justify-between mb-6">
                    <div>
                        <h1 class="text-3xl font-semibold text-gray-900 dark:text-white">{{ weatherData.name }}</h1>
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ weatherData.sys?.country }} &middot; {{ weatherData.weather[0].description }}</p>
                    </div>
                    <div class="flex items-center">
                        <img
                            :src="`https://openweathermap.org/img/wn/${weatherData.weather[0].icon}@2x.png`"
                            :alt="weatherData.weather[0].description"
                            class="h-16 w-16"
                        />
                        <span class="text-5xl font-light text-gray-900 dark:text-white">{{ Math.round(weatherData.main.temp) }}°</span>
                    </div>
                </div>

                <!-- Details -->
                <div class="grid grid-cols-3 gap-px overflow-hidden rounded-lg border border-gray-200 bg-gray-200 dark:border-gray-700 dark:bg-gray-700">
                    <div class="bg-white px-4 py-3 dark:bg-gray-800">
                        <p class="text-xs text-gray-400">Feels like</p>
                        <p class="text-lg font-medium text-gray-900 dark:text-white">{{ Math.round(weatherData.main.feels_like) }}°</p>
                    </div>
                    <div class="bg-white px-4 py-3 dark:bg-gray-800">
                        <p class="text-xs text-gray-400">Humidity</p>
                        <p class="text-lg font-medium text-gray-900 dark:text-white">{{ weatherData.main.humidity }}%</p>
                    </div>
                    <div class="bg-white px-4 py-3 dark:bg-gray-800">
                        <p class="text-xs text-gray-400">Wind</p>
                        <p class="text-lg font-medium text-gray-900 dark:text-white">{{ weatherData.wind.speed }} m/s {{ windDir(weatherData.wind.deg) }}</p>
                    </div>
                    <div class="bg-white px-4 py-3 dark:bg-gray-800">
                        <p class="text-xs text-gray-400">Pressure</p>
                        <p class="text-lg font-medium text-gray-900 dark:text-white">{{ weatherData.main.pressure }} hPa</p>
                    </div>
                    <div class="bg-white px-4 py-3 dark:bg-gray-800">
                        <p class="text-xs text-gray-400">Visibility</p>
                        <p class="text-lg font-medium text-gray-900 dark:text-white">{{ (weatherData.visibility / 1000).toFixed(1) }} km</p>
                    </div>
                    <div class="bg-white px-4 py-3 dark:bg-gray-800">
                        <p class="text-xs text-gray-400">Clouds</p>
                        <p class="text-lg font-medium text-gray-900 dark:text-white">{{ weatherData.clouds.all }}%</p>
                    </div>
                    <div class="bg-white px-4 py-3 dark:bg-gray-800">
                        <p class="text-xs text-gray-400">Sunrise</p>
                        <p class="text-lg font-medium text-gray-900 dark:text-white">{{ formatTime(weatherData.sys.sunrise, weatherData.timezone) }}</p>
                    </div>
                    <div class="bg-white px-4 py-3 dark:bg-gray-800">
                        <p class="text-xs text-gray-400">Sunset</p>
                        <p class="text-lg font-medium text-gray-900 dark:text-white">{{ formatTime(weatherData.sys.sunset, weatherData.timezone) }}</p>
                    </div>
                    <div class="bg-white px-4 py-3 dark:bg-gray-800">
                        <p class="text-xs text-gray-400">High / Low</p>
                        <p class="text-lg font-medium text-gray-900 dark:text-white">{{ Math.round(weatherData.main.temp_max) }}° / {{ Math.round(weatherData.main.temp_min) }}°</p>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>