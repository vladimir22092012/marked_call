<script>
import MainLayout from "@/Pages/Layout/MainLayout.vue";
import {useForm} from '@inertiajs/vue3';
import InputError from "@/Pages/Components/InputError.vue";
import TextInput from "@/Pages/Components/TextInput.vue";

export default {
    layout: MainLayout,
    components: {
        MainLayout,
        InputError,
        TextInput,
        useForm
    },
    data() {
        return {
            form: useForm({
                email: '',
                password: '',
            })
        }
    },
    methods: {
        submit() {
            this.form.post(route('login'), {
                onFinish: () => this.form.reset('password'),
            });
        }
    }
}
</script>

<template>
    <MainLayout>
        <main class="form-signin w-30 m-auto">
            <form @submit.prevent="submit">
                <h1 class="h3 mb-3 fw-normal">Войти</h1>
                <TextInput
                    placeholder="Логин"
                    v-model="form.email"
                    label="Email"
                    :message="form.errors.email"
                />
                <TextInput
                    type="password"
                    label="Пароль"
                    placeholder="Пароль"
                    v-model="form.password"
                    :message="form.errors.password"
                />
                <button class="btn btn-primary w-100 py-2" type="submit">
                    Войти
                </button>
            </form>
        </main>
    </MainLayout>
</template>
