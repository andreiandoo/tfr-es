const theme = require("./theme.json");
const tailpress = require("@jeffreyvr/tailwindcss-tailpress");

/** @type {import('tailwindcss').Config} */
module.exports = {
  content: [
    "./*.php",
    "./**/*.php",
    "./resources/css/*.css",
    "./resources/js/*.js",
    "./safelist.txt",
    "./node_modules/flowbite/**/*.js",
    "./src/**/*.{html,js}",
  ],
  theme: {
    container: {
      screens: {},
      center: true,
      padding: {
        DEFAULT: "1rem",
        sm: "2rem",
        lg: "0rem",
      },
    },
    extend: {
      colors: {
        ...tailpress.colorMapper(
          tailpress.theme("settings.color.palette", theme)
        ),
        "es-orange": "#fd431c",
        "es-orange-light": "#ff6b3f",
        "es-orange-super-light": "#fd431c1a",
        "es-blue": "#34aada",
        "es-green": "#057a55",
        "es-green-dark": "#045d4b",
        "es-blue-dark": "#0c506c",
      },
      fontSize: tailpress.fontSizeMapper(
        tailpress.theme("settings.typography.fontSizes", theme)
      ),
      fontSize: {
        xxs: "0.625rem",
        "3xs": "0.5rem",
      },
      gridTemplateColumns: {
        5: "repeat(5, minmax(0, 1fr))",
      },
      width: {
        14: "3.5rem",
        30: "7.5rem",
        56: "14rem",
        5: "1.25rem",
      },
      height: {
        14: "3.5rem",
        30: "7.5rem",
        56: "14rem",
        5: "1.25rem",
      },
      maxWidth: {
        "1/2": "50%",
        "1/3": "33.33%",
        "1/4": "25%",
        "1/5": "20%",
        "1/6": "16.666667%",
        "2/3": "66.66%",
      },
      minWidth: {
        "1/3": "33.33%",
      },
      padding: {
        0: "0",
        23: "5.75rem",
        26: "6.5rem",
        28: "7rem",
        30: "7.5rem",
        32: "8rem",
      },
      margin: {
        30: "7.5rem",
        26: "6.5rem",
        18: "4.5rem",
        12: "3rem",
        "-12": "-3rem",
        "-14": "-3.5rem",
        "-16": "-4rem",
        "-18": "-4.5rem",
        "-26": "-6.5rem",
        "-30": "-7.5rem",
        "-32": "-8rem",
        "-40": "-10rem",
      },
      rotate: {
        45: "45deg",
        90: "90deg",
        180: "180deg",
      },
      zIndex: {
        100: "100",
      },
      flex: {
        2: "2 2 0%",
      },
      scale: {
        101: "1.01",
        110: "1.1",
        115: "1.15",
        120: "1.2",
      },
      animation: {
        "fade-in": "fadeIn 1s ease-in-out",
        "fade-in-up": "fadeInUp 1s ease-in-out",
        "fade-in-left": "fadeInLeft 1s ease-in-out",
        "fade-in-right": "fadeInRight 1s ease-in-out",
        "slide-in-left": "slideInLeft 1s ease-in-out",
        "slide-in-right": "slideInRight 1s ease-in-out",
        "slide-in-up": "slideInUp 1s ease-in-out",
        "slide-in-down": "slideInDown 1s ease-in-out",
        "zoom-in": "zoomIn 1s ease-in-out",

        // reveals use a custom easing var you can override on any element
        "reveal-right": "revealright .4s var(--reveal-easing) forwards",
        "reveal-left": "revealleft .4s var(--reveal-easing) forwards",
        "reveal-top": "revealtop .4s var(--reveal-easing) forwards",
        "reveal-bottom": "revealbottom .4s var(--reveal-easing) forwards",
      },
      keyframes: {
        revealright: {
          "0%": {transform: "scale(.9) translateX(20px)", opacity: "0", transformOrigin: "right"},
          "100%": {transform: "scale(1) translateX(0)", opacity: "1"}
        },
        revealleft: {
          "0%": {transform: "scale(.9) translateX(-20px)", opacity: "0", transformOrigin: "left"},
          "100%": {transform: "scale(1) translateX(0)", opacity: "1"}
        },
        revealtop: {
          "0%": {transform: "scale(.9) translateY(-20px)", opacity: "0", transformOrigin: "top"},
          "100%": {transform: "scale(1) translateY(0)", opacity: "1"}
        },
        revealbottom: {
          "0%": {transform: "scale(.9) translateY(20px)", opacity: "0", transformOrigin: "bottom"},
          "100%": {transform: "scale(1) translateY(0)", opacity: "1"}
        },
        fade: {
          "0%": { opacity: "0" },
          "100%": { opacity: "1" }
        },
        fadeIn: {
          "0%": { opacity: "0" },
          "100%": { opacity: "1" }
        },
        fadeInUp: {
          "0%": { opacity: "0", transform: "translateY(20px)" },
          "100%": { opacity: "1", transform: "translateY(0)" }
        },
        fadeInLeft: {
          "0%": { opacity: "0", transform: "translateX(-20px)" },
          "100%": { opacity: "1", transform: "translateX(0)" }
        },
        fadeInRight: {
          "0%": { opacity: "0", transform: "translateX(20px)" },
          "100%": { opacity: "1", transform: "translateX(0)" }
        },
        slideInLeft: {
          "0%": { transform: "translateX(-100%)", opacity: "0" },
          "100%": { transform: "translateX(0)", opacity: "1" }
        },
        slideInRight: {
          "0%": { transform: "translateX(100%)", opacity: "0" },
          "100%": { transform: "translateX(0)", opacity: "1" }
        },
        slideInUp: {
          "0%": { transform: "translateY(100%)", opacity: "0" },
          "100%": { transform: "translateY(0)", opacity: "1" }
        },
        slideInDown: {
          "0%": { transform: "translateY(-100%)", opacity: "0" },
          "100%": { transform: "translateY(0)", opacity: "1" }
        },
        zoomIn: {
          "0%": { transform: "scale(0)", opacity: "0" },
          "100%": { transform: "scale(1)", opacity: "1" }
        },
      },
    },
    screens: {
      mobile: { max: "768px" },
      xs: "480px",
      sm: "600px",
      md: "782px",
      lg: tailpress.theme("settings.layout.contentSize", theme),
      xl: tailpress.theme("settings.layout.wideSize", theme),
      "2xl": "1440px",
    },
  },
  plugins: [
    tailpress.tailwind,
    require("flowbite/plugin"),
    require("tailwind-scrollbar")({ nocompatible: true }),
    // Staggered delays for children: .reveal-children > * get increasing delays
    function({ addUtilities }) {
      const max = 30; // number of children to support; adjust as you wish
      const utils = {
        ".reveal-children": {
          "--reveal-easing": "cubic-bezier(0.31,0.5,0.36,1)",
          "--a-step": ".1s",    // 0.1s between items
          "--a-offset": "0s"    // extra offset you can tweak per container
        },
        ".reveal-children > *": {
          animationFillMode: "both"
        }
      };
      for (let i = 1; i <= max; i++) {
        utils[`.reveal-children > *:nth-child(${i})`] = {
          "--a-scss-delay": `calc(var(--a-step) * ${i} + var(--a-offset))`,
          "-webkit-animation-delay": "var(--a-scss-delay) !important",
          "animation-delay": "var(--a-scss-delay) !important"
        };
      }
      addUtilities(utils);
    },
  ],
};
