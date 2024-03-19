<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { Link } from '@inertiajs/vue3';
    import { DateTime } from 'luxon';
    import Card from '@/Components/Card.vue';
    import LineChart from '@/Components/LineChart.vue';
    import { computed, onMounted, ref } from 'vue';

    const props = defineProps({
        bill: Object,
        charges: Array,
        isCurrent: Boolean,
        isLatest: Boolean,
    });

    /**
     * Calculate daily totals based on the provided data.
     *
     * @param {Array} data - The data to calculate totals from.
     *     Each item in the array should have the following properties:
     *     - started_at: The start date and time of the item (ISO 8601 formatted string).
     *     - cost: The cost of the item (string representing a number).
     *     - energy_consumed: The energy consumed of the item (string representing a number).
     * @return {Array} An array of objects containing the calculated daily totals.
     *     Each object in the array has the following properties:
     *     - date: The date of the daily total (formatted as a medium-length date).
     *     - totalCost: The total cost for the day (number).
     *     - totalEnergy: The total energy consumed for the day (number).
     */
    const calculateDailyTotals = (data) => {
        // Reduce the data array into an object, grouping items by date.
        // For each item, add its cost and energy consumed to the corresponding date's total.
        const totalsByDate = data.reduce((acc, item) => {
            const date = DateTime.fromISO(item.started_at).toLocaleString(DateTime.DATE_MED);
            
            // If the date hasn't been encountered before, initialize its totals to 0.
            if (!acc[date]) {
                acc[date] = { date, totalCost: 0, totalEnergy: 0 };
            }
            
            // Add the item's cost and energy consumed to the date's totals.
            acc[date].totalCost += parseFloat(item.cost);
            acc[date].totalEnergy += parseFloat(item.energy_consumed);
            
            return acc;
        }, {});
        
        // Return the array of daily totals, extracting the values from the object.
        return Object.values(totalsByDate);
    }

    const chartCharges = computed(() => {
        const data = calculateDailyTotals(props.charges.data);

        const reversedData = data.slice().reverse(); // Reverse the data array

        return {
            labels: reversedData.map((item) => item.date),
            datasets: [
                {
                    label: 'Cost',
                    backgroundColor: '#6775F5',
                    data: reversedData.map((item) => item.totalCost.toFixed(2))
                },
                {
                    label: 'Energy',
                    backgroundColor: '#679900',
                    data: reversedData.map((item) => item.totalEnergy.toFixed(2))
                },
            ]
        }
    })
</script>

<template>
    <AppLayout title="Usage">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                <Link :href="route('bills.index')" class="font-medium text-gray-400 dark:text-gray-500 hover:underline">Usage</Link> / {{ DateTime.fromISO(bill.from).toLocaleString(DateTime.DATE_MED) }} - {{ DateTime.fromISO(bill.to).toLocaleString(DateTime.DATE_MED) }} ({{ bill.billing_profile.name }}) <span v-if="isCurrent" class="bg-red-100 text-red-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-red-900 dark:text-red-300">Not Finalized Yet</span><span v-if="isLatest" class="bg-indigo-100 text-indigo-800 text-xs font-medium me-2 px-2.5 py-0.5 rounded dark:bg-indigo-900 dark:text-indigo-300">Latest Bill</span>
            </h2>
        </template>

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <div class="mb-4 flex justify-between gap-4">
                    <div class="grow">
                        <Card title="From">{{ DateTime.fromISO(bill.from).toLocaleString(DateTime.DATE_MED) }}</Card>
                    </div>
                    <div class="grow">
                        <Card title="To">{{ DateTime.fromISO(bill.to).toLocaleString(DateTime.DATE_MED) }}</Card>
                    </div>
                    <div class="grow">
                        <Card title="Total Usage">{{ bill.energy_consumed.toFixed(2) }} kWh</Card>
                    </div>
                    <div class="grow">
                        <Card title="Total Cost">{{ bill.billing_profile.currency }} {{ bill.total_cost.toFixed(2) }}</Card>
                    </div>
                </div>
                <div class="grid grid-cols-1 gap-4 justify-between mb-4">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        <LineChart key="charge" :data="chartCharges" :options="{responsive: true, maintainAspectRatio: true, scales: {y: { ticks: {stepSize: 1}}}}" />
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
                                        Vehiicle
                                    </th>
                                    <th scope="col" class="px-6 py-3  text-right">
                                        Energy Consumed
                                    </th>
                                    <th scope="col" class="px-6 py-3  text-right">
                                        Cost
                                    </th>
                                    <th scope="col" class="px-6 py-3 text-right">
                                        Battery
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="(charge, index) in charges.data" :key="provider" class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                                    <td scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                        {{ DateTime.fromISO(charge.started_at).toLocaleString(DateTime.DATETIME_MED) }}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ DateTime.fromISO(charge.ended_at).toLocaleString(DateTime.DATETIME_MED)}}
                                    </td>
                                    <td class="px-6 py-4">
                                        {{ charge.vehicle.name }} / {{ charge.vehicle.plate }}
                                    </td>
                                    <td class="px-6 py-4  text-right">
                                        {{ charge.energy_consumed.toFixed(2) }} kWh
                                    </td>
                                    <td class="px-6 py-4  text-right">
                                        {{ bill.billing_profile.currency }} {{ charge.cost.toFixed(2) }}
                                    </td>
                                    <td class="px-6 py-4  text-right">
                                        {{ charge.starting_battery }}% to {{ charge.ending_battery }}%
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
