<script setup lang="ts">
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import DeleteUserForm from './Partials/DeleteUserForm.vue';
import UpdatePasswordForm from './Partials/UpdatePasswordForm.vue';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm.vue';
import { Head } from '@inertiajs/vue3';

defineProps<{
    mustVerifyEmail?: boolean;
    status?: string;
}>();
</script>

<template>
    <Head title="Profile" />

    <AuthenticatedLayout>
        <template #header>
            <div>
                <h2 class="text-xl font-bold text-gray-900 dark:text-white">Profile</h2>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-0.5">Kelola informasi profil dan keamanan akun Anda</p>
            </div>
        </template>

        <div class="py-6 space-y-6">
            <div class="mx-4 sm:mx-6 lg:mx-8 bg-surface-warm dark:bg-gray-800 rounded-2xl border border-border-warm shadow-sm p-6">
                <UpdateProfileInformationForm
                    :must-verify-email="mustVerifyEmail"
                    :status="status"
                    class="max-w-xl"
                />
            </div>

            <div class="mx-4 sm:mx-6 lg:mx-8 bg-surface-warm dark:bg-gray-800 rounded-2xl border border-border-warm shadow-sm p-6">
                <UpdatePasswordForm class="max-w-xl" />
            </div>

            <div class="mx-4 sm:mx-6 lg:mx-8 bg-surface-warm dark:bg-gray-800 rounded-2xl border border-border-warm shadow-sm p-6">
                <DeleteUserForm class="max-w-xl" />
            </div>
        </div>
    </AuthenticatedLayout>
</template>
