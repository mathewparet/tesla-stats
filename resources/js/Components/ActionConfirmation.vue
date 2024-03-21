<script setup>
    import ConfirmationModal from '@/Components/ConfirmationModal.vue';
    import DangerButton from '@/Components/DangerButton.vue';
    import SecondaryButton from '@/Components/SecondaryButton.vue';
    import PrimaryButton from '@/Components/PrimaryButton.vue';
    import { computed, ref } from 'vue';
    import TextInput from '@/Components/TextInput.vue';
    import InputError from '@/Components/InputError.vue';
    import InputLabel from '@/Components/InputLabel.vue';
    import { useForm } from '@inertiajs/vue3';

    let show = ref(Boolean);
    let data = ref(null)
    let codeInput = ref(null)

    const emit = defineEmits(['cancelled', 'confirmed'])

    const confirmation = useForm({
        code: null
    })

    const props = defineProps({
        title: {
            type: String,
        },
        message: {
            type: String,
            default: 'Please confirm whether you wish to proceed with this action.'
        },
        type: {
            type: String,
            default: 'primary',
            validator(value) {
                ["primary", "danger"].includes(value)
            }
        },
        confirmationLabel: {
            type: String,
            default: 'Confirm'
        },
        cancelationLabel: {
            type: String,
            default: 'Cancel'
        },
        processing: {
            type: Object,
            required: false,
        },
        requireConfirmation: {
            type: Boolean,
            default: false
        },
        confirmationCode: {
            type: String,
        },
        when: {
            type: Boolean,
            default: true
        },
        itemName: {
            type: String,
        }
    });

    const confirmationCodeText = computed(() => props.confirmationCode ? props.confirmationCode : (props.itemName ? props.itemName : 'CONFIRM'));

    const confirmed = (requireConfirmation, confirmationCode) => {
        if(requireConfirmation) {
            if(confirmation.code != confirmationCode)
                return;
        }

        emit('confirmed', data.value);
    }

    const cancel = () => {
        confirmation.code = '';
        show.value = false;
        emit("cancelled", data.value);
    }

    const showModal = (when) => {
        if(when) {
            show.value = true;
            setTimeout(() => codeInput.value?.focus(), 250);
        }
        else
            emit('confirmed', data.value)
    }

    const titleText = computed(() => props.title ? props.title : props.confirmationLabel + (props.itemName ? ' - ' + props.itemName : ''));

    defineExpose({ data, show, close: () => show.value = false});

</script>

<template>
        <span @click="showModal(when)">
            <slot />
        </span>
        <ConfirmationModal :show="show==true" @close="cancel">
            <template #title>
                <slot name="title">{{titleText}}</slot>
            </template>

            <template #content>
                <slot name="message">{{message}}</slot>
                <span v-if="requireConfirmation">
                    <div class="grid">
                        <span class="mt-2 flex">
                            To confirm, please enter <span class="ml-2 mr-2 font-mono bg-gray-100 px-1 rounded">{{confirmationCodeText}}</span> below.
                        </span>
                        <span class="mt-2">
                            <TextInput
                                v-model="confirmation.code"
                                type="text"
                                class="mt-1 block w-full"
                                @keyup.enter="confirmed(requireConfirmation, confirmationCodeText)"
                                ref="codeInput"
                            />
                        </span>
                    </div>
                </span>
            </template>

            <template #footer>
                <SecondaryButton @click="cancel">
                    {{cancelationLabel}}
                </SecondaryButton>

                <PrimaryButton
                    v-if="type=='primary'"
                    class="ml-3"
                    :class="{ 'opacity-25': processing  || (requireConfirmation && confirmation.code!=confirmationCodeText)}"
                    :disabled="processing || (requireConfirmation && confirmation.code!=confirmationCodeText)"
                    @click="confirmed(requireConfirmation, confirmationCodeText)"
                >
                    {{confirmationLabel}}
                </PrimaryButton>
                <DangerButton
                    v-if="type=='danger'"
                    class="ml-3"
                    :class="{ 'opacity-25': processing  || (requireConfirmation && confirmation.code!=confirmationCodeText)}"
                    :disabled="processing || (requireConfirmation && confirmation.code!=confirmationCodeText)"
                    @click="confirmed(requireConfirmation, confirmationCodeText)"
                >
                    {{confirmationLabel}}
                </DangerButton>
            </template>
        </ConfirmationModal>
</template>