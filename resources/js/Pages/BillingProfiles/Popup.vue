<script setup>
    import DialogModal from '@/Components/DialogModal.vue';
    import { useForm, usePage } from '@inertiajs/vue3';
    import { computed, ref } from 'vue';
    import { Link } from '@inertiajs/vue3';
    import InputLabel from '@/Components/InputLabel.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import SecondaryButton from '@/Components/SecondaryButton.vue';

    const billingProfiles = ref(null);

    const showModal = ref(false);

    const currentSelection = ref(null);

    const currentVehicle = ref(null);

    defineProps({
        modelValue: String,
    });

    const form = useForm({
        vehicle_id: null
    });

    const linkVehicle = () => {
        form.post(route('billing-profiles.link', {billingProfile: currentSelection.value}), {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
            }
        })
    }

    defineEmits(['update:modelValue']);

    defineExpose({ 
        show: (vehicle) => form.get(route('billing-profiles.list'), {
                                preserveScroll: true,
                                preserveState: true,
                                onSuccess: () => {
                                    billingProfiles.value = usePage().props.jetstream.flash.billing_profiles;
                                    currentSelection.value = vehicle.billing_profile?.id;
                                    currentVehicle.value = vehicle;
                                    form.vehicle_id = vehicle.id;
                                    showModal.value = true;
                                }
                            }),
    });

    const canCreateNewProfile = computed(() => currentVehicle.value.can.update && billingProfiles.value.can.create);

</script>
<template>
    <DialogModal :show="showModal" @close="showModal = false">
        <template #title>Select Billing Profile</template>
        <template #content>
            <div>
                <p class="mb-2 text-sm text-gray-600 dark:text-gray-400">
                    <InputLabel value="Vehicle" />
                    <div>
                        {{ currentVehicle.name }} - {{ currentVehicle.plate }} / {{ currentVehicle.masked_vin }}
                    </div>
                </p>
            </div>
            <div class="col-span-6 lg:col-span-4">
                <InputLabel for="billing_profile" value="Billing Profile" />

                <div class="relative z-0 mt-1 border border-gray-200 dark:border-gray-700 rounded-lg cursor-pointer">
                    <button
                        v-for="(billingProfile, i) in billingProfiles.data"
                        :key="billingProfile.id"
                        type="button"
                        class="relative px-4 py-3 inline-flex w-full rounded-lg focus:z-10 focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600"
                        :class="{'border-t border-gray-200 dark:border-gray-700 focus:border-none rounded-t-none rounded-b-none': i > 0 && canCreateNewProfile, 'border-t border-gray-200 dark:border-gray-700 focus:border-none rounded-t-none': i > 0 && !canCreateNewProfile , 'rounded-b-none': i != Object.keys(billingProfiles).length - 1}"
                        @click="currentSelection = billingProfile.id"
                    >
                        <div :class="{'opacity-50': billingProfile.id != currentSelection}">
                            <div class="flex items-center">
                                <div class="text-sm text-gray-600 dark:text-gray-400" :class="{'font-semibold': billingProfile.id == currentSelection}">
                                    {{ billingProfile.name }}
                                </div>

                                <svg v-if="billingProfile.id == currentSelection" class="ms-2 h-5 w-5 text-green-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>

                            <div class="mt-2 text-xs text-gray-600 dark:text-gray-400 text-start">
                                Billed on day {{ billingProfile.bill_day }} every month when charging within a radius of {{ billingProfile.radius }} meters of {{ billingProfile.address }}
                            </div>
                        </div>
                    </button>
                    <Link
                        v-if="canCreateNewProfile"
                        class="border-t border-gray-200 dark:border-gray-700 focus:border-none rounded-t-none relative px-4 py-3 inline-flex w-full rounded-lg focus:z-10 focus:outline-none focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-2 focus:ring-indigo-500 dark:focus:ring-indigo-600"
                        :href="route('billing-profiles.create', {vehicle_id: currentVehicle.id, returnTo: route(route().current())})"
                    >
                        <div class="opacity-50">
                            <div class="flex items-center">
                                <div class="text-sm text-orange-600 dark:text-orange-400 font-semibold">
                                    New
                                </div>
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="ms-2 w-6 h-6 text-yellow-600">
                                    <path fill-rule="evenodd" d="M12 2.25c-5.385 0-9.75 4.365-9.75 9.75s4.365 9.75 9.75 9.75 9.75-4.365 9.75-9.75S17.385 2.25 12 2.25ZM12.75 9a.75.75 0 0 0-1.5 0v2.25H9a.75.75 0 0 0 0 1.5h2.25V15a.75.75 0 0 0 1.5 0v-2.25H15a.75.75 0 0 0 0-1.5h-2.25V9Z" clip-rule="evenodd" />
                                </svg>

                            </div>

                            <div class="mt-2 text-xs text-gray-900 dark:text-gray-400 text-start">
                                Create a new Billing Profile
                            </div>
                        </div>
                    </Link>
                </div>
            </div>
        </template>
        <template #footer>
            <SecondaryButton @click="showModal = false" class="mr-2" :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                Close
            </SecondaryButton>
            <PrimaryButton @click="linkVehicle" :class="{ 'opacity-25': form.processing || !currentSelection }" :disabled="form.processing || !currentSelection">
                Link
            </PrimaryButton>
        </template>
    </DialogModal>
</template>