<script setup>
    import {browserSupportsWebAuthn, startAuthentication, startRegistration} from "@simplewebauthn/browser";
    import ActionSection from "@/Components/ActionSection.vue";
    import { Link, useForm } from '@inertiajs/vue3';
    import { DateTime } from 'luxon';
    import { usePage } from '@inertiajs/vue3';
    import DialogModal from "@/Components/DialogModal.vue";
    import { ref } from "vue";
    import TextInput from "@/Components/TextInput.vue";
    import SecondaryButton from "@/Components/SecondaryButton.vue";
    import PrimaryButton from "@/Components/PrimaryButton.vue";
    import InputLabel from "@/Components/InputLabel.vue";

    const props = defineProps({
        
    });

    const registeringNewPasskey = ref(false)

    const registrationInProgress = ref(false)

    const nameInput = ref(null)

    const form = useForm({
        passkey: '',
        name: '',
    });

    const closeModal = () => {
        registeringNewPasskey.value = false;
        form.reset();
        form.passkey = '';
        form.name = '';
        form.clearErrors();
    }

    const showModal = () => {
        registeringNewPasskey.value = true;
        setTimeout(() => nameInput.value?.focus(), 250);
    }

    const register = () => {
        registrationInProgress.value = true
        form.post(route('passkeys.registration-options'), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                console.log('from server', usePage().props.jetstream.flash.options);
                startRegistration(JSON.parse(JSON.stringify(usePage().props.jetstream.flash.options)))
                    .then((res) =>{
                        form.passkey = res;
                        console.log(res); 
                        form.post(route('passkeys.store'), {
                            preserveScroll: true
                        })
                    })
                    .catch((err) => console.log(err))
                    .finally(() => registrationInProgress.value = false);
            }
        })
    }

    const unregister = (passkey) => {
        form.post(route('passkeys.unregister', {passkey: passkey.id}), {
            preserveScroll: true
        })
    }
</script>
<template>
    <ActionSection>
        <template #title>Passkeys</template>
        <template #description>Passkeys are a secure form of authentication which enables you to authenticate with your device's authentication mechanism. With passkeys you will not need to login using your password or 2FA, instead you could just use your passkey.</template>
        <template #content>
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-xl sm:rounded-lg">
                <div class="relative overflow-x-auto shadow-md sm:rounded-lg">
                    <table class="w-full text-sm text-left rtl:text-right text-gray-500 dark:text-gray-400">
                        <thead class="text-xs text-gray-700 uppercase bg-gray-50 dark:bg-gray-700 dark:text-gray-400">
                            <tr>
                                <th scope="col" class="px-6 py-3">
                                    Name
                                </th>
                                <th scope="col" class="px-6 py-3">
                                    Created At
                                </th>
                                <th scope="col" class="px-6 py-3 text-right">
                                    Action
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="odd:bg-white odd:dark:bg-gray-900 even:bg-gray-50 even:dark:bg-gray-800 border-b dark:border-gray-700">
                                <th scope="row" class="px-6 py-4 font-medium text-gray-900 whitespace-nowrap dark:text-white">

                                </th>
                                <td class="px-6 py-4">

                                </td>
                                <td class="px-6 py-4">

                                </td>
                            </tr>
                            <tr><button @click="showModal" class="inline-flex items-center font-medium text-blue-600 dark:text-blue-500 hover:underline">Create Passkey?</button></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </ActionSection>
    <DialogModal :show="registeringNewPasskey" @close="closeModal">
            <template #title>Create a Passkey</template>
            <template #content>
                <span class="mt-2">
                    <InputLabel for="name" value="Device Name" />
                    <TextInput
                        v-model="form.name"
                        type="text"
                        ref="nameInput"
                        class="mt-1 block w-full"
                    />
                </span>
            </template>
            <template #footer>
                <SecondaryButton @click="closeModal">
                    Cancel
                </SecondaryButton>

                <PrimaryButton
                    class="ml-3"
                    :class="{ 'opacity-25': (form.processing || registrationInProgress || form.name?.length == 0)}"
                    :disabled="(form.processing || registrationInProgress || form.name?.length == 0)"
                    @click="register"
                >
                    Register Passkey
                </PrimaryButton>
            </template>
        </DialogModal>
</template>