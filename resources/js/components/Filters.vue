<script setup lang="ts">
import { reactive, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Search } from 'lucide-vue-next';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from './ui/select';

const props = defineProps<{
    initialFilters?: {
        search?: string;
        status?: string;
        priority?: string;
    }
}>();

let timeout: ReturnType<typeof setTimeout>;
const filters = reactive({
    search: props.initialFilters?.search || '',
    status: props.initialFilters?.status || '',
    priority: props.initialFilters?.priority || '',
})

watch(filters, (newFilters) => {
    clearTimeout(timeout);

    timeout = setTimeout(() => {
        router.get(window.location.pathname, { search: newFilters.search, status: newFilters.status, priority: newFilters.priority }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 500);
}, { deep: true });
</script>

<template>
    <div class="relative w-full sm:w-64 lg:w-80">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <Search class="w-4 h-4 text-gray-500 dark:text-gray-400" />
        </div>
        <Input v-model="filters.search" type="text" placeholder="Cari judul atau deskripsi..."
            class="pl-10 w-full bg-white dark:bg-[#18181b]" />
    </div>
    <div class="flex gap-2">
        <Select v-model="filters.status" placeholder="Status">
            <SelectTrigger>
                <SelectValue placeholder="Status" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="*">Semua</SelectItem>
                <SelectItem value="open">Open</SelectItem>
                <SelectItem value="in_progress">In Progress</SelectItem>
                <SelectItem value="resolved">Resolved</SelectItem>
                <SelectItem value="rejected">Rejected</SelectItem>
            </SelectContent>
        </Select>
        <Select v-model="filters.priority" placeholder="Prioritas">
            <SelectTrigger>
                <SelectValue placeholder="Prioritas" />
            </SelectTrigger>
            <SelectContent>
                <SelectItem value="*">Semua</SelectItem>
                <SelectItem value="low">Low</SelectItem>
                <SelectItem value="medium">Medium</SelectItem>
                <SelectItem value="high">High</SelectItem>
                <SelectItem value="urgent">Urgent</SelectItem>
            </SelectContent>
        </Select>
    </div>
</template>
