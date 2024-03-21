<script setup>
    import AppLayout from '@/Layouts/AppLayout.vue';
    import { Link, useForm } from '@inertiajs/vue3';
    import ActionSection from '@/Components/ActionSection.vue';
    import ActionConfirmation from '@/Components/ActionConfirmation.vue';
    import { ref } from 'vue';

    defineProps({
        teslaAccount: Object,
        providers: Array,
        can: Object,
    });

    const unlinkConfirmation = ref(null)

    const form = useForm({});

    const unlinkAccount = (provider) => {
        form.delete(route('tesla-accounts.unlink', { provider: provider }), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                unlinkConfirmation.value?.hide();
            }
        })
    }

</script>

<template>
    <AppLayout title="Tesla Account API Providers">
        <template #header>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Tesla Account API Provider
            </h2>
        </template>
        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                <ActionSection @submitted="createTeam">
                    <template #title>Connect Tesla Account API</template>
                    <template #description>We pull data from other Tesla API service providers. Please chose the provider you would like to use. Only a single provider can be connected per group.</template>
                    <template #content>
                        <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                            <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                                <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                                    <tr>
                                        <th scope="col" class="px-6 py-3">
                                            Provider
                                        </th>
                                        <th scope="col" class="px-6 py-3 text-right">
                                            Action
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="provider in providers" :key="provider" class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                                        <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">
                                            {{ provider }}
                                        </th>
                                        <td class="px-6 py-4  text-right">
                                            <ActionConfirmation 
                                                ref="unlinkConfirmation"
                                                confirmation-label="Unlink Account"
                                                type="danger"
                                                :key="provider"
                                                :processing="form.processing"
                                                :item-name="provider"
                                                v-if="teslaAccount?.provider == provider && teslaAccount.can.delete"
                                                require-confirmation
                                                @confirmed="unlinkAccount(provider)"
                                            >
                                                <template #message>
                                                    If you continue, polling of new data stops and any vehicles attached to this account will not be updated.
                                                </template>
                                                <span class="cursor-pointer font-medium text-red-600 dark:text-red-500 hover:underline">Unlink</span>
                                            </ActionConfirmation>
                                            <Link v-else-if="!teslaAccount && can.link" class="font-medium text-green-600 dark:text-green-500 hover:underline" :href="route('tesla-accounts.link-form', { provider: provider})">Link</Link>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </template>
                </ActionSection>
            </div>
        </div>
    </AppLayout>
</template>
