<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { Link } from '@inertiajs/vue3';

    defineProps({
        vehicles: Object,
    });

</script>

<template>
    <AppLayout title="Vehicles">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Vehicles
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
                                        Plate
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        VIN
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Billing Profile
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="vehicle in vehicles.data" :key="provider" class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                                    <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ vehicle.name }}
                                    </th>
                                    <td class="px-6 py-4">
                                        {{ vehicle.plate }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ vehicle.masked_vin }}
                                    </td>
                                    <td class="px-6 py-4">
                                        <Link class="font-medium text-blue-600 dark:text-blue-500 hover:underline" :href="route('billing-profiles.show', { billing_profile: vehicle.billing_profile.id })">{{ vehicle.billing_profile.name }}</Link>
                                    </td>
                                    <td class="px-6 py-4  text-right">
                                        <!-- <Link v-if="vehicles?.provider == provider && vehicles.can.delete" as="button" method="post" class="font-medium text-red-600 dark:text-red-500 hover:underline" :href="route('tesla-accounts.unlink', { provider: provider })">Unlink</Link>
                                        <Link v-else-if="!vehicles && can.link" class="font-medium text-green-600 dark:text-green-500 hover:underline" :href="route('tesla-accounts.link-form', { provider: provider})">Link</Link> -->
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
