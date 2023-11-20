<template>
    <MainLayout>
        <main class="w-50 m-auto">
            <table>
                <tr v-for="item in history">
                    <td></td>
                    <td></td>
                    <td></td>
                    <td></td>
                </tr>
            </table>
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
export default {
    components: {InputError, SelectInput, MainLayout, TextInput, VueDatePicker},
    layout: MainLayout,
    props: {
        history: {},
    },
    data() {
        return {

        }
    },
    methods: {

    }
}
</script>
