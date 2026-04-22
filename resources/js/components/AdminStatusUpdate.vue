<script setup lang="ts">
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import { Ticket } from '@/types/ticket';
import { update } from '@/routes/admin';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { Button } from '@/components/ui/button';
import { Settings2, Loader2 } from 'lucide-vue-next';

const props = defineProps<{
    ticket: Ticket;
}>();

const isOpen = ref(false);

const form = useForm({
    status: props.ticket.status,
});

const submit = () => {
    form.submit(update(props.ticket.id), {
        onSuccess: () => {
            isOpen.value = false;
        },
        preserveScroll: true,
    });
};
</script>

<template>
    <Dialog v-model:open="isOpen">
        <DialogTrigger as-child>
            <Button variant="outline" size="icon" class="h-9 w-9">
                <Settings2 class="h-4 w-4" />
            </Button>
        </DialogTrigger>
        <DialogContent class="sm:max-w-[425px]">
            <DialogHeader>
                <DialogTitle>Update Status</DialogTitle>
                <DialogDescription>
                    Ubah status pengerjaan untuk laporan ini.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="submit" class="grid gap-4 py-4">
                <div class="grid gap-2">
                    <Select v-model="form.status">
                        <SelectTrigger>
                            <SelectValue placeholder="Pilih Status" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="in_progress">In Progress</SelectItem>
                            <SelectItem value="resolved">Resolved</SelectItem>
                            <SelectItem value="rejected">Rejected</SelectItem>
                        </SelectContent>
                    </Select>
                </div>

                <DialogFooter>
                    <Button type="submit" :disabled="form.processing">
                        <Loader2 v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" />
                        Simpan Perubahan
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
