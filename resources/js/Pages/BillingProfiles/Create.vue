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
    import SelectInput from '@/Components/SelectInput.vue';
    import { computed, ref } from 'vue';

    const props = defineProps({
        billingProfile: Object,
        vehicles: Array,
        editMode: Boolean,
        timeZones: Array,
    });

    const form = useForm({
        name: props.billingProfile?.name || '',
        timezone: props.billingProfile?.timezone || Intl.DateTimeFormat().resolvedOptions().timeZone,
        bill_day: props.billingProfile?.bill_day || '',
        activated_on: props.billingProfile?.activated_on || '',
        deactivated_on: props.billingProfile?.deactivated_on || '',
    })

    const vehicles = ref(null)

    const createModel = () => {
        form.post(route('billing-profiles.store'), {
            preserveScroll: true,
        })
    }

    const updateModel = () => {
        form.put(route('billing-profiles.update', {billing_profile: props.billingProfile.id}), {
            preserveScroll: true,
        })
    }

    const submitForm = () => props.editMode ? updateModel() : createModel()

    const action = computed(() => props.editMode ? 'Update' : 'Create')

    const title = computed(() => props.editMode ? 'Edit Billing Profile' : 'Create Billing Profile')

</script>

<template>
    <AppLayout :title="title">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                <Link :href="route('billing-profiles.index')" class="font-medium text-gray-600 dark:text-gray-500 hover:underline">Billing Profiles</Link> <span class="font-medium text-gray-600 dark:text-gray-500" v-if="billingProfile?.id"> / <Link :href="route('billing-profiles.show', {billing_profile: billingProfile.id})" class="font-medium text-gray-600 dark:text-gray-500 hover:underline">{{ billingProfile.name }}</Link></span> / {{ action }}
            </h2>
        </template>

        <div>
            <div class="max-w-7xl mx-auto py-10 sm:px-6 lg:px-8">
                <FormSection @submitted="submitForm">
                    <template #title>
                        {{ title }}
                    </template>

                    <template #description>
                        Billing Profiles define when a bill is generated, and between which dates the service was active with that provider.
                    </template>

                    <template #form>
                        <!-- Name -->
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="name" value="Name" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                                placeholder="My Electric Company"
                                @keyup.prevent="vehicles=null"
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="bill_day" value="Bill Day" />
                            <TextInput
                                id="bill_day"
                                v-model="form.bill_day"
                                type="number"
                                min="1"
                                max="31"
                                class="mt-1 block w-full"
                                required
                                placeholder="1-31"
                                @keyup.prevent="vehicles=null"
                            />
                            <InputError :message="form.errors.bill_day" class="mt-2" />
                        </div>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="activated_on" value="Activated On" />
                            <TextInput
                                id="activated_on"
                                v-model="form.activated_on"
                                type="date"
                                required
                                class="mt-1 block w-full"
                                placeholder="1-31"
                                @keyup.prevent="vehicles=null"
                            />
                            <InputError :message="form.errors.activated_on" class="mt-2" />
                        </div>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="deactivated_on" value="Deactivated On" />
                            <TextInput
                                id="deactivated_on"
                                v-model="form.deactivated_on"
                                type="date"
                                class="mt-1 block w-full"
                                placeholder="1-31"
                                @keyup.prevent="vehicles=null"
                            />
                            <InputError :message="form.errors.deactivated_on" class="mt-2" />
                        </div>
                        <div class="col-span-6 sm:col-span-4">
                            <InputLabel for="timezone" value="Timezone" />
                            <SelectInput
                                id="timezone"
                                :options="timeZones"
                                v-model="form.timezone"
                                type="date"
                                class="mt-1 block w-full"
                                placeholder="1-31"
                                @keyup.prevent="vehicles=null"
                            />
                            <InputError :message="form.errors.timezone" class="mt-2" />
                        </div>
                    </template>

                    <template #actions>
                        <ActionMessage :on="form.recentlySuccessful" class="mr-3">Saved.</ActionMessage>
                        <PrimaryButton :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                            {{ action }}
                        </PrimaryButton>
                    </template>
                </FormSection>
            </div>
        </div>
    </AppLayout>
</template>
