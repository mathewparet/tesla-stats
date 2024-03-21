<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { Link, useForm } from '@inertiajs/vue3';
    import BillingProfilesPopup from '@/Pages/BillingProfiles/Popup.vue';
    import { ref } from 'vue';
    import ActionConfirmation from '@/Components/ActionConfirmation.vue';

    const profileSelector = ref(null);

    const archiveConfirmation = ref(null)

    defineProps({
        vehicles: Object,
    });

    const form = useForm({});

    const archiveVehicle = (vehicle) => {
        form.delete(route('vehicles.destroy', { vehicle: vehicle.hash_id }), {
            preserveScroll: true,
            onSuccess: () => {
                if(archiveConfirmation.value)
                    archiveConfirmation.value.show = false;
            },
        });
    }

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
                                        Billing Profiles
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
                                        {{ vehicle.billing_profiles.map((billing_profile) => billing_profile.name).join(', ') }}
                                        <span v-if="vehicle.billing_profiles.length"> / </span>
                                        <button class="font-medium hover:underline text-green-600 dark:text-green-500" @click.prevent="() => profileSelector.show(vehicle)">
                                            <span v-if="!vehicle.billing_profiles.length">Link</span>
                                            <span v-else>Change</span>
                                        </button>
                                    </td>
                                    <td class="px-6 py-4  text-right">
                                        <ActionConfirmation 
                                            ref="archiveConfirmation"
                                            confirmation-label="Archive Billing Profile"
                                            title="Archive Billing Profile"
                                            type="danger"
                                            :key="vehicle.hash_id"
                                            :processing="form.processing"
                                            v-if="vehicle.can.delete"
                                            :confirmation-code="vehicle.name"
                                            require-confirmation
                                            @confirmed="archiveVehicle(vehicle)"
                                        >
                                            <template #message>
                                                If you continue, you will no longer see usages for this profile. Are you sure you want to archive this billing profile?
                                            </template>
                                            <span class="cursor-pointer ml-2 font-medium text-red-600 dark:text-red-500 hover:underline">Archive</span>
                                        </ActionConfirmation>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AppLayout>
    <BillingProfilesPopup ref="profileSelector"/>
</template>
