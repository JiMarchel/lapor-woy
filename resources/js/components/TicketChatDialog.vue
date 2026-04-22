<script setup lang="ts">
import { ref, watch, onUnmounted, nextTick } from 'vue';
import { Ticket, TicketReply } from '@/types/ticket';
import { Button } from './ui/button';
import { Input } from './ui/input';
import { MessageSquare, Send, Loader2 } from 'lucide-vue-next';
import { usePage } from '@inertiajs/vue3';
import { index, store } from '@/actions/App/Http/Controllers/TicketReplyController';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import axios from 'axios';

const props = defineProps<{
    ticket: Ticket;
}>();

const replies = ref<TicketReply[]>([]);
const newMessage = ref('');
const isSubmitting = ref(false);
const isLoading = ref(true);
const isOpen = ref(false);
const scrollContainer = ref<HTMLElement | null>(null);

const page = usePage();
const currentUserId = String(page.props.auth?.user?.id);

let pollInterval: ReturnType<typeof setInterval> | null = null;

const scrollToBottom = async () => {
    await nextTick();
    if (scrollContainer.value) {
        scrollContainer.value.scrollTop = scrollContainer.value.scrollHeight;
    }
};

const fetchReplies = async () => {
    try {
        const isNearBottom = scrollContainer.value
            ? (scrollContainer.value.scrollHeight - scrollContainer.value.scrollTop - scrollContainer.value.clientHeight < 150)
            : true;

        const response = await axios.get(index.url(props.ticket.id));
        const previousLength = replies.value.length;

        replies.value = response.data;

        if (isLoading.value || (replies.value.length > previousLength && isNearBottom)) {
            scrollToBottom();
        }
    } catch (error) {
        console.error('Failed to fetch replies', error);
    } finally {
        isLoading.value = false;
    }
};

const sendReply = async () => {
    if (!newMessage.value.trim() || isSubmitting.value) return;

    isSubmitting.value = true;
    try {
        const response = await axios.post(store.url(props.ticket.id), {
            content: newMessage.value.trim()
        });

        // Append Optimistically
        replies.value.push(response.data);
        newMessage.value = '';
        scrollToBottom();

        if (props.ticket.unread_replies_count) {
            props.ticket.unread_replies_count = 0;
        }

    } catch (error) {
        console.error('Failed to send reply', error);
    } finally {
        isSubmitting.value = false;
    }
};

watch(isOpen, (newVal) => {
    if (newVal) {
        isLoading.value = true;
        fetchReplies();

        if (props.ticket.unread_replies_count) {
            props.ticket.unread_replies_count = 0;
        }

        pollInterval = setInterval(() => {
            fetchReplies();
        }, 3000);
    } else {
        if (pollInterval) {
            clearInterval(pollInterval);
            pollInterval = null;
        }
    }
});

onUnmounted(() => {
    if (pollInterval) clearInterval(pollInterval);
});

const formatTime = (dateStr: string) => {
    const d = new Date(dateStr);
    return d.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button variant="outline" size="icon" class="relative group">
                <MessageSquare class="w-4 h-4" />
                <!-- Red Ping Dot for Unread Messages -->
                <span v-if="ticket.unread_replies_count && ticket.unread_replies_count > 0"
                    class="absolute -top-1.5 -right-1.5 flex h-4 w-4 items-center justify-center">
                    <span
                        class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                </span>
            </Button>
        </DialogTrigger>

        <DialogContent
            class="sm:max-w-[500px] h-[600px] flex flex-col p-0 overflow-hidden bg-gray-50 dark:bg-gray-900 border-gray-200 dark:border-gray-800">
            <!-- Header -->
            <DialogHeader class="p-4 bg-white dark:bg-[#18181b] border-b border-gray-200 dark:border-gray-800 shrink-0">
                <DialogTitle>Diskusi Laporan Dengan Admin</DialogTitle>
                <DialogDescription class="truncate">
                    {{ ticket.title }}
                </DialogDescription>
            </DialogHeader>

            <!-- Chat Area -->
            <div ref="scrollContainer" class="flex-1 overflow-y-auto p-4 space-y-4">
                <div v-if="isLoading" class="flex justify-center items-center h-full">
                    <Loader2 class="w-6 h-6 animate-spin text-gray-400" />
                </div>

                <div v-else-if="replies.length === 0"
                    class="flex flex-col items-center justify-center h-full text-center text-gray-500 dark:text-gray-400">
                    <MessageSquare class="w-8 h-8 mb-2 opacity-50" />
                    <p class="text-sm">Belum ada percakapan.<br />Kirim pesan pertama Anda!</p>
                </div>

                <div v-else class="flex flex-col space-y-4 pb-4">
                    <div v-for="reply in replies" :key="reply.id"
                        :class="['flex w-full', reply.user_id === currentUserId ? 'justify-end' : 'justify-start']">
                        <div :class="[
                            'max-w-[80%] rounded-2xl px-4 py-2.5 text-sm',
                            reply.user_id === currentUserId
                                ? 'bg-indigo-600 text-white rounded-br-none shadow-sm'
                                : 'bg-white dark:bg-gray-800 text-gray-900 dark:text-gray-100 rounded-bl-none shadow-[0_1px_2px_rgba(0,0,0,0.05)] border border-gray-100 dark:border-gray-700'
                        ]">
                            <!-- Sender Name (Only for received messages) -->
                            <div v-if="reply.user_id !== currentUserId"
                                class="font-semibold text-[11px] mb-1 opacity-70 tracking-wide uppercase">
                                {{ reply.user?.name || 'Admin' }}
                            </div>

                            <p class="whitespace-pre-wrap leading-relaxed">{{ reply.content }}</p>

                            <!-- Timestamp & Read Status -->
                            <div
                                :class="['text-[10px] mt-1.5 flex items-center justify-end gap-1', reply.user_id === currentUserId ? 'text-indigo-200' : 'text-gray-400']">
                                {{ formatTime(reply.created_at) }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Input Area -->
            <div class="p-3 bg-white dark:bg-[#18181b] border-t border-gray-200 dark:border-gray-800 shrink-0">
                <form @submit.prevent="sendReply" class="flex items-center gap-2">
                    <Input v-model="newMessage" placeholder="Ketik pesan..."
                        class="flex-1 rounded-full px-4 border-gray-200 dark:border-gray-700 focus-visible:ring-indigo-500"
                        :disabled="isSubmitting" />
                    <Button type="submit" size="icon"
                        class="rounded-full shrink-0 w-10 h-10 bg-indigo-600 hover:bg-indigo-700 text-white"
                        :disabled="isSubmitting || !newMessage.trim()">
                        <Loader2 v-if="isSubmitting" class="w-4 h-4 animate-spin" />
                        <Send v-else class="w-4 h-4 ml-0.5" />
                    </Button>
                </form>
            </div>
        </DialogContent>
    </Dialog>
</template>
