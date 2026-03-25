<script setup lang="ts">
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

const props = defineProps<{
    order: {
        id: number;
        total: number;
        status: string;
        payment_id: string;
        created_at: string;
    };
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Shop', href: '/shop' },
    { title: 'Order Confirmed', href: '#' },
];
</script>

<template>
    <Head title="Order Confirmed" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 items-center justify-center p-6">
            <div class="w-full max-w-md text-center">
                <div class="mx-auto mb-4 flex h-16 w-16 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/30">
                    <svg class="h-8 w-8 text-green-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                    </svg>
                </div>

                <h1 class="mb-2 text-2xl font-bold text-gray-900 dark:text-white">Order Confirmed!</h1>
                <p class="mb-6 text-gray-500 dark:text-gray-400">Thank you for your purchase.</p>

                <div class="mb-6 rounded-xl border border-gray-200 bg-white p-4 text-left shadow-sm dark:border-gray-700 dark:bg-gray-800/50">
                    <div class="flex flex-col gap-2 text-sm">
                        <div class="flex justify-between">
                            <span class="text-gray-500">Order #</span>
                            <span class="font-medium text-gray-900 dark:text-white">{{ order.id }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Status</span>
                            <span class="rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900/30 dark:text-green-400">
                                {{ order.status }}
                            </span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Total</span>
                            <span class="font-bold text-gray-900 dark:text-white">&euro;{{ order.total }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-gray-500">Date</span>
                            <span class="text-gray-900 dark:text-white">{{ order.created_at }}</span>
                        </div>
                    </div>
                </div>

                <Link href="/shop" class="inline-flex items-center gap-2 rounded-xl bg-blue-500 px-6 py-2.5 text-sm font-medium text-white hover:bg-blue-600">
                    Continue Shopping
                </Link>
            </div>
        </div>
    </AppLayout>
</template>