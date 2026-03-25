<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Recipe {
    id: number;
    title: string;
    image: string;
    description: string;
    prep_time: number;
    servings: number;
    cuisine: string | null;
    difficulty: string;
    user: { id: number; name: string } | null;
    created_at: string;
}

const props = defineProps<{
    recipes: {
        data: Recipe[];
        links: any[];
        current_page: number;
        last_page: number;
    };
    filters: {
        search: string;
        cuisine: string;
        difficulty: string;
        sort: string;
        order: string;
    };
    cuisines: string[];
}>();

const page = usePage();
const currentUser = page.props.auth?.user as { id: number } | undefined;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Recipes', href: '/recipes' },
];

// Filters
const search = ref(props.filters.search);
const cuisine = ref(props.filters.cuisine);
const difficulty = ref(props.filters.difficulty);
const sortBy = ref(props.filters.sort);
const sortOrder = ref(props.filters.order);

let searchTimeout: ReturnType<typeof setTimeout>;

function applyFilters() {
    router.get('/recipes', {
        search: search.value || undefined,
        cuisine: cuisine.value || undefined,
        difficulty: difficulty.value || undefined,
        sort: sortBy.value !== 'created_at' ? sortBy.value : undefined,
        order: sortOrder.value !== 'desc' ? sortOrder.value : undefined,
    }, {
        preserveState: true,
        replace: true,
    });
}

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});

watch([cuisine, difficulty, sortBy, sortOrder], applyFilters);

// Modal
const showModal = ref(false);
const editingRecipe = ref<Recipe | null>(null);

const form = ref({
    title: '',
    image: '',
    description: '',
    prep_time: 30,
    servings: 4,
    cuisine: '',
    difficulty: 'medium',
});

function openCreate() {
    editingRecipe.value = null;
    form.value = {
        title: '',
        image: '',
        description: '',
        prep_time: 30,
        servings: 4,
        cuisine: '',
        difficulty: 'medium',
    };
    showModal.value = true;
}

function openEdit(recipe: Recipe) {
    editingRecipe.value = recipe;
    form.value = {
        title: recipe.title,
        image: recipe.image,
        description: recipe.description,
        prep_time: recipe.prep_time,
        servings: recipe.servings,
        cuisine: recipe.cuisine || '',
        difficulty: recipe.difficulty,
    };
    showModal.value = true;
}

function submitForm() {
    if (editingRecipe.value) {
        router.put(`/recipes/${editingRecipe.value.id}`, form.value, {
            onSuccess: () => { showModal.value = false; },
        });
    } else {
        router.post('/recipes', form.value, {
            onSuccess: () => { showModal.value = false; },
        });
    }
}

function deleteRecipe(recipe: Recipe) {
    if (!confirm(`Delete "${recipe.title}"?`)) return;
    router.delete(`/recipes/${recipe.id}`);
}

function difficultyColor(d: string) {
    if (d === 'easy') return 'bg-green-100 text-green-700 dark:bg-green-900/30 dark:text-green-400';
    if (d === 'hard') return 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-400';
    return 'bg-yellow-100 text-yellow-700 dark:bg-yellow-900/30 dark:text-yellow-400';
}

function goToPage(url: string | null) {
    if (url) router.visit(url, { preserveState: true });
}
</script>

<template>
    <Head title="Recipes" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">

            <!-- Header -->
            <div class="flex items-center justify-between">
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Recipes</h1>
                <button
                    @click="openCreate"
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-500 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-600 active:scale-[0.98]"
                >
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    Add Recipe
                </button>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap items-center gap-3">
                <div class="relative flex-1 min-w-[200px] max-w-sm">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z" />
                    </svg>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="Search recipes..."
                        class="w-full rounded-lg border border-gray-200 py-2 pl-9 pr-3 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 focus:outline-none dark:border-gray-600 dark:bg-gray-800 dark:text-white"
                    />
                </div>
                <select v-model="cuisine" class="rounded-lg border border-gray-200 py-2 px-3 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <option value="">All cuisines</option>
                    <option v-for="c in cuisines" :key="c" :value="c">{{ c }}</option>
                </select>
                <select v-model="difficulty" class="rounded-lg border border-gray-200 py-2 px-3 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <option value="">All difficulties</option>
                    <option value="easy">Easy</option>
                    <option value="medium">Medium</option>
                    <option value="hard">Hard</option>
                </select>
                <select v-model="sortBy" class="rounded-lg border border-gray-200 py-2 px-3 text-sm dark:border-gray-600 dark:bg-gray-800 dark:text-white">
                    <option value="created_at">Newest</option>
                    <option value="title">Title</option>
                    <option value="prep_time">Prep time</option>
                    <option value="servings">Servings</option>
                </select>
                <button
                    @click="sortOrder = sortOrder === 'desc' ? 'asc' : 'desc'; applyFilters()"
                    class="rounded-lg border border-gray-200 p-2 text-sm hover:bg-gray-50 dark:border-gray-600 dark:hover:bg-gray-700"
                >
                    <svg :class="['h-4 w-4 transition-transform', sortOrder === 'asc' ? 'rotate-180' : '']" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 13.5L12 21m0 0l-7.5-7.5M12 21V3" />
                    </svg>
                </button>
            </div>

            <!-- API docs hint -->
            <div class="rounded-lg border border-blue-100 bg-blue-50 px-4 py-2 text-xs text-blue-600 dark:border-blue-800/30 dark:bg-blue-950/20 dark:text-blue-400">
                JSON API available at <code class="font-mono">/api/recipes</code> — supports <code>?search=</code>, <code>?cuisine=</code>, <code>?difficulty=</code>, <code>?sort=</code>, <code>?order=</code>, <code>?limit=</code>
            </div>

            <!-- Empty State -->
            <div v-if="recipes.data.length === 0" class="flex flex-1 flex-col items-center justify-center text-gray-400 dark:text-gray-600">
                <svg class="mb-3 h-16 w-16 opacity-40" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8.25v-1.5m0 1.5c-1.355 0-2.697.056-4.024.166C6.845 8.51 6 9.473 6 10.608v2.513m6-4.871c1.355 0 2.697.056 4.024.166C17.155 8.51 18 9.473 18 10.608v2.513M15 8.25v-1.5m-6 1.5v-1.5m12 9.75l-1.5.75a3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0 3.354 3.354 0 00-3 0 3.354 3.354 0 01-3 0L3 16.5m15-3.379a48.474 48.474 0 00-6-.371c-2.032 0-4.034.126-6 .371m12 0c.39.049.777.102 1.163.16 1.07.16 1.837 1.094 1.837 2.175v5.169c0 .621-.504 1.125-1.125 1.125H4.125A1.125 1.125 0 013 20.625v-5.17c0-1.08.768-2.014 1.837-2.174A47.78 47.78 0 016 13.12M12.265 3.11a.375.375 0 11-.53 0L12 2.845l.265.265zm-3 0a.375.375 0 11-.53 0L9 2.845l.265.265zm6 0a.375.375 0 11-.53 0L15 2.845l.265.265z" />
                </svg>
                <p class="text-sm">No recipes yet. Add the first one!</p>
            </div>

            <!-- Recipes Grid -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
                <div
                    v-for="recipe in recipes.data"
                    :key="recipe.id"
                    class="group flex flex-col overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800/50"
                >
                    <div class="relative">
                        <img :src="recipe.image" :alt="recipe.title" class="h-44 w-full object-cover" />
                        <span :class="['absolute top-2 right-2 rounded-full px-2 py-0.5 text-[10px] font-semibold', difficultyColor(recipe.difficulty)]">
                            {{ recipe.difficulty }}
                        </span>
                    </div>
                    <div class="flex flex-1 flex-col p-4">
                        <div class="mb-1 flex items-center justify-between">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-white">{{ recipe.title }}</h3>
                            <div v-if="currentUser?.id === recipe.user?.id" class="flex gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                                <button @click="openEdit(recipe)" class="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700">
                                    <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                    </svg>
                                </button>
                                <button @click="deleteRecipe(recipe)" class="rounded-md p-1 text-gray-400 hover:bg-red-100 hover:text-red-500 dark:hover:bg-red-900/30">
                                    <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 line-clamp-2 dark:text-gray-400">{{ recipe.description }}</p>

                        <div class="mt-auto flex items-center gap-3 pt-3 text-[11px] text-gray-400">
                            <span v-if="recipe.cuisine" class="rounded-full bg-gray-100 px-2 py-0.5 dark:bg-gray-700">{{ recipe.cuisine }}</span>
                            <span>{{ recipe.prep_time }} min</span>
                            <span>{{ recipe.servings }} servings</span>
                        </div>

                        <div class="mt-2 border-t border-gray-100 pt-2 text-[10px] text-gray-400 dark:border-gray-700/50">
                            by {{ recipe.user?.name }} &middot; {{ recipe.created_at }}
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pagination -->
            <div v-if="recipes.last_page > 1" class="flex justify-center gap-1">
                <button
                    v-for="link in recipes.links"
                    :key="link.label"
                    @click="goToPage(link.url)"
                    :disabled="!link.url"
                    :class="[
                        'rounded-lg px-3 py-1.5 text-xs',
                        link.active
                            ? 'bg-blue-500 text-white'
                            : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-700 disabled:opacity-30'
                    ]"
                    v-html="link.label"
                />
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <Teleport to="body">
            <div v-if="showModal" class="fixed inset-0 z-[2000] flex items-center justify-center bg-black/50" @click.self="showModal = false">
                <div class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        {{ editingRecipe ? 'Edit Recipe' : 'Add Recipe' }}
                    </h3>
                    <div class="flex flex-col gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500">Title</label>
                            <input v-model="form.title" type="text" placeholder="Recipe name" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500">Image URL</label>
                            <input v-model="form.image" type="url" placeholder="https://..." class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500">Description</label>
                            <textarea v-model="form.description" rows="3" placeholder="Describe the recipe..." class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500">Prep Time (min)</label>
                                <input v-model.number="form.prep_time" type="number" min="1" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500">Servings</label>
                                <input v-model.number="form.servings" type="number" min="1" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500">Cuisine</label>
                                <input v-model="form.cuisine" type="text" placeholder="e.g. Italian, Japanese" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-gray-500">Difficulty</label>
                                <select v-model="form.difficulty" class="w-full rounded-lg border border-gray-200 px-3 py-2 text-sm dark:border-gray-600 dark:bg-gray-700 dark:text-white">
                                    <option value="easy">Easy</option>
                                    <option value="medium">Medium</option>
                                    <option value="hard">Hard</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="mt-5 flex justify-end gap-2">
                        <button @click="showModal = false" class="rounded-lg px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700">Cancel</button>
                        <button @click="submitForm" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600">
                            {{ editingRecipe ? 'Save Changes' : 'Add Recipe' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>