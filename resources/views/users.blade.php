<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Users') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <div x-data='userManagement(@json($users))'>

                        <div class="flex justify-end mb-4">
                            <button x-on:click="$refs.userDialogRef.showModal()" class="px-3 py-2 bg-blue-500 text-white rounded">Add User</button>
                            <dialog @click="closeFromEvent(event)" x-ref="userDialogRef" class="fixed bg-white rounded-lg shadow-lg max-w-md w-full p-4">
                                <!-- Form -->
                                <div class="mb-6 p-4 border rounded bg-gray-50">
                                    <h2 x-text="isEditing ? 'Edit User' : 'Add User'" class="text-lg font-semibold mb-2"></h2>
                                    <div class="space-y-2">
                                        <input type="text" x-model="form.name" x-ref="nameInput" placeholder="Name" class="w-full mb-2 p-2 border rounded">
                                        <div x-show="errors.name" class="text-red-500 text-sm" x-text="errors.name ? errors.name[0] : ''"></div>

                                        <input type="email" x-model="form.email" placeholder="Email" class="w-full mb-2 p-2 border rounded">
                                        <div x-show="errors.email" class="text-red-500 text-sm" x-text="errors.email ? errors.email[0] : ''"></div>

                                        <button @click="saveUser" x-text="isEditing ? 'Update' : 'Add User'" :disabled="isLoading" x-bind:class="isEditing ? 'bg-blue-500 hover:bg-blue-600' : 'bg-green-500 hover:bg-green-600'" class="px-3 py-1  text-white rounded">Update</button>
                                        <button @click="cancelEdit" class="ml-2 px-3 py-1 bg-gray-400 text-white rounded hover:bg-gray-500">Cancel</button>
                                        <span x-show="isLoading" class="ml-2 text-sm text-gray-600 italic">Loading...</span>
                                    </div>
                                </div>
                            </dialog>
                        </div>

                        <!-- Daftar Users -->
                        <div class="space-y-2">
                            <div x-show="users.length > 0" class="space-y-2">
                                <template x-for="user in users" :key="user.id">
                                    <div class="border p-4 rounded shadow-sm flex justify-between items-center">
                                        <div>
                                            <p><strong x-text="user.name"></strong></p>
                                            <p class="text-sm text-gray-600" x-text="user.email"></p>
                                        </div>
                                        <div class="space-x-2">
                                            <button @click="editUser(user)" class="text-blue-500 hover:underline">Edit</button>
                                            <button @click="deleteUser(user.id)" class="text-red-500 hover:underline">Delete</button>
                                        </div>
                                    </div>
                                </template>

                                <!-- Pagination -->
                                <div class="mt-4 space-y-2">
                                    <div class="text-sm text-gray-600 justify-end">
                                        <span x-text="`Showing ${users.length} of ${pagination.total} entries`"></span>
                                    </div>

                                    <div class="space-x-2 space-y-2">
                                        <button @click="prevPage" :disabled="pagination.current_page === 1"
                                                class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400"
                                                :class="{'opacity-50 cursor-not-allowed': pagination.current_page === 1}">
                                            Previous
                                        </button>

                                        <template x-for="page in pagination.last_page" :key="page">
                                            <button
                                                @click="loadPage(page)"
                                                x-text="page"
                                                :class="{
                                                    'bg-blue-500 text-white': page === pagination.current_page,
                                                    'bg-gray-200': page !== pagination.current_page
                                                }"
                                                class="px-3 py-1 rounded hover:bg-gray-300"
                                            ></button>
                                        </template>

                                        <button @click="nextPage" :disabled="pagination.current_page === pagination.last_page"
                                                class="px-3 py-1 bg-gray-300 rounded hover:bg-gray-400"
                                                :class="{'opacity-50 cursor-not-allowed': pagination.current_page === pagination.last_page}">
                                            Next
                                        </button>
                                    </div>
                                </div>
                            </div>
                            <template x-if="users.length === 0">
                                <p class="text-gray-600">No users found.</p>
                            </template>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
