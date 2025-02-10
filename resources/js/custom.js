document.addEventListener("DOMContentLoaded", function () {
    document.addEventListener("keydown", function (event) {
        if (event.key === "Enter") {
            event.preventDefault(); // Prevent form submission

            let inputs = Array.from(document.querySelectorAll("input, select, textarea"));
            let index = inputs.indexOf(document.activeElement);

            if (index > -1 && index < inputs.length - 1) {
                inputs[index + 1].focus();
            }
        }
    });
});
