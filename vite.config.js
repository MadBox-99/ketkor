import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";

export default defineConfig({
    plugins: [
        laravel({
            input: [
                "resources/css/app.css",
                "resources/js/app.js",
                // Referenced via Vite::asset() in components/application-logo.blade.php,
                // so it must be an explicit input to land in the manifest.
                "resources/img/ketkor_logo.webp",
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
});
