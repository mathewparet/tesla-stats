<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import ActionSection from '@/Components/ActionSection.vue';
import ConfirmationModal from '@/Components/ConfirmationModal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import ActionConfirmation from '@/Components/ActionConfirmation.vue';

const props = defineProps({
    team: Object,
});

const makeDefaultConfirmation = ref(null)

const form = useForm({});

const makeDefault = () => {
    form.post(route('teams.make-default', props.team), {
        onSuccess: () => {
            makeDefaultConfirmation.value?.hide()
        }
    });
};
</script>

<template>
    <ActionSection>
        <template #title>
            Make Default Group
        </template>

        <template #description>
            Make this your default Group
        </template>

        <template #content>
            <div class="max-w-xl text-sm text-gray-600 dark:text-gray-400">
                Once marked as default, you will not be able to delete this team. Your current default group will then be deletable.
            </div>

            <div class="mt-5">
                <ActionConfirmation 
                    ref="makeDefaultConfirmation"
                    confirmation-label="Make Group Default"
                    type="primary"
                    :key="team.hash_id"
                    :processing="form.processing"
                    :item-name="team.name"
                    @confirmed="makeDefault"
                >
                    <PrimaryButton>Make Default</PrimaryButton>
                </ActionConfirmation>
            </div>
        </template>
    </ActionSection>
</template>
