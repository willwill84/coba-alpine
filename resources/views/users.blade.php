<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" x-cloak>
<head>
    <meta charset="UTF-8">
    {{-- <meta name="csrf-token" content="{{ csrf_token() }}"> --}}
    <title>Users</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100 p-4">

<div x-data='userManagement(@json($users))' class="max-w-xl mx-auto">
    <h1 class="text-2xl font-bold mb-4">Daftar Pengguna</h1>

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
</body>

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
                        console.log(response.data);
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
            resetForm() {
                this.form = { id: null, name: '', email: '' };
                this.errors = {};
            },
            errors: {}, // <-- Untuk simpan error dari backend

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
                        this.resetForm();
                        this.$refs.nameInput.focus();
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

                        this.resetForm();
                        this.isEditing = false;
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
                this.isEditing = false;
                this.resetForm();
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

