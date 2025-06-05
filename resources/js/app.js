import "./bootstrap";
import Alpine from "alpinejs";

window.Alpine = Alpine;

document.addEventListener("alpine:init", () => {
    Alpine.data("dropdown", () => ({
        open: false,

        toggle() {
            this.open = !this.open;
        },
    }));
});

Alpine.start();
console.log("Hello Vite!");
