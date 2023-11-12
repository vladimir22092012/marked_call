<script setup>
import { onMounted, ref } from 'vue';
import InputError from "@/Pages/Components/InputError.vue";

defineProps({
    modelValue: {
        type: String,
        required: true,
    },
    placeholder: {
        type: String,
        required: false
    },
    label: {
        type: String,
        required: false,
    },
    message: {
        type: String,
        required: false,
    },
    type: {
        type: String,
        required: false,
        default: 'text',
    }
});

defineEmits(['update:modelValue']);

const input = ref(null);

onMounted(() => {
    if (input.value.hasAttribute('autofocus')) {
        input.value.focus();
    }
});

defineExpose({ focus: () => input.value.focus() });
</script>

<template>
    <div class="form-floating">
        <input
            :class="{'form-control': true, 'is-invalid': message}"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            :placeholder="placeholder"
            ref="input"
            :type="type"
        />
        <label v-if="label">{{label}}</label>
        <InputError :message="message"/>
    </div>

</template>
