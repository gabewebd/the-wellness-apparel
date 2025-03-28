document.addEventListener("DOMContentLoaded", function () {
    const filterBtn = document.getElementById("filter-btn");
    const filterOptions = document.getElementById("filter-options");
    const applyFiltersBtn = document.getElementById("apply-filters");

    // Toggle filter dropdown
    filterBtn.addEventListener("click", function (event) {
        event.stopPropagation(); // Prevent event from bubbling
        filterOptions.classList.toggle("show");
    });

    // Close dropdown when clicking outside
    document.addEventListener("click", function (event) {
        if (!filterOptions.contains(event.target) && !filterBtn.contains(event.target)) {
            filterOptions.classList.remove("show");
        }
    });

    // Preserve selected filters on page load
    function preserveFilters() {
        const urlParams = new URLSearchParams(window.location.search);

        document.getElementById("category-filter").value = urlParams.get("category") || "all";
        document.getElementById("sort-filter").value = urlParams.get("sort") || "newest";
        document.getElementById("stock-filter").value = urlParams.get("stock") || "all";
    }

    preserveFilters(); // Call function to set default values

    // Apply filters
    applyFiltersBtn.addEventListener("click", function () {
        const category = document.getElementById("category-filter").value;
        const sort = document.getElementById("sort-filter").value;
        const stock = document.getElementById("stock-filter").value;

        const url = new URL(window.location.href);
        if (category !== "all") {
            url.searchParams.set("category", category);
        } else {
            url.searchParams.delete("category");
        }

        if (sort !== "newest") {
            url.searchParams.set("sort", sort);
        } else {
            url.searchParams.delete("sort");
        }

        if (stock !== "all") {
            url.searchParams.set("stock", stock);
        } else {
            url.searchParams.delete("stock");
        }

        window.location.href = url.toString();
    });
});
