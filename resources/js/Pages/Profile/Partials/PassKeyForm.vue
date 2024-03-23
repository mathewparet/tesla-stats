<script setup>
    import {browserSupportsWebAuthn, startAuthentication, startRegistration} from "@simplewebauthn/browser";
    import ActionSection from "@/Components/ActionSection.vue";
    import { Link, useForm } from '@inertiajs/vue3';
    import { DateTime } from 'luxon';
    import { usePage } from '@inertiajs/vue3';

    const props = defineProps({
        
    });

    const form = useForm({
        passkey: '',
    });

    const register = () => {
        form.post(route('passkeys.registration-options'), {
            preserveScroll: true,
            preserveState: true,
            onSuccess: () => {
                console.log('from server', usePage().props.jetstream.flash.options);
                startRegistration(usePage().props.jetstream.flash.options)
                    .then((res) =>{
                        form.passkey = res;
                        console.log(res); 
                        form.post(route('passkeys.store'), {
                            preserveScroll: true
                        })
                    })
                    .catch((err) => console.log(err));
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
                            <tr><button @click="register" class="inline-flex items-center font-medium text-blue-600 dark:text-blue-500 hover:underline">Create Passkey?</button></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </template>
    </ActionSection>
</template>