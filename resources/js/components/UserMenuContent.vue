<script setup lang="ts">
import UserInfo from '@/components/UserInfo.vue';
import {
    DropdownMenuGroup,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
} from '@/components/ui/dropdown-menu';
import { logout } from '@/routes';
import { edit } from '@/routes/profile';
import type { User } from '@/types';
import { Link, router } from '@inertiajs/vue3';
import { LogOut, Settings } from 'lucide-vue-next';

interface Props {
    user: User;
}

const handleLogout = () => {
    router.flushAll();
};

defineProps<Props>();
</script>

<template>
    <div
  class="min-w-56 p-4 shadow-xl"
  style="background:linear-gradient(120deg,#232a45 0%,#23234a 100%); border-radius:1rem; border:1px solid #31395c; box-shadow:0 2px 16px 0 rgba(0,0,0,0.10);"
>

   
    <DropdownMenuLabel class="p-0 font-normal">
        <div class="flex items-center gap-2 px-1 py-1.5 text-left text-sm">
            <UserInfo :user="user" :show-email="true" />
        </div>
    </DropdownMenuLabel>
    <DropdownMenuSeparator />
    <DropdownMenuGroup>
        <DropdownMenuItem :as-child="true">
                <Link
                    class="block w-full transition-all duration-200 ease-in-out hover:scale-[1.07] hover:bg-gradient-to-r hover:from-[#31395c] hover:to-[#232a45] hover:text-[#4C70FF] rounded-lg"
                    :href="edit()" prefetch as="button"
                >
                    <Settings class="mr-2 h-4 w-4 transition-all duration-200 ease-in-out hover:rotate-12" />
                    Settings
                </Link>
        </DropdownMenuItem>
    </DropdownMenuGroup>
    <DropdownMenuSeparator />
    <DropdownMenuItem :as-child="true">
        <Link
            class="block w-full transition-all duration-200 ease-in-out hover:scale-[1.07] hover:bg-gradient-to-r hover:from-[#31395c] hover:to-[#232a45] hover:text-[#4C70FF] rounded-lg"
            :href="logout()"
            @click="handleLogout"
            as="button"
            data-test="logout-button"
        >
                <LogOut class="mr-2 h-4 w-4 transition-all duration-200 ease-in-out hover:-rotate-12" />
                Log out
            </Link>
    </DropdownMenuItem>
     </div>
</template>
