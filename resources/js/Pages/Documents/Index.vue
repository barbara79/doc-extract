<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

defineProps({
    documents: Array,
});

const form = useForm({
    file: null,
    document_type: 'invoice',
});

function submit() {
    form.post(route('documents.store'), {
        onSuccess: () => form.reset(),
    });
}

function handleFileChange(event) {
    form.file = event.target.files[0];
}
</script>

<template>
    <Head title="Documents" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Documents</h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <!-- Upload form -->
                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <form @submit.prevent="submit" class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Document Type</label>
                            <select v-model="form.document_type" class="mt-1 block w-full rounded-md border-gray-300">
                                <option value="invoice">Invoice</option>
                                <option value="contract">Contract</option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700">PDF File</label>
                            <input type="file" accept=".pdf" @change="handleFileChange"
                                class="mt-1 block w-full" />
                            <div v-if="form.errors.file" class="text-red-600 text-sm mt-1">
                                {{ form.errors.file }}
                            </div>
                        </div>

                        <button type="submit" :disabled="form.processing"
                            class="bg-indigo-600 text-white px-4 py-2 rounded-md disabled:opacity-50">
                            {{ form.processing ? 'Uploading...' : 'Upload' }}
                        </button>
                    </form>
                </div>

                <!-- Document list -->
                <div class="bg-white shadow sm:rounded-lg divide-y">
                    <div v-if="documents.length === 0" class="p-6 text-gray-500">
                        No documents uploaded yet.
                    </div>
                    <Link v-for="doc in documents" :key="doc.id"
                        :href="route('documents.show', doc.id)"
                        class="block p-4 hover:bg-gray-50">
                        <div class="flex justify-between items-center">
                            <div>
                                <p class="font-medium">{{ doc.original_filename }}</p>
                                <p class="text-sm text-gray-500">{{ doc.document_type }}</p>
                            </div>
                                <span class="text-xs px-2 py-1 rounded-full"
                                    :class="{
                                        'bg-yellow-100 text-yellow-800': doc.status === 'received',
                                        'bg-blue-100 text-blue-800': doc.status === 'extracted',
                                        'bg-green-100 text-green-800': doc.status === 'validated',
                                        'bg-red-100 text-red-800': doc.status === 'needs_review',
                                        'bg-emerald-100 text-emerald-800': doc.status === 'approved',
                                    }">
                                    {{ doc.status }}
                                </span>
                        </div>
                    </Link>
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>