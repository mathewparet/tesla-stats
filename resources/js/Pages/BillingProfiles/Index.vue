<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { Link } from '@inertiajs/vue3';

    defineProps({
        billing_profiles: Object,
    });

</script>

<template>
    <AppLayout title="Billing Profiles">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Billing Profiles
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                    <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                        <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                            <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                <tr>
                                    <th scope="col" class="px-6 py-3">
                                        Name
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Billing Day
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Activated On
                                    </th>
                                    <th scope="col" class="px-6 py-">
                                        Deactivated On
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="billing_profile in billing_profiles.data" :key="provider" class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ billing_profile.name }}
                                    </th>
                                    <td class="px-6 py-4">
                                        {{ billing_profile.bill_day }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ billing_profile.activated_on }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ billing_profile.deactivated_on }}
                                    </td>
                                    <td class="px-6 py-4  text-right">
                                        <Link v-if="billing_profile.can.update" class="ml-2 font-medium text-blue-600 dark:text-blue-500 hover:underline" :href="route('billing-profiles.edit', { billing_profile: billing_profile.id })">Update</Link>
                                        <Link v-if="billing_profile.can.delete" as="button" method="delete" class="ml-2 font-medium text-red-600 dark:text-red-500 hover:underline" :href="route('billing-profiles.destroy', { billing_profile: billing_profile.id })">Delete</Link>
                                    </td>
                                </tr>
                                <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                                    <td v-if="billing_profiles.data.length === 0" colspan="5" class="px-6 py-4 text-center">
                                        You do not have any billing profiles, would you like to <Link v-if="billing_profiles.can.create" class="font-medium text-blue-600 dark:text-blue-500 hover:underline" :href="route('billing-profiles.create')">create one</Link>?
                                    </td>
                                    <td v-else colspan="5" class="px-6 py-4 text-center">
                                        Would you like to <Link v-if="billing_profiles.can.create" class="font-medium text-blue-600 dark:text-blue-500 hover:underline" :href="route('billing-profiles.create')">create another</Link> one?
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
