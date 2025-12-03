<script setup lang="ts">
import InputError from '@/components/InputError.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import AuthBase from '@/layouts/AuthLayout.vue';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';
import { Form, Head } from '@inertiajs/vue3';

defineProps<{
    status?: string;
    canResetPassword: boolean;
    canRegister: boolean;
}>();
</script>

<template>
    <AuthBase
        title="LINQUAFLOW" description="" class="text-white"
    >
        <Head title="Log in" />

        <div
            v-if="status"
            class="mb-4 text-center text-sm font-medium text-green-400" >
            {{ status }}
        </div>

        <Form
            v-bind="store.form()"
            :reset-on-success="['password']"
            v-slot="{ errors, processing }"
            class="flex flex-col gap-6"
        >
            <div class="grid gap-6">
                <div class="grid gap-2 text-white "> <Label for="email">Adresse e-mail</Label> <Input 
                    class="custom-input"
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="Adresse e-mail" />
                    <InputError :message="errors.email" />
                </div>

                <div class="grid gap-2 text-white"> <div class="flex items-center justify-between">
                        <Label for="password">Mot de passe</Label> <TextLink
                            v-if="canResetPassword"
                            :href="request()"
                            class="text-sm text-indigo-400 hover:text-indigo-300" :tabindex="5"
                        >
                            Mot de passe oublié ?
                        </TextLink>
                    </div >
                    <Input 
                        class="custom-input"
                        id="password"
                        type="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="Mot de passe" />
                    <InputError :message="errors.password" />
                </div>

                <div class="flex items-center justify-between text-white"> <Label for="remember" class="flex items-center space-x-3">
                        <Checkbox id="remember" name="remember" :tabindex="3" class="bg-gray-700 border-gray-600" /> <span>Se souvenir de moi</span> </Label>
                </div>

                <Button
                    
                    type="submit"
                    class="mt-4 w-full custom-button"
                    :tabindex="4"
                    :disabled="processing"
                    data-test="login-button"
                >
                    <Spinner v-if="processing" />
                    Se connecter </Button>
            </div>

            <div
                class="text-center text-sm text-gray-400" v-if="canRegister"
            >
                Pas de compte ?
                <TextLink :href="register()" :tabindex="5" class="text-indigo-400 hover:text-indigo-300">Créer un compte</TextLink> </div>
            
             <div class="text-white text-3xl font-bold text-center mt-12">
                LOGIN
            </div>
        </Form>
    </AuthBase>
</template>
<style scoped>

</style>
