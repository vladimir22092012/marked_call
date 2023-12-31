<template>
    <MainLayout>
        <main class="w-30 m-auto">
            <form @submit.prevent="submit">
                <h1 class="h3 mb-3 fw-normal">Разметка</h1>

                <SelectInput
                    v-model="form.owner"
                    label="Владелец"
                    :options="owners"
                    :message="form?.errors?.owner"
                />

                <SelectInput
                    v-model="form.type"
                    label="Выбор звонка"
                    :options="types"
                    :message="form?.errors?.type"
                />

                <TextInput
                    v-if="form.type === 'call'"
                    placeholder="Номер звонка"
                    v-model="form.call_id"
                    label="Номер звонка"
                    type="number"
                    :message="form?.errors?.call_id"
                />
                <div class="form-floating" v-else>
                  <VueDatePicker
                      :class="{'is-invalid':form?.errors?.date_interval}"
                      v-model="form.date_interval"
                      locale="ru"
                      timezone="UTC"
                      cancel-text="Отменить"
                      select-text="Выбрать"
                      :format="format"
                      range
                      no-disabled-range
                  ></VueDatePicker>
                  <div v-show="form?.errors?.date_interval" class="invalid-feedback">{{form?.errors?.date_interval}}</div>
                </div>
              <br>
                <button class="btn btn-primary w-100 py-2" type="submit">
                    Разметить
                </button>
            </form>
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
        owners: {},
    },
    data() {
        return {
            types: {
                call: 'Выбор по номеру звонка',
                interval: 'Все звонки за выбранный период'
            },
            form: useForm({
                owner: Object.keys(this.owners)[0],
                type: 'interval',
                call_id: '',
                date_interval: '',
            }),
        }
    },
    methods: {
        format(date) {
            let format = '';
            if (date.length) {
                if (date[0] != undefined) {
                    var day = date[0].getDate(),
                        month = date[0].getMonth() + 1,
                        year = date[0].getFullYear(),
                        hours = date[0].getHours(),
                        minutes = date[0].getMinutes();
                    if (minutes <= 9) {
                        minutes = `0${minutes}`
                    }

                    format = `${day}.${month}.${year} ${hours}:${minutes}`
                }
                if (date[1] != undefined) {
                    day = date[1].getDate();
                    month = date[1].getMonth() + 1;
                    year = date[1].getFullYear();
                    hours = date[1].getHours();
                    minutes = date[1].getMinutes();
                    if (minutes <= 9) {
                        minutes = `0${minutes}`
                    }
                    format += ` - ${day}.${month}.${year} ${hours}:${minutes}`
                }
            }
            return format;
        },
        submit() {
          this.form.errors = {}
          axios.post(route('start'), this.form)
              .then(response => {
                if (response?.data?.data?.status === 'ok') {
                  this.$swal({
                    position: "top-end",
                    icon: "success",
                    title: "Мы начали разметку и сообщим о её завершении. Можете покинуть страницу, или запустить новую разметку.",
                    showConfirmButton: true,
                  });
                } else {
                    if (response?.data?.data?.custom_message) {
                        this.$swal({
                            position: "top-end",
                            icon: "error",
                            title: response?.data?.data?.custom_message,
                            showConfirmButton: true,
                        });
                    }
                  this.form.errors = response?.data?.data?.errors;
                }
              })
              .catch(ex => {
                this.form.errors = responseToErrors(ex.response.data.errors)
              })
        }
    }
}
</script>
