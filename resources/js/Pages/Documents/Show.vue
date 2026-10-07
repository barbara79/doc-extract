<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    document: Object,
});
</script>

<template>
    <Head :title="document.original_filename" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                {{ document.original_filename }}
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

                <div class="bg-white p-6 shadow sm:rounded-lg">
                    <p class="text-sm text-gray-500">Status: <span class="font-medium">{{ document.status }}</span></p>
                    <p class="text-sm text-gray-500">Type: {{ document.document_type }}</p>
                </div>

                <div v-if="document.latest_extraction" class="bg-white p-6 shadow sm:rounded-lg">
                    <h3 class="font-medium mb-3">Extracted Data</h3>
                    <pre class="bg-gray-50 p-4 rounded text-sm overflow-x-auto">{{
                        JSON.stringify(document.latest_extraction.extracted_fields, null, 2)
                    }}</pre>
                </div>

                <div v-else class="bg-white p-6 shadow sm:rounded-lg text-gray-500">
                    Still processing — refresh in a moment.
                </div>

            </div>
        </div>
    </AuthenticatedLayout>
</template>