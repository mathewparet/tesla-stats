<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { Link } from '@inertiajs/vue3';
    import { DateTime } from 'luxon';
    import { computed, ref } from 'vue';
    import LineChart from '@/Components/LineChart.vue';
    import Paginator from '@/Components/Paginator.vue';


    const props = defineProps({
        bills: Object,
    });

    const chartCharges = computed(() => {
        const currentDate = DateTime.now();
        const twelveMonthsAgo = currentDate.minus({ months: 12 });

        const lastTwelveMonthsData = props.bills.data.filter((bill) => {
            const billDate = DateTime.fromISO(bill.from);
            return billDate >= twelveMonthsAgo && billDate <= currentDate;
        });

        const reversedData = lastTwelveMonthsData.slice().reverse(); // Reverse the data array

        const labels = reversedData.map((bill) => DateTime.fromISO(bill.from).monthShort + '-' + DateTime.fromISO(bill.to).monthShort + ' \'' + DateTime.fromISO(bill.to).year);

        return {
            labels,
            datasets: [
                {
                    label: 'Cost',
                    backgroundColor: '#6775F5',
                    data: reversedData.map((bill) => bill.total_cost.toFixed(2))
                },
            ]
        }
    })

    const chartEnergy = computed(() => {
        const currentDate = DateTime.now();
        const twelveMonthsAgo = currentDate.minus({ months: 12 });

        const lastTwelveMonthsData = props.bills.data.filter((bill) => {
            const billDate = DateTime.fromISO(bill.from);
            return billDate >= twelveMonthsAgo && billDate <= currentDate;
        });

        const reversedData = lastTwelveMonthsData.slice().reverse(); // Reverse the data array

        const labels = reversedData.map((bill) => DateTime.fromISO(bill.from).monthShort + '-' + DateTime.fromISO(bill.to).monthShort + ' \'' + DateTime.fromISO(bill.to).year);

        return {
            labels,
            datasets: [
                {
                    label: 'Energy',
                    backgroundColor: '#679900',
                    data: reversedData.map((bill) => bill.energy_consumed.toFixed(2))
                }
            ]
        }
    })
</script>

<template>
    <AppLayout title="Usage">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Usage
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 gap-4 justify-between mb-4">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        <LineChart key="charge" :data="chartCharges" :options="{responsive: true, maintainAspectRatio: true}" />
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        <LineChart key="energy" :data="chartEnergy" :options="{responsive: true, maintainAspectRatio: true}" />
                    </div>
                </div>
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
                                <tr v-for="(bill, i) in bills.data" :key="provider" class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                                    <td scope="row" :class="{'text-gray-400 dark:text-gray-500': i==0, 'text-gray-900 dark:text-white': i!=0, 'bg-indigo-200': i==1}" class="px-6 py-4 font-medium  whitespace-nowrap">
                                        {{ DateTime.fromISO(bill.from).toLocaleString(DateTime.DATE_MED) }}
                                    </td>
                                    <td class="px-6 py-4" :class="{'text-gray-400 dark:text-gray-500': i==0, 'text-gray-900 dark:text-white': i!=0, 'bg-indigo-200': i==1}">
                                        {{DateTime.fromISO(bill.to).toLocaleString(DateTime.DATE_MED)}}
                                    </td>
                                    <td class="px-6 py-4" :class="{'text-gray-400 dark:text-gray-500': i==0, 'text-gray-900 dark:text-white': i!=0, 'bg-indigo-200': i==1}">
                                        {{ bill.billing_profile.name }}
                                    </td>
                                    <td class="px-6 py-4  text-right" :class="{'text-gray-400 dark:text-gray-500': i==0, 'text-gray-900 dark:text-white': i!=0, 'bg-indigo-200': i==1}">
                                        {{ bill.billing_profile.currency }} {{ bill.total_cost.toFixed(2) }}
                                    </td>
                                    <td class="px-6 py-4  text-right" :class="{'text-gray-400 dark:text-gray-500': i==0, 'text-gray-900 dark:text-white': i!=0, 'bg-indigo-200': i==1}">
                                        {{ bill.energy_consumed.toFixed(2) }} kWh
                                    </td>
                                    <td class="px-6 py-4  text-right" :class="{'text-gray-400 dark:text-gray-500': i==0, 'text-gray-900 dark:text-white': i!=0, 'bg-indigo-200': i==1}">
                                        <Link v-if="bill.can.view" :class="{'text-gray-400 dark:text-gray-500': i==0, 'text-blue-600 dark:text-blue-500': i, 'bg-indigo-200': i==1!=0}" class="ml-2 font-medium text-blue-600 dark:text-blue-500 hover:underline" :href="route('bills.show', { bill: bill.hash_id })">Details</Link>
                                    </td>
                                </tr>
                                <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                                    <td v-if="bills.data.length === 0" colspan="6" class="px-6 py-4 text-center">
                                        You do not have any bills generated yet!
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <Paginator :links="bills.meta.links" />
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
