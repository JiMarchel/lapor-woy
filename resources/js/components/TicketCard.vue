<script setup lang="ts">
import { Ticket } from '@/types/ticket';
import TicketForm from '@/components/TicketForm.vue';
import { CheckCircle, Loader2, AlertCircle, Clock, ArrowUpCircle, Flame } from 'lucide-vue-next';
import TicketChatDialog from '@/components/TicketChatDialog.vue';
import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

const page = usePage();
const user = computed(() => page.props.auth.user);
const isAdmin = user.value.role === 'admin';

defineProps<{
    ticket: Ticket;
    showActions?: boolean;
}>();

const pathName = window.location.pathname;
const isInArsip = pathName.includes('/arsip');

const formatDate = (dateString: string) => {
    return new Intl.DateTimeFormat('id-ID', {
        dateStyle: 'medium',
        timeStyle: 'short',
    }).format(new Date(dateString));
};

const priorityColor = (priority: string) => {
    switch (priority.toLowerCase()) {
        case 'urgent': return 'bg-red-50 text-red-600 dark:bg-red-500/10 dark:text-red-400 border-red-200 dark:border-red-500/20';
        case 'high': return 'bg-orange-50 text-orange-600 dark:bg-orange-500/10 dark:text-orange-400 border-orange-200 dark:border-orange-500/20';
        case 'medium': return 'bg-blue-50 text-blue-600 dark:bg-blue-500/10 dark:text-blue-400 border-blue-200 dark:border-blue-500/20';
        case 'low': return 'bg-gray-50 text-gray-600 dark:bg-gray-500/10 dark:text-gray-400 border-gray-200 dark:border-gray-500/20';
        default: return 'bg-gray-100 text-gray-700 dark:bg-gray-800 dark:text-gray-300 border-gray-200 dark:border-gray-700';
    }
};

const statusColor = (status: string) => {
    switch (status?.toLowerCase()) {
        case 'resolved': return 'bg-green-50 text-green-700 border-green-200 dark:bg-green-500/10 dark:text-green-400 dark:border-green-500/20';
        case 'in_progress': return 'bg-yellow-50 text-yellow-700 border-yellow-200 dark:bg-yellow-500/10 dark:text-yellow-400 dark:border-yellow-500/20';
        default: return 'bg-indigo-50 text-indigo-700 border-indigo-200 dark:bg-indigo-500/10 dark:text-indigo-400 dark:border-indigo-500/20';
    }
};


</script>

<template>
    <div
        class="group relative flex flex-col bg-white dark:bg-[#18181b] border border-gray-200 dark:border-gray-800/60 rounded-2xl overflow-hidden shadow-sm">

        <!-- Image Container -->
        <div
            class="relative w-full h-48 overflow-hidden bg-gray-100 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800/60">
            <img :src="ticket.image_url || '/no_image.jpg'" alt="Cover Laporan" class="w-full h-full object-cover" />
        </div>

        <!-- Card Content -->
        <div class="flex flex-col flex-1 p-5">

            <div class="flex justify-between">
                <!-- Badges -->
                <div class="flex flex-wrap gap-2 mb-4">
                    <span
                        :class="['inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-semibold tracking-wide rounded-md border', statusColor(ticket.status)]">
                        <CheckCircle v-if="ticket.status === 'resolved'" class="w-3.5 h-3.5" />
                        <Loader2 v-else-if="ticket.status === 'in_progress'" class="w-3.5 h-3.5 animate-spin" />
                        <AlertCircle v-else class="w-3.5 h-3.5" />
                        {{ ticket.status ? ticket.status.replace('_', ' ').toUpperCase() : 'OPEN' }}
                    </span>

                    <span
                        :class="['inline-flex items-center gap-1.5 px-2.5 py-1 text-[11px] font-semibold tracking-wide rounded-md border', priorityColor(ticket.priority)]">
                        <Flame v-if="ticket.priority === 'urgent'" class="w-3.5 h-3.5" />
                        <ArrowUpCircle v-else-if="ticket.priority === 'high'" class="w-3.5 h-3.5" />
                        <Clock v-else class="w-3.5 h-3.5" />
                        {{ ticket.priority.toUpperCase() }}
                    </span>
                </div>

                <div v-if="ticket.user && isAdmin" class="mb-4 flex items-center gap-2.5">
                    <div
                        class="w-7 h-7 rounded-full bg-indigo-50 dark:bg-indigo-900/20 flex items-center justify-center border border-indigo-100 dark:border-indigo-800/30">
                        <span class="text-[10px] font-extrabold text-indigo-600 dark:text-indigo-400">
                            {{ ticket.user.name.charAt(0).toUpperCase() }}
                        </span>
                    </div>
                    <div class="flex flex-col">
                        <span
                            class="text-[10px] uppercase font-bold text-gray-400 dark:text-gray-500 leading-none mb-0.5">Pelapor</span>
                        <span class="text-xs font-bold text-gray-700 dark:text-gray-300 truncate max-w-[120px]">
                            {{ ticket.user.name }}
                        </span>
                    </div>
                </div>
            </div>

            <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 line-clamp-1 mb-2 break-all">
                {{ ticket.title }}
            </h3>

            <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 flex-1 break-all">
                {{ ticket.description }}
            </p>

            <!-- Footer -->
            <div class="mt-5 pt-4 flex items-center justify-between border-t border-gray-100 dark:border-gray-800/60">
                <div class="flex flex-col">
                    <span
                        class="text-[10px] uppercase tracking-wider font-bold text-gray-400 dark:text-gray-500 mb-0.5">Dibuat
                        pada</span>
                    <span class="text-xs text-gray-700 dark:text-gray-300 font-medium">{{
                        formatDate(ticket.created_at) }}</span>
                </div>

                <div v-if="showActions !== false" class="flex items-center gap-2">
                    <TicketChatDialog :ticket="ticket" />
                    <slot name="actions" :ticket="ticket">
                        <TicketForm :ticket="ticket" />
                    </slot>
                </div>
            </div>
        </div>
    </div>
</template>