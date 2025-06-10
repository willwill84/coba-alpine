document.addEventListener("alpine:init", () => {
    Alpine.data("userManagement", (initialData) => ({
        users: initialData.data || [],
        pagination: {
            current_page: initialData.current_page,
            last_page: initialData.last_page,
            path: initialData.path,
            per_page: initialData.per_page,
            total: initialData.total,
        },
        loadPage(page) {
            this.isLoading = true;
            axios
                .get(`${this.pagination.path}?page=${page}`)
                .then((response) => {
                    this.users = response.data.data;
                    this.pagination.current_page = response.data.current_page;
                    this.pagination.last_page = response.data.last_page;
                })
                .catch((error) => {
                    alert("Gagal memuat data");
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
            name: "",
            email: "",
        },
        errors: {}, // <-- Untuk simpan error dari backend

        resetForm() {
            this.isEditing = false;
            this.form = { id: null, name: "", email: "" };
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
                alert("Semua field harus diisi");
                return;
            }

            try {
                this.isLoading = true;
                const response = await axios.post("/users", this.form);

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
                    alert(
                        "Terjadi kesalahan jaringan. Gagal menambahkan user."
                    );
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
                alert("Tidak ada user yang sedang diedit.");
                return;
            }

            const user = this.users.find((u) => u.id === this.form.id);
            if (
                user &&
                this.form.name === user.name &&
                this.form.email === user.email
            ) {
                alert("Tidak ada perubahan data.");
                this.close();
                return;
            }

            try {
                this.isLoading = true;
                const response = await axios.put(
                    `/users/${this.form.id}`,
                    this.form
                );

                if (response.data.success) {
                    const index = this.users.findIndex(
                        (u) => u.id === this.form.id
                    );
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
                    alert("Terjadi kesalahan jaringan. Gagal memperbarui user");
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
            if (!confirm("Yakin ingin menghapus user ini?")) return;

            try {
                const response = await axios.delete(`/users/${userId}`);

                if (response.data.success) {
                    // this.users = this.users.filter(u => u.id !== userId);
                    this.loadPage(this.pagination.current_page);
                    this.pagination.total -= 1;
                    this.isEditing = false;
                    this.resetForm();
                } else {
                    alert("Gagal menghapus user");
                }
            } catch (error) {
                alert("Terjadi kesalahan jaringan. Gagal menghapus user");
                console.error("Error:", error);
            }
        },
    }));
});
