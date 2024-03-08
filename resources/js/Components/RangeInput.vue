<script setup>
    import { initFlowbite } from 'flowbite';
    import { ref, onMounted } from 'vue';

    defineProps({
        modelValue: String,
        min: {
            type: String,
            default: "0"
        },
        max: {
            type: String,
            default: "100"
        },
        step: {
            type: String,
            default: "1"
        }
    });

    defineEmits(['update:modelValue']);

    const input = ref(null);

    onMounted(() => initFlowbite());

    defineExpose({ focus: () => input.value.focus() });
</script>
<template>
    <input 
        type="range" 
        :min="min" 
        :max="max" 
        :step="step" 
        ref="input" 
        :value="modelValue"
        @input="$emit('update:modelValue', $event.target.value)" 
        class="w-full h-2 bg-gray-200 rounded-lg appearance-none cursor-pointer dark:bg-gray-700">
</template>