<template>
    <MainLayout>
        <main class="col-md-12 ms-sm-auto col-lg-12 px-md-4"><div class="chartjs-size-monitor"><div class="chartjs-size-monitor-expand"><div class=""></div></div><div class="chartjs-size-monitor-shrink"><div class=""></div></div></div>
            <br>
            <div class="d-flex align-items-center">
                <h2>Пользователи</h2>
            </div>
            <div class="justify-content-end list-unstyled d-flex">
                <Link class="btn btn-outline-primary" :href="route('users.form')">Добавить пользователя</Link>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-sm">
                    <thead>
                    <tr>
                        <th scope="col">#ID</th>
                        <th scope="col">Имя</th>
                        <th scope="col">E-mail</th>
                        <th scope="col">Дата создания</th>
                        <th scope="col"></th>
                    </tr>
                    </thead>
                    <tbody>
                    <tr v-for="user in dataUsers">
                        <td>{{user.id}}</td>
                        <td>{{user.name}}</td>
                        <td>{{user.email}}</td>
                        <td>{{user.created_at}}</td>
                        <td>
                            <div class="btn-group" role="group" aria-label="Действия над пользователем">
                                <button @click.prevent="deleteUser(user)" type="button" class="btn btn-danger">Удалить</button>
                            </div>
                        </td>
                    </tr>
                    </tbody>
                </table>
            </div>
        </main>
    </MainLayout>
</template>
<script>
import MainLayout from "@/Pages/Layout/MainLayout.vue";
import { Link, router } from "@inertiajs/vue3";

export default {
    components: {MainLayout, Link},
    layout: MainLayout,
    props: {
        users: {},
    },
    data() {
        return {
            dataUsers: this.users
        }
    },
    methods: {
        deleteUser(user) {
            this.$swal({
                title: "Подтвердите действие",
                text: "Вы уверены что хотите удалить пользователя " + user.email + "?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Да удалить!"
            }).then((result) => {
                if (result.isConfirmed) {
                    axios.get(route('users.delete', user.id)).then(response => {
                        if (response?.data?.data?.status === 'ok') {
                            this.$swal({
                                title: "Успешно!",
                                text: "Пользователь успешно удалён.",
                                icon: "success"
                            });
                            this.dataUsers = response.data.data.users;
                        } else {
                            this.$swal({
                                title: "Ошибка!",
                                text: "Пользователь НЕ удалён!",
                                icon: "error"
                            });
                        }
                    }).catch(error => {
                        this.$swal({
                            title: "Ошибка!",
                            text: "Пользователь НЕ удалён!",
                            icon: "error"
                        });
                    })

                }
            });
        },
    }
}
</script>
