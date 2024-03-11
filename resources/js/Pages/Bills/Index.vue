<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { Link } from '@inertiajs/vue3';
    import { DateTime } from 'luxon';

    defineProps({
        bills: Object,
    });

</script>

<template>
    <AppLayout title="Bills">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Bills
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
                                        From
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        To
                                    </th>
                                    <th scope="col" class="px-6 py-3">
                                        Profile
                                    </th>
                                    <th scope="col" class="px-6 py-3  text-right">
                                        Total Amount
                                    </th>
                                    <th scope="col" class="px-6 py-3  text-right">
                                        Total Energy
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right">
                                        Action
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="bill in bills.data" :key="provider" class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                                    <td scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ DateTime.fromISO(bill.from).toLocaleString(DateTime.DATE_MED) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{DateTime.fromISO(bill.to).toLocaleString(DateTime.DATE_MED)}}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ bill.billing_profile.name }}
                                    </td>
                                    <td class="px-6 py-4  text-right">
                                        {{ bill.billing_profile.currency }} {{ bill.total_cost.toFixed(2) }}
                                    </td>
                                    <td class="px-6 py-4  text-right">
                                        {{ bill.energy_consumed.toFixed(2) }} kWh
                                    </td>
                                    <td class="px-6 py-4  text-right">
                                        <Link v-if="bill.can.view" class="ml-2 font-medium text-blue-600 dark:text-blue-500 hover:underline" :href="route('bills.show', { bill: bill.id })">View</Link>
                                    </td>
                                </tr>
                                <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                                    <td v-if="bills.data.length === 0" colspan="6" class="px-6 py-4 text-center">
                                        You do not have any bills generated yet!
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
