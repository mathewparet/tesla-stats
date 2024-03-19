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

    const reverseCharges = ref([])

    onMounted(() => {
        reverseCharges.value = props.charges.data.reverse();
    })

    const chartCharges = computed(() => {
        const labels = reverseCharges.value.map((charge) => DateTime.fromISO(charge.started_at).toLocaleString(DateTime.DATE_MED));

        return {
            labels: labels,
            datasets: [
                {
                    label: 'Cost',
                    backgroundColor: '#6775F5',
                    data: reverseCharges.value.map((charge) => charge.cost.toFixed(2))
                },
            ]
        }
    })
    
    const chartEnergy = computed(() => {
        const labels = reverseCharges.value.map((charge) => DateTime.fromISO(charge.started_at).toLocaleString(DateTime.DATE_MED));

        return {
            labels,
            datasets: [
                {
                    label: 'Energy',
                    backgroundColor: '#6775F5',
                    data: reverseCharges.value.map((charge) => charge.energy_consumed.toFixed(2))
                }
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
                <div class="grid grid-cols-2 gap-4 justify-between mb-4">
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        <LineChart key="charge" :data="chartCharges" :options="{responsive: true, maintainAspectRatio: true, plugins: {tooltip: {callbacks: { label: (context) => bill.billing_profile.currency +' '+context.formattedValue}}}, scales: {y: {title:{ display: true, text: 'Cost in '+bill.billing_profile.currency}, ticks: {stepSize: 1}}}}" />
                    </div>
                    <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                        <LineChart key="energy" :data="chartEnergy" :options="{responsive: true, maintainAspectRatio: true, plugins: {tooltip: {callbacks: { label: (context) => context.formattedValue + ' kWh'}}}, scales: { y: {title:{ display: true, text: 'kWh'}, ticks: {stepSize: 5}}}}" />
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
                                <tr v-for="charge in charges.data" :key="provider" class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
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
