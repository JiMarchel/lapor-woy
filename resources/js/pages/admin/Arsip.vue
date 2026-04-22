<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { index } from '@/routes/admin';
import { dashboard } from '@/routes/admin';
import TicketCard from '@/components/TicketCard.vue';
import TicketForm from '@/components/TicketForm.vue';
import Filters from '@/components/Filters.vue';
import { Ticket } from '@/types/ticket';
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
                title: 'Dashboard',
                href: dashboard(),
            },
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
        status: props.filters?.status,
        priority: props.filters?.priority,
        page: page
    }, {
        preserveState: true,
        preserveScroll: true
    });
};
</script>

<template>

    <Head title="Admin - Kelola Tiket" />

    <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-4 md:p-6 lg:p-8">

        <!-- Header Section -->
        <div class="flex flex-col md:flex-row items-start md:items-center justify-between gap-4 pb-2">
            <div>
                <h1 class="text-2xl font-bold tracking-tight text-gray-900 dark:text-gray-100">Semua Laporan</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Kelola seluruh tiket laporan dari semua
                    pengguna.</p>
            </div>

            <div class="flex flex-col sm:flex-row w-full md:w-auto items-stretch sm:items-center gap-3 mt-2 md:mt-0">
                <Filters :initialFilters="filters" />
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
                Belum ada tiket laporan dari pengguna manapun.
            </p>
        </div>

        <!-- Ticket Card Grid -->
        <div v-else class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 gap-6">
            <TicketCard v-for="ticket in tickets.data" :key="ticket.id" :ticket="ticket" show-actions />
        </div>

        <!-- Pagination Section -->
        <div v-if="tickets.total > tickets.per_page" class="mt-8 mb-4">
            <Pagination v-slot="{ page }" :total="tickets.total" :sibling-count="1" show-edges
                :default-page="tickets.current_page" :items-per-page="tickets.per_page" @update:page="handlePageChange">
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