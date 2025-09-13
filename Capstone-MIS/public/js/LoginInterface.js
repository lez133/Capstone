document.addEventListener("DOMContentLoaded", () => {
    const root = document.documentElement;
    const container = document.querySelector(".accessibility-container");
    const toggleBtn = document.querySelector(".accessibility-toggle");

    // Toggle menu
    toggleBtn.addEventListener("click", () => {
        container.classList.toggle("open");
    });

    // Mobile close button
    const closeBtn = document.querySelector(".accessibility-close");
    if (closeBtn) {
        closeBtn.addEventListener("click", () => {
            container.classList.remove("open");
        });
    }

    // Handle actions
    document.querySelectorAll(".category-options button, .reset-btn").forEach(button => {
        button.addEventListener("click", () => {
            const action = button.dataset.action;
            const value = button.dataset.size || button.dataset.mode || button.dataset.family;

            if (action === "font-size") {
                root.style.fontSize = value === "small" ? "14px" : value === "large" ? "18px" : "16px";
            }
            if (action === "font-family") {
                root.style.fontFamily = value;
            }
            if (action === "contrast") {
                document.body.classList.remove("high-contrast", "grayscale", "negative");
                if (value === "high") document.body.classList.add("high-contrast");
                if (value === "grayscale") document.body.classList.add("grayscale");
                if (value === "negative") document.body.classList.add("negative");
            }
            if (action === "links") {
                document.body.classList.remove("underline-links", "highlight-links");
                if (value === "underline") document.body.classList.add("underline-links");
                if (value === "highlight") document.body.classList.add("highlight-links");
            }
            if (action === "reset") {
                root.style.fontSize = "16px";
                root.style.fontFamily = "sans-serif";
                document.body.classList.remove("high-contrast", "grayscale", "negative", "underline-links", "highlight-links");
            }
        });
    });
});
