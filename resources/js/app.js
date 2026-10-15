import "./bootstrap";

import Alpine from "alpinejs";

window.Alpine = Alpine;

document.addEventListener("alpine:init", () => {
    Alpine.data("sectionTabs", (initialTab = "overview") => ({
        activeTab: initialTab || "overview",
        activate(tab) {
            this.activeTab = tab;
        },
    }));

    Alpine.data(
        "searchableSelect",
        ({ selectedValue = "", selectedLabel = "", options = [] } = {}) => ({
            open: false,
            query: selectedLabel || "",
            selectedValue: selectedValue ? String(selectedValue) : "",
            options: Array.isArray(options) ? options : [],
            init() {
                if (!this.query && this.selectedValue) {
                    const selectedOption = this.options.find(
                        (option) => String(option.value) === this.selectedValue,
                    );
                    this.query = selectedOption ? selectedOption.label : "";
                }
            },
            get filteredOptions() {
                const searchTerm = this.query.trim().toLowerCase();

                if (!searchTerm) {
                    return this.options;
                }

                return this.options.filter((option) =>
                    option.label.toLowerCase().includes(searchTerm),
                );
            },
            select(option) {
                this.selectedValue = String(option.value);
                this.query = option.label;
                this.open = false;
            },
            clear() {
                this.selectedValue = "";
                this.query = "";
            },
        }),
    );
});

// Use `data-x-` prefixed attributes in Blade templates (data-x-data, data-x-on, etc.)
if (typeof Alpine.prefix === "function") {
    Alpine.prefix("data-x-");
}

Alpine.start();
