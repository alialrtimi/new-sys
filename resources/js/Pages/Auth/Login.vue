<script setup>
import { Head, Link, useForm } from '@inertiajs/vue3';
import AuthenticationCard from '@/Components/AuthenticationCard.vue';
import AuthenticationCardLogo from '@/Components/AuthenticationCardLogo.vue';
import Checkbox from '@/Components/Checkbox.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';

defineProps({
    canResetPassword: Boolean,
    status: String,
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.transform(data => ({
        ...data,
        remember: form.remember ? 'on' : '',
    })).post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>

    <Head title="Log in" />

    <AuthenticationCard>
        <template #logo>
            <AuthenticationCardLogo />
        </template>

        <div v-if="status" class="mb-4 font-medium text-sm text-green-600">
            {{ status }}
        </div>

        <form @submit.prevent="submit">
            <div>
                <InputLabel id="email-label" for="اسم المستخدم" value="اسم المستخدم" />
                <TextInput id="email" v-model="form.email" type="text" class="mt-1 block w-full" required autofocus
                    autocomplete="username" />
                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="mt-4">
                <InputLabel id="password-label" for="كلمة المرور" value="كلمة المرور" />
                <TextInput id="password" v-model="form.password" type="password" class="mt-1 block w-full" required
                    autocomplete="current-password" />
                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <!-- <div class="block mt-4">
                <label class="flex items-center">
                    <Checkbox v-model:checked="form.remember" name="remember" />
                    <span class="ms-2 text-sm text-gray-600">Remember me</span>
                </label>
            </div> -->

            <div class="flex items-center justify-end mt-4 flex-column">


                <PrimaryButton
                    class="ms-4 w-full text-center d-flex align-items-center justify-content-center bnt-primary bg-primary m-0"
                    :class="{ 'opacity-25': form.processing }" :disabled="form.processing">
                    تسحيل الدخول
                </PrimaryButton>


                <Link v-if="canResetPassword" :href="route('password.request')"
                    class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 mt-4">
                    هل نسيت كلمة المرور
                </Link>
            </div>
        </form>
    </AuthenticationCard>
</template>
<style>
#auth-card {
    background-color: #191c24 !important;
}

#email-label,
#password-label {
    color: #ffffff !important;
}

#email,
#password {
    border: none !important;
    background-color: #404247 !important;
    color: #ffffff !important;
    text-align: center !important;

}

#auth-div {
    background-color: #e0e0e0 !important;
}
</style>