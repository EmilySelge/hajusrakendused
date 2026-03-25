<script setup lang="ts">
import { Head, router, usePage, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Post {
    id: number;
    title: string;
    description: string;
    created_at: string;
    user: { id: number; name: string };
    comments_count: number;
}

const props = defineProps<{
    posts: Post[];
}>();

const page = usePage();
const currentUser = page.props.auth?.user as { id: number } | undefined;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Blog', href: '/blog' },
];

const showCreateModal = ref(false);
const showEditModal = ref(false);
const editingPost = ref<Post | null>(null);

const createForm = ref({ title: '', description: '' });
const editForm = ref({ title: '', description: '' });

function submitCreate() {
    router.post('/blog', createForm.value, {
        onSuccess: () => {
            showCreateModal.value = false;
            createForm.value = { title: '', description: '' };
        },
    });
}

function openEdit(post: Post) {
    editingPost.value = post;
    editForm.value = { title: post.title, description: post.description };
    showEditModal.value = true;
}

function submitEdit() {
    if (!editingPost.value) return;
    router.put(`/blog/${editingPost.value.id}`, editForm.value, {
        onSuccess: () => {
            showEditModal.value = false;
        },
    });
}

function deletePost(post: Post) {
    if (!confirm(`Delete "${post.title}"?`)) return;
    router.delete(`/blog/${post.id}`);
}
</script>

<template>
    <Head title="Blog" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="flex items-center justify-between">
                
                <button
                    @click="showCreateModal = true"
                    class="inline-flex items-center gap-2 rounded-xl bg-blue-500 px-4 py-2.5 text-sm font-medium text-white shadow-sm hover:bg-blue-600 active:scale-[0.98]"
                >
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                    </svg>
                    New Post
                </button>
            </div>
            <div v-if="posts.length === 0" class="flex flex-1 flex-col items-center justify-center text-gray-400 dark:text-gray-600">
                <svg class="mb-3 h-16 w-16 opacity-40" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                </svg>
                <p class="text-sm">No posts yet. Create the first one!</p>
            </div>
            <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                <div
                    v-for="post in posts"
                    :key="post.id"
                    class="group flex flex-col rounded-xl border border-gray-200 bg-white shadow-sm transition-all hover:shadow-md dark:border-gray-700 dark:bg-gray-800/50"
                >
                    <div class="flex flex-1 flex-col p-5">
                        <div class="mb-3 flex items-center justify-between">
                            <span class="text-xs text-gray-400">{{ post.created_at }}</span>
                            <div v-if="currentUser?.id === post.user.id" class="flex gap-1 opacity-0 transition-opacity group-hover:opacity-100">
                                <button
                                    @click.prevent="openEdit(post)"
                                    class="rounded-md p-1 text-gray-400 hover:bg-gray-100 hover:text-gray-600 dark:hover:bg-gray-700 dark:hover:text-gray-300"
                                >
                                    <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931z" />
                                    </svg>
                                </button>
                                <button
                                    @click.prevent="deletePost(post)"
                                    class="rounded-md p-1 text-gray-400 hover:bg-red-100 hover:text-red-500 dark:hover:bg-red-900/30 dark:hover:text-red-400"
                                >
                                    <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <Link :href="`/blog/${post.id}`" class="flex-1">
                            <h2 class="mb-2 text-lg font-semibold text-gray-900 hover:text-blue-500 dark:text-white dark:hover:text-blue-400">
                                {{ post.title }}
                            </h2>
                            <p class="text-sm text-gray-500 line-clamp-3 dark:text-gray-400">
                                {{ post.description }}
                            </p>
                        </Link>

                        <div class="mt-4 flex items-center justify-between border-t border-gray-100 pt-3 dark:border-gray-700/50">
                            <span class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ post.user.name }}</span>
                            <span class="text-xs text-gray-400">{{ post.comments_count }} comments</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <Teleport to="body">
            <div v-if="showCreateModal" class="fixed inset-0 z-[2000] flex items-center justify-center bg-black/50" @click.self="showCreateModal = false">
                <div class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">New Post</h3>
                    <div class="flex flex-col gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Title</label>
                            <input v-model="createForm.title" type="text" placeholder="Post title" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Content</label>
                            <textarea v-model="createForm.description" rows="6" placeholder="Write your post..." class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                    </div>
                    <div class="mt-5 flex justify-end gap-2">
                        <button @click="showCreateModal = false" class="rounded-lg px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700">Cancel</button>
                        <button @click="submitCreate" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600">Publish</button>
                    </div>
                </div>
            </div>
        </Teleport>
        <Teleport to="body">
            <div v-if="showEditModal" class="fixed inset-0 z-[2000] flex items-center justify-center bg-black/50" @click.self="showEditModal = false">
                <div class="w-full max-w-lg rounded-2xl border border-gray-200 bg-white p-6 shadow-xl dark:border-gray-700 dark:bg-gray-800">
                    <h3 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">Edit Post</h3>
                    <div class="flex flex-col gap-3">
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Title</label>
                            <input v-model="editForm.title" type="text" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                        <div>
                            <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Content</label>
                            <textarea v-model="editForm.description" rows="6" class="w-full rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white" />
                        </div>
                    </div>
                    <div class="mt-5 flex justify-end gap-2">
                        <button @click="showEditModal = false" class="rounded-lg px-4 py-2 text-sm text-gray-500 hover:bg-gray-100 dark:text-gray-400 dark:hover:bg-gray-700">Cancel</button>
                        <button @click="submitEdit" class="rounded-lg bg-blue-500 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600">Save Changes</button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AppLayout>
</template>