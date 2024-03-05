<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { Link, useForm, usePage } from '@inertiajs/vue3';
    import ActionMessage from '@/Components/ActionMessage.vue';
    import FormSection from '@/Components/FormSection.vue';
    import InputError from '@/Components/InputError.vue';
    import InputHelp from '@/Components/InputHelp.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import SecondaryButton from '@/Components/SecondaryButton.vue';
    import TextInput from '@/Components/TextInput.vue';
    import { computed, ref } from 'vue';

    const form = useForm({
        token: '',
    })

    const vehicles = ref(null)

    const createModel = () => {
        form.post(route('tesla-accounts.link', {provider: 'Tessie'}))
    }

    const submitForm = () => {
        if(vehicles.value) {
            createModel();
        }
        else
        {
            getVehicles();
        }
    }

    const getVehicles = () => {
        form.post(route('tesla-accounts.get-vehicles', {provider: 'Tessie'}), {
            preserveScroll: true,
            onSuccess: () => {
                vehicles.value = usePage().props.jetstream.flash.vehicles;
            },
        });
    }

    const action = computed(() => {
        return vehicles.value ? 'Link' :  'Get Vehicles'
    })

</script>

<template>
    <AppLayout title="Link Tessie Account">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                <Link :href="route('tesla-accounts.index')" class="font-medium text-gray-600 dark:text-gray-500 hover:underline">Tesla Account API Provider</Link> / Link
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <FormSection @submitted="submitForm">
                    <template #title>
                        Link Tessie Account
                    </template>

                    <template #description>
                        Link your Tessie Account. Your account credentials and vehicle's confidential information are stored encrypted with AES-256 bit encryption.
                    </template>

                    <template #form>
                        <!-- Name -->
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="provider" value="Provider" />
                            <span class="text-sm text-gray-600 dark:text-gray-400">Tessie</span>
                        </div>

                        <!-- Email -->
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="token" value="API Token" />
                            <TextInput
                                id="token"
                                v-model="form.token"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                                @keyup.prevent="vehicles=null"
                            />
                            <InputHelp class="mt-2">This data is encrypted with AES-256 bit.</InputHelp>
                            <InputError :message="form.errors['config.token']" class="mt-2" />
                        </div>
                        <div class="col-span-6 sm:col-span-4" v-if="vehicles">
                            <InputLabel for="vehicles" value="Vehicles" />
                            <ol class="text-gray-600 dark:text-gray-400 list-decimal pl-4">
                                <li v-for="vehicle in vehicles" :key="vehicle.vin">{{ vehicle.display_name }} - {{ vehicle.plate }} / {{ vehicle.vin }}</li>
                            </ol>
                        </div>
                    </template>

                    <template #actions>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            {{ action }}
                        </PrimaryButton>
                    </template>
                </FormSection>
            </div>
        </div>
    </AppLayout>
</template>
