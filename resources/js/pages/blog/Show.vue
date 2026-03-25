<script setup lang="ts">
import { ref } from 'vue';
import { Head, router, usePage, Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import type { BreadcrumbItem } from '@/types';

interface Comment {
    id: number;
    body: string;
    created_at: string;
    user: { id: number; name: string };
}

interface Post {
    id: number;
    title: string;
    description: string;
    created_at: string;
    updated_at: string;
    user: { id: number; name: string };
    comments: Comment[];
}

const props = defineProps<{
    post: Post;
}>();

const page = usePage();
const currentUser = page.props.auth?.user as { id: number } | undefined;

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Blog', href: '/blog' },
    { title: props.post.title, href: `/blog/${props.post.id}` },
];

const commentBody = ref('');

function submitComment() {
    if (!commentBody.value.trim()) return;
    router.post(`/blog/${props.post.id}/comments`, { body: commentBody.value }, {
        onSuccess: () => {
            commentBody.value = '';
        },
    });
}

function deleteComment(comment: Comment) {
    if (!confirm('Delete this comment?')) return;
    router.delete(`/comments/${comment.id}`);
}

function isCommentOwner(comment: Comment): boolean {
    return currentUser?.id === comment.user.id;
}

function isPostOwner(): boolean {
    return currentUser?.id === props.post.user.id;
}
</script>

<template>
    <Head :title="post.title" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 p-6">
            <div class="mx-auto w-full max-w-3xl">

                <!-- Back link -->
                <Link href="/blog" class="mb-4 inline-flex items-center gap-1 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-300">
                    <svg class="h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                    </svg>
                    Back to Blog
                </Link>

                <!-- Post -->
                <article class="rounded-xl border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-800/50">
                    <div class="mb-4 flex items-center gap-3 text-sm text-gray-500 dark:text-gray-400">
                        <span class="font-medium text-gray-700 dark:text-gray-300">{{ post.user.name }}</span>
                        <span>&middot;</span>
                        <span>{{ post.created_at }}</span>
                        <span v-if="post.updated_at !== post.created_at" class="text-xs">(edited)</span>
                    </div>
                    <h1 class="mb-4 text-3xl font-bold text-gray-900 dark:text-white">{{ post.title }}</h1>
                    <p class="whitespace-pre-wrap text-gray-600 leading-relaxed dark:text-gray-300">{{ post.description }}</p>
                </article>

                <!-- Comments Section -->
                <div class="mt-8">
                    <h2 class="mb-4 text-lg font-semibold text-gray-900 dark:text-white">
                        Comments ({{ post.comments.length }})
                    </h2>

                    <!-- Add Comment -->
                    <div class="mb-6 flex gap-3">
                        <textarea
                            v-model="commentBody"
                            rows="2"
                            placeholder="Write a comment..."
                            class="flex-1 rounded-lg border border-gray-200 bg-white px-3 py-2 text-sm focus:border-blue-400 focus:ring-2 focus:ring-blue-100 focus:outline-none dark:border-gray-600 dark:bg-gray-700 dark:text-white"
                        />
                        <button
                            @click="submitComment"
                            class="self-end rounded-lg bg-blue-500 px-4 py-2 text-sm font-medium text-white hover:bg-blue-600"
                        >
                            Post
                        </button>
                    </div>

                    <!-- Comments List -->
                    <div v-if="post.comments.length === 0" class="py-8 text-center text-sm text-gray-400">
                        No comments yet. Be the first!
                    </div>

                    <div class="flex flex-col gap-3">
                        <div
                            v-for="comment in post.comments"
                            :key="comment.id"
                            class="group rounded-lg border border-gray-100 bg-gray-50 p-4 dark:border-gray-700/50 dark:bg-gray-800/30"
                        >
                            <div class="mb-2 flex items-center justify-between">
                                <div class="flex items-center gap-2 text-xs text-gray-500 dark:text-gray-400">
                                    <span class="font-medium text-gray-700 dark:text-gray-300">{{ comment.user.name }}</span>
                                    <span>&middot;</span>
                                    <span>{{ comment.created_at }}</span>
                                </div>
                                <button
                                    v-if="isCommentOwner(comment) || isPostOwner()"
                                    @click="deleteComment(comment)"
                                    class="rounded-md p-1 text-gray-400 opacity-0 transition-opacity hover:bg-red-100 hover:text-red-500 group-hover:opacity-100 dark:hover:bg-red-900/30 dark:hover:text-red-400"
                                >
                                    <svg class="h-3.5 w-3.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                    </svg>
                                </button>
                            </div>
                            <p class="text-sm text-gray-600 dark:text-gray-300">{{ comment.body }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>