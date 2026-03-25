<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Product {
    id: number;
    name: string;
    description: string;
    price: number;
    image: string;
}

interface CartItem {
    product: Product;
    quantity: number;
}

const props = defineProps<{
    products: Product[];
}>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Shop', href: '/shop' },
];

const cart = ref<CartItem[]>([]);
const showCart = ref(false);

const cartTotal = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.product.price * item.quantity, 0);
});

const cartCount = computed(() => {
    return cart.value.reduce((sum, item) => sum + item.quantity, 0);
});

function addToCart(product: Product, qty: number) {
    if (qty < 1) return;
    const existing = cart.value.find(item => item.product.id === product.id);
    if (existing) {
        existing.quantity += qty;
    } else {
        cart.value.push({ product, quantity: qty });
    }
    showCart.value = true;
}

function updateQuantity(productId: number, quantity: number) {
    const item = cart.value.find(i => i.product.id === productId);
    if (!item) return;
    if (quantity < 1) {
        removeFromCart(productId);
        return;
    }
    item.quantity = quantity;
}

function removeFromCart(productId: number) {
    cart.value = cart.value.filter(i => i.product.id !== productId);
}

function goToCheckout() {
    // Store cart in sessionStorage so Checkout page can read it
    sessionStorage.setItem('cart', JSON.stringify(cart.value));
    router.visit('/shop/checkout');
}

// Track per-product quantity selection
const quantities = ref<Record<number, number>>({});
function getQty(id: number) { return quantities.value[id] || 1; }
function setQty(id: number, val: number) { quantities.value[id] = Math.max(1, val); }
</script>

<template>
    <Head title="Shop" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Shop</h1>
                <button
                    @click="showCart = !showCart"
                    class="relative inline-flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-4 py-2.5 text-sm font-medium shadow-sm hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-white dark:hover:bg-gray-700"
                >
                    <svg class="h-5 w-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 3h1.386c.51 0 .955.343 1.087.835l.383 1.437M7.5 14.25a3 3 0 00-3 3h15.75m-12.75-3h11.218c1.121 0 2.09-.773 2.34-1.867l1.887-8.256H5.507M7.5 14.25L5.106 5.272M6 20.25a.75.75 0 11-1.5 0 .75.75 0 011.5 0zm12.75 0a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" />
                    </svg>
                    Cart
                    <span v-if="cartCount > 0" class="absolute -right-2 -top-2 flex h-5 w-5 items-center justify-center rounded-full bg-blue-500 text-[10px] font-bold text-white">
                        {{ cartCount }}
                    </span>
                </button>
            </div>

            <div class="flex gap-6">
                <!-- Products Grid -->
                <div class="flex-1">
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div
                            v-for="product in products"
                            :key="product.id"
                            class="flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800/50"
                        >
                            <img :src="product.image" :alt="product.name" class="h-48 w-full object-cover" />
                            <div class="flex flex-1 flex-col p-4">
                                <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ product.name }}</h3>
                                <p class="mt-1 text-xs text-gray-500 line-clamp-2 dark:text-gray-400">{{ product.description }}</p>
                                <div class="mt-auto pt-3">
                                    <p class="text-lg font-bold text-gray-900 dark:text-white">&euro;{{ product.price.toFixed(2) }}</p>
                                    <div class="mt-2 flex items-center gap-2">
                                        <div class="flex items-center rounded-lg border border-gray-200 dark:border-gray-600">
                                            <button @click="setQty(product.id, getQty(product.id) - 1)" class="px-2 py-1 text-gray-500 hover:text-gray-700 dark:text-gray-400">-</button>
                                            <span class="w-8 text-center text-sm">{{ getQty(product.id) }}</span>
                                            <button @click="setQty(product.id, getQty(product.id) + 1)" class="px-2 py-1 text-gray-500 hover:text-gray-700 dark:text-gray-400">+</button>
                                        </div>
                                        <button
                                            @click="addToCart(product, getQty(product.id)); setQty(product.id, 1)"
                                            class="flex-1 rounded-lg bg-blue-500 py-1.5 text-xs font-medium text-white hover:bg-blue-600"
                                        >
                                            Add to cart
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Cart Sidebar -->
                <div
                    v-if="showCart"
                    class="w-80 shrink-0 rounded-xl border border-gray-200 bg-white shadow-sm dark:border-gray-700 dark:bg-gray-800/50"
                >
                    <div class="border-b border-gray-200 px-4 py-3 dark:border-gray-700">
                        <h2 class="text-sm font-semibold text-gray-900 dark:text-white">Cart ({{ cartCount }})</h2>
                    </div>

                    <div v-if="cart.length === 0" class="p-8 text-center text-sm text-gray-400">
                        Your cart is empty
                    </div>

                    <div v-else class="flex flex-col">
                        <div class="max-h-96 overflow-y-auto">
                            <div
                                v-for="item in cart"
                                :key="item.product.id"
                                class="flex items-center gap-3 border-b border-gray-100 px-4 py-3 dark:border-gray-700/50"
                            >
                                <img :src="item.product.image" class="h-12 w-12 rounded-lg object-cover" />
                                <div class="flex-1 min-w-0">
                                    <p class="truncate text-xs font-medium text-gray-900 dark:text-white">{{ item.product.name }}</p>
                                    <p class="text-xs text-gray-500">&euro;{{ item.product.price.toFixed(2) }}</p>
                                    <div class="mt-1 flex items-center gap-1">
                                        <button @click="updateQuantity(item.product.id, item.quantity - 1)" class="rounded px-1.5 text-xs text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">-</button>
                                        <span class="w-6 text-center text-xs">{{ item.quantity }}</span>
                                        <button @click="updateQuantity(item.product.id, item.quantity + 1)" class="rounded px-1.5 text-xs text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700">+</button>
                                    </div>
                                </div>
                                <button @click="removeFromCart(item.product.id)" class="text-gray-400 hover:text-red-500">
                                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <div class="border-t border-gray-200 px-4 py-3 dark:border-gray-700">
                            <div class="mb-3 flex items-center justify-between">
                                <span class="text-sm font-medium text-gray-500">Total</span>
                                <span class="text-lg font-bold text-gray-900 dark:text-white">&euro;{{ cartTotal.toFixed(2) }}</span>
                            </div>
                            <button
                                @click="goToCheckout"
                                class="w-full rounded-lg bg-blue-500 py-2.5 text-sm font-medium text-white hover:bg-blue-600"
                            >
                                Checkout
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>