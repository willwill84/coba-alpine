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

<script>
    function userManagement(initialData) {
        return {
            users: initialData.data || [],
            pagination: {
                current_page: initialData.current_page,
                last_page: initialData.last_page,
                path: initialData.path,
                per_page: initialData.per_page,
                total: initialData.total
            },
            loadPage(page) {
                this.isLoading = true;
                axios.get(`${this.pagination.path}?page=${page}`)
                    .then(response => {
                        this.users = response.data.data;
                        this.pagination.current_page = response.data.current_page;
                        this.pagination.last_page = response.data.last_page;
                    })
                    .catch(error => {
                        alert('Gagal memuat data');
                        console.error(error);
                    })
                    .finally(() => {
                        this.isLoading = false;
                    });
            },
            nextPage() {
                if (this.pagination.current_page < this.pagination.last_page) {
                    this.loadPage(this.pagination.current_page + 1);
                }
            },

            prevPage() {
                if (this.pagination.current_page > 1) {
                    this.loadPage(this.pagination.current_page - 1);
                }
            },
            isEditing: false,
            isLoading: false,
            form: {
                id: null,
                name: '',
                email: ''
            },
            errors: {}, // <-- Untuk simpan error dari backend

            resetForm() {
                this.isEditing = false;
                this.form = { id: null, name: '', email: '' };
                this.errors = {};
            },

            saveUser() {
                if (this.isEditing) {
                    this.updateUser();
                } else {
                    this.addUser();
                }
            },

            async addUser() {

                this.errors = {}; // Reset errors sebelum kirim

                if (!this.form.name.trim() || !this.form.email.trim()) {
                    alert('Semua field harus diisi');
                    return;
                }

                try {
                    this.isLoading = true;
                    const response = await axios.post('/users', this.form);

                    if (response.data.success) {
                        // this.users.unshift(response.data.user);
                        this.loadPage(this.pagination.current_page);
                        this.pagination.total += 1;
                        // this.resetForm();
                        // this.$refs.nameInput.focus();
                        this.close();

                    }
                } catch (error) {
                    if (error.response && error.response.status === 422) {
                        this.errors = error.response.data.errors;
                    } else {
                        alert('Terjadi kesalahan jaringan. Gagal menambahkan user.');
                    }
                } finally {
                    this.isLoading = false;
                }
            },

            editUser(user) {
                this.isEditing = true;
                this.errors = {};
                this.form = { ...user };

                this.$refs.userDialogRef.showModal();
                this.$refs.nameInput.focus();
            },

            async updateUser() {
                this.errors = {}; // Reset errors sebelum kirim
                if (!this.form.id) {
                    alert('Tidak ada user yang sedang diedit.');
                    return;
                }

                const user = this.users.find(u => u.id === this.form.id);
                if (user && this.form.name === user.name && this.form.email === user.email) {
                    alert('Tidak ada perubahan data.');
                    this.close();
                    return;
                }

                try {
                    this.isLoading = true;
                    const response = await axios.put(`/users/${this.form.id}`, this.form);

                    if (response.data.success) {
                        const index = this.users.findIndex(u => u.id === this.form.id);
                        if (index !== -1) {
                            this.users[index] = response.data.user;
                        }

                        this.close();

                        alert(response.data.message);
                    }
                } catch (error) {
                    if (error.response && error.response.status === 422) {
                        this.errors = error.response.data.errors;
                    } else {
                        alert('Terjadi kesalahan jaringan. Gagal memperbarui user');
                    }
                } finally {
                    this.isLoading = false;
                }
            },

            cancelEdit() {
                this.close();
            },

            closeFromEvent(event) {
                if (event.currentTarget === event.target) {
                    this.close();
                }
            },

            close() {
                this.resetForm();
                this.$refs.userDialogRef.close();
            },

            async deleteUser(userId) {
                if (!confirm('Yakin ingin menghapus user ini?')) return;

                try {
                    const response = await axios.delete(`/users/${userId}`);

                    if (response.data.success) {
                        // this.users = this.users.filter(u => u.id !== userId);
                        this.loadPage(this.pagination.current_page);
                        this.pagination.total -= 1;
                        this.isEditing = false;
                    this.resetForm();
                    } else {
                        alert('Gagal menghapus user');
                    }
                } catch (error) {
                    alert('Terjadi kesalahan jaringan. Gagal menghapus user');
                    console.error('Error:', error);
                }
            }
        };
    }
</script>
</html>

