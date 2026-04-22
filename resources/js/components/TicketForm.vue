<script setup lang="ts">
import { Button } from './ui/button';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
    DialogClose
} from '@/components/ui/dialog'
import { store, update } from '@/routes/tickets';
import { useForm } from '@inertiajs/vue3';
import { Label } from './ui/label';
import { Input } from './ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from './ui/select';
import { Textarea } from './ui/textarea';
import { ref } from 'vue';
import { Ticket } from '@/types/ticket';
import { PenLine, Plus } from 'lucide-vue-next';

const props = defineProps<{
    ticket?: Ticket;
}>()

const form = useForm({
    title: props.ticket?.title || "",
    description: props.ticket?.description || "",
    priority: props.ticket?.priority || "",
    image_url: null as File | null,
})
const fileInputKey = ref(0);

const handleFileChange = (event: Event) => {
    const file = (event.target as HTMLInputElement).files?.[0];
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            form.setError('image_url', 'Ukuran gambar luar biasa besar. Maksimal 2MB!');
            form.image_url = null;
            (event.target as HTMLInputElement).value = '';
        } else {
            form.clearErrors('image_url');
            form.image_url = file;
        }
    } else {
        form.image_url = null;
    }
};



</script>

<template>
    <Dialog>
        <DialogTrigger as-child>
            <Button :variant="ticket ? 'secondary' : 'default'" :size="ticket ? 'icon' : 'default'">
                {{ ticket ? '' : 'Buat Laporan' }}
                <PenLine v-if="ticket" class="h-4 w-4" />
                <Plus v-else class="h-4 w-4" />
            </Button>
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ ticket ? 'Edit Laporan' : 'Buat Laporan' }}</DialogTitle>
                <DialogDescription>
                    Silahkan isi form di bawah ini untuk {{ ticket ? 'mengedit' : 'membuat' }} laporan.
                </DialogDescription>
            </DialogHeader>

            <form @submit.prevent="form.submit(ticket ? update(ticket.id) : store(), {
                onSuccess: () => {
                    form.reset();
                    fileInputKey++;
                }
            })">
                <div class="grid gap-2 py-2">
                    <Label for="title">Title</Label>
                    <Input id="title" placeholder="Judul Laporan" type="text" v-model="form.title" />
                    <div v-if="form.errors.title">{{ form.errors.title }}</div>
                </div>
                <div class="grid gap-2 py-2">
                    <Label for="description">Description</Label>
                    <Textarea id="description" placeholder="Deskripsi Laporan" v-model="form.description" />
                    <div v-if="form.errors.description">{{ form.errors.description }}</div>
                </div>
                <div class="grid gap-2 py-2">
                    <Label for="image_url">Gambar Pendukung</Label>
                    <Input :key="fileInputKey" id="image_url" type="file" accept="image/*" @input="handleFileChange" />
                    <div v-if="form.errors.image_url" class="text-sm text-red-500">{{ form.errors.image_url }}</div>
                </div>
                <div class="grid gap-2 py-2">
                    <Label for="priority">Priority</Label>
                    <Select v-model="form.priority">
                        <SelectTrigger class="w-full">
                            <SelectValue placeholder="Select a priority" />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem value="low">
                                Low
                            </SelectItem>
                            <SelectItem value="medium">
                                Medium
                            </SelectItem>
                            <SelectItem value="high">
                                High
                            </SelectItem>
                            <SelectItem value="urgent">
                                Urgent
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <div v-if="form.errors.priority">{{ form.errors.priority }}</div>
                </div>

                <DialogFooter class="mt-6">
                    <DialogClose as-child>
                        <Button variant="destructive" :disabled="form.processing">Cancel</Button>
                    </DialogClose>
                    <Button :disabled="form.processing">{{ form.processing ? 'Submitting...' : 'Submit' }}</Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>