<script>
import { Link, router } from "@inertiajs/vue3";
import route from "ziggy-js";

export default {
    methods: {route},
    components: {
        Link
    },
    data() {
        return {
            user: {},
        }
    },
    mounted() {
        this.user = this.$page?.props?.auth.user;
        router.on('finish', (event) => {
            this.user = this.$page?.props?.auth.user;
        })
    }
}
</script>
<template>
    <nav class="navbar navbar-expand-lg bg-body-tertiary">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">GPT</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarSupportedContent">
                <ul v-if="user" class="navbar-nav me-auto mb-2 mb-lg-0">
                    <span></span>
                    <li class="nav-item">
                        <li><Link class="nav-link active" :href="route('marked_call.form')">Разметка звонков</Link></li>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{user.email}}
                        </a>
                        <ul class="dropdown-menu">
                            <li><Link class="dropdown-item" :href="route('marked_call.form')">Разметка звонков</Link></li>
                            <li v-if="user.name === 'admin'"><Link class="dropdown-item" :href="route('users')">Пользователи</Link></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><Link class="dropdown-item" :href="route('logout')">Выйти</Link></li>
                        </ul>
                    </li>
                </ul>
                <ul v-else class="navbar-nav me-auto mb-2 mb-lg-0">
                    <li>
                        <Link class="nav-link" :href="route('login')">Войти</Link>
                    </li>
                </ul>

            </div>
        </div>
    </nav>
</template>
