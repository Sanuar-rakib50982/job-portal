document.addEventListener("DOMContentLoaded", function () {
    const buttons = document.querySelectorAll(".toggle-featured-btn");

    buttons.forEach(function (button) {
        button.addEventListener("click", function () {
            const jobId = this.getAttribute("data-job-id");
            const statusBox = document.getElementById("featured-status-" + jobId);

            const formData = new FormData();
            formData.append("job_id", jobId);

            fetch("../../../api/admin/toggle_featured.php", {
                method: "POST",
                body: formData
            })
            .then(function (response) {
                return response.json();
            })
            .then(function (data) {
                if (data.success) {
                    if (data.is_featured == 1) {
                        statusBox.innerHTML = '<span class="badge yellow">Featured</span>';
                    } else {
                        statusBox.innerHTML = '<span class="badge gray">Not Featured</span>';
                    }

                    alert(data.message);
                } else {
                    alert(data.message);
                }
            })
            .catch(function () {
                alert("Something went wrong while updating featured status.");
            });
        });
    });
});