<script setup>
import { onMounted, ref } from 'vue';
import InputError from "@/Pages/Components/InputError.vue";

defineProps({
    options: {
        type: Object,
        required: true,
    },
    modelValue: {
        type: String,
        required: true,
    },
    label: {
        type: String,
        required: false,
    },
    message: {
        type: String,
        required: false,
    },
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
        <select
            :class="{'form-control': true, 'is-invalid': message}"
            :value="modelValue"
            @input="$emit('update:modelValue', $event.target.value)"
            ref="input"
        >
            <option v-for="(option, key) in options" :value="key">{{option}}</option>
        </select>

        <label v-if="label">{{label}}</label>
        <InputError :message="message"/>
    </div>

</template>
