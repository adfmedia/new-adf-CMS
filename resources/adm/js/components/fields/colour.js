import "@melloware/coloris/dist/coloris.css";
import Coloris from "@melloware/coloris";

export function bindColourPickers() {
    Coloris.init();
    Coloris({
        el: '[data-colour-picker]'
    });
}