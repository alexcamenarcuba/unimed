import { definePreset } from "@primeuix/themes";
import Aura from "@primeuix/themes/aura";

export default definePreset(Aura, {
    semantic: {
        primary: {
            50: "#FFFDE7", // bem claro (backgrounds leves)
            100: "#FFF9C4",
            200: "#FFF59D",
            300: "#FFF176",
            400: "#FFEE58",
            500: "#FFEB3B", // amarelo principal
            600: "#FDD835",
            700: "#FBC02D",
            800: "#F9A825",
            900: "#F57F17",
            950: "#E6C200",
        },
        surface: {
            0: "#ffffff",
            50: "#F5F5F5",
            100: "#E0E0E0",
            200: "#C2C2C2",
            300: "#9E9E9E",
            400: "#757575",
            500: "#616161",
            600: "#424242",
            700: "#2C2C2C",
            800: "#1A1A1A",
            900: "#121212",
            950: "#0A0A0A", // preto profundo (base do layout)
        },
        highlight: {
            background: "#FFEB3B",
            focusBackground: "#FDD835",
            color: "#000000",
            focusColor: "#000000",
        },
    },
});
