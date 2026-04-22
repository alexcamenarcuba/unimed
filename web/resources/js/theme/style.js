import { definePreset } from "@primeuix/themes";
import Aura from "@primeuix/themes/aura";

export default definePreset(Aura, {
    semantic: {
        primary: {
            50: "#E6F4EE",  // bem claro
            100: "#CCE9DD",
            200: "#99D3BB",
            300: "#66BD99",
            400: "#33A777",
            500: "#00995D", // verde principal Unimed
            600: "#008A54",
            700: "#007A4A",
            800: "#006B40",
            900: "#005C36",
            950: "#003D24", // mais profundo
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
            950: "#0A0A0A",
        },
        highlight: {
            background: "#00995D",
            focusBackground: "#008A54",
            color: "#ffffff",
            focusColor: "#ffffff",
        },
    },
});
