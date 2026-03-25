<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface CartItem {
    product: { id: number; name: string; price: number; image: string };
    quantity: number;
}

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Shop', href: '/shop' },
    { title: 'Checkout', href: '/shop/checkout' },
];

const page = usePage();
const errors = computed(() => page.props.errors as Record<string, string>);

const cart = ref<CartItem[]>([]);
const loading = ref(false);

const form = ref({
    first_name: '',
    last_name: '',
    email: '',
    phone: '',
});

const cartTotal = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.product.price * item.quantity, 0);
});

onMounted(() => {
    const stored = sessionStorage.getItem('cart');
    if (stored) {
        cart.value = JSON.parse(stored);
    }
    if (cart.value.length === 0) {
        router.visit('/shop');
    }
});

function submitOrder() {
    loading.value = true;

    router.post('/shop/checkout', {
        ...form.value,
        items: cart.value.map(item => ({
            product_id: item.product.id,
            quantity: item.quantity,
        })),
    }, {
        onSuccess: () => {
            sessionStorage.removeItem('cart');
        },
        onFinish: () => {
            loading.value = false;
        },
    });
}
</script>

<template>
    <Head title="Checkout" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="mx-auto w-full max-w-3xl">
                <h1 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">Checkout</h1>

                <!-- Payment Error -->
                <div v-if="errors.payment" class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-600 dark:border-red-800/50 dark:bg-red-950/20 dark:text-red-400">
                    {{ errors.payment }}
                </div>

                <div class="flex gap-6">
                    <!-- Form -->
                    <div class="flex-1">
                        <div class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-800/50">
                            <h2 class="mb-4 text-sm font-semibold text-gray-900 dark:text-white">Your Details</h2>
                            <div class="flex flex-col gap-3">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-gray-500">First Name</label>
                                        <input v-model="form.first_name" type="text" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-xs font-medium text-gray-500">Last Name</label>
                                        <input v-model="form.last_name" type="text" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                    </div>
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-500">Email</label>
                                    <input v-model="form.email" type="email" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-gray-500">Phone</label>
                                    <input v-model="form.phone" type="tel" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                                </div>
                            </div>

                            <div class="mt-4 rounded-lg bg-gray-50 p-3 dark:bg-gray-700/30">
                                <p class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                                    </svg>
                                    You'll be redirected to Stripe's secure payment page
                                </p>
                            </div>

                            <button
                                @click="submitOrder"
                                :disabled="loading"
                                class="mt-4 w-full rounded-lg bg-blue-500 py-3 text-sm font-medium text-white hover:bg-blue-600 disabled:opacity-50"
                            >
                                {{ loading ? 'Redirecting to payment...' : `Pay €${cartTotal.toFixed(2)}` }}
                            </button>
                        </div>
                    </div>

                    <!-- Order Summary -->
                    <div class="w-72 shrink-0">
                        <div class="rounded-xl border border-gray-200 bg-white p-4 shadow-sm dark:border-gray-700 dark:bg-gray-800/50">
                            <h2 class="mb-3 text-sm font-semibold text-gray-900 dark:text-white">Order Summary</h2>
                            <div class="flex flex-col gap-2">
                                <div v-for="item in cart" :key="item.product.id" class="flex items-center justify-between text-xs">
                                    <span class="text-gray-600 dark:text-gray-400">{{ item.product.name }} x{{ item.quantity }}</span>
                                    <span class="font-medium text-gray-900 dark:text-white">&euro;{{ (item.product.price * item.quantity).toFixed(2) }}</span>
                                </div>
                            </div>
                            <div class="mt-3 border-t border-gray-200 pt-3 dark:border-gray-700">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-gray-500">Total</span>
                                    <span class="text-lg font-bold text-gray-900 dark:text-white">&euro;{{ cartTotal.toFixed(2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>