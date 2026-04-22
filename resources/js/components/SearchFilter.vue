<script setup lang="ts">
import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';
import { Input } from '@/components/ui/input';
import { Search } from 'lucide-vue-next';

const props = defineProps<{
    initialSearch?: string;
}>();

const search = ref(props.initialSearch || '');
let timeout: ReturnType<typeof setTimeout>;

watch(search, (value) => {
    clearTimeout(timeout);
    
    timeout = setTimeout(() => {
        router.get(window.location.pathname, { search: value }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 500);
});
</script>

<template>
    <div class="relative w-full sm:w-64 lg:w-80">
        <div class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
            <Search class="w-4 h-4 text-gray-500 dark:text-gray-400" />
        </div>
        <Input 
            v-model="search" 
            type="text" 
            placeholder="Cari judul atau deskripsi..." 
            class="pl-10 w-full bg-white dark:bg-[#18181b]"
        />
    </div>
</template>
