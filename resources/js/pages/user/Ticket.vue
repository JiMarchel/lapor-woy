<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { index } from '@/routes/tickets';
import TicketForm from '@/components/TicketForm.vue';
import TicketChatDialog from '@/components/TicketChatDialog.vue';
import Filters from '@/components/Filters.vue';
import { Ticket } from '@/types/ticket';
import { CheckCircle, Loader2, AlertCircle, Clock, ArrowUpCircle, Flame } from 'lucide-vue-next';
import {
    Pagination,
    PaginationContent,
    PaginationEllipsis,
    PaginationFirst,
    PaginationItem,
    PaginationLast,
    PaginationNext,
    PaginationPrevious,
} from '@/components/ui/pagination';
import { Button } from '@/components/ui/button';

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Tickets',
                href: index(),
            },
        ],
    },
});

const props = defineProps<{
    tickets: {
        data: Array<Ticket>;
        total: number;
        per_page: number;
        current_page: number;
    },
    filters?: {
        search?: string
        status?: string
        priority?: string
    }
}>();

const handlePageChange = (page: number) => {
    router.get(window.location.pathname, { 
        search: props.filters?.search,
        page: page 
    }, {
        preserveState: true,
        preserveScroll: true
    });
};

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

    <Head title="Tickets" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6 lg:p-8">

        <!-- Header Section -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-2">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Laporan Anda</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Pantau status laporan tiket yang telah Anda
                    buat.</p>
            </div>
            
            <div class="flex flex-col sm:flex-row w-full md:w-auto items-stretch sm:items-center gap-3 mt-2 md:mt-0">
                <Filters :initialFilters="filters" />
                <TicketForm />
            </div>
        </div>

        <!-- Empty State -->
        <div v-if="tickets.data.length === 0"
            class="flex flex-col items-center justify-center p-12 text-center border-2 border-dashed border-gray-200 dark:border-gray-800 rounded-2xl h-64">
            <div class="bg-gray-50 dark:bg-gray-800/50 p-4 rounded-full mb-4">
                <svg class="w-8 h-8 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
            </div>
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ filters?.search ? 'Tidak Ditemukan' : 'Belum ada laporan' }}
            </h3>
            
            <p v-if="filters?.search" class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-sm">
                Laporan dengan kata kunci "<span class="font-bold">{{ filters.search }}</span>" tidak ditemukan.
            </p>
            <p v-else class="text-sm text-gray-500 dark:text-gray-400 mt-1 max-w-sm">
                Anda belum membuat tiket laporan apapun. Klik tombol "Buat Laporan" di pojok kanan atas untuk memulai.
            </p>
        </div>

        <!-- Ticket Card Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-6">
            <div v-for="ticket in tickets.data" :key="ticket.id"
                class="group relative flex flex-col bg-white dark:bg-[#18181b] border border-gray-200 dark:border-gray-800/60 rounded-2xl overflow-hidden shadow-sm">

                <!-- Image Container -->
                <div
                    class="relative w-full h-48 overflow-hidden bg-gray-100 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-800/60">
                    <img :src="ticket.image_url || '/no_image.jpg'" alt="Cover Laporan"
                        class="w-full h-full object-cover" />
                </div>

                <!-- Card Content -->
                <div class="flex flex-col flex-1 p-5">

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

                    <h3 class="text-lg font-bold text-gray-900 dark:text-gray-100 line-clamp-1 mb-2 break-all">
                        {{ ticket.title }}
                    </h3>

                    <p class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 flex-1 break-all">
                        {{ ticket.description }}
                    </p>

                    <!-- Footer Data -->
                    <div
                        class="mt-5 pt-4 flex items-center justify-between border-t border-gray-100 dark:border-gray-800/60">
                        <div class="flex flex-col">
                            <span
                                class="text-[10px] uppercase tracking-wider font-bold text-gray-400 dark:text-gray-500 mb-0.5">Dibuat
                                pada</span>
                            <span class="text-xs text-gray-700 dark:text-gray-300 font-medium">{{
                                formatDate(ticket.created_at) }}</span>
                        </div>

                        <div class="flex items-center gap-2">
                            <TicketChatDialog :ticket="ticket" />
                            <TicketForm :ticket="ticket" />
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Pagination Section -->
        <div v-if="tickets.total > tickets.per_page" class="mt-8 mb-4">
            <Pagination
                v-slot="{ page }"
                :total="tickets.total"
                :sibling-count="1"
                show-edges
                :default-page="tickets.current_page"
                :items-per-page="tickets.per_page"
                @update:page="handlePageChange"
            >
                <PaginationContent v-slot="{ items }">
                    <PaginationFirst />
                    <PaginationPrevious />

                    <template v-for="(item, index) in items">
                        <PaginationItem v-if="item.type === 'page'" :key="index" :value="item.value" as-child>
                            <Button class="w-10 h-10 p-0" :variant="item.value === page ? 'default' : 'outline'">
                                {{ item.value }}
                            </Button>
                        </PaginationItem>
                        <PaginationEllipsis v-else :key="item.type" :index="index" />
                    </template>

                    <PaginationNext />
                    <PaginationLast />
                </PaginationContent>
            </Pagination>
        </div>

    </div>
</template>
