<template>
    <MainLayout>
        <main class="w-50 m-auto">
            <br>
            <textarea class="form-control" v-model="prompt" cols="30" rows="10"></textarea>
            <br>
            <input type="text" class="form-control" v-model="gpt_email">
            <br>
            <button type="button" class="btn btn-success" @click="save">Сохранить</button>
        </main>
    </MainLayout>
</template>
<script>
import MainLayout from "@/Pages/Layout/MainLayout.vue";
import {useForm} from "@inertiajs/vue3";
import SelectInput from "@/Pages/Components/SelectInput.vue";
import TextInput from "@/Pages/Components/TextInput.vue";
import VueDatePicker from '@vuepic/vue-datepicker';
import '@vuepic/vue-datepicker/dist/main.css';
import InputError from "@/Pages/Components/InputError.vue";
import {responseToErrors} from "@/functions";
import route from "ziggy-js";
export default {
    components: {InputError, SelectInput, MainLayout, TextInput, VueDatePicker},
    layout: MainLayout,
    props: {
        gpt_prompt: {},
        email: {},
    },
    data() {
        return {
            prompt: this.gpt_prompt,
            gpt_email: this.email
        }
    },
    methods: {
        save() {
            axios.post(route('api.gpt.save'), {
                'gpt_prompt': this.prompt,
                'email': this.gpt_email,
                'user_id': this.$page?.props?.auth.user.id,
            });
        }
    }
}
</script>
